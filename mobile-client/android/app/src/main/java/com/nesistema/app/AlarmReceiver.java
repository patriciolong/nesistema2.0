package com.nesistema.app;

import android.app.AlarmManager;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.media.AudioAttributes;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.PowerManager;
import android.util.Log;

import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class AlarmReceiver extends BroadcastReceiver {

    private static final String TAG = "NesistemaAlarmReceiver";
    public static final String CHANNEL_ID = "nesistema_instant_channel";
    public static final String CHANNEL_NAME = "Notificaciones Inmediatas NESISTEMA";
    private static final int CHECK_INTERVAL_SECONDS = 15; // Revisar cada 15 segundos en segundo plano

    @Override
    public void onReceive(Context context, Intent intent) {
        PowerManager pm = (PowerManager) context.getSystemService(Context.POWER_SERVICE);
        PowerManager.WakeLock wakeLock = null;
        if (pm != null) {
            wakeLock = pm.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "NESISTEMA:NotifWakeLock");
            wakeLock.acquire(8000); // 8 segundos max
        }

        final PowerManager.WakeLock finalWakeLock = wakeLock;

        new Thread(() -> {
            try {
                String token = getSavedClientToken(context);
                if (token != null && !token.trim().isEmpty()) {
                    checkAndNotifyTramites(context, token);
                    checkAndNotifyChat(context, token);
                }
            } catch (Throwable t) {
                Log.e(TAG, "Error in AlarmReceiver check: " + t.getMessage());
            } finally {
                // Programar el siguiente ciclo
                scheduleNextAlarm(context);
                if (finalWakeLock != null && finalWakeLock.isHeld()) {
                    try { finalWakeLock.release(); } catch (Throwable ignored) {}
                }
            }
        }).start();
    }

    public static void startAlarm(Context context) {
        try {
            scheduleNextAlarm(context);
        } catch (Throwable t) {
            Log.w(TAG, "Could not start alarm: " + t.getMessage());
        }
    }

    public static void triggerImmediateCheck(Context context) {
        try {
            Intent intent = new Intent(context, AlarmReceiver.class);
            context.sendBroadcast(intent);
        } catch (Throwable t) {
            Log.w(TAG, "Could not trigger immediate check: " + t.getMessage());
        }
    }

    private static void scheduleNextAlarm(Context context) {
        try {
            AlarmManager am = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
            if (am == null) return;

            Intent intent = new Intent(context, AlarmReceiver.class);
            PendingIntent pi = PendingIntent.getBroadcast(
                    context,
                    998,
                    intent,
                    PendingIntent.FLAG_UPDATE_CURRENT | (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M ? PendingIntent.FLAG_IMMUTABLE : 0)
            );

            long triggerAtMillis = System.currentTimeMillis() + (CHECK_INTERVAL_SECONDS * 1000L);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                am.setExactAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerAtMillis, pi);
            } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.KITKAT) {
                am.setExact(AlarmManager.RTC_WAKEUP, triggerAtMillis, pi);
            } else {
                am.set(AlarmManager.RTC_WAKEUP, triggerAtMillis, pi);
            }
        } catch (Throwable t) {
            Log.w(TAG, "Error scheduling alarm: " + t.getMessage());
        }
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
                    String message = "Tienes una nueva actualización en tus trámites de NESISTEMA.";

                    if (notifs != null && notifs.length() > 0) {
                        JSONObject first = notifs.getJSONObject(0);
                        if (!first.optBoolean("leido", false)) {
                            title = first.optString("titulo", title);
                            message = first.optString("mensaje", message);
                        }
                    }

                    showInstantNotification(context, 2001, title, message);
                }

                prefs.edit().putInt("last_unread_notif_count", unreadCount).apply();
            }
        } catch (Throwable t) {
            Log.e(TAG, "Error in checkAndNotifyTramites: " + t.getMessage());
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
                    String message = "La Notaría NESISTEMA te ha enviado una respuesta en el chat en vivo.";
                    showInstantNotification(context, 2002, title, message);
                }

                prefs.edit().putInt("last_unread_chat_count", unreadCount).apply();
            }
        } catch (Throwable t) {
            Log.e(TAG, "Error in checkAndNotifyChat: " + t.getMessage());
        }
    }

    private void showInstantNotification(Context context, int notificationId, String title, String message) {
        try {
            createInstantNotificationChannel(context);

            Intent intent = new Intent(context, MainActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
            PendingIntent pendingIntent = PendingIntent.getActivity(
                    context,
                    notificationId,
                    intent,
                    PendingIntent.FLAG_UPDATE_CURRENT | (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M ? PendingIntent.FLAG_IMMUTABLE : 0)
            );

            Uri soundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
            int appIcon = context.getApplicationInfo().icon != 0 ? context.getApplicationInfo().icon : android.R.drawable.ic_dialog_info;

            NotificationCompat.Builder builder = new NotificationCompat.Builder(context, CHANNEL_ID)
                    .setSmallIcon(appIcon)
                    .setContentTitle(title)
                    .setContentText(message)
                    .setStyle(new NotificationCompat.BigTextStyle().bigText(message))
                    .setPriority(NotificationCompat.PRIORITY_MAX)
                    .setCategory(NotificationCompat.CATEGORY_MESSAGE)
                    .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                    .setDefaults(NotificationCompat.DEFAULT_ALL)
                    .setSound(soundUri)
                    .setVibrate(new long[]{0, 250, 100, 250})
                    .setAutoCancel(true)
                    .setContentIntent(pendingIntent);

            NotificationManagerCompat manager = NotificationManagerCompat.from(context);
            manager.notify(notificationId, builder.build());
        } catch (Throwable t) {
            Log.w(TAG, "Error showing instant notification: " + t.getMessage());
        }
    }

    public static void createInstantNotificationChannel(Context context) {
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
                        channel.setDescription("Alertas inmediatas de trámites listos y mensajes de asesoría");
                        channel.enableVibration(true);
                        channel.setVibrationPattern(new long[]{0, 250, 100, 250});
                        channel.enableLights(true);

                        Uri soundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
                        AudioAttributes audioAttributes = new AudioAttributes.Builder()
                                .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                                .setUsage(AudioAttributes.USAGE_NOTIFICATION_COMMUNICATION_INSTANT)
                                .build();
                        channel.setSound(soundUri, audioAttributes);

                        manager.createNotificationChannel(channel);
                    }
                }
            }
        } catch (Throwable t) {
            Log.w(TAG, "Error creating channel: " + t.getMessage());
        }
    }

    private String getSavedClientToken(Context context) {
        try {
            String[] prefNames = new String[]{"NESISTEMA_BG_PREFS", "CapacitorStorage", "CapacitorPreferences"};
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
        } catch (Throwable t) {
            Log.w(TAG, "Error reading token: " + t.getMessage());
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
            conn.setConnectTimeout(6000);
            conn.setReadTimeout(6000);
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
        } catch (Throwable t) {
            Log.w(TAG, "HTTP GET error for " + urlStr + ": " + t.getMessage());
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
