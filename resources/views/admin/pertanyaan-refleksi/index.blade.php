<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-4 flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <a href="{{ route('admin.virtual-museum') }}" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-cube mr-2"></i>Virtual Living Museum
                        </a>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-chevron-right mr-4 text-gray-400"></i>
                        <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}"
                            class="text-gray-500 hover:text-gray-700">{{ $museum->nama }}</a>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-chevron-right mr-4 text-gray-400"></i>
                        <span class="font-medium text-gray-900">Refleksi</span>
                    </li>
                </ol>
            </nav>

            <div class="mb-6 sm:flex sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pertanyaan Refleksi</h1>
                    <p class="mt-1 text-gray-600">
                        Ditampilkan di layar biasa setelah siswa selesai menjelajahi museum ini.
                    </p>
                </div>
                <a href="{{ route('admin.pertanyaan-refleksi.create', $museum->museum_id) }}"
                    class="inline-flex items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">
                    <i class="fas fa-plus mr-2"></i>Tambah Pertanyaan
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            @if ($pertanyaan->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 p-10 text-center">
                    <p class="text-gray-500">Belum ada pertanyaan refleksi untuk museum ini.</p>
                    <p class="mt-1 text-sm text-gray-400">
                        Selama kosong, siswa yang membuka halaman refleksi akan diberi tahu bahwa
                        pertanyaannya belum disusun.
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($pertanyaan as $soal)
                        <div class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="min-w-0">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">#{{ $soal->urutan }}</span>
                                    <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                        {{ $soal->nilai_karakter->label() }}
                                    </span>
                                </div>
                                <p class="whitespace-pre-line text-gray-900">{{ $soal->pertanyaan }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="{{ route('admin.pertanyaan-refleksi.edit', $soal->pertanyaan_id) }}"
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                                <form method="POST" action="{{ route('admin.pertanyaan-refleksi.destroy', $soal->pertanyaan_id) }}"
                                    onsubmit="return confirm('Hapus pertanyaan ini beserta jawaban siswa yang terkait?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded-md border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
