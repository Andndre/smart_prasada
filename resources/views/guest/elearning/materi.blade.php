<x-elearning-layout>
    {{-- Header Section --}}
    <div class="bg-primary px-6 py-6 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <button class="back-button rounded-full p-2 transition-colors hover:bg-white/10">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <a href="{{ route('profile.edit') }}"
                    class="h-12 w-12 overflow-hidden rounded-full border-2 border-white/30 transition-colors hover:border-white/50">
                    @if (auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile Picture"
                            class="h-full w-full object-cover" />
                    @else
                        <img src="{{ asset('images/placeholder/profile-picture.png') }}" alt="Profile Picture"
                            class="h-full w-full object-cover" />
                    @endif
                </a>
            </div>

            <div class="mb-8">
                <div class="mb-2 flex items-center space-x-2">
                    <i class="fas fa-book text-yellow-400"></i>
                    @if ($materi->era)
                        <span class="text-sm opacity-90">{{ $materi->era->kode }}. {{ $materi->era->nama }}</span>
                        @if ($materi->bab)
                            <span class="text-sm opacity-70">— {{ __('elearning.bab') }} {{ $materi->bab }}</span>
                        @endif
                    @else
                        <span class="text-sm opacity-90">{{ __('elearning.materi') }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold">{{ $materi->judul }}</h1>
            </div>

            {{-- Tab Navigation --}}
            <div class="mb-4 grid grid-cols-4 gap-2">
                {{-- Pre-test Tab --}}
                <button onclick="switchTab('pretest')" id="pretest-tab"
                    class="tab-button flex flex-col items-center rounded-lg p-2 transition-colors">
                    <div
                        class="relative mb-2 flex h-12 w-12 items-center justify-center rounded-full border-2 border-white">
                        @if ($pretest_completed)
                            <i class="fas fa-check text-white"></i>
                        @elseif(true)
                            {{-- Pre-test is always available --}}
                            <i class="fas fa-file-alt text-white"></i>
                        @endif
                    </div>
                    <span class="text-center text-xs text-white">{{ __('elearning.materi.pre_test') }}</span>
                </button>

                {{-- E-Book Tab --}}
                <button onclick="switchTab('ebook')" id="ebook-tab"
                    class="tab-button {{ !$ebook_available ? 'opacity-50' : '' }} flex flex-col items-center rounded-lg p-2 transition-colors">
                    <div
                        class="relative mb-2 flex h-12 w-12 items-center justify-center rounded-full border-2 border-white">
                        @if (!$ebook_available)
                            <i class="fas fa-lock text-white"></i>
                        @elseif($all_ebooks_read)
                            <i class="fas fa-check text-white"></i>
                        @else
                            <i class="fas fa-book text-white"></i>
                        @endif
                    </div>
                    <span class="text-center text-xs text-white">{{ __('elearning.materi.e_book') }}</span>
                </button>

                {{-- Virtual Living Museum Tab --}}
                <button onclick="switchTab('museum')" id="museum-tab"
                    class="tab-button {{ !$museum_available ? 'opacity-50' : '' }} flex flex-col items-center rounded-lg p-2 transition-colors">
                    <div
                        class="relative mb-2 flex h-12 w-12 items-center justify-center rounded-full border-2 border-white">
                        @if (!$museum_available)
                            <i class="fas fa-lock text-white"></i>
                        @elseif($all_museums_visited)
                            <i class="fas fa-check text-white"></i>
                        @else
                            <i class="fas fa-map-marker-alt text-white"></i>
                        @endif
                    </div>
                    <span class="text-center text-xs text-white">{{ __('elearning.materi.virtual_living_museum') }}</span>
                </button>

                {{-- Post-test Tab --}}
                <button onclick="switchTab('posttest')" id="posttest-tab"
                    class="tab-button {{ !$posttest_available ? 'opacity-50' : '' }} flex flex-col items-center rounded-lg p-2 transition-colors">
                    <div
                        class="relative mb-2 flex h-12 w-12 items-center justify-center rounded-full border-2 border-white">
                        @if (!$posttest_available)
                            <i class="fas fa-lock text-white"></i>
                        @elseif($posttest_completed)
                            <i class="fas fa-check text-white"></i>
                        @else
                            <i class="fas fa-clipboard-check text-white"></i>
                        @endif
                    </div>
                    <span class="text-center text-xs text-white">{{ __('elearning.materi.post_test') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="relative -mt-6 min-h-screen rounded-t-3xl bg-white">
        <div class="max-w-7xl mx-auto px-6 py-8">
            {{-- Pre Test Tab Content --}}
            <div id="pretest-content" class="tab-content">
                @if ($materi->pretest->count() > 0)
                    <div class="mb-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-center text-xl font-bold text-gray-900">
                            {{ __('elearning.materi.pre_test') }}</h2>

                        <div class="mb-6 rounded-2xl bg-gray-50 p-6">
                            <p class="text-center leading-relaxed text-gray-700">
                                {{ __('elearning.materi.akan_mengerjakan_pretest', ['materi' => $materi->judul]) }}
                            </p>
                        </div>

                        <div class="text-center">
                            @if ($pretest_completed)
                                <a href="{{ route('guest.elearning.pretest', $materi->materi_id) }}"
                                    class="inline-flex items-center rounded-xl bg-green-600 px-8 py-3 font-medium text-white transition-colors hover:bg-green-700">
                                    <i class="fas fa-eye mr-2"></i>
                                    {{ __('elearning.lihat_hasil') }}
                                </a>
                            @else
                                <a href="{{ route('guest.elearning.pretest', $materi->materi_id) }}"
                                    class="inline-flex items-center rounded-xl bg-blue-600 px-8 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                                    {{ __('elearning.mulai') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Panduan Mengisi Pre Test --}}
                    @if (!$pretest_completed)
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-bold text-gray-900">
                                {{ __('elearning.materi.panduan_mengisi_pretest') }}</h3>

                            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 sm:p-6">
                                {{-- Mobile Layout (Vertical Stack) --}}
                                <div class="sm:hidden">
                                    {{-- Mascot Section --}}
                                    <div class="mb-4 flex justify-center">
                                        <div
                                            class="flex h-32 w-28 items-center justify-center rounded-xl bg-yellow-100">
                                            <img src="{{ asset('images/mascot.png') }}" alt="Guide"
                                                class="h-24 w-24 object-contain">
                                        </div>
                                    </div>
                                    {{-- Content Section --}}
                                    <div class="space-y-3">
                                        <p class="text-sm leading-relaxed text-gray-700">
                                            <span class="font-medium">Sebelum memulai</span> pembelajaran, Anda diminta
                                            untuk mengerjakan pretest sebagai evaluasi awal.
                                            Pretest berisi <span class="font-medium">pertanyaan pilihan ganda</span>.
                                            Silakan pilih jawaban yang
                                            <span class="font-medium">paling sesuai</span> pada setiap pertanyaan.
                                        </p>
                                        <p class="text-sm leading-relaxed text-gray-700">
                                            Waktu pengerjaan <span class="font-medium">tidak dibatasi</span>, jadi
                                            pastikan Anda
                                            membaca soal dengan teliti sebelum menjawab. Hasil pretest
                                            <span class="font-medium">tidak mempengaruhi nilai akhir</span>, namun
                                            digunakan
                                            untuk mengetahui pengertian awal Anda.
                                        </p>
                                    </div>
                                </div>

                                {{-- Desktop Layout (Side by Side) --}}
                                <div class="hidden items-start space-x-4 sm:flex">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="flex h-20 w-16 items-center justify-center rounded-xl bg-yellow-100">
                                            <img src="{{ asset('images/mascot.png') }}" alt="Guide"
                                                class="h-12 w-12 object-contain">
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm leading-relaxed text-gray-700">
                                            <span class="font-medium">Sebelum memulai</span> pembelajaran, Anda diminta
                                            untuk mengerjakan pretest sebagai evaluasi awal.
                                            Pretest berisi <span class="font-medium">pertanyaan pilihan ganda</span>.
                                            Silakan pilih jawaban yang
                                            <span class="font-medium">paling sesuai</span> pada setiap pertanyaan.
                                        </p>
                                        <br>
                                        <p class="text-sm leading-relaxed text-gray-700">
                                            Waktu pengerjaan <span class="font-medium">tidak dibatasi</span>, jadi
                                            pastikan Anda
                                            membaca soal dengan teliti sebelum menjawab. Hasil pretest
                                            <span class="font-medium">tidak mempengaruhi nilai akhir</span>, namun
                                            digunakan
                                            untuk mengetahui pengertian awal Anda.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl bg-gray-100 p-8 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                            <i class="fas fa-file-alt text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.pretest_tidak_tersedia') }}</h3>
                        <p class="text-gray-600">{{ __('elearning.materi.materi_tidak_memiliki_pretst') }}</p>
                    </div>
                @endif
            </div>

            {{-- E-Book Tab Content --}}
            <div id="ebook-content" class="tab-content hidden">
                @if ($materi->ebook->count() > 0)
                    <h2 class="mb-6 text-center text-xl font-bold text-gray-900">{{ __('elearning.materi.e_book') }}</h2>
                    <div class="mb-6">
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center text-sm text-blue-900">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>{{ __('elearning.materi.harus_baca_seluruh_halaman') }}</span>
                        </div>
                    </div>
                    @if (!$ebook_available)
                        <div class="rounded-2xl bg-gray-100 p-8 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                                <i class="fas fa-lock text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.ebook_terkunci') }}</h3>
                            <p class="text-gray-600">{{ __('elearning.materi.selesaikan_pretese_terlebih_dulu') }}</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($materi->ebook as $ebook)
                                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                                    {{-- E-book Preview --}}
                                    <div
                                        class="flex aspect-[3/4] items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                                        @if ($ebook->path_file && file_exists(storage_path('app/public/' . $ebook->path_file)))
                                            <div class="text-center">
                                                <div
                                                    class="mx-auto mb-4 flex h-24 w-20 items-center justify-center rounded-lg border-2 border-gray-200 bg-white shadow-lg">
                                                    <i class="fas fa-file-pdf text-3xl text-red-500"></i>
                                                </div>
                                                <h3 class="font-medium text-gray-900">{{ $ebook->judul }}</h3>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <div
                                                    class="mx-auto mb-4 flex h-24 w-20 items-center justify-center rounded-lg bg-gray-200">
                                                    <i class="fas fa-exclamation-triangle text-2xl text-gray-400"></i>
                                                </div>
                                                <p class="text-gray-500">{{ __('elearning.materi.file_tidak_ditemukan') }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- E-book Actions --}}
                                    <div class="p-4">
                                        @if ($ebook->path_file && file_exists(storage_path('app/public/' . $ebook->path_file)))
                                            <a href="{{ route('guest.elearning.ebook', $ebook->ebook_id) }}"
                                                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                                                <i class="fas fa-book-open mr-2"></i>
                                                {{ __('elearning.materi.baca_ebook') }}
                                            </a>
                                        @else
                                            <div
                                                class="inline-flex w-full items-center justify-center rounded-xl bg-gray-400 px-4 py-3 font-medium text-white">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                {{ __('elearning.materi.file_error') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl bg-gray-100 p-8 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                            <i class="fas fa-book text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.ebook_tidak_tersedia') }}</h3>
                        <p class="text-gray-600">{{ __('elearning.materi.materi_tidak_memiliki_ebook') }}</p>
                    </div>
                @endif
            </div>

            {{-- Virtual Living Museum Tab Content --}}
            <div id="museum-content" class="tab-content hidden">
                @if ($materi->situsPeninggalan->count() > 0)
                    <h2 class="mb-6 text-center text-xl font-bold text-gray-900">{{ config('app.name') }}</h2>

                    @if (!$museum_available)
                        <div class="rounded-2xl bg-gray-100 p-8 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                                <i class="fas fa-lock text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.museum_terkunci', ['appName' => config('app.name')]) }}</h3>
                            <p class="text-gray-600">{{ __('elearning.materi.baca_semua_ebook_terlebih_dulu', ['appName' => config('app.name')]) }}</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($materi->situsPeninggalan as $situs)
                                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                                    <div class="flex">
                                        {{-- Situs Image --}}
                                        <div class="h-24 w-24 flex-shrink-0">
                                            <img src="{{ $situs->thumbnailUrl }}" alt="{{ $situs->nama }}"
                                                class="h-full w-full object-cover">
                                        </div>

                                        {{-- Situs Content --}}
                                        <div class="flex-1 p-4">
                                            <h3 class="mb-1 font-semibold text-gray-900">{{ $situs->nama }}</h3>
                                            <p class="mb-3 text-sm text-gray-600">{{ $situs->alamat }}</p>

                                            <a href="{{ route('guest.situs.detail', $situs->situs_id) }}"
                                                class="inline-flex items-center rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-orange-700">
                                                <i class="fas fa-eye mr-1"></i>
                                                {{ __('elearning.materi.kunjungi') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl bg-gray-100 p-8 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                            <i class="fas fa-map-marker-alt text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.museum_tidak_tersedia', ['appName' => config('app.name')]) }}
                        </h3>
                        <p class="text-gray-600">{{ __('elearning.materi.materi_tidak_memiliki_museum', ['appName' => config('app.name')]) }}</p>
                    </div>
                @endif
            </div>

            {{-- Post Test Tab Content --}}
            <div id="posttest-content" class="tab-content hidden">
                @if ($materi->posttest->count() > 0)
                    <h2 class="mb-6 text-center text-xl font-bold text-gray-900">{{ __('elearning.materi.post_test') }}</h2>

                    @if (!$posttest_available)
                        <div class="rounded-2xl bg-gray-100 p-8 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                                <i class="fas fa-lock text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.posttest_terkunci') }}</h3>
                            <p class="text-gray-600">{{ __('elearning.materi.kunjungi_semua_museum', ['appName' => config('app.name')]) }}</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                            <div class="mb-6 rounded-2xl bg-gray-50 p-6">
                                <p class="text-center leading-relaxed text-gray-700">
                                    {{ __('elearning.materi.akan_mengerjakan_pretest', ['materi' => $materi->judul]) }}
                                </p>
                            </div>

                            <div class="text-center">
                                @if ($posttest_completed)
                                    <a href="{{ route('guest.elearning.posttest', $materi->materi_id) }}"
                                        class="inline-flex items-center rounded-xl bg-green-600 px-8 py-3 font-medium text-white transition-colors hover:bg-green-700">
                                        <i class="fas fa-eye mr-2"></i>
                                        {{ __('elearning.lihat_hasil') }}
                                    </a>

                                    <!-- Rekap Section -->
                                    <div class="mt-8">
                                        <h3 class="mb-4 text-lg font-bold text-gray-900">{{ __('elearning.materi.rekap') }}</h3>

                                        <div class="mb-6 grid grid-cols-2 gap-4">
                                            <!-- Pre-test Score -->
                                            <div class="rounded-lg bg-gray-100 p-4">
                                                <div class="mb-1 text-sm text-gray-600">{{ __('elearning.materi.nilai_pretest') }}</div>
                                                <div class="text-2xl font-bold">
                                                    @php
                                                        $pretestAnswers = \App\Models\JawabanUser::where(
                                                            'user_id',
                                                            auth()->id(),
                                                        )
                                                            ->where('materi_id', $materi->materi_id)
                                                            ->where('jenis', 'pretest')
                                                            ->get();

                                                        $totalPretest = $pretestAnswers->count();
                                                        $correctPretest = $pretestAnswers
                                                            ->where('benar', true)
                                                            ->count();

                                                        $pretestScore =
                                                            $totalPretest > 0
                                                                ? number_format(
                                                                    ($correctPretest / $totalPretest) * 100,
                                                                    1,
                                                                )
                                                                : 0;
                                                    @endphp
                                                    {{ $pretestScore }}
                                                </div>
                                            </div>

                                            <!-- Post-test Score -->
                                            <div class="rounded-lg bg-gray-100 p-4">
                                                <div class="mb-1 text-sm text-gray-600">{{ __('elearning.materi.nilai_posttest') }}</div>
                                                <div class="flex items-center justify-center text-2xl font-bold">
                                                    @php
                                                        $posttestAnswers = \App\Models\JawabanUser::where(
                                                            'user_id',
                                                            auth()->id(),
                                                        )
                                                            ->where('materi_id', $materi->materi_id)
                                                            ->where('jenis', 'posttest')
                                                            ->get();

                                                        $totalPosttest = $posttestAnswers->count();
                                                        $correctPosttest = $posttestAnswers
                                                            ->where('benar', true)
                                                            ->count();

                                                        $posttestScore =
                                                            $totalPosttest > 0
                                                                ? number_format(
                                                                    ($correctPosttest / $totalPosttest) * 100,
                                                                    1,
                                                                )
                                                                : 0;
                                                    @endphp
                                                    {{ $posttestScore }}
                                                    @if ($pretestScore < $posttestScore)
                                                        <i class="fas fa-chart-line ml-2 text-green-500"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tugas Section -->
                                        @if ($materi->tugas->count() > 0)
                                            <div class="mb-6">
                                                <h4 class="mb-2 text-center font-medium">{{ __('elearning.materi.tugas') }}</h4>
                                                <div class="rounded-lg bg-gray-100 p-4">
                                                    <p class="mb-3">{{ __('elearning.materi.beberapa_tugas_harus_diselesaikan') }}</p>
                                                    <div class="text-center">
                                                        <a href="{{ route('guest.elearning.tugas', $materi->materi_id) }}"
                                                            class="inline-flex items-center rounded-lg bg-blue-500 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-600">
                                                            {{ __('elearning.lihat') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Progress Chart -->
                                        <div class="mb-6">
                                            <h4 class="mb-2 text-center font-medium">{{ __('elearning.materi.perkembangan_anda') }}</h4>
                                            <div
                                                class="flex h-24 items-center justify-center rounded-lg bg-gray-100 p-4">
                                                <div class="relative w-full">
                                                    <div
                                                        class="absolute left-0 top-1/2 h-3 w-3 -translate-y-1/2 transform rounded-full bg-black">
                                                    </div>
                                                    <div
                                                        class="absolute right-0 top-1/2 h-3 w-3 -translate-y-1/2 transform rounded-full bg-black">
                                                    </div>
                                                    <div
                                                        class="absolute top-1/2 h-px w-full -translate-y-1/2 transform bg-black">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Next Material -->
                                        @if ($materi->urutan < \App\Models\Materi::max('urutan'))
                                            @php
                                                $nextMateri = \App\Models\Materi::where(
                                                    'urutan',
                                                    $materi->urutan + 1,
                                                )->first();
                                            @endphp
                                            @if ($nextMateri)
                                                <div>
                                                    <h4 class="mb-2 text-center font-medium">{{ __('elearning.materi.siap_ke_materi_berikutnya') }}</h4>
                                                    <a href="{{ route('guest.elearning.materi', $nextMateri->materi_id) }}"
                                                        class="block overflow-hidden rounded-lg bg-gray-100">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="flex h-16 w-16 items-center justify-center bg-gray-200">
                                                                @if ($nextMateri->gambar_sampul)
                                                                    <img src="{{ asset('storage/' . $nextMateri->gambar_sampul) }}"
                                                                        alt="{{ $nextMateri->judul }}"
                                                                        class="h-full w-full object-cover">
                                                                @else
                                                                    <i class="fas fa-book text-gray-400"></i>
                                                                @endif
                                                            </div>
                                                            <div class="p-3">
                                                                <div class="font-medium">{{ $nextMateri->judul }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <a href="{{ route('guest.elearning.posttest', $materi->materi_id) }}"
                                        class="inline-flex items-center rounded-xl bg-blue-600 px-8 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                                        {{ __('elearning.mulai') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl bg-gray-100 p-8 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200">
                            <i class="fas fa-clipboard-check text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.materi.post_test_tidak_tersedia') }}</h3>
                        <p class="text-gray-600">{{ __('elearning.materi.materi_tidak_memiliki_posttest') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tab JavaScript --}}
    <script>
        function switchTab(tabName) {
            // Check if tab is available
            const tabButton = document.getElementById(tabName + '-tab');
            if (tabButton.classList.contains('opacity-50')) {
                return; // Don't switch if tab is locked
            }

            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('bg-white/20');
            });

            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');

            // Add active class to selected tab button
            document.getElementById(tabName + '-tab').classList.add('bg-white/20');
        }

        // Initialize tab based on query param
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil query string ?tab=xxx
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab');
            const allowedTabs = ['pretest', 'ebook', 'museum', 'posttest'];
            if (tab && allowedTabs.includes(tab)) {
                switchTab(tab);
            } else {
                switchTab('pretest');
            }
        });
    </script>
</x-elearning-layout>