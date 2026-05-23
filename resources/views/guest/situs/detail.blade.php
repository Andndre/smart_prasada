<x-elearning-layout>
    {{-- Header Section --}}
    <div class="bg-primary px-6 py-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button class="back-button rounded-full p-2 transition-colors hover:bg-white/10">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <div class="flex-1">
                    <h1 class="text-lg font-bold">Virtual Living Museum</h1>
                    <p class="text-sm opacity-90">{{ $situs->nama }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="min-h-screen bg-gray-50 px-6 py-6">
        {{-- Hero Image Section --}}
        <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm">
            <div class="relative aspect-[16/9] bg-gradient-to-br from-orange-400 to-orange-600">
                {{-- Tampilkan thumbnail menggunakan accessor --}}
                <img src="{{ $situs->getThumbnailUrlAttribute() }}" alt="{{ $situs->nama }}"
                    class="h-full w-full object-cover">

                {{-- Overlay with basic info --}}
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                    <h2 class="mb-2 text-2xl font-bold text-white">{{ $situs->nama }}</h2>
                    @if ($situs->alamat)
                        <div class="flex items-center text-white/90">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span class="text-sm">{{ $situs->alamat }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Location Info Card --}}
        @if ($situs->lat && $situs->lng)
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-lg font-bold text-gray-900">
                    <i class="fas fa-map-marked-alt mr-2 text-orange-600"></i>
                    Lokasi
                </h3>

                <div class="space-y-3">
                    @if ($situs->alamat)
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-location-dot mt-1 text-gray-400"></i>
                            <div>
                                <p class="font-medium text-gray-900">Alamat</p>
                                <p class="text-gray-600">{{ $situs->alamat }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start space-x-3">
                        <i class="fas fa-globe mt-1 text-gray-400"></i>
                        <div>
                            <p class="font-medium text-gray-900">Koordinat</p>
                            <p class="text-gray-600">{{ $situs->lat }}, {{ $situs->lng }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Description Card --}}
        @if ($situs->deskripsi)
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-lg font-bold text-gray-900">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Deskripsi
                </h3>
                <div class="prose prose-gray max-w-none">
                    <p class="leading-relaxed text-gray-700">{{ $situs->deskripsi }}</p>
                </div>
            </div>
        @endif

        {{-- Virtual Living Museum Spots --}}
        @if ($situs->virtualMuseum->count() > 0)
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-lg font-bold text-gray-900">
                    <i class="fas fa-location-dot mr-2 text-purple-600"></i>
                    Spot AR Virtual Living Museum
                </h3>

                <div class="grid grid-cols-1 gap-4">
                    @foreach ($situs->virtualMuseum as $museum)
                        <div class="rounded-xl border border-gray-200 p-4 transition-shadow hover:shadow-md">
                            <div class="mb-4 flex items-center space-x-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100">
                                    <i class="fas fa-location-dot text-purple-600"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $museum->nama }}</h4>
                                    @if ($museum->virtualMuseumObjects->count() > 0)
                                        <p class="text-sm text-gray-600">
                                            <span
                                                class="font-medium">{{ $museum->virtualMuseumObjects->count() }}</span>
                                            objek peninggalan tersedia
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- AR Launch Button for this specific spot --}}
                            <button onclick="launchSpotAR({{ $museum->museum_id }}, '{{ $museum->nama }}')"
                                class="inline-flex w-full transform items-center justify-center rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 px-4 py-3 font-medium text-white shadow-md transition-all duration-200 hover:scale-[1.02] hover:from-purple-700 hover:to-blue-700">
                                <i class="fas fa-vr-cardboard mr-2"></i>
                                Jelajahi AR
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Virtual 360 Tour Section --}}
        @if ($situs->panoramaScenes && $situs->panoramaScenes->count() > 0)
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-lg font-bold text-gray-900">
                    <i class="fas fa-street-view mr-2 text-cyan-600"></i>
                    Tur Virtual 360°
                </h3>
                
                <div class="rounded-xl border border-gray-200 p-4 transition-shadow hover:shadow-md">
                    <div class="mb-4 flex items-center space-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-100">
                            <i class="fas fa-street-view text-cyan-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">Panorama Interaktif</h4>
                            <p class="text-sm text-gray-600">
                                Jelajahi situs ini melalui sudut pandang 360 derajat yang interaktif.
                            </p>
                        </div>
                    </div>
                    
                    <a href="{{ route('guest.situs.panorama', $situs->situs_id) }}"
                        class="inline-flex w-full transform items-center justify-center rounded-lg bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-3 font-medium text-white shadow-md transition-all duration-200 hover:scale-[1.02] hover:from-cyan-700 hover:to-blue-700">
                        <i class="fas fa-play-circle mr-2"></i>
                        Mulai Tur 360°
                    </a>
                </div>
            </div>
        @endif

        {{-- Heritage Objects Section --}}
        @php
            $allObjects = collect();
            foreach ($situs->virtualMuseum as $museum) {
                foreach ($museum->virtualMuseumObjects as $object) {
                    $object->museum_name = $museum->nama;
                    $allObjects->push($object);
                }
            }
        @endphp

        @if ($allObjects->count() > 0)
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 flex items-center text-lg font-bold text-gray-900">
                    <i class="fas fa-gem mr-2 text-amber-600"></i>
                    Heritage Objects
                    <span class="ml-2 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                        {{ $allObjects->count() }}
                    </span>
                </h3>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($allObjects as $object)
                        <div class="group cursor-pointer overflow-hidden rounded-xl border border-gray-100 bg-gray-50 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                            onclick="showObjectModal({{ $object->object_id }}, '{{ addslashes($object->nama) }}', '{{ addslashes($object->deskripsi ?? '') }}', '{{ $object->gambar_real ? asset('storage/' . $object->gambar_real) : asset('images/placeholder/object.png') }}', '{{ $object->museum_name }}')">
                            <div class="relative aspect-square overflow-hidden bg-gray-200">
                                @if ($object->gambar_real)
                                    <img src="{{ asset('storage/' . $object->gambar_real) }}"
                                        alt="{{ $object->nama }}"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <i class="fas fa-gem text-4xl text-gray-300"></i>
                                    </div>
                                @endif

                                {{-- Lock indicator overlay --}}
                                @if (!$is_unlocked)
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                                        <div class="rounded-full bg-white/90 p-2">
                                            <i class="fas fa-lock text-sm text-gray-600"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="truncate text-sm font-medium text-gray-800">{{ $object->nama }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $object->museum_name }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- AR Experience Section --}}
        <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="mb-4 flex items-center text-lg font-bold text-gray-900">
                <i class="fas fa-mobile-alt mr-2 text-green-600"></i>
                Cara Menggunakan AR
            </h3>

            <div class="space-y-4">
                {{-- Instructions --}}
                <div class="rounded-lg bg-gradient-to-br from-green-50 to-blue-50 p-4">
                    <h5 class="mb-3 flex items-center font-semibold text-gray-900">
                        <i class="fas fa-list-ol mr-2 text-gray-600"></i>
                        Langkah-langkah:
                    </h5>
                    <ol class="ml-4 space-y-2 text-sm text-gray-700">
                        <li class="flex items-start">
                            <span
                                class="mr-3 mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">1</span>
                            <span>Pilih spot yang ingin dijelajahi dan klik tombol "Jelajahi AR"</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="mr-3 mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">2</span>
                            <span>Izinkan akses kamera dan arahkan ke permukaan datar</span>
                        </li>
                        <li class="flex items-start">
                            <span
                                class="mr-3 mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">3</span>
                            <span>Ketuk lingkaran untuk menempatkan objek berukuran besar</span>
                        </li>
                    </ol>
                </div>

                {{-- Requirements Notice --}}
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-exclamation-triangle mt-0.5 text-amber-600"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-amber-800">Persyaratan:</p>
                            <p class="mt-1 text-xs text-amber-700">Browser dengan dukungan WebXR, kamera, koneksi
                                stabil, dan pencahayaan cukup</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Actions --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-bold text-gray-900">Navigasi</h3>

            <div class="space-y-3">
                @if ($situs->materi)
                    <a href="{{ route('guest.elearning.materi', $situs->materi->materi_id) }}"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Materi: {{ $situs->materi->judul }}
                    </a>
                @endif

                <a href="{{ route('guest.elearning') }}"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-gray-100 px-4 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-200">
                    <i class="fas fa-home mr-2"></i>
                    Kembali ke Beranda E-Learning
                </a>

                @if ($situs->lat && $situs->lng)
                    <a href="https://www.google.com/maps?q={{ $situs->lat }},{{ $situs->lng }}" target="_blank"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-green-600 px-4 py-3 font-medium text-white transition-colors hover:bg-green-700">
                        <i class="fas fa-map-marked-alt mr-2"></i>
                        Lihat di Google Maps
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Object Modal --}}
    <div id="object-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" id="modal-backdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-md scale-95 transform overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl transition-all duration-300"
                id="modal-content">
                <button onclick="closeObjectModal()"
                    class="absolute right-4 top-4 z-10 rounded-full bg-white/90 p-2 text-gray-800 shadow-lg transition-all hover:scale-110 hover:bg-white">
                    <i class="fas fa-times"></i>
                </button>
                <div class="aspect-[4/3] w-full bg-gray-100">
                    <img id="modal-object-image" src="" alt="" class="h-full w-full object-cover">
                </div>
                <div class="p-6">
                    <div class="mb-4 flex items-start">
                        <div class="flex-1">
                            <h2 id="modal-object-title" class="text-xl font-bold text-gray-900"></h2>
                            <p id="modal-object-museum" class="text-sm text-purple-600"></p>
                        </div>
                    </div>
                    <p id="modal-object-description" class="text-sm leading-relaxed text-gray-600"></p>
                </div>
                <div class="flex gap-3 border-t border-gray-100 bg-gray-50 p-4">
                    @if ($is_unlocked)
                        <button id="modal-ar-btn" onclick="launchARFromModal()"
                            class="inline-flex flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 px-4 py-3 text-center font-medium text-white transition-all hover:-translate-y-0.5 hover:shadow-lg">
                            <i class="fas fa-vr-cardboard mr-2"></i>
                            Jelajahi AR
                        </button>
                    @else
                        <div class="flex-1 text-center">
                            <div
                                class="mb-2 inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600">
                                <i class="fas fa-lock mr-2"></i>
                                {{ __('maps.locked') }}
                            </div>
                            <p class="text-xs text-gray-500">{{ __('maps.complete_material_to_unlock') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- AR JavaScript (Three.js WebXR implementation) --}}
    <script>
        let currentObjectId = null;
        let currentMuseumId = null;

        function launchSpotAR(museumId, museumName) {
            const confirmation = confirm(
                `Memulai pengalaman AR untuk spot "${museumName}"?\n\nPastikan Anda berada di tempat yang memiliki pencahayaan cukup dan permukaan datar untuk penempatan objek.`
            );
            if (confirmation) {
                window.location.href = `{{ url('/situs') }}/{{ $situs->situs_id }}/ar/${museumId}`;
            }
        }

        function showObjectModal(objectId, name, description, imageUrl, museumName) {
            currentObjectId = objectId;

            // Find museum for this object
            @foreach ($situs->virtualMuseum as $museum)
                @foreach ($museum->virtualMuseumObjects as $object)
                    if (objectId == {{ $object->object_id }}) {
                        currentMuseumId = {{ $museum->museum_id }};
                    }
                @endforeach
            @endforeach

            document.getElementById('modal-object-title').textContent = name;
            document.getElementById('modal-object-description').textContent = description || 'Tidak ada deskripsi';
            document.getElementById('modal-object-museum').textContent = museumName;
            document.getElementById('modal-object-image').src = imageUrl;

            const modal = document.getElementById('object-modal');
            const modalContent = document.getElementById('modal-content');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeObjectModal() {
            const modal = document.getElementById('object-modal');
            const modalContent = document.getElementById('modal-content');
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                currentObjectId = null;
                currentMuseumId = null;
            }, 200);
        }

        function launchARFromModal() {
            if (currentMuseumId) {
                closeObjectModal();
                launchSpotAR(currentMuseumId, '');
            }
        }

        document.getElementById('modal-backdrop')?.addEventListener('click', closeObjectModal);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeObjectModal();
        });
    </script>
</x-elearning-layout>
