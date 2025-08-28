<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>League of Legends フレンド募集</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                background: url('/images/KogMaw_37.jpg') no-repeat center center;
                background-size: cover;
                background-attachment: scroll;
                font-family: 'Figtree', sans-serif;
            }
            .lol-container {
                background: rgba(0, 0, 0, 0.6);
                border: 2px solid #c89b3c;
                border-radius: 20px;
                box-shadow: 0 0 40px rgba(200, 155, 60, 0.4);
                backdrop-filter: blur(15px);
                padding: 2rem;
            }
            .lol-button {
                background: #00bfff;
                border: none;
                color: #000000;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                border-radius: 10px;
                padding: 0.75rem 1.5rem;
            }
            .lol-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0, 191, 255, 0.5);
            }
            .lol-input {
                background: rgba(255, 255, 255, 0.15);
                border: 2px solid #c89b3c;
                color: #ffffff;
                transition: all 0.3s ease;
                border-radius: 8px;
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
            .lol-input:focus {
                border-color: #00bfff;
                box-shadow: 0 0 15px rgba(0, 191, 255, 0.4);
                background: rgba(255, 255, 255, 0.2);
            }
            .lol-input::placeholder {
                color: rgba(255, 255, 255, 0.7);
            }
            .lol-title {
                color: #c89b3c;
                text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.9);
                font-size: 2.5rem;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 3px;
            }
            .lol-subtitle {
                color: #ffffff;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.9);
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }
            .lol-label {
                color: #ffffff !important;
                font-weight: 800;
                font-size: 1.2rem;
                margin-bottom: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.9);
            }
            .lol-link {
                color: #00bfff;
                text-decoration: none;
                transition: all 0.3s ease;
            }
            .lol-link:hover {
                color: #ffffff;
                text-shadow: 0 0 8px rgba(0, 191, 255, 0.6);
            }
            .lol-checkbox {
                accent-color: #00bfff;
                transform: scale(1.2);
            }
            .lol-divider {
                border-top: 1px solid rgba(200, 155, 60, 0.5);
                margin: 1.5rem 0;
                position: relative;
            }
            .lol-divider span {
                background: rgba(0, 0, 0, 0.6);
                padding: 0 1rem;
                color: #c89b3c;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
        </style>
    </head>
    <body class="font-sans text-white antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">
            <!-- Background particles effect -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-20 left-20 w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                <div class="absolute top-40 right-40 w-1 h-1 bg-yellow-300 rounded-full animate-ping"></div>
                <div class="absolute bottom-40 left-40 w-3 h-3 bg-yellow-500 rounded-full animate-bounce"></div>
                <div class="absolute bottom-20 right-20 w-1 h-1 bg-yellow-400 rounded-full animate-pulse"></div>
            </div>
            
            <div class="text-center mb-8">
                <h1 class="lol-title">League of Legends</h1>
                <p class="lol-subtitle">フレンド募集</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-8 lol-container">
                {{ $slot }}
            </div>
            
            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm">© 2025 League of Laravel</p>
            </div>
        </div>
    </body>
</html>
