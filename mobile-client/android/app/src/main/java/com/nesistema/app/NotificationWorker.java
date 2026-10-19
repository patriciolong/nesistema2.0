package com.nesistema.app;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;
import androidx.work.Worker;
import androidx.work.WorkerParameters;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class NotificationWorker extends Worker {

    private static final String TAG = "NesistemaNotifWorker";
    public static final String CHANNEL_ID = "nesistema_channel_alerts";
    public static final String CHANNEL_NAME = "Alertas y Trámites NESISTEMA";

    public NotificationWorker(@NonNull Context context, @NonNull WorkerParameters workerParams) {
        super(context, workerParams);
    }

    @NonNull
    @Override
    public Result doWork() {
        Context context = getApplicationContext();
        try {
            String token = getSavedClientToken(context);
            if (token == null || token.trim().isEmpty()) {
                Log.d(TAG, "No active client token found for background check.");
                return Result.success();
            }

            // 1. Consultar notificaciones pendientes
            checkAndNotifyTramites(context, token);

            // 2. Consultar mensajes no leídos del chat
            checkAndNotifyChat(context, token);

        } catch (Throwable e) {
            Log.e(TAG, "Error in background NotificationWorker: " + e.getMessage(), e);
        }

        return Result.success();
    }

    private void checkAndNotifyTramites(Context context, String token) {
        try {
            String endpoint = "https://nesistema.com/api/v1/client/notificaciones?token=" + Uri.encode(token);
            String response = httpGet(endpoint, token);
            if (response == null || response.isEmpty()) return;

            JSONObject json = new JSONObject(response);
            if (json.optBoolean("success", false)) {
                int unreadCount = json.optInt("unread_count", 0);
                SharedPreferences prefs = context.getSharedPreferences("NESISTEMA_BG_PREFS", Context.MODE_PRIVATE);
                int lastCount = prefs.getInt("last_unread_notif_count", 0);

                if (unreadCount > 0 && unreadCount > lastCount) {
                    JSONArray notifs = json.optJSONArray("notificaciones");
                    String title = "🔔 Trámite Notarial Actualizado";
                    String message = "Tienes " + unreadCount + " nueva(s) notificación(es) en NESISTEMA.";

                    if (notifs != null && notifs.length() > 0) {
                        JSONObject first = notifs.getJSONObject(0);
                        if (!first.optBoolean("leido", false)) {
                            title = first.optString("titulo", title);
                            message = first.optString("mensaje", message);
                        }
                    }

                    showNativeNotification(context, 1001, title, message);
                }

                prefs.edit().putInt("last_unread_notif_count", unreadCount).apply();
            }
        } catch (Throwable e) {
            Log.e(TAG, "Error checking tramites notifications: " + e.getMessage());
        }
    }

    private void checkAndNotifyChat(Context context, String token) {
        try {
            String endpoint = "https://nesistema.com/api/v1/client/chat/unread?token=" + Uri.encode(token);
            String response = httpGet(endpoint, token);
            if (response == null || response.isEmpty()) return;

            JSONObject json = new JSONObject(response);
            if (json.optBoolean("success", false)) {
                int unreadCount = json.optInt("unread_count", 0);
                SharedPreferences prefs = context.getSharedPreferences("NESISTEMA_BG_PREFS", Context.MODE_PRIVATE);
                int lastChatCount = prefs.getInt("last_unread_chat_count", 0);

                if (unreadCount > 0 && unreadCount > lastChatCount) {
                    String title = "💬 Nuevo Mensaje de Asesoría";
                    String message = "La Notaría NESISTEMA te ha enviado una respuesta en el chat.";
                    showNativeNotification(context, 1002, title, message);
                }

                prefs.edit().putInt("last_unread_chat_count", unreadCount).apply();
            }
        } catch (Throwable e) {
            Log.e(TAG, "Error checking chat unread: " + e.getMessage());
        }
    }

    private void showNativeNotification(Context context, int notificationId, String title, String message) {
        try {
            createNotificationChannel(context);

            Intent intent = new Intent(context, MainActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
            PendingIntent pendingIntent = PendingIntent.getActivity(
                    context,
                    notificationId,
                    intent,
                    PendingIntent.FLAG_UPDATE_CURRENT | (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M ? PendingIntent.FLAG_IMMUTABLE : 0)
            );

            Uri defaultSound = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
            int appIcon = context.getApplicationInfo().icon != 0 ? context.getApplicationInfo().icon : android.R.drawable.ic_dialog_info;

            NotificationCompat.Builder builder = new NotificationCompat.Builder(context, CHANNEL_ID)
                    .setSmallIcon(appIcon)
                    .setContentTitle(title)
                    .setContentText(message)
                    .setStyle(new NotificationCompat.BigTextStyle().bigText(message))
                    .setPriority(NotificationCompat.PRIORITY_HIGH)
                    .setDefaults(NotificationCompat.DEFAULT_ALL)
                    .setSound(defaultSound)
                    .setAutoCancel(true)
                    .setContentIntent(pendingIntent);

            NotificationManagerCompat manager = NotificationManagerCompat.from(context);
            manager.notify(notificationId, builder.build());
        } catch (Throwable se) {
            Log.w(TAG, "Could not show native notification: " + se.getMessage());
        }
    }

    public static void createNotificationChannel(Context context) {
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                NotificationManager manager = context.getSystemService(NotificationManager.class);
                if (manager != null) {
                    NotificationChannel existing = manager.getNotificationChannel(CHANNEL_ID);
                    if (existing == null) {
                        NotificationChannel channel = new NotificationChannel(
                                CHANNEL_ID,
                                CHANNEL_NAME,
                                NotificationManager.IMPORTANCE_HIGH
                        );
                        channel.setDescription("Notificaciones de entrega de trámites y mensajes de asesoría en tiempo real");
                        channel.enableVibration(true);
                        channel.enableLights(true);
                        manager.createNotificationChannel(channel);
                    }
                }
            }
        } catch (Throwable t) {
            Log.w(TAG, "createNotificationChannel error: " + t.getMessage());
        }
    }

    private String getSavedClientToken(Context context) {
        try {
            String[] prefNames = new String[]{"CapacitorStorage", "NESISTEMA_PREFS", "CapacitorPreferences"};
            for (String name : prefNames) {
                SharedPreferences sp = context.getSharedPreferences(name, Context.MODE_PRIVATE);
                String token = sp.getString("nesistema_client_token", null);
                if (token != null && !token.trim().isEmpty()) {
                    return token.trim();
                }
                for (String key : sp.getAll().keySet()) {
                    if (key.toLowerCase().contains("token")) {
                        Object val = sp.getAll().get(key);
                        if (val instanceof String && !((String) val).trim().isEmpty()) {
                            return ((String) val).trim();
                        }
                    }
                }
            }
        } catch (Throwable e) {
            Log.w(TAG, "Error reading token from prefs: " + e.getMessage());
        }
        return null;
    }

    private String httpGet(String urlStr, String token) {
        HttpURLConnection conn = null;
        BufferedReader reader = null;
        try {
            URL url = new URL(urlStr);
            conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setConnectTimeout(8000);
            conn.setReadTimeout(8000);
            conn.setRequestProperty("Accept", "application/json");
            conn.setRequestProperty("Authorization", "Bearer " + token);
            conn.setRequestProperty("X-Client-Token", token);

            int code = conn.getResponseCode();
            if (code == 200) {
                reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    sb.append(line);
                }
                return sb.toString();
            }
        } catch (Throwable e) {
            Log.w(TAG, "HTTP GET failed for " + urlStr + ": " + e.getMessage());
        } finally {
            if (reader != null) {
                try { reader.close(); } catch (Throwable ignored) {}
            }
            if (conn != null) {
                conn.disconnect();
            }
        }
        return null;
    }
}
