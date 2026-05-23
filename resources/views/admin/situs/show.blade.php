<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mr-4"></i>
                                <a href="{{ route('admin.situs') }}" class="text-gray-400 hover:text-gray-500">Kelola Situs</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mr-4"></i>
                                <span class="text-gray-900 font-medium">{{ $situs->nama }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="mb-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Detail Situs Peninggalan</h1>
                    <p class="mt-2 text-sm sm:text-base text-gray-600">Informasi lengkap situs: <strong>{{ $situs->nama }}</strong></p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-3">
                    <a href="{{ route('admin.situs.edit', $situs->situs_id) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        <span class="hidden sm:inline">Edit Situs</span>
                        <span class="sm:hidden">Edit</span>
                    </a>
                    <a href="{{ route('admin.situs') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Kembali ke Situs</span>
                        <span class="sm:hidden">Kembali</span>
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Main Information -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>

                            <!-- Thumbnail Image -->
                            <div class="mb-5">
                                <img src="{{ $situs->getThumbnailUrlAttribute() }}"
                                     alt="{{ $situs->nama }}"
                                     class="h-48 w-full object-cover rounded-lg border border-gray-200 shadow-md">
                            </div>

                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nama Situs</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $situs->nama }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Masa</dt>
                                    <dd class="mt-1">
                                        @if($situs->materi)
                                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-purple-100 text-purple-800">
                                                {{ $situs->materi->judul }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Tidak ada kategori</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $situs->alamat }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Koordinat</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <div class="flex items-center space-x-4">
                                            <span><strong>Lat:</strong> {{ $situs->lat }}</span>
                                            <span><strong>Lng:</strong> {{ $situs->lng }}</span>
                                            <a href="https://www.google.com/maps?q={{ $situs->lat }},{{ $situs->lng }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-external-link-alt mr-1"></i>Lihat di Peta
                                            </a>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Deskripsi</h3>
                            <div class="prose prose-sm max-w-none text-gray-900">
                                {{ $situs->deskripsi }}
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Map Card -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                Lokasi pada Peta
                            </h3>

                            <!-- Interactive Map -->
                            <div class="mb-4">
                                <div class="border border-gray-300 rounded-md overflow-hidden">
                                    <div id="map" style="height: 350px; width: 100%;"></div>
                                </div>
                            </div>

                            <!-- Coordinate Details -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">Latitude</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $situs->lat }}</span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">Longitude</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $situs->lng }}</span>
                                </div>
                            </div>

                            <!-- Map Controls -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="copyCoordinates()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-copy mr-2"></i>
                                    Salin Koordinat
                                </button>
                                <a href="https://www.google.com/maps?q={{ $situs->lat }},{{ $situs->lng }}" target="_blank" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Virtual Living Museum Card -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-building text-blue-600 mr-2"></i>
                                    Virtual Living Museum
                                </h3>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $situs->virtualMuseum->count() > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $situs->virtualMuseum->count() }} museum
                                    </span>
                                    <a href="{{ route('admin.virtual-museum.create') }}?situs_id={{ $situs->situs_id }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah Museum
                                    </a>
                                </div>
                            </div>

                            @if($situs->virtualMuseum->count() > 0)
                                <div class="space-y-4">
                                    @foreach($situs->virtualMuseum as $museum)
                                        <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-r from-blue-50 to-blue-100">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h4 class="text-lg font-semibold text-gray-900">{{ $museum->nama }}</h4>
                                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-200 text-blue-800">
                                                            ID: {{ $museum->museum_id }}
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-gray-600 mb-3">Museum virtual untuk situs {{ $situs->nama }}</p>

                                                    <!-- File Info -->
                                                    <div class="flex items-center space-x-3 text-sm text-gray-600 mb-4">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-file-code text-green-600 mr-1"></i>
                                                            <span class="font-mono text-xs">{{ Str::limit($museum->path_obj, 50) }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Museum Stats -->
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                                        <div class="text-center p-3 bg-white rounded border">
                                                            <div class="text-sm font-semibold text-gray-900">{{ $museum->virtualMuseumObjects->count() }}</div>
                                                            <div class="text-xs text-gray-500">Objek AR Marker</div>
                                                        </div>
                                                        <div class="text-center p-3 bg-white rounded border">
                                                            <div class="text-sm font-semibold text-gray-900">{{ $museum->created_at->format('d/m/Y') }}</div>
                                                            <div class="text-xs text-gray-500">Dibuat</div>
                                                        </div>
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="flex flex-wrap gap-2">
                                                        <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                            <i class="fas fa-eye mr-2"></i>
                                                            Lihat Detail Museum
                                                        </a>
                                                        <a href="{{ route('admin.virtual-museum.edit', $museum->museum_id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                                            <i class="fas fa-edit mr-2"></i>
                                                            Edit Museum
                                                        </a>
                                                        @if($museum->virtualMuseumObjects->count() == 0)
                                                            <form action="{{ route('admin.virtual-museum.destroy', $museum->museum_id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus museum virtual ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                                    <i class="fas fa-trash mr-2"></i>
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                    <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                                        <i class="fas fa-building text-5xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada Virtual Living Museum</h3>
                                    <p class="text-sm text-gray-500 mb-4">Situs ini belum memiliki Virtual Living Museum. Buat museum virtual untuk memberikan pengalaman 3D yang immersive.</p>
                                    <a href="{{ route('admin.virtual-museum.create') }}?situs_id={{ $situs->situs_id }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Buat Virtual Living Museum
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Virtual 360 Panorama Card -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-street-view text-cyan-600 mr-2"></i>
                                    Tur Virtual 360° Panorama
                                </h3>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $situs->scenes->count() > 0 ? 'bg-cyan-100 text-cyan-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $situs->scenes->where('scene_type', 'panorama')->count() }} adegan
                                    </span>
                                </div>
                            </div>

                            <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                                    <i class="fas fa-street-view text-5xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Kelola Tur 360° Panorama</h3>
                                <p class="text-sm text-gray-500 mb-4">Akses editor VR Panorama untuk menambahkan adegan 360° dan memasang hotspot interaktif.</p>
                                <a href="{{ url('admin/panorama/editor/' . $situs->situs_id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition-colors">
                                    <i class="fas fa-edit mr-2"></i>
                                    Buka Editor Panorama
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Information -->
                <div class="xl:col-span-1 space-y-6">
                    <!-- Meta Information -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sistem</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Materi Terkait</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($situs->materi)
                                            <a href="{{ route('admin.materi.show', $situs->materi->materi_id) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $situs->materi->judul }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">Tidak ada</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ditambahkan Oleh</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($situs->user)
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-6 w-6">
                                                    <div class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-user-plus text-gray-500 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <span class="font-medium">{{ $situs->user->name }}</span>
                                                    <div class="text-xs text-gray-500">{{ $situs->user->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">Tidak ada</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.situs.edit', $situs->situs_id) }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-yellow-300 rounded-md shadow-sm text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition-colors">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit Situs
                                </a>
                                <a href="https://www.google.com/maps?q={{ $situs->lat }},{{ $situs->lng }}" target="_blank" class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    Lihat di Google Maps
                                </a>
                                @if($situs->virtualMuseumObject->count() == 0 && $situs->virtualMuseum->count() == 0)
                                    <form action="{{ route('admin.situs.destroy', $situs->situs_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus situs ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                                            <i class="fas fa-trash mr-2"></i>
                                            Hapus Situs
                                        </button>
                                    </form>
                                @else
                                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-yellow-800">
                                                    Situs tidak dapat dihapus karena memiliki
                                                    @if($situs->virtualMuseum->count() > 0)
                                                        {{ $situs->virtualMuseum->count() }} virtual living museum
                                                        @if($situs->virtualMuseumObject->count() > 0)
                                                            dan {{ $situs->virtualMuseumObject->count() }} objek virtual living museum.
                                                        @else
                                                            .
                                                        @endif
                                                    @else
                                                        {{ $situs->virtualMuseumObject->count() }} objek virtual living museum.
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Situs coordinates
            const situsLat = {{ $situs->lat }};
            const situsLng = {{ $situs->lng }};
            const situsName = "{{ $situs->nama }}";

            // Initialize map
            const map = L.map('map').setView([situsLat, situsLng], 15);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add marker with custom popup
            const marker = L.marker([situsLat, situsLng]).addTo(map);
            marker.bindPopup(`
                <div class="text-center">
                    <h3 class="font-semibold text-gray-900">${situsName}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $situs->materi->judul }}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        <strong>Koordinat:</strong><br>
                        ${situsLat}, ${situsLng}
                    </p>
                </div>
            `).openPopup();

            // Disable zoom on double click to prevent accidental zooming
            map.doubleClickZoom.disable();
        });

        // Function to copy coordinates to clipboard
        function copyCoordinates() {
            const coordinates = "{{ $situs->lat }}, {{ $situs->lng }}";
            if (navigator.clipboard) {
                navigator.clipboard.writeText(coordinates).then(function() {
                    // Show success message
                    const button = event.target.closest('button');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check mr-2"></i>Tersalin!';
                    button.classList.add('text-green-700', 'border-green-300', 'bg-green-50');

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('text-green-700', 'border-green-300', 'bg-green-50');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Could not copy text: ', err);
                    fallbackCopyTextToClipboard(coordinates);
                });
            } else {
                fallbackCopyTextToClipboard(coordinates);
            }
        }

        // Fallback copy function for older browsers
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    alert('Koordinat berhasil disalin ke clipboard!');
                }
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
                alert('Gagal menyalin koordinat. Silakan salin secara manual: ' + text);
            }

            document.body.removeChild(textArea);
        }
    </script>
</x-app-layout>
