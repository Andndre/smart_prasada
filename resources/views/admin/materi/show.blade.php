<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Detail Materi</h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base">Informasi lengkap materi pembelajaran</p>
                    </div>
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-3 sm:space-y-0">
                        <a href="{{ route('admin.materi.edit', $materi->materi_id) }}"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-yellow-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-yellow-700">
                            <i class="fas fa-edit mr-2"></i>
                            <span class="hidden sm:inline">Edit Materi</span>
                            <span class="sm:hidden">Edit</span>
                        </a>
                        <a href="{{ route('admin.materi') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span class="hidden sm:inline">Kembali ke Materi</span>
                            <span class="sm:hidden">Kembali</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <!-- Main Information -->
                <div class="space-y-6 xl:col-span-2">
                    <!-- Basic Info Card -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Informasi Materi</h3>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Urutan</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            #{{ $materi->urutan }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Era</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if ($materi->era)
                                            {{ $materi->era->kode }}. {{ $materi->era->nama }}
                                        @else
                                            <span class="text-gray-400">Belum dipilih</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Bab</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $materi->bab ? 'Bab ' . $materi->bab : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $materi->created_at->format('d M Y, H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $materi->updated_at->format('d M Y, H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span
                                            class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                            Aktif
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Title and Description -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="mb-4 text-xl font-semibold text-gray-900">{{ $materi->judul }}</h3>
                            <div class="prose max-w-none">
                                <p class="leading-relaxed text-gray-700">{{ $materi->deskripsi }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Related Data -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Data Terkait</h3>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Ebook -->
                                <div class="rounded-lg border p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">E-book</h4>
                                            <p class="text-sm text-gray-500">{{ $materi->ebook()->count() }} e-book</p>
                                        </div>
                                        <i class="fas fa-book text-xl text-blue-600"></i>
                                    </div>
                                </div>

                                <!-- Situs Peninggalan -->
                                <a href="{{ route('admin.situs', ['materi_id' => $materi->materi_id]) }}"
                                    class="block rounded-lg border border-gray-200 p-4 hover:border-blue-500 hover:bg-blue-50/30 transition-all duration-200 group">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors duration-200">Situs Peninggalan</h4>
                                            <p class="text-sm text-gray-500">{{ $materi->situsPeninggalan()->count() }}
                                                situs</p>
                                        </div>
                                        <i class="fas fa-monument text-xl text-yellow-600 group-hover:scale-110 transition-transform duration-200"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ebook List -->
                    @if ($materi->ebook()->count() > 0)
                        <div class="rounded-lg bg-white shadow">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">Daftar E-book</h3>
                                    <a href="{{ route('admin.ebook', $materi->materi_id) }}"
                                        class="text-sm text-blue-600 hover:text-blue-900">
                                        Lihat Semua →
                                    </a>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($materi->ebook()->take(5)->get() as $ebook)
                                        <div
                                            class="flex items-center justify-between rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-book text-lg text-purple-600"></i>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900">{{ $ebook->judul }}
                                                    </h4>
                                                    <p class="text-xs text-gray-500">
                                                        <i class="fas fa-file-pdf mr-1"></i>
                                                        {{ basename($ebook->path_file) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if ($ebook->path_file && file_exists(storage_path('app/public/' . $ebook->path_file)))
                                                    <a href="{{ asset('storage/' . $ebook->path_file) }}"
                                                        target="_blank" class="text-blue-600 hover:text-blue-900"
                                                        title="Buka E-book">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @else
                                                    <span class="text-red-500" title="File tidak ditemukan">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($materi->ebook()->count() > 5)
                                        <div class="pt-2 text-center">
                                            <a href="{{ route('admin.ebook', $materi->materi_id) }}"
                                                class="text-sm text-gray-500 hover:text-gray-700">
                                                Dan {{ $materi->ebook()->count() - 5 }} e-book lainnya...
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg bg-white shadow">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <i class="fas fa-book mb-4 text-4xl text-gray-400"></i>
                                    <h3 class="mb-2 text-lg font-medium text-gray-900">Belum Ada E-book</h3>
                                    <p class="mb-4 text-sm text-gray-500">Belum ada e-book yang ditambahkan untuk materi
                                        ini.</p>
                                    <a href="{{ route('admin.ebook.create', $materi->materi_id) }}"
                                        class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah E-book Pertama
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Side Statistics -->
                <div class="space-y-6">
                    <!-- Pretest Stats -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clipboard-list text-2xl text-blue-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Pretest</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $materi->pretest()->count() }}
                                    </p>
                                    <p class="text-xs text-gray-500">soal tersedia</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Posttest Stats -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tasks text-2xl text-green-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Posttest</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $materi->posttest()->count() }}
                                    </p>
                                    <p class="text-xs text-gray-500">soal tersedia</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ebook Stats -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-book text-2xl text-purple-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Ebook</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $materi->ebook()->count() }}
                                    </p>
                                    <p class="text-xs text-gray-500">ebook tersedia</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <h4 class="mb-4 text-sm font-medium text-gray-900">Kelola Konten</h4>
                            <div class="space-y-2">
                                <a href="{{ route('admin.pretest', $materi->materi_id) }}"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-blue-300 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 shadow-sm hover:bg-blue-100">
                                    <i class="fas fa-clipboard-list mr-2"></i>
                                    Kelola Pretest ({{ $materi->pretest()->count() }})
                                </a>
                                <a href="{{ route('admin.posttest', $materi->materi_id) }}"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-green-300 bg-green-50 px-3 py-2 text-sm font-medium text-green-700 shadow-sm hover:bg-green-100">
                                    <i class="fas fa-tasks mr-2"></i>
                                    Kelola Posttest ({{ $materi->posttest()->count() }})
                                </a>
                                <a href="{{ route('admin.ebook', $materi->materi_id) }}"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-purple-300 bg-purple-50 px-3 py-2 text-sm font-medium text-purple-700 shadow-sm hover:bg-purple-100">
                                    <i class="fas fa-book mr-2"></i>
                                    Kelola Ebook ({{ $materi->ebook()->count() }})
                                </a>
                                <a href="{{ route('admin.tugas', $materi->materi_id) }}"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-orange-300 bg-orange-50 px-3 py-2 text-sm font-medium text-orange-700 shadow-sm hover:bg-orange-100">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    Kelola Tugas
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Other Actions -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="px-4 py-5 sm:p-6">
                            <h4 class="mb-4 text-sm font-medium text-gray-900">Aksi Lainnya</h4>
                            <div class="space-y-2">
                                <a href="{{ route('admin.materi.edit', $materi->materi_id) }}"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit Materi
                                </a>
                                <form action="{{ route('admin.materi.destroy', $materi->materi_id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50">
                                        <i class="fas fa-trash mr-2"></i>
                                        Hapus Materi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
