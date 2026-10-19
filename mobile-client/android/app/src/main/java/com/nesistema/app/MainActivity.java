package com.nesistema.app;

import android.Manifest;
import android.app.DownloadManager;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.util.Base64;
import android.util.Log;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.URLUtil;
import android.webkit.WebView;
import android.widget.Toast;

import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.content.FileProvider;

import com.getcapacitor.BridgeActivity;

import java.io.File;
import java.io.FileOutputStream;
import java.io.OutputStream;

public class MainActivity extends BridgeActivity {

    private static final String TAG = "NesistemaMainActivity";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // 1. Crear canal de notificaciones inmediatas
        try {
            AlarmReceiver.createInstantNotificationChannel(this);
            NotificationWorker.createNotificationChannel(this);
        } catch (Throwable t) {
            Log.w(TAG, "Could not create notification channel: " + t.getMessage());
        }

        // 2. Solicitar permiso de notificaciones en Android 13+ (API 33)
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS)
                        != PackageManager.PERMISSION_GRANTED) {
                    ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.POST_NOTIFICATIONS}, 101);
                }
            }
        } catch (Throwable t) {
            Log.w(TAG, "Could not request notification permission: " + t.getMessage());
        }

        // 3. Iniciar el servicio de notificaciones periódicas en segundo plano
        try {
            AlarmReceiver.startAlarm(this);
        } catch (Throwable t) {
            Log.w(TAG, "Could not start alarm service: " + t.getMessage());
        }

        // 4. Configurar WebView con puente Javascript y descargas
        try {
            if (this.bridge != null) {
                WebView webView = this.bridge.getWebView();
                if (webView != null) {
                    // Registrar interfaz JavaScript para sincronizar token con SharedPreferences nativo
                    webView.addJavascriptInterface(new WebAppInterface(this), "AndroidBridge");

                    webView.setDownloadListener((url, userAgent, contentDisposition, mimetype, contentLength) -> {
                        try {
                            DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
                            request.setMimeType(mimetype != null && !mimetype.isEmpty() ? mimetype : "application/pdf");
                            String cookies = CookieManager.getInstance().getCookie(url);
                            if (cookies != null) {
                                request.addRequestHeader("cookie", cookies);
                            }
                            request.addRequestHeader("User-Agent", userAgent);
                            request.setDescription("Descargando documento de trámite...");
                            String fileName = URLUtil.guessFileName(url, contentDisposition, mimetype);
                            if (fileName == null || fileName.isEmpty() || fileName.equals("downloadfile")) {
                                fileName = "Documento_Tramite.pdf";
                            }
                            if (!fileName.toLowerCase().endsWith(".pdf")) {
                                fileName += ".pdf";
                            }
                            request.setTitle(fileName);
                            request.allowScanningByMediaScanner();
                            request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
                            request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName);

                            DownloadManager dm = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);
                            if (dm != null) {
                                dm.enqueue(request);
                                Toast.makeText(getApplicationContext(), "Descargando " + fileName + " en Descargas...", Toast.LENGTH_LONG).show();
                            }
                        } catch (Throwable e) {
                            try {
                                Intent i = new Intent(Intent.ACTION_VIEW);
                                i.setData(Uri.parse(url));
                                startActivity(i);
                            } catch (Throwable ex) {
                                ex.printStackTrace();
                            }
                        }
                    });
                }
            }
        } catch (Throwable e) {
            Log.e(TAG, "Error configuring webView: " + e.getMessage(), e);
        }
    }

    @Override
    public void onPause() {
        super.onPause();
        try {
            AlarmReceiver.startAlarm(this);
        } catch (Throwable ignored) {}
    }

    @Override
    public void onResume() {
        super.onResume();
        try {
            AlarmReceiver.startAlarm(this);
        } catch (Throwable ignored) {}
    }

    @Override
    public void onStop() {
        super.onStop();
        try {
            AlarmReceiver.startAlarm(this);
        } catch (Throwable ignored) {}
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        try {
            AlarmReceiver.startAlarm(this);
        } catch (Throwable ignored) {}
    }

    /**
     * Interfaz expuesta a JavaScript (window.AndroidBridge)
     */
    public static class WebAppInterface {
        private final Context context;

        public WebAppInterface(Context ctx) {
            this.context = ctx;
        }

        @JavascriptInterface
        public void syncClientToken(String token) {
            if (token != null && !token.trim().isEmpty()) {
                try {
                    String clean = token.trim();
                    SharedPreferences prefs = context.getSharedPreferences("NESISTEMA_BG_PREFS", Context.MODE_PRIVATE);
                    prefs.edit().putString("nesistema_client_token", clean).apply();

                    SharedPreferences capPrefs = context.getSharedPreferences("CapacitorStorage", Context.MODE_PRIVATE);
                    capPrefs.edit().putString("nesistema_client_token", clean).apply();

                    // Disparar ciclo inmediato de notificaciones en segundo plano
                    AlarmReceiver.triggerImmediateCheck(context);
                    AlarmReceiver.startAlarm(context);
                } catch (Throwable t) {
                    t.printStackTrace();
                }
            }
        }

        @JavascriptInterface
        public void clearClientToken() {
            try {
                SharedPreferences prefs = context.getSharedPreferences("NESISTEMA_BG_PREFS", Context.MODE_PRIVATE);
                prefs.edit().remove("nesistema_client_token").apply();

                SharedPreferences capPrefs = context.getSharedPreferences("CapacitorStorage", Context.MODE_PRIVATE);
                capPrefs.edit().remove("nesistema_client_token").apply();
            } catch (Throwable t) {
                t.printStackTrace();
            }
        }

        @JavascriptInterface
        public void openWhatsApp(String phone, String message) {
            try {
                String cleanPhone = phone != null ? phone.replaceAll("[^0-9]", "") : "17187173257";
                String text = message != null ? message : "";
                Uri uri = Uri.parse("https://api.whatsapp.com/send?phone=" + cleanPhone + "&text=" + Uri.encode(text));
                Intent intent = new Intent(Intent.ACTION_VIEW, uri);
                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                context.startActivity(intent);
            } catch (Exception e) {
                try {
                    String cleanPhone = phone != null ? phone.replaceAll("[^0-9]", "") : "17187173257";
                    String text = message != null ? message : "";
                    Uri uri = Uri.parse("whatsapp://send?phone=" + cleanPhone + "&text=" + Uri.encode(text));
                    Intent intent = new Intent(Intent.ACTION_VIEW, uri);
                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    context.startActivity(intent);
                } catch (Exception ex) {
                    Toast.makeText(context, "No se pudo abrir WhatsApp. Verifica si tienes la aplicación instalada.", Toast.LENGTH_SHORT).show();
                }
            }
        }

        @JavascriptInterface
        public void openExternalUrl(String url) {
            if (url == null || url.trim().isEmpty()) return;
            try {
                Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                context.startActivity(intent);
            } catch (Exception e) {
                Toast.makeText(context, "No se pudo abrir el enlace.", Toast.LENGTH_SHORT).show();
            }
        }

        @JavascriptInterface
        public void downloadFile(String fileUrl, String fileName) {
            try {
                if (fileUrl == null || fileUrl.trim().isEmpty()) return;
                
                String cleanFileName = (fileName != null && !fileName.trim().isEmpty()) 
                    ? fileName.trim() 
                    : "Documento_Tramite.pdf";
                if (!cleanFileName.toLowerCase().endsWith(".pdf")) {
                    cleanFileName += ".pdf";
                }

                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(fileUrl));
                request.setMimeType("application/pdf");
                request.setDescription("Descargando documento de trámite...");
                request.setTitle(cleanFileName);
                request.allowScanningByMediaScanner();
                request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
                request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, cleanFileName);

                DownloadManager dm = (DownloadManager) context.getSystemService(Context.DOWNLOAD_SERVICE);
                if (dm != null) {
                    dm.enqueue(request);
                    Toast.makeText(context, "📥 Descargando " + cleanFileName + "...", Toast.LENGTH_SHORT).show();
                } else {
                    openExternalUrl(fileUrl);
                }
            } catch (Exception e) {
                Log.e(TAG, "Error in downloadFile: " + e.getMessage(), e);
                openExternalUrl(fileUrl);
            }
        }

        @JavascriptInterface
        public void saveBase64Pdf(String base64Data, String fileName) {
            try {
                if (base64Data == null || base64Data.trim().isEmpty()) {
                    Toast.makeText(context, "El documento PDF está vacío.", Toast.LENGTH_SHORT).show();
                    return;
                }
                
                String cleanData = base64Data;
                if (cleanData.contains(",")) {
                    cleanData = cleanData.substring(cleanData.indexOf(",") + 1);
                }
                
                byte[] pdfBytes = Base64.decode(cleanData, Base64.DEFAULT);
                String cleanFileName = (fileName != null && !fileName.trim().isEmpty()) 
                    ? fileName.trim() 
                    : "Documento_Tramite.pdf";
                if (!cleanFileName.toLowerCase().endsWith(".pdf")) {
                    cleanFileName += ".pdf";
                }

                // 1. Guardar en carpeta Downloads pública mediante MediaStore (Android 10+)
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                    try {
                        ContentValues values = new ContentValues();
                        values.put(MediaStore.MediaColumns.DISPLAY_NAME, cleanFileName);
                        values.put(MediaStore.MediaColumns.MIME_TYPE, "application/pdf");
                        values.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS);
                        
                        Uri insertUri = context.getContentResolver().insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, values);
                        if (insertUri != null) {
                            OutputStream os = context.getContentResolver().openOutputStream(insertUri);
                            if (os != null) {
                                os.write(pdfBytes);
                                os.flush();
                                os.close();
                            }
                        }
                    } catch (Throwable t) {
                        Log.w(TAG, "MediaStore insert warning: " + t.getMessage());
                    }
                } else {
                    // Android 9 y menor: Guardar en Downloads tradicional
                    try {
                        File downloadDir = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS);
                        if (!downloadDir.exists()) {
                            downloadDir.mkdirs();
                        }
                        File publicFile = new File(downloadDir, cleanFileName);
                        FileOutputStream fosPublic = new FileOutputStream(publicFile);
                        fosPublic.write(pdfBytes);
                        fosPublic.flush();
                        fosPublic.close();
                    } catch (Throwable t) {
                        Log.w(TAG, "Legacy Downloads write warning: " + t.getMessage());
                    }
                }

                // 2. Guardar también en almacenamiento interno con FileProvider para visualización inmediata 100% garantizada
                File cacheDir = new File(context.getCacheDir(), "documents");
                if (!cacheDir.exists()) {
                    cacheDir.mkdirs();
                }
                File cacheFile = new File(cacheDir, cleanFileName);
                FileOutputStream fos = new FileOutputStream(cacheFile);
                fos.write(pdfBytes);
                fos.flush();
                fos.close();

                Uri fileProviderUri = FileProvider.getUriForFile(
                    context,
                    context.getPackageName() + ".fileprovider",
                    cacheFile
                );

                Toast.makeText(context, "✅ Guardado en Descargas: " + cleanFileName, Toast.LENGTH_LONG).show();

                // 3. Abrir automáticamente con el lector de PDF del celular
                try {
                    Intent viewIntent = new Intent(Intent.ACTION_VIEW);
                    viewIntent.setDataAndType(fileProviderUri, "application/pdf");
                    viewIntent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
                    viewIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    
                    Intent chooser = Intent.createChooser(viewIntent, "Abrir Documento PDF");
                    chooser.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    context.startActivity(chooser);
                } catch (Throwable t) {
                    Log.w(TAG, "Could not open PDF viewer automatically: " + t.getMessage());
                }

            } catch (Exception e) {
                Log.e(TAG, "Error saving base64 PDF: " + e.getMessage(), e);
                Toast.makeText(context, "Error al guardar el archivo PDF: " + e.getMessage(), Toast.LENGTH_SHORT).show();
            }
        }
    }
}
