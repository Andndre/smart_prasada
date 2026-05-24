<x-guest-layout>
    {{-- Header Section --}}
    <div class="px-6 py-6 bg-primary text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <x-application-logo class="w-12 h-12" />
                <x-user-profile-navbar />
            </div>

            <div class="pt-8 pb-8">
                <h1 class="text-xl font-bold">{{ __('guides.app_guide') }}</h1>
                <p class="text-sm opacity-90">{{ __('guides.learn_how_to_use') }}</p>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="px-6 py-8 pb-24 bg-gray-50 rounded-t-3xl -mt-6 relative">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-4" x-data="{ activeGuide: '{{ $guides[0]['id'] }}' }">
                @foreach($guides as $guide)
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <button @click="activeGuide = (activeGuide === '{{ $guide['id'] }}' ? null : '{{ $guide['id'] }}')"
                            class="w-full p-4 flex items-center justify-between focus:outline-none"
                            :aria-expanded="activeGuide === '{{ $guide['id'] }}'">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 bg-primary rounded-full flex-shrink-0 flex items-center justify-center mr-4">
                                    <i class="fas {{ $guide['icon'] }} text-white text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="text-base font-semibold text-gray-800">{{ $guide['title'] }}</h3>
                                    <p class="text-xs text-gray-500">{{ $guide['description'] }}</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-500 transition-transform duration-200 transform"
                                :class="{ 'rotate-180': activeGuide === '{{ $guide['id'] }}' }" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="activeGuide === '{{ $guide['id'] }}'"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="px-4 pb-4 -mt-2 text-gray-600 text-sm">
                            <div class="pl-16 pr-2 space-y-5">
                                @foreach($guide['steps'] as $index => $step)
                                    <div class="flex items-start">
                                        <div
                                            class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center mr-4 mt-0.5 text-base">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-base">{{ $step['title'] }}</p>
                                            <p class="text-gray-600 text-sm mt-1">{{ $step['description'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden" />
</x-guest-layout>