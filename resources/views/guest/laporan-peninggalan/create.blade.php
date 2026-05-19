<x-guest-layout>
    <div class="px-6 py-6 bg-primary text-white">
        <div class="flex items-center">
            <button class="back-button mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </button>
            <h1 class="text-xl font-bold">{{ __('laporan-peninggalan.add_report') }}</h1>
        </div>
    </div>

    <div class="px-6 pt-6 pb-24 bg-gray-50 min-h-screen">
        <form action="{{ route('guest.laporan-peninggalan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                {{-- Form Fields --}}
                <div class="space-y-5">
                    {{-- Nama Peninggalan --}}
                    <div>
                        <label for="nama_peninggalan"
                               class="block text-sm font-medium text-gray-700 mb-1">{{ __('laporan-peninggalan.heritage_name') }} <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nama_peninggalan" name="nama_peninggalan" required
                               value="{{ old('nama_peninggalan') }}"
                               class="w-full px-3 py-2 border {{ $errors->has('nama_peninggalan') ? 'border-red-500' : 'border-gray-300' }} rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                               placeholder="Masukkan nama peninggalan">
                        @error('nama_peninggalan')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label for="alamat"
                               class="block text-sm font-medium text-gray-700 mb-1"> {{ __('laporan-peninggalan.address') }}
                            <span
                                class="text-red-500">*</span></label>
                        <textarea id="alamat" name="alamat" rows="2" required
                                  class="w-full px-3 py-2 border {{ $errors->has('alamat') ? 'border-red-500' : 'border-gray-300' }} rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                  placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                        @error('alamat')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Map --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('laporan-peninggalan.location_on_the_map') }}
                            <span
                                class="text-red-500">*</span></label>
                        <div id="map" class="h-64 w-full bg-gray-100 rounded-lg mb-2 z-10"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="lat"
                                       class="block text-xs font-medium text-gray-600 mb-1">{{ __('laporan-peninggalan.latitude') }}</label>
                                <input type="text" id="lat" name="lat" required readonly
                                       value="{{ old('lat') }}"
                                       class="w-full px-3 py-2 border {{ $errors->has('lat') ? 'border-red-500' : 'border-gray-300' }} rounded-md focus:outline-none bg-gray-50">
                                @error('lat')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="lng"
                                       class="block text-xs font-medium text-gray-600 mb-1">{{ __('laporan-peninggalan.longitude') }}</label>
                                <input type="text" id="lng" name="lng" required readonly
                                       value="{{ old('lng') }}"
                                       class="w-full px-3 py-2 border {{ $errors->has('lng') ? 'border-red-500' : 'border-gray-300' }} rounded-md focus:outline-none bg-gray-50">
                                @error('lng')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('laporan-peninggalan.click_on_the_map_heritage_location') }}</p>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="deskripsi"
                               class="block text-sm font-medium text-gray-700 mb-1">{{ __('laporan-peninggalan.description') }} <span
                                class="text-red-500">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" required
                                  class="w-full px-3 py-2 border {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-gray-300' }} rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                  placeholder="Deskripsikan peninggalan ini secara detail">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gambar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('laporan-peninggalan.images') }}</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="gambar"
                                   class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-xl mb-2"></i>
                                    <p class="text-sm text-gray-500"><span class="font-medium">Klik untuk unggah</span>
                                        atau seret dan lepas</p>
                                    <p class="text-xs text-gray-500">PNG, JPG, atau JPEG (maks. 5MB per file)</p>
                                </div>
                                <input id="gambar" name="gambar[]" type="file" class="hidden" multiple accept="image/*">
                            </label>
                        </div>
                        <div id="image-previews" class="flex flex-wrap gap-2 mt-2"></div>
                        @error('gambar.*')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-3 rounded-lg transition-colors mb-5">
                {{ __('laporan-peninggalan.save_report') }}
            </button>
        </form>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden"/>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize Leaflet map
                const map = L.map('map').setView([-8.409518, 115.188919], 10); // Default to Bali

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Handle marker placement
                let marker;

                map.on('click', function (e) {
                    // Remove any existing marker
                    if (marker) {
                        map.removeLayer(marker);
                    }

                    // Add a new marker
                    marker = L.marker(e.latlng).addTo(map);

                    // Update form fields
                    document.getElementById('lat').value = e.latlng.lat.toFixed(8);
                    document.getElementById('lng').value = e.latlng.lng.toFixed(8);
                });

                // Try to get user's location
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        map.setView([position.coords.latitude, position.coords.longitude], 13);
                    });
                }

                // Handle image previews
                const inputElement = document.getElementById('gambar');
                const previewContainer = document.getElementById('image-previews');

                inputElement.addEventListener('change', function () {
                    previewContainer.innerHTML = '';

                    if (this.files) {
                        for (let i = 0; i < this.files.length; i++) {
                            if (i >= 5) break; // Limit to 5 images

                            const file = this.files[i];
                            const reader = new FileReader();

                            reader.onload = function (e) {
                                const div = document.createElement('div');
                                div.className = 'relative';

                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'h-16 w-16 object-cover rounded-md';
                                div.appendChild(img);

                                previewContainer.appendChild(div);
                            }

                            reader.readAsDataURL(file);
                        }
                    }
                });
            });
        </script>
    @endpush
</x-guest-layout>
