<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <p class="text-sm font-medium uppercase tracking-wide text-purple-600">Refleksi</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $museum->nama }}</h1>
                @if ($museum->situsPeninggalan)
                    <p class="mt-1 text-gray-600">{{ $museum->situsPeninggalan->nama }}</p>
                @endif
            </div>

            @if ($pertanyaan->isEmpty())
                {{-- Kelas kegagalan yang sama dengan "scene tanpa slot" di Fase 3: jangan
                     tampilkan halaman kosong misterius, sebutkan alasannya. --}}
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <h2 class="font-semibold text-amber-900">Belum ada pertanyaan refleksi untuk museum ini</h2>
                    <p class="mt-2 text-sm text-amber-800">
                        Sesi VR-mu tetap tercatat. Pertanyaan refleksi untuk situs ini belum disusun,
                        jadi tidak ada yang perlu kamu isi sekarang.
                    </p>
                    @if (auth()->user()?->role === 'admin')
                        <a href="{{ route('admin.pertanyaan-refleksi', $museum->museum_id) }}"
                            class="mt-4 inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                            Susun pertanyaan refleksi
                        </a>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('guest.home') }}" class="text-sm font-medium text-amber-900 underline">Kembali ke beranda</a>
                    </div>
                </div>
            @else
                <p class="mb-6 text-gray-700">
                    Jawab sejujurnya berdasarkan apa yang kamu amati di dalam museum virtual.
                    Tidak ada jawaban benar atau salah.
                </p>

                <form method="POST" action="{{ route('refleksi.store', $museum->museum_id) }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="kode_responden" value="{{ $kodeResponden }}">
                    <input type="hidden" name="sesi_id" value="{{ $sesiId }}">

                    @foreach ($pertanyaan as $index => $soal)
                        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="mb-3 flex items-start justify-between gap-4">
                                <label for="jawaban-{{ $soal->pertanyaan_id }}" class="font-medium text-gray-900">
                                    {{ $index + 1 }}. {{ $soal->pertanyaan }}
                                </label>
                                <span class="shrink-0 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                    {{ $soal->nilai_karakter->label() }}
                                </span>
                            </div>
                            <textarea id="jawaban-{{ $soal->pertanyaan_id }}"
                                name="jawaban[{{ $soal->pertanyaan_id }}]" rows="4"
                                maxlength="{{ $maksPanjang }}"
                                class="block w-full rounded-md border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Tulis refleksimu di sini...">{{ old('jawaban.'.$soal->pertanyaan_id) }}</textarea>
                            @error('jawaban.'.$soal->pertanyaan_id)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <button type="submit"
                        class="w-full rounded-md bg-purple-600 px-6 py-3 font-semibold text-white hover:bg-purple-700 sm:w-auto">
                        Kirim Refleksi
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
