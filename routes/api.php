<?php

use App\Http\Controllers\Api\ClientAuthController;
use App\Http\Controllers\Api\ClientTramiteController;
use App\Http\Controllers\Api\ClientNotificationController;
use App\Http\Controllers\Api\ClientChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - NESISTEMA 2.0 Client Portal & Mobile App
|--------------------------------------------------------------------------
*/

Route::prefix('v1/client')->group(function () {
    
    // Autenticación pública del cliente
    Route::post('/login', [ClientAuthController::class, 'login'])->name('api.client.login');
    Route::get('/servicios', [ClientTramiteController::class, 'servicios'])->name('api.client.servicios');

    // Rutas protegidas por Token de Cliente
    Route::middleware('auth.client')->group(function () {
        
        // Perfil y Dispositivo
        Route::get('/profile', [ClientAuthController::class, 'profile'])->name('api.client.profile');
        Route::post('/register-device', [ClientAuthController::class, 'registerDevice'])->name('api.client.register_device');
        Route::post('/update-password', [ClientAuthController::class, 'updatePassword'])->name('api.client.update_password');
        Route::post('/logout', [ClientAuthController::class, 'logout'])->name('api.client.logout');

        // Trámites
        Route::get('/tramites', [ClientTramiteController::class, 'index'])->name('api.client.tramites.index');
        Route::get('/tramites/{categoria}/{id}', [ClientTramiteController::class, 'show'])->name('api.client.tramites.show');
        Route::get('/tramites/{categoria}/{id}/download', [ClientTramiteController::class, 'download'])->name('api.client.tramites.download');
        Route::post('/tramites/{categoria}/{id}/resena', [ClientTramiteController::class, 'guardarResena'])->name('api.client.tramites.resena');

        // Notificaciones
        Route::get('/notificaciones', [ClientNotificationController::class, 'index'])->name('api.client.notificaciones.index');
        Route::post('/notificaciones/{id}/read', [ClientNotificationController::class, 'markAsRead'])->name('api.client.notificaciones.read');

        // Chat en Vivo con la Notaría
        Route::get('/chat', [ClientChatController::class, 'getMensajes'])->name('api.client.chat.mensajes');
        Route::post('/chat/enviar', [ClientChatController::class, 'enviarMensaje'])->name('api.client.chat.enviar');
        Route::get('/chat/unread', [ClientChatController::class, 'getUnreadCount'])->name('api.client.chat.unread');
    });
});
