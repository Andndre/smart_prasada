<x-app-layout tanpa-navigasi>
    <div class="bg-primary px-6 py-6 text-white">
        <div class="mx-auto max-w-7xl">
            <h1 class="text-lg font-bold">Refleksi</h1>
            <p class="text-sm opacity-90">Selesai</p>
        </div>
    </div>

    {{-- Kartu penutup meniru "state selesai" pretest/posttest: lingkaran centang hijau,
         judul, lalu satu tombol lanjut. --}}
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900">Refleksi tersimpan</h3>
                    <p class="mb-6 text-gray-600">
                        Terima kasih. Jawabanmu sudah tercatat dan akan digunakan untuk penelitian.
                    </p>

                    @if (!empty($isKiosk))
                        <div class="mb-4 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            @if (!empty($urlBerikutnya))
                                <a href="{{ $urlBerikutnya }}"
                                    class="inline-flex items-center rounded-xl bg-emerald-600 px-6 py-3.5 text-base font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700">
                                    <i class="fas fa-file-pen mr-2"></i>
                                    @if (!empty($kodeBerikutnya))
                                        Siapkan Responden Berikutnya ({{ $kodeBerikutnya }})
                                    @else
                                        Siapkan Responden Berikutnya
                                    @endif
                                </a>
                            @else
                                <div
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-medium text-blue-800">
                                    <i class="fas fa-info-circle mr-1.5"></i>
                                    Seluruh rangkaian responden pada sesi ini telah selesai.
                                </div>
                            @endif

                            @if ($museum)
                                <a href="{{ route('vr.peluncur', $museum->museum_id) }}"
                                    class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-5 py-3.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                                    <i class="fas fa-desktop mr-2 text-purple-600"></i>
                                    Kembali ke Peluncur Sesi
                                </a>
                            @endif
                        </div>
                    @else
                        {{-- Kembali ke materi asal situs ini, bukan ke daftar materi: siswa
                             melanjutkan tab yang sedang ia kerjakan. Turun ke beranda kalau
                             halaman dibuka tanpa konteks museum. --}}
                        <a href="{{ $materiId ? route('guest.elearning.materi', $materiId) : route('guest.home') }}"
                            class="inline-flex items-center rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-600 transition-colors hover:bg-gray-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            {{ $materiId ? 'Kembali ke materi' : 'Kembali ke beranda' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
