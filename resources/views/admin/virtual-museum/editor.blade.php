<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <title>Editor VR — {{ $museum->nama }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script type="importmap">
        {
          "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@v0.153.0/build/three.module.js",
            "three/jsm/": "https://cdn.jsdelivr.net/npm/three@v0.153.0/examples/jsm/"
          }
        }
    </script>

    @vite(['resources/css/app.css'])
</head>

<body class="overflow-hidden font-sans antialiased">
    <script>
        window.editorData = {
            museum: @json($museum),
            objects: @json($objects),
            saveUrl: @json(route('admin.virtual-museum.editor.save', $museum->museum_id)),
            deleteUrlTemplate: @json(route('admin.virtual-museum-object.destroy', ['object_id' => '__ID__'])),
            editUrlTemplate: @json(route('admin.virtual-museum-object.edit', ['object_id' => '__ID__'])),
            csrf: @json(csrf_token()),
        };
    </script>

    <div class="flex h-screen flex-col">
        <!-- Top bar -->
        <div class="flex items-center gap-4 border-b border-gray-200 bg-white px-4 py-3">
            <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}"
                class="rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100" title="Kembali">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-sm font-bold text-gray-900">Editor VR — {{ $museum->nama }}</h1>
                <p class="text-xs text-gray-500">{{ $museum->situsPeninggalan->nama }} · klik mesh di canvas atau tree untuk mengatur objek</p>
            </div>
            <span id="editor-status" class="text-xs text-gray-400"></span>
        </div>

        <div class="flex min-h-0 flex-1">
            <!-- Tree view -->
            <div class="w-72 shrink-0 overflow-y-auto border-r border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Struktur Scene
                </div>
                <div id="mesh-tree" class="p-2 text-sm text-gray-700">
                    <p class="p-2 text-xs text-gray-400">Memuat model…</p>
                </div>
            </div>

            <!-- 3D canvas -->
            <div id="canvas-container" class="relative min-w-0 flex-1 bg-gray-900">
                <div id="loading-container" class="absolute left-1/2 top-1/2 z-10 w-3/4 max-w-md -translate-x-1/2 -translate-y-1/2">
                    <div class="h-2 w-full rounded-full bg-gray-700">
                        <div id="loading-bar" class="h-full rounded-full bg-purple-500 transition-all duration-200" style="width:0"></div>
                    </div>
                    <p class="mt-2 text-center text-sm text-gray-400">Memuat model 3D…</p>
                </div>
                <div id="pick-slot-banner"
                    class="absolute left-1/2 top-4 z-10 hidden -translate-x-1/2 rounded-full bg-amber-500 px-4 py-1.5 text-xs font-semibold text-white shadow">
                    Mode pilih slot: klik mesh target di canvas atau tree (Esc untuk batal)
                </div>
            </div>

            <!-- Property panel -->
            <div class="w-80 shrink-0 overflow-y-auto border-l border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Properti Objek
                </div>
                <div id="panel-empty" class="p-4 text-sm text-gray-400">
                    Pilih mesh dari canvas atau tree untuk mulai mengatur.
                </div>
                <form id="panel-form" class="hidden space-y-4 p-4" autocomplete="off">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Mesh Terpilih</label>
                        <input type="text" id="field-mesh-name" readonly
                            class="block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    </div>
                    <div>
                        <label for="field-nama" class="mb-1 block text-xs font-medium text-gray-500">Nama Tampilan</label>
                        <input type="text" id="field-nama" required
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Arca Buddha Amitabha">
                    </div>
                    <div>
                        <label for="field-deskripsi" class="mb-1 block text-xs font-medium text-gray-500">Deskripsi (panel info di VR)</label>
                        <textarea id="field-deskripsi" rows="5"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Deskripsi yang tampil saat objek disentuh di VR"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Slot Puzzle (opsional)</label>
                        <div class="flex gap-2">
                            <input type="text" id="field-slot" readonly
                                class="block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                                placeholder="Belum ada slot">
                            <button type="button" id="btn-pick-slot"
                                class="shrink-0 rounded-md bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600">
                                Pilih
                            </button>
                            <button type="button" id="btn-clear-slot"
                                class="shrink-0 rounded-md border border-gray-300 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50">
                                ✕
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Jika diisi, objek jadi puzzle: user harus memindahkannya ke posisi slot di VR.</p>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" id="btn-save"
                            class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Simpan
                        </button>
                        <button type="button" id="btn-delete"
                            class="hidden rounded-md border border-red-300 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            Hapus
                        </button>
                    </div>
                    <p class="text-xs text-gray-400">Audio narasi & gambar diatur lewat <a id="link-full-edit" href="#" class="text-blue-600 underline">form lengkap</a> setelah objek disimpan.</p>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/vr-editor.js') }}?v={{ filemtime(public_path('assets/js/vr-editor.js')) }}" type="module"></script>
</body>

</html>
