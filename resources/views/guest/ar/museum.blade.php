<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>AR</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Joti+One&display=swap" rel="stylesheet">

    <script type="importmap">
        {
          "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@v0.153.0/build/three.module.js",
            "three/jsm/": "https://cdn.jsdelivr.net/npm/three@v0.153.0/examples/jsm/"
          }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"
        integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>

    <script src="https://launchar.app/sdk/v1?key=RDgojZ30201nvtK5SuFFTIUNDDLpxiVj&redirect=true"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;600;700&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('css')

    <style>
        /* Ensure canvas is full screen */
        canvas {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1 !important;
        }

        /* Make sure body and html don't have padding/margin that affects canvas */
        body,
        html {
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            /* Prevent touch actions that could trigger refresh */
            touch-action: none !important;
            -webkit-touch-callout: none !important;
            -webkit-user-select: none !important;
            -khtml-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }

        /* Specific for iOS Safari/WebView */
        @supports (-webkit-touch-callout: none) {
            * {
                -webkit-touch-callout: none !important;
                -webkit-tap-highlight-color: transparent !important;
            }
        }

        .toaster {
            background-color: black;
            color: white;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: 0.25rem;
            position: relative;
            animation: slide-in 0.2s forwards, slide-out 0.2s 2.5s forwards;
        }

        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slide-out {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
    </style>
    <style>
        #ar-button-container #ARButton {
            position: absolute !important;
            left: 50% !important;
            bottom: 20px !important;
            transform: translateX(-50%) !important;
            width: 200px !important;
            padding: 16px 8px !important;
            border: 2px solid #fff !important;
            border-radius: 9999px !important;
            background: #2563eb !important;
            /* bg-primary (Tailwind blue-600) */
            color: #fff !important;
            font: 600 15px 'Inter', 'sans-serif' !important;
            text-align: center !important;
            opacity: 0.95 !important;
            outline: none !important;
            z-index: 10000001 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.12) !important;
            transition: opacity 0.2s, background 0.2s, border 0.2s !important;
        }

        #ar-button-container #ARButton:hover {
            opacity: 1 !important;
            background: #1d4ed8 !important;
            /* biru lebih gelap saat hover */
            border-width: 3px !important;
        }


        @keyframes circle {
            from {
                transform: translateX(-50%) rotate(0deg) translateX(50px) rotate(0deg);
            }

            to {
                transform: translateX(-50%) rotate(360deg) translateX(50px) rotate(-360deg);
            }
        }

        @keyframes elongate {
            from {
                transform: translateX(100px);
            }

            to {
                transform: translateX(-100px);
            }
        }

        #tracking-prompt {
            position: absolute;
            left: 50%;
            bottom: 175px;
            animation: elongate 2s infinite ease-in-out alternate;
            display: none;
        }

        #tracking-prompt>img {
            animation: circle 4s linear infinite;
        }
    </style>
</head>

