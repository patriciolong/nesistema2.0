<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' | ' : '' }}{{ config('app.name', 'NESISTEMA 2.0') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            /* Barra de scroll ultradelgada y personalizada para el menú lateral */
            .custom-sidebar-scroll {
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar {
                width: 4px;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.12);
                border-radius: 9999px;
            }
            .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.3);
            }
        </style>
    </head>
    <body class="font-sans antialiased" style="margin: 0; padding: 0; background-color: #f1f5f9; color: #0f172a;">
        <div style="display: flex; min-height: 100vh; width: 100%;">
            
            <!-- Left Sidebar -->
            @include('layouts.navigation')

            <!-- Right Main Area -->
            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; background-color: #f8fafc;">
                
                @isset($header)
                    <header style="background-color: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 30;">
                        <div style="width: 100%; max-width: 1700px; margin: 0 auto;">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Main Page Content -->
                <main style="flex: 1; padding: 32px; width: 100%; max-width: 1700px; margin: 0 auto; box-sizing: border-box;">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
