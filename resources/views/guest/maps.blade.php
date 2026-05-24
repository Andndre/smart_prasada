<x-guest-layout>
    {{-- Header Section --}}
    <div class="px-6 py-6 bg-primary text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <button type="button"
                    class="back-button w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-arrow-left text-white"></i>
                </button>
                <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white/30">
                    @if (auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                            alt="{{ __('app.profile_picture') }}" class="w-full h-full object-cover" />
                    @else
                        <img src="{{ asset('images/placeholder/profile-picture.png') }}"
                            alt="{{ __('app.profile_picture') }}" class="w-full h-full object-cover" />
                    @endif
                </div>
            </div>

            <div class="pt-8 pb-4 flex items-center">
                <img src="{{ asset('images/mascot.png') }}" alt="{{ __('app.mascot') }}"
                    class="w-32 h-32 md:w-40 md:h-40 object-contain mr-4 drop-shadow-xl" />
                <div>
                    <h2 class="text-lg md:text-xl font-bold mb-2">{{ __('app.maps_greeting') }}</h2>
                    <p class="text-white/90 text-sm md:text-base leading-relaxed">
                        {{ __('app.maps_description') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Menu Section --}}
    <div class="px-6 py-8 bg-white rounded-t-3xl -mt-6 relative min-h-[60vh] flex flex-col items-center">
        <div class="max-w-7xl mx-auto w-full flex flex-col items-center">
            <h3 class="text-lg font-bold text-gray-900 mb-6 text-center">{{ __('app.maps_choose_menu') }}</h3>
            <div class="flex w-full max-w-lg gap-8 justify-center">
                {{-- Maps --}}
                <a href="{{ route('guest.maps.view') }}"
                    class="flex flex-col items-center justify-center bg-gray-50 rounded-2xl shadow-md hover:shadow-lg transition w-full py-8 mx-2">
                    <img src="{{ asset('images/icons/maps-view.png') }}" alt="{{ __('app.maps_maps') }}"
                        class="w-16 h-16 mb-4" />
                    <span
                        class="text-base font-medium text-gray-700 text-center block mt-2">{{ __('app.maps_maps') }}</span>
                </a>
                {{-- Virtual Living Museum --}}
                <a href="{{ route('guest.maps.peninggalan') }}"
                    class="flex flex-col items-center justify-center bg-gray-50 rounded-2xl shadow-md hover:shadow-lg transition w-full py-8 mx-2">
                    <img src="{{ asset('images/icons/peninggalan.png') }}" alt="{{ __('app.maps_virtual_museum') }}"
                        class="w-16 h-16 mb-4" />
                    <span
                        class="text-base font-medium text-gray-700 text-center block mt-2">{{ __('app.maps_virtual_museum') }}</span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>