<body>
    <script>
        var popupVisible = false;
        var arToken = '{{ $arToken }}' + 'wow';
    </script>
    <div class="pointer-events-auto z-[10000000] rounded-b-3xl bg-primary px-6 py-6 text-white">
        <div class="flex items-center space-x-4">
            <a href="{{ route('guest.home') }}" class="rounded-full p-2 transition-colors hover:bg-white/10">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-lg font-bold">{{ config('app.name') }} AR</h1>
                <p class="text-sm opacity-90">Eksplorasi Objek Peninggalan</p>
            </div>
            <div class="flex-1">
        </div>
    </div>
    <div id="overlay"
        class="pointer-events-none absolute inset-0 z-[999999] flex flex-col items-center justify-center">
        <div id="instructions"
            class="invisible absolute top-0 z-[100000] w-full rounded-t-xl bg-primary p-4 text-center text-white">Tekan
            lingkaran untuk memunculkan ruangan
        </div>
        <div id="tracking-prompt" class="invisible absolute bottom-[44] left-1/2 animate-pulse">
            <img src="{{ asset('images/icons/hand.png') }}" class="w-24" style="animation: circle 4s linear infinite;"
                alt="" />
        </div>
        <div id="toaster-container" class="fixed bottom-0 right-0 z-[99999] m-4"></div>
        <div id="bottom-sheet"
            class="pointer-events-auto fixed bottom-0 left-0 right-0 z-[10000000] h-16 translate-y-full transform rounded-t-2xl bg-white shadow-lg transition-transform duration-300">
            <div class="border-b p-4">
                <h3 class="pointer-events-auto text-lg font-bold text-primary">Objek Peninggalan</h3>
            </div>
            <div id="bottom-sheet-content"
                class="pointer-events-auto hidden h-[80vh] overflow-y-scroll p-4 text-center text-gray-800">
                <ul id="lukisan-list" class="flex flex-col gap-6 py-2">
                    @foreach ($museum->virtualMuseumObjects as $i => $object)
                        <li>
                            <img class="mx-auto w-60 max-w-full rounded-xl shadow"
                                src="{{ asset('/storage/' . $object->gambar_real) }}" alt="">
                            <p class="mt-2">{{ $object->deskripsi }}</p>
                        </li>
                    @endforeach
                    <li>
                        <div class="pt-8"></div>
                    </li>
                </ul>
                <button id="close-bottom-sheet" class="absolute right-2 top-2 text-2xl text-gray-600">&times;</button>
            </div>
        </div>
    </div>
    <div>
        <button id="expand-bottom-sheet"
            class="fixed bottom-6 left-1/2 z-[90000] -translate-x-1/2 rounded-full border-2 border-white bg-primary px-6 py-3 font-bold text-white shadow-lg"
            style="display: none;">
            Lihat Objek Peninggalan
        </button>
        <script>
            document.getElementById('expand-bottom-sheet').addEventListener('click', function() {
                document.getElementById('bottom-sheet').style.transform = 'translateY(0)';
                document.getElementById('bottom-sheet').style.height = '80vh';
                document.getElementById('bottom-sheet-content').style.display = 'block';
            });
            document.getElementById('close-bottom-sheet').addEventListener('click', function() {
                document.getElementById('bottom-sheet').style.transform = 'translateY(100%)';
                document.getElementById('bottom-sheet').style.height = '16px';
                document.getElementById('bottom-sheet-content').style.display = 'none';
            });
        </script>
    </div>
    <div id="app">
        <div
            class="pointer-events-none absolute inset-0 z-[15] mx-auto flex min-h-[80vh] min-w-[320px] max-w-5xl flex-col items-center gap-4 p-8 pt-36 text-center">
            <div class="mb-2 flex justify-center">
                <x-application-logo class="h-12 w-12" />
            </div>
            <h2 class="mb-2 text-center text-2xl font-bold text-primary">{{ config('app.name') }}</h2>
            <div id="ar-not-supported" class="invisible mt-8 w-full rounded-xl bg-white p-6 shadow">
                <p class="font-semibold text-red-600">Teknologi WebXR tidak didukung di perangkat Anda.</p>
                <p class="text-gray-600">Untuk dokumentasi selengkapnya, kunjungi <a
                        href="https://launch.variant3d.com/docs" class="underline">https://launch.variant3d.com</a>.</p>
                <div class="mt-4 flex flex-col items-center justify-center">
                    <div id="qr-code"
                        class="mx-auto flex flex-col items-center gap-3 rounded-lg bg-white p-4 text-black shadow">
                        <span class="sr-only">Loading...</span>
                        <p>Error 426: Perangkat/Browser tidak didukung</p>
                    </div>
                </div>
            </div>

            <div id="loading-container" class="mx-auto my-8 h-2 w-4/5 max-w-xl rounded-full bg-gray-300">
                <div id="loading-bar" class="h-full rounded-full bg-orange-400 transition-all duration-200"
                    style="width:0"></div>
            </div>
        </div>
        <div id="ar-button-container"></div>

        <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
        <script>
            var museum = @json($museum);
            console.log(museum);
        </script>

        <script src="{{ asset('assets/js/ar-museum.js') }}" type="module"></script>
    </div>
</body>

</html>
