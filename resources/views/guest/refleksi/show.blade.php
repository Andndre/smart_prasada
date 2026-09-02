<x-app-layout tanpa-navigasi>
    {{-- Susunannya sengaja meniru pretest/posttest: bilah judul bg-primary, isi di atas
         bg-gray-50, tiap blok kartu putih rounded-2xl. Responden mengerjakan ketiganya
         berurutan dalam satu sesi, jadi tampilan yang berbeda terbaca sebagai aplikasi
         lain. Navigasinya saja yang dilepas — lihat catatan di layouts/app.blade.php. --}}
    <div class="bg-primary px-6 py-6 text-white">
        <div class="mx-auto max-w-7xl">
            <h1 class="text-lg font-bold">Refleksi</h1>
            <p class="text-sm opacity-90">
                {{ $museum->nama }}@if ($museum->situsPeninggalan) &middot; {{ $museum->situsPeninggalan->nama }}@endif
            </p>
        </div>
    </div>

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-6">
            @if ($pertanyaan->isEmpty())
                {{-- Kelas kegagalan yang sama dengan "scene tanpa slot" di Fase 3: jangan
                     tampilkan halaman kosong misterius, sebutkan alasannya. --}}
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <h2 class="font-semibold text-amber-900">Belum ada pertanyaan refleksi untuk museum ini</h2>
                    <p class="mt-2 text-sm text-amber-800">
                        Sesi VR-mu tetap tercatat. Pertanyaan refleksi untuk situs ini belum disusun,
                        jadi tidak ada yang perlu kamu isi sekarang.
                    </p>
                    @if (auth()->user()?->role === 'admin')
                        <a href="{{ route('admin.pertanyaan-refleksi', $museum->museum_id) }}"
                            class="mt-4 inline-flex items-center rounded-lg bg-amber-600 px-6 py-3 font-medium text-white transition-colors hover:bg-amber-700">
                            Susun pertanyaan refleksi
                        </a>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('guest.home') }}" class="text-sm font-medium text-amber-900 underline">Kembali ke beranda</a>
                    </div>
                </div>
            @else
                <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4">
                            <div class="mb-2 flex items-center space-x-2">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                                <h4 class="font-medium text-red-800">Terjadi kesalahan</h4>
                            </div>
                            <ul class="list-inside list-disc space-y-1 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h3 class="mb-4 text-xl font-bold text-gray-900">Instruksi Refleksi</h3>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start space-x-3">
                            <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-purple-100">
                                <span class="text-xs font-medium text-purple-600">1</span>
                            </div>
                            <p>Refleksi ini terdiri dari {{ $pertanyaan->count() }} pertanyaan terbuka.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-purple-100">
                                <span class="text-xs font-medium text-purple-600">2</span>
                            </div>
                            <p>Jawab sejujurnya berdasarkan apa yang kamu amati di dalam museum virtual.</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-purple-100">
                                <span class="text-xs font-medium text-purple-600">3</span>
                            </div>
                            <p>Tidak ada jawaban benar atau salah.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('refleksi.store', $museum->museum_id) }}">
                    @csrf
                    <input type="hidden" name="kode_responden" value="{{ $kodeResponden }}">
                    <input type="hidden" name="sesi_id" value="{{ $sesiId }}">

                    @foreach ($pertanyaan as $index => $soal)
                        <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                            <div class="mb-6">
                                <div class="mb-4 flex items-center justify-between gap-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100">
                                            <span class="text-sm font-medium text-purple-600">{{ $index + 1 }}</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Pertanyaan {{ $index + 1 }}</h3>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                        {{ $soal->nilai_karakter->label() }}
                                    </span>
                                </div>
                                <label for="jawaban-{{ $soal->pertanyaan_id }}" class="leading-relaxed text-gray-800">
                                    {{ $soal->pertanyaan }}
                                </label>
                            </div>

                            <textarea id="jawaban-{{ $soal->pertanyaan_id }}"
                                name="jawaban[{{ $soal->pertanyaan_id }}]" rows="4"
                                maxlength="{{ $maksPanjang }}"
                                class="block w-full rounded-lg border border-gray-200 p-4 transition-all duration-300 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Tulis refleksimu di sini...">{{ old('jawaban.'.$soal->pertanyaan_id) }}</textarea>
                            @error('jawaban.'.$soal->pertanyaan_id)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <button type="submit"
                            class="w-full rounded-lg bg-green-600 px-6 py-3 font-medium text-white transition-colors hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i>
                            Kirim Refleksi
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
