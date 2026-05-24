<x-guest-layout>
    {{-- Header Section --}}
    <div class="px-6 py-6 bg-primary text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <x-application-logo class="w-12 h-12" />
                <x-user-profile-navbar />
            </div>

            <div class="pt-8 pb-8">
                <h1 class="text-xl font-bold">{{ __('app.settings') }}</h1>
                <p class="text-sm opacity-90">{{ __('app.manage_preferences') }}</p>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="px-6 py-8 pb-24 bg-gray-50 rounded-t-3xl -mt-6 relative">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-4">
                {{-- Language Settings --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('app.language') }}</h3>
                    <div class="space-y-4">
                        <a href="{{ route('language.change', 'id') }}"
                            class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-language text-gray-400 w-5"></i>
                                <span class="text-gray-700">{{ __('app.indonesian') }}</span>
                            </div>
                            @if(app()->getLocale() == 'id')
                                <div class="w-5 h-5 border-2 border-primary rounded-full flex items-center justify-center">
                                    <div class="w-3 h-3 bg-primary rounded-full"></div>
                                </div>
                            @else
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full"></div>
                            @endif
                        </a>
                        <a href="{{ route('language.change', 'en') }}" class="flex items-center justify-between py-3">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-language text-gray-400 w-5"></i>
                                <span class="text-gray-700">{{ __('app.english') }}</span>
                            </div>
                            @if(app()->getLocale() == 'en')
                                <div class="w-5 h-5 border-2 border-primary rounded-full flex items-center justify-center">
                                    <div class="w-3 h-3 bg-primary rounded-full"></div>
                                </div>
                            @else
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full"></div>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- Menu Options --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="space-y-4">
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-user text-gray-400 w-5"></i>
                                <span class="text-gray-700">{{ __('app.profile') }}</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                        </a>
                        <a href="{{ route('guest.kritik-saran') }}"
                            class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-comment-alt text-gray-400 w-5"></i>
                                <span class="text-gray-700">{{ __('app.feedback') }}</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-between py-3">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-sign-out-alt text-red-400 w-5"></i>
                                    <span class="text-red-600">{{ __('app.logout') }}</span>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden" />
</x-guest-layout>