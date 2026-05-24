<x-guest-layout>
    <div class="bg-primary p-4 text-white">
        <div class="mb-4 flex items-center">
            <button class="back-button mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </button>
            <h1 class="text-xl font-bold">{{ __('maps.virtual_living_museum') }}</h1>
        </div>

        <!-- Search Bar -->
        <form id="search-form" action="{{ route('guest.maps.peninggalan') }}" method="GET" class="relative">
            <div class="flex items-center rounded-full bg-white/20 p-2">
                <div class="ml-2 mr-2 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input id="search-input" name="q" type="search" inputmode="search" autocomplete="off"
                    value="{{ $searchQuery ?? '' }}" placeholder="{{ __('maps.search_placeholder_situs') }}"
                    class="w-full border-none bg-transparent text-white placeholder-white/70 focus:outline-none focus:ring-0"
                    style="touch-action: manipulation;" aria-label="{{ __('maps.search_placeholder_situs') }}">
                @if (isset($searchQuery) && !empty($searchQuery))
                    <a href="{{ route('guest.maps.peninggalan') }}" class="mr-2 text-white/70 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Content -->
    <div class="p-4 pb-24">
        <!-- Search Results Header -->
        @if (isset($searchQuery) && !empty($searchQuery))
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">@lang('maps.search_results_for', ['query' => $searchQuery])
                </h2>
                <p class="text-sm text-gray-500">{{ $situs->count() }}
                    {{ $situs->count() == 1 ? __('maps.result_found') : __('maps.results_found') }}
                </p>
            </div>
        @endif

        <!-- Situs Grid -->
        @if ($situs->isNotEmpty())
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($situs as $s)
                    <a href="{{ route('guest.situs.detail', $s->situs_id) }}"
                        class="group overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                        <div class="relative aspect-[4/3] overflow-hidden">
                            <img src="{{ $s->thumbnail_url }}" alt="{{ $s->nama }}"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>

                            <!-- Status Badge -->
                            <div class="absolute right-2 top-2">
                                @if ($s->is_unlocked)
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-500/90 px-2.5 py-1 text-xs font-medium text-white shadow-sm backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-3 w-3" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ __('maps.unlocked') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-500/90 px-2.5 py-1 text-xs font-medium text-white shadow-sm backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-3 w-3" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ __('maps.locked') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Content Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <h3 class="text-lg font-bold text-white">{{ $s->nama }}</h3>
                                <div class="mt-1 flex items-center gap-3 text-sm text-white/90">
                                    @if ($s->museum_count > 0)
                                        <span class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ $s->museum_count }}
                                            {{ $s->museum_count == 1 ? __('maps.vr_spot') : __('maps.vr_spots') }}
                                        </span>
                                    @endif
                                    @if ($s->object_count > 0)
                                        <span class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.25 12c0-2.8 2.2-5 5-5h9.5c2.8 0 5 2.2 5 5v6c0 2.8-2.2 5-5 5h-9.5c-2.8 0-5-2.2-5-5v-6z" />
                                            </svg>
                                            {{ $s->object_count }}
                                            {{ $s->object_count == 1 ? __('maps.heritage_object') : __('maps.heritage_objects') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="py-16 text-center">
                <div class="mb-4 text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                        stroke="currentColor" class="mx-auto h-20 w-20">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 21v-8.25M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM3.75 12h.008v.008H3.75V12z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700">
                    @if (isset($searchQuery) && !empty($searchQuery))
                        {{ __('maps.no_situs_found') }}
                    @else
                        {{ __('maps.no_situs_available') }}
                    @endif
                </h3>
                <p class="mt-2 text-gray-500">
                    @if (isset($searchQuery) && !empty($searchQuery))
                        {{ __('maps.try_different_keywords') }}
                    @else
                        {{ __('maps.check_back_later') }}
                    @endif
                </p>
                @if (isset($searchQuery) && !empty($searchQuery))
                    <a href="{{ route('guest.maps.peninggalan') }}"
                        class="mt-6 inline-flex items-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('maps.clear_search') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-guest-layout>