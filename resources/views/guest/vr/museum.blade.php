<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>VR</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;600;700&display=swap"
        rel="stylesheet" />

    <script type="importmap">
        {
          "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@v0.153.0/build/three.module.js",
            "three/jsm/": "https://cdn.jsdelivr.net/npm/three@v0.153.0/examples/jsm/"
          }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        canvas {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1 !important;
        }

        body,
        html {
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            touch-action: none !important;
            -webkit-touch-callout: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            user-select: none !important;
        }

        #vr-button-container button {
            position: absolute !important;
            left: 50% !important;
            bottom: 20px !important;
            transform: translateX(-50%) !important;
            width: 220px !important;
            padding: 16px 8px !important;
            border: 2px solid #fff !important;
            border-radius: 9999px !important;
            background: #7c3aed !important;
            color: #fff !important;
            font: 600 15px 'Inter', 'sans-serif' !important;
            text-align: center !important;
            opacity: 0.95 !important;
            outline: none !important;
            z-index: 10000001 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.12) !important;
        }
    </style>
</head>

<body>
    <script>
        var arToken = '{{ $arToken }}';
        var museum = @json($museum);
        var situsNama = @json($situs->nama);
        var vrObjects = @json($vrObjects);
    </script>

    <div class="pointer-events-none fixed inset-x-0 top-0 z-[10000000]">
        <div class="pointer-events-auto flex items-center space-x-4 rounded-b-3xl bg-primary px-6 py-6 text-white">
            <a href="{{ route('guest.situs.detail', $situs->situs_id) }}"
                class="rounded-full p-2 transition-colors hover:bg-white/10">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-lg font-bold">{{ config('app.name') }} VR</h1>
                <p class="text-sm opacity-90">{{ $situs->nama }}</p>
            </div>
        </div>
    </div>

    <div id="app">
        <div id="vr-not-supported"
            class="pointer-events-auto invisible fixed inset-0 z-[999999] flex flex-col items-center justify-center gap-4 bg-white p-8 text-center">
            <x-application-logo class="h-12 w-12" />
            <p class="font-semibold text-red-600">Perangkat/browser Anda tidak mendukung mode VR.</p>
            <p class="text-gray-600">Scan QR ini di headset VR atau browser lain yang mendukung WebXR.</p>
            <div id="qr-code" class="mx-auto flex flex-col items-center gap-3 rounded-lg bg-white p-4 shadow"></div>
        </div>

        <div id="loading-container" class="fixed left-1/2 top-1/2 z-[500] w-4/5 max-w-xl -translate-x-1/2 -translate-y-1/2">
            <div class="h-2 w-full rounded-full bg-gray-300">
                <div id="loading-bar" class="h-full rounded-full bg-purple-500 transition-all duration-200" style="width:0"></div>
            </div>
            <p class="mt-2 text-center text-sm text-gray-500">Memuat model 3D...</p>
        </div>

        <div id="vr-button-container"></div>

        <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
        <script src="{{ asset('assets/js/vr-museum.js') }}?v={{ filemtime(public_path('assets/js/vr-museum.js')) }}" type="module"></script>
    </div>
</body>

</html>
