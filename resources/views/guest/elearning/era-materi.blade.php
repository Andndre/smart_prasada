<x-elearning-layout>
    <div class="bg-primary px-6 py-6 text-white">
        <div class="mb-6 flex items-center justify-between">
            <button class="back-button rounded-full p-2 transition-colors hover:bg-white/10"
                data-fallback-url="{{ route('guest.elearning') }}">
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

        <p class="text-sm text-blue-100">{{ __('elearning.era_materi.daftar_materi_era') }}</p>
        <h1 class="mt-1 text-2xl font-bold">
            @if ($era)
                Era {{ $era->kode }}. {{ $era->nama }}
            @else
                {{ __('elearning.era_materi.materi_lainnya') }}
            @endif
        </h1>

        @if ($era && $era->rentang_waktu)
            <p class="mt-1 text-sm text-blue-100">{{ $era->rentang_waktu }}</p>
        @endif

        <div class="pt-5">
            <div class="mb-4 space-y-2.5 rounded-xl bg-white/10 px-4 py-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-100">{{ __('elearning.era_materi.progress_era') }}</span>
                    <span class="font-semibold text-white">{{ $progressPercentage }}%</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-white/30">
                    <div class="h-full rounded-full bg-white" style="width: {{ $progressPercentage }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative -mt-6 min-h-screen rounded-t-3xl bg-white px-6 py-6">
        @if ($materis->isEmpty())
            <div class="rounded-2xl bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                    <i class="fas fa-book text-2xl text-gray-400"></i>
                </div>
                <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('elearning.era_materi.belum_ada_materi') }}
                </h3>
                <p class="text-gray-600">{{ __('elearning.era_materi.materi_segment_available_soon') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($materis as $materi)
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <div class="flex">
                            <div class="h-20 w-20 flex-shrink-0">
                                @if ($materi->gambar_sampul)
                                    <img src="{{ asset('storage/' . $materi->gambar_sampul) }}"
                                        alt="{{ $materi->judul }}" class="h-full w-full object-cover">
                                @else
                                    <div
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-400 to-blue-600">
                                        <i class="fas fa-book text-xl text-white"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 p-4">
                                @if ($materi->bab)
                                    <span
                                        class="mb-1 inline-block rounded bg-blue-50 px-1.5 py-0.5 text-[11px] font-medium text-blue-600">{{ __('elearning.bab') }}
                                        {{ $materi->bab }}</span>
                                @endif
                                <h3 class="mb-1 font-semibold text-gray-900">{{ $materi->judul }}</h3>
                                <p class="mb-3 line-clamp-2 text-sm text-gray-600">{{ $materi->deskripsi }}</p>

                                @if ($materi->is_available)
                                    <a href="{{ route('guest.elearning.materi', $materi->materi_id) }}"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-blue-700">
                                        @if ($materi->is_completed)
                                            <i class="fas fa-redo mr-1"></i>
                                            {{ __('elearning.lihat') }}
                                        @else
                                            <i class="fas fa-play mr-1"></i>
                                            {{ __('elearning.mulai') }}
                                        @endif
                                    </a>
                                @else
                                    <div
                                        class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-400">
                                        <i class="fas fa-lock mr-1"></i>
                                        {{ __('elearning.terkunci') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-elearning-layout>
