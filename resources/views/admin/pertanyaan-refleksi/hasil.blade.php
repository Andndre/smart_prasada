<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-4 flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-3 text-sm">
                    <li>
                        <a href="{{ route('admin.virtual-museum') }}" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-cube mr-1.5"></i>Virtual Living Museum
                        </a>
                    </li>
                    <li class="flex items-center text-gray-400">
                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                        <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}" class="hover:text-gray-600">
                            {{ $museum->nama }}
                        </a>
                    </li>
                    <li class="flex items-center text-gray-400">
                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                        <a href="{{ route('admin.pertanyaan-refleksi', $museum->museum_id) }}" class="hover:text-gray-600">
                            Refleksi
                        </a>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-chevron-right mx-2 text-xs text-gray-400"></i>
                        <span class="font-semibold text-gray-900">Hasil Jawaban</span>
                    </li>
                </ol>
            </nav>

            <!-- Page Title & Actions -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Hasil Jawaban Refleksi</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Rekapitulasi jawaban refleksi responden untuk <strong class="text-gray-800">{{ $museum->nama }}</strong>.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.refleksi.export', ['museum' => $museum->museum_id]) }}"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-file-csv mr-2"></i>Unduh CSV Refleksi
                    </a>
                    <a href="{{ route('admin.vr-events.export', ['museum' => $museum->museum_id]) }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        <i class="fas fa-chart-line mr-2 text-purple-600"></i>Unduh Log VR (CSV)
                    </a>
                    <a href="{{ route('admin.pertanyaan-refleksi', $museum->museum_id) }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        <i class="fas fa-sliders mr-2"></i>Kelola Soal
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800">
                    <i class="fas fa-check-circle mr-2 text-green-600"></i>{{ session('success') }}
                </div>
            @endif

            <!-- Summary Stats -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                            <i class="fas fa-comments text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Jawaban</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalJawaban) }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Responden Unik</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalResponden) }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <i class="fas fa-clipboard-question text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Jumlah Pertanyaan</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pertanyaanList->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Card -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('admin.hasil-refleksi', $museum->museum_id) }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3.5 top-3 text-xs text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari kode responden atau kata kunci jawaban..."
                                class="w-full rounded-xl border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                    </div>

                    @if ($pertanyaanList->isNotEmpty())
                        <div class="sm:w-64">
                            <select name="pertanyaan_id" class="w-full rounded-xl border border-gray-300 py-2 text-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Semua Pertanyaan</option>
                                @foreach ($pertanyaanList as $p)
                                    <option value="{{ $p->pertanyaan_id }}" @selected(request('pertanyaan_id') == $p->pertanyaan_id)>
                                        #{{ $p->urutan }}: {{ Str::limit($p->pertanyaan, 40) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <button type="submit" class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">
                            <i class="fas fa-filter mr-1.5"></i>Filter
                        </button>
                        @if (request()->hasAny(['search', 'pertanyaan_id']))
                            <a href="{{ route('admin.hasil-refleksi', $museum->museum_id) }}" class="rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Answers Table -->
            @if ($jawaban->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                        <i class="fas fa-inbox text-2xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">Belum ada jawaban refleksi</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if (request()->hasAny(['search', 'pertanyaan_id']))
                            Tidak ditemukan data jawaban yang cocok dengan filter pencarian di atas.
                        @else
                            Siswa yang mengisi form refleksi setelah sesi VR akan otomatis tercatat di sini.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">Responden</th>
                                    <th scope="col" class="px-6 py-3.5">Pertanyaan</th>
                                    <th scope="col" class="px-6 py-3.5">Jawaban Siswa</th>
                                    <th scope="col" class="px-6 py-3.5">Waktu Submit</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($jawaban as $row)
                                    <tr class="transition hover:bg-gray-50/80">
                                        <td class="whitespace-nowrap px-6 py-4 align-top">
                                            @if ($row->kode_responden)
                                                <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 font-mono text-xs font-bold text-emerald-700 border border-emerald-200">
                                                    {{ $row->kode_responden }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-0.5 text-xs text-gray-500 italic">
                                                    Anonim
                                                </span>
                                            @endif
                                            @if ($row->user && $row->user->name !== $row->kode_responden)
                                                <div class="mt-1 text-xs text-gray-400">
                                                    {{ $row->user->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 align-top max-w-xs">
                                            @if ($row->pertanyaan)
                                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800 mb-1">
                                                    {{ $row->pertanyaan->nilai_karakter?->label() }}
                                                </span>
                                                <p class="text-xs font-medium text-gray-800 line-clamp-2">
                                                    {{ $row->pertanyaan->pertanyaan }}
                                                </p>
                                            @else
                                                <span class="text-xs italic text-gray-400">(Pertanyaan telah dihapus)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 text-sm text-gray-900 leading-relaxed whitespace-pre-line">
                                                {{ $row->jawaban }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 align-top text-xs text-gray-500">
                                            <div class="font-medium text-gray-800">{{ $row->created_at?->format('d M Y') }}</div>
                                            <div class="text-gray-400">{{ $row->created_at?->format('H:i') }} WITA</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 align-top text-right text-xs">
                                            <form method="POST" action="{{ route('admin.jawaban-refleksi.destroy', $row->jawaban_id) }}"
                                                onsubmit="return confirm('Hapus baris jawaban ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="Hapus jawaban ini">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($jawaban->hasPages())
                        <div class="border-t border-gray-100 px-6 py-4">
                            {{ $jawaban->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
