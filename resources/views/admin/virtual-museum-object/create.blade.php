<x-app-layout>
    <x-slot name="header">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <!-- Breadcrumb -->
                    <nav class="mb-2 flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.virtual-museum') }}"
                                    class="text-gray-400 hover:text-gray-500">{{ config('app.name') }}</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}"
                                        class="ml-1 text-gray-400 hover:text-gray-500">{{ $museum->nama }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-1 text-gray-500">Tambah Object</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Tambah Object {{ config('app.name') }}</h1>
                    <p class="mt-2 text-sm text-gray-600 sm:text-base">Untuk {{ $museum->nama }} -
                        {{ $museum->situsPeninggalan->nama }}</p>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="mb-2 flex items-center">
                    <svg class="mr-2 h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada input:</h3>
                </div>
                <ul class="list-inside list-disc space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.virtual-museum-object.store', $museum->museum_id) }}"
            enctype="multipart/form-data">
            @csrf

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">Informasi Object</h2>
                    <p class="mt-1 text-sm text-gray-600">Lengkapi data object {{ config('app.name') }} yang akan
                        ditambahkan.</p>
                </div>

                <div class="space-y-6 px-6 py-6">

                    <!-- Nama Object -->
                    <div>
                        <label for="nama" class="mb-2 block text-sm font-medium text-gray-700">
                            Nama Object <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Arca Buddha Amitabha" required>
                        <p class="mt-1 text-xs text-gray-500">Nama object yang akan ditampilkan dalam Virtual Living
                            Museum</p>
                    </div>

                    <!-- Nama Mesh di GLB (untuk interaksi VR) -->
                    <div>
                        <label for="mesh_name" class="mb-2 block text-sm font-medium text-gray-700">
                            Nama Mesh di GLB (VR)
                        </label>
                        <input type="text" id="mesh_name" name="mesh_name" value="{{ old('mesh_name') }}"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Lukisan_Barong">
                        <p class="mt-1 text-xs text-gray-500">Nama objek persis seperti di Blender (terbawa saat export
                            .glb). Jika diisi, objek ini bisa diklik di mode VR untuk menampilkan info.</p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label for="deskripsi" class="mb-2 block text-sm font-medium text-gray-700">
                            Deskripsi Object
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="4"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Deskripsi detail tentang object ini...">{{ old('deskripsi') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Deskripsi yang akan ditampilkan kepada pengunjung museum
                        </p>
                    </div>

                    <!-- Audio Narasi -->
                    <div>
                        <label for="audio_file" class="mb-2 block text-sm font-medium text-gray-700">
                            File Audio Narasi <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors"
                            id="audio-drop-area">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M24 4c-4.418 0-8 3.582-8 8v16c0 4.418 3.582 8 8 8s8-3.582 8-8V12c0-4.418-3.582-8-8-8zm-6 24c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M42 24c0 9.941-8.059 18-18 18S6 33.941 6 24s8.059-18 18-18 18 8.059 18 18z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="audio_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload file audio</span>
                                        <input id="audio_file" name="audio_file" type="file" class="sr-only" accept=".mp3,.wav,.ogg,.aac">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">MP3, WAV, OGG, AAC up to 10MB</p>
                            </div>
                        </div>
                        <div id="audio-file-name" class="mt-2 text-sm text-gray-600 hidden"></div>
                    </div>

                    <!-- Pengaturan Marker -->
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <h3 class="text-sm font-semibold text-blue-900">Pengaturan Marker AR</h3>
                        <p class="mt-1 text-xs text-blue-700">Pilih marker yang sudah ada agar satu marker bisa
                            memuat lebih dari satu object, atau upload marker baru.</p>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="marker_id" class="mb-2 block text-sm font-medium text-gray-700">
                                    Gunakan Marker Yang Sudah Ada
                                </label>
                                <select id="marker_id" name="marker_id"
                                    class="block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Buat marker baru / tanpa marker --</option>
                                    @foreach ($museum->arMarkers as $marker)
                                        <option value="{{ $marker->marker_id }}" @selected(old('marker_id') == $marker->marker_id)>
                                            {{ $marker->nama ?: 'Marker #' . $marker->marker_id }}
                                            @if ($marker->virtualMuseumObjects->count() > 0)
                                                ({{ $marker->virtualMuseumObjects->count() }} object)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="nama_marker" class="mb-2 block text-sm font-medium text-gray-700">
                                    Nama Marker Baru
                                </label>
                                <input type="text" id="nama_marker" name="nama_marker"
                                    value="{{ old('nama_marker') }}"
                                    class="block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Contoh: Marker Candi Utama">
                                <p class="mt-1 text-xs text-gray-500">Digunakan saat Anda upload gambar marker baru.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- File Uploads Section -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">File dan Asset</h2>
                    <p class="mt-1 text-sm text-gray-600">Upload file-file yang dibutuhkan untuk object virtual living
                        museum.</p>
                </div>

                <div class="space-y-6 px-6 py-6">

                    <!-- Gambar Real Object -->
                    <div>
                        <label for="gambar_real" class="mb-2 block text-sm font-medium text-gray-700">
                            Gambar Real Object
                        </label>
                        <div id="gambar_real_dropzone"
                            class="mt-1 flex cursor-pointer justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5 transition-colors hover:border-gray-400">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="gambar_real"
                                        class="relative cursor-pointer rounded-md bg-white font-medium text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 hover:text-blue-500">
                                        <span id="gambar_real_text">Upload gambar</span>
                                        <input id="gambar_real" name="gambar_real" type="file" class="sr-only"
                                            accept="image/*" onchange="handleFileSelect('gambar_real')">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Foto asli dari object untuk referensi pengunjung</p>
                    </div>

                    <!-- File 3D Object -->
                    <div>
                        <label for="path_obj" class="mb-2 block text-sm font-medium text-gray-700">
                            File 3D Object
                        </label>
                        <div id="path_obj_dropzone"
                            class="mt-1 flex cursor-pointer justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5 transition-colors hover:border-gray-400">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="path_obj"
                                        class="relative cursor-pointer rounded-md bg-white font-medium text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 hover:text-blue-500">
                                        <span id="path_obj_text">Upload file 3D</span>
                                        <input id="path_obj" name="path_obj" type="file" class="sr-only"
                                            accept=".obj,.glb,.gltf" onchange="handleFileSelect('path_obj')">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">OBJ, GLB, GLTF up to 50MB</p>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Model 3D object untuk ditampilkan dalam Virtual Living
                            Museum</p>
                    </div>

                    <!-- AR Marker Image -->
                    <div>
                        <label for="path_gambar_marker" class="mb-2 block text-sm font-medium text-gray-700">
                            Gambar Marker AR (Baru)
                        </label>
                        <div id="path_gambar_marker_dropzone"
                            class="mt-1 flex cursor-pointer justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5 transition-colors hover:border-gray-400">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="path_gambar_marker"
                                        class="relative cursor-pointer rounded-md bg-white font-medium text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 hover:text-blue-500">
                                        <span id="path_gambar_marker_text">Upload gambar marker</span>
                                        <input id="path_gambar_marker" name="path_gambar_marker" type="file"
                                            class="sr-only" accept="image/*"
                                            onchange="handleFileSelect('path_gambar_marker')">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Gambar marker yang akan digunakan untuk mendeteksi AR.
                            File pattern `.patt` akan dibuat otomatis dan marker baru akan dibagikan ke object lain
                            jika dipilih di form berikutnya.</p>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end sm:gap-4">
                <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}"
                    class="order-2 inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:order-1 sm:w-auto">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batal
                </a>
                <button type="submit"
                    class="order-1 inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:order-2 sm:w-auto">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Object
                </button>
            </div>
        </form>
    </div>

    <script>
        // File upload handling
        function handleFileSelect(inputId) {
            const input = document.getElementById(inputId);
            const textElement = document.getElementById(inputId + '_text');
            const dropzoneElement = document.getElementById(inputId + '_dropzone');

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB

                textElement.textContent = `${fileName} (${fileSize} MB)`;
                dropzoneElement.classList.remove('border-gray-300');
                dropzoneElement.classList.add('border-green-400', 'bg-green-50');
            } else {
                // Reset to default state
                resetFileInput(inputId);
            }
        }

        function resetFileInput(inputId) {
            const textElement = document.getElementById(inputId + '_text');
            const dropzoneElement = document.getElementById(inputId + '_dropzone');

            dropzoneElement.classList.remove('border-green-400', 'bg-green-50');
            dropzoneElement.classList.add('border-gray-300');

            switch (inputId) {
                case 'gambar_real':
                    textElement.textContent = 'Upload gambar';
                    break;
                case 'path_obj':
                    textElement.textContent = 'Upload file 3D';
                    break;
                case 'path_gambar_marker':
                    textElement.textContent = 'Upload gambar marker';
                    break;
            }
        }

        function updateAudioFileName(input) {
            const fileNameDiv = document.getElementById('audio-file-name');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.name.match(/\.(mp3|wav|ogg|aac)$/i)) {
                    alert('Hanya file audio MP3, WAV, OGG, AAC yang diizinkan!');
                    input.value = '';
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 10MB.');
                    input.value = '';
                    return;
                }
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileNameDiv.innerHTML = `
                    <div class="flex items-center space-x-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                            <p class="text-xs text-gray-500">${fileSize} MB</p>
                        </div>
                    </div>
                `;
                fileNameDiv.classList.remove('hidden');
            }
        }

        function clearAudioFile() {
            document.getElementById('audio_file').value = '';
            document.getElementById('audio-file-name').classList.add('hidden');
        }

        // Drag and drop for audio
        const audioDropArea = document.getElementById('audio-drop-area');
        const audioFileInput = document.getElementById('audio_file');
        if (audioDropArea && audioFileInput) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                audioDropArea.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
            });
            ['dragenter', 'dragover'].forEach(eventName => {
                audioDropArea.addEventListener(eventName, () => audioDropArea.classList.add('border-blue-500', 'bg-blue-50'), false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                audioDropArea.addEventListener(eventName, () => audioDropArea.classList.remove('border-blue-500', 'bg-blue-50'), false);
            });
            audioDropArea.addEventListener('drop', e => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (file.name.match(/\.(mp3|wav|ogg|aac)$/i) && file.size <= 10 * 1024 * 1024) {
                        audioFileInput.files = files;
                        updateAudioFileName(audioFileInput);
                    } else {
                        alert('File tidak valid! Gunakan MP3, WAV, OGG, atau AAC (maksimal 10MB).');
                    }
                }
            }, false);
        }

        // Drag and drop functionality
        function setupDragAndDrop(inputId) {
            const dropzoneElement = document.getElementById(inputId + '_dropzone');
            const inputElement = document.getElementById(inputId);

            dropzoneElement.addEventListener('click', function() {
                inputElement.click();
            });

            dropzoneElement.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneElement.classList.add('border-blue-400', 'bg-blue-50');
            });

            dropzoneElement.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneElement.classList.remove('border-blue-400', 'bg-blue-50');
            });

            dropzoneElement.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneElement.classList.remove('border-blue-400', 'bg-blue-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    inputElement.files = files;
                    handleFileSelect(inputId);
                }
            });
        }

        // Initialize drag and drop for all file inputs
        document.addEventListener('DOMContentLoaded', function() {
            setupDragAndDrop('gambar_real');
            setupDragAndDrop('path_obj');
            setupDragAndDrop('path_gambar_marker');
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const namaInput = document.getElementById('nama');

            if (!namaInput.value.trim()) {
                e.preventDefault();
                alert('Nama object harus diisi!');
                namaInput.focus();
                return false;
            }

            // Show loading state
            const submitButton = document.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;

            // Reset button after 30 seconds (failsafe)
            setTimeout(function() {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }, 30000);
        });
    </script>
</x-app-layout>
