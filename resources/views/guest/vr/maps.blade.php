<x-guest-layout>
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <style>
            @media (prefers-reduced-motion: reduce) {

                *,
                *::before,
                *::after {
                    animation-duration: 0.01ms !important;
                    transition-duration: 0.01ms !important;
                }
            }

            body {
                overflow: hidden;
            }

            #map-container {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
            }

            #search-bar {
                position: fixed;
                top: env(safe-area-inset-top, 0);
                left: 0;
                right: 0;
                padding: 10px;
                padding-top: calc(env(safe-area-inset-top, 0) + 10px);
                z-index: 1000;
            }

            #bottom-sheet {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                transform: translateY(100%);
                transition: transform 300ms cubic-bezier(0.16, 1, 0.3, 1), visibility 300ms;
                visibility: hidden;
                padding-bottom: env(safe-area-inset-bottom, 0);
            }

            #bottom-sheet.visible {
                transform: translateY(0);
                visibility: visible;
            }

            .custom-marker-icon-wrapper,
            .selected-marker-icon-wrapper {
                background: transparent;
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .selected-marker-icon-wrapper {
                z-index: 9999 !important;
            }

            .custom-marker-pin {
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                width: 100%;
                height: 100%;
            }

            .pin-inner {
                position: relative;
                width: 36px;
                height: 36px;
                background: #ffffff;
                border-radius: 50%;
                border: 2px solid #7c3aed;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transition: transform 200ms cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 200ms, box-shadow 200ms;
            }

            .custom-marker-pin:hover .pin-inner {
                transform: scale(1.15);
            }

            .pin-img {
                width: 20px;
                height: 20px;
                object-fit: contain;
            }

            .custom-marker-pin.locked .pin-inner {
                border-color: #9ca3af;
                background: #f3f4f6;
            }

            .lock-badge {
                position: absolute;
                bottom: -3px;
                right: -3px;
                background-color: #ea580c;
                border-radius: 50%;
                padding: 2.5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #ffffff;
            }

            .pin-inner-selected {
                position: relative;
                width: 44px;
                height: 44px;
                background: #ffffff;
                border-radius: 50%;
                border: 3px solid #7c3aed;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
            }

            .custom-marker-pin.selected.locked .pin-inner-selected {
                border-color: #ea580c;
            }

            .spinning-circle-wrapper {
                position: absolute;
                width: 54px;
                height: 54px;
                border: 2px dashed #7c3aed;
                border-radius: 50%;
                animation: spin 12s linear infinite;
                pointer-events: none;
            }

            .custom-marker-pin.selected.locked .spinning-circle-wrapper {
                border-color: #ea580c;
            }

            .marker-title {
                position: absolute;
                bottom: -35px;
                left: 50%;
                transform: translateX(-50%);
                white-space: nowrap;
                color: #1f2937;
                font-weight: 700;
                font-size: 13px;
                background-color: rgba(255, 255, 255, 0.95);
                padding: 4px 10px;
                border-radius: 9999px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                border: 1px solid #e5e7eb;
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            .leaflet-bottom.leaflet-left,
            .leaflet-bottom.leaflet-right {
                bottom: 30px;
            }

            .leaflet-bar {
                border: none !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
                border-radius: 12px !important;
                overflow: hidden;
            }

            .action-btn {
                min-height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 12px 16px;
                border-radius: 12px;
                font-weight: 600;
                transition: all 200ms ease-out;
            }

            .action-btn-primary {
                background-color: #7c3aed;
                color: white;
            }

            .action-btn-primary:hover {
                background-color: #6d28d9;
            }

            .action-btn-primary:active {
                transform: scale(0.98);
            }

            .action-btn-secondary {
                background-color: #e5e7eb;
                color: #374151;
            }

            .action-btn-secondary:hover {
                background-color: #d1d5db;
            }

            #locked-message {
                display: flex !important;
                align-items: center;
                gap: 0.5rem;
            }
        </style>
    @endpush

    <!-- Map Container -->
    <div id="map-container">
        <div id="map" style="height: 100%; width: 100%;"></div>
    </div>

    <!-- Top Bar -->
    <div id="search-bar">
        <div class="mx-auto flex max-w-md items-center gap-2">
            <button
                class="back-button flex h-[48px] w-[48px] flex-shrink-0 items-center justify-center rounded-full bg-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="h-6 w-6 text-gray-700">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </button>
            <div class="flex h-[48px] flex-grow items-center rounded-full bg-white px-5 shadow-lg">
                <i class="fas fa-vr-cardboard mr-2 text-purple-600"></i>
                <span class="font-semibold text-gray-800">{{ __('maps.vr_map_title') }}</span>
            </div>
        </div>
    </div>

    <!-- Bottom Sheet -->
    <div id="bottom-sheet" role="dialog" aria-labelledby="overlay-title" aria-describedby="overlay-description">
        <div class="mx-auto max-h-[85vh] w-full overflow-hidden rounded-t-2xl bg-white shadow-xl lg:max-w-xl">
            <div class="flex justify-center pb-2 pt-3">
                <div class="h-1.5 w-12 rounded-full bg-gray-300"></div>
            </div>
            <div class="px-5 pb-5">
                <h2 id="overlay-title" class="mb-1 text-xl font-bold text-gray-900"></h2>
                <p id="overlay-address" class="text-sm text-gray-600"></p>
                <p id="overlay-description" class="mt-3 line-clamp-2 text-sm text-gray-500"></p>
                <a id="overlay-link" href="#" class="action-btn action-btn-primary mt-4 w-full" role="button"
                    style="display: none;">
                    <i class="fas fa-vr-cardboard mr-2"></i>
                    {{ __('maps.enter_vr') }}
                </a>
                <p id="locked-message"
                    class="locked-message mt-4 items-center justify-center gap-2 text-center text-sm text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {{ __('maps.site_locked_message') }}
                </p>
                <button id="close-overlay" class="action-btn action-btn-secondary mt-3 w-full" role="button">
                    {{ __('maps.close') }}
                </button>
            </div>
        </div>
    </div>

    @if ($vrSitus->isEmpty())
        <div class="fixed inset-0 z-[900] flex items-center justify-center bg-white/90 px-6 text-center">
            <div>
                <i class="fas fa-vr-cardboard mb-4 text-5xl text-gray-300"></i>
                <p class="text-gray-600">{{ __('maps.no_vr_situs') }}</p>
            </div>
        </div>
    @endif

    <script>
        const isMobileDevice = window.innerWidth < 768;

        var map = L.map('map', {
            zoomControl: false,
            tap: true
        }).setView([-8.409518, 115.188919], isMobileDevice ? 8 : 10);

        var activeMarker = null;

        L.control.zoom({
            position: 'bottomright',
            zoomInTitle: '{{ __('maps.zoom_in') }}',
            zoomOutTitle: '{{ __('maps.zoom_out') }}'
        }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        const bottomSheet = document.getElementById('bottom-sheet');
        const closeOverlayBtn = document.getElementById('close-overlay');
        const overlayTitle = document.getElementById('overlay-title');
        const overlayAddress = document.getElementById('overlay-address');
        const overlayDescription = document.getElementById('overlay-description');
        const overlayLink = document.getElementById('overlay-link');
        const lockedMessage = document.getElementById('locked-message');

        var situsIcon = L.divIcon({
            className: 'custom-marker-icon-wrapper',
            html: `
                <div class="custom-marker-pin unlocked">
                    <div class="pin-inner shadow-md">
                        <i class="fas fa-vr-cardboard text-purple-600"></i>
                    </div>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        var lockedSitusIcon = L.divIcon({
            className: 'custom-marker-icon-wrapper',
            html: `
                <div class="custom-marker-pin locked">
                    <div class="pin-inner shadow-md">
                        <i class="fas fa-vr-cardboard text-gray-400"></i>
                        <div class="lock-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-2.5 h-2.5 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        @foreach ($vrSitus as $s)
            @php
                $isUnlocked = in_array($s->situs_id, $unlockedSitusIds);
                $firstMuseum = $s->virtualMuseum->first();
            @endphp
            @if ($firstMuseum)
                (function() {
                    var marker = L.marker([{{ $s->lat }}, {{ $s->lng }}], {
                        icon: {{ $isUnlocked ? 'situsIcon' : 'lockedSitusIcon' }}
                    }).addTo(map);

                    marker.situsInfo = {
                        nama: @json($s->nama),
                        alamat: @json($s->alamat),
                        deskripsi: @json(\Illuminate\Support\Str::limit($s->deskripsi, 100)),
                        unlocked: {{ $isUnlocked ? 'true' : 'false' }},
                        url: @json(route('vr.museum', ['situs_id' => $s->situs_id, 'museum_id' => $firstMuseum->museum_id]))
                    };

                    marker.on('click', function(e) {
                        L.DomEvent.stopPropagation(e);
                        focusOnMarker(this);
                    });
                })();
            @endif
        @endforeach

        function hideBottomSheet() {
            bottomSheet.classList.remove('visible');
            activeMarker = null;
            overlayLink.style.display = 'none';
            lockedMessage.style.display = 'none';
        }

        closeOverlayBtn.addEventListener('click', hideBottomSheet);
        map.on('click', hideBottomSheet);

        function focusOnMarker(marker) {
            activeMarker = marker;

            const targetZoom = 14;
            let targetLatLng = marker.getLatLng();

            if (isMobileDevice) {
                const targetPoint = map.project(targetLatLng, targetZoom);
                const offsetPoint = L.point(targetPoint.x, targetPoint.y + (window.innerHeight * 0.25));
                targetLatLng = map.unproject(offsetPoint, targetZoom);
            }

            map.flyTo(targetLatLng, targetZoom, {
                animate: true,
                duration: 0.8
            });

            overlayTitle.textContent = marker.situsInfo.nama;
            overlayAddress.textContent = marker.situsInfo.alamat;
            overlayDescription.textContent = marker.situsInfo.deskripsi;

            if (marker.situsInfo.unlocked) {
                overlayLink.href = marker.situsInfo.url;
                overlayLink.style.display = 'flex';
                lockedMessage.style.display = 'none';
            } else {
                overlayLink.style.display = 'none';
                lockedMessage.style.display = 'flex';
            }

            bottomSheet.classList.add('visible');
        }
    </script>
</x-guest-layout>
