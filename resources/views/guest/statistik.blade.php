<x-guest-layout>
    {{-- Header Section --}}
    <div class="px-6 py-6 bg-primary text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <button type="button" class="back-button p-2 hover:bg-white/10 rounded-full transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <a href="{{ route('profile.edit') }}"
                    class="w-12 h-12 rounded-full overflow-hidden border-2 border-white/30 hover:border-white/50 transition-colors">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile Picture"
                            class="w-full h-full object-cover" />
                    @else
                        <img src="{{ asset('images/placeholder/profile-picture.png') }}" alt="Profile Picture"
                            class="w-full h-full object-cover" />
                    @endif
                </a>
            </div>

            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">{{ __('app.statistics') }}</h1>
            </div>

            <!-- User's ranking showcase -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <!-- Profile Picture with Circular Progress -->
                    <div class="relative w-32 h-32 mx-auto mt-8">
                        <!-- Circular Progress Background -->
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <!-- Background Circle -->
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#e6e6e6" stroke-width="3"></circle>
                            <!-- Progress Circle -->
                            <circle class="text-green-500" cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor"
                                stroke-width="3" stroke-dasharray="{{ $userScore }}, 100" stroke-linecap="round">
                            </circle>
                        </svg>

                        <!-- Profile Picture -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-md">
                                @if(auth()->user()->profile_photo)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile Picture"
                                        class="w-full h-full object-cover" />
                                @else
                                    <img src="{{ asset('images/placeholder/profile-picture.png') }}" alt="Profile Picture"
                                        class="w-full h-full object-cover" />
                                @endif
                            </div>
                        </div>

                        <!-- Score Badge -->
                        <div
                            class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-white px-4 py-1 rounded-full shadow-md border border-gray-200 whitespace-nowrap">
                            <span class="font-bold text-gray-800 text-sm">{{ $userScore }}</span>
                            <span class="text-xs text-gray-500">/100</span>
                        </div>
                    </div>

                    <!-- Rank -->
                    <div class="text-center mt-4">
                        <h2 class="text-4xl font-bold text-white">
                            {{ $currentUserRank > 0 ? $currentUserRank : '-' }}{{ $currentUserRank === 1 ? 'st' : ($currentUserRank === 2 ? 'nd' : ($currentUserRank === 3 ? 'rd' : 'th')) }}
                        </h2>
                        <p class="text-white/80 mb-4">{{ __('app.your_rank') }}</p>
                        <a href="{{ route('guest.statistik.rapor') }}"
                            class="inline-flex items-center justify-center px-4 py-2 bg-white text-primary rounded-full text-sm font-medium shadow-sm hover:bg-gray-50 transition-colors">
                            <i class="fas fa-file-alt mr-2"></i> {{ __('app.view_report') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="px-6 py-8 bg-white rounded-t-3xl -mt-6 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('app.leaderboard') }}</h2>
            </div>

            <!-- Leaderboard -->
            <div class="space-y-4 pb-32">
                @foreach($topUsers as $user)
                    <div
                        class="flex items-center p-3 rounded-lg {{ $user['id'] === $currentUser->id ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }}">
                        <!-- Rank -->
                        <div class="w-8 text-center font-bold {{ $user['rank'] <= 3 ? 'text-blue-600' : 'text-gray-700' }}">
                            {{ $user['rank'] }}
                        </div>

                        <!-- User photo -->
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 ml-2">
                            @if($user['profile_photo'])
                                <img src="{{ asset('storage/' . $user['profile_photo']) }}" alt="{{ $user['name'] }}"
                                    class="w-full h-full object-cover" />
                            @else
                                <img src="{{ asset('images/placeholder/profile-picture.png') }}" alt="{{ $user['name'] }}"
                                    class="w-full h-full object-cover" />
                            @endif
                        </div>

                        <!-- User name -->
                        <div class="flex-1 ml-3">
                            <p class="font-medium text-gray-900">{{ $user['name'] }}</p>
                        </div>

                        <!-- Score -->
                        <div class="text-right">
                            <div class="font-bold text-gray-900">{{ $user['total_score'] ?? 0 }}</div>
                            <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-green-500 h-1.5 rounded-full"
                                    style="width: {{ $user['total_score'] ?? 0 }}%"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">/100</div>
                        </div>
                    </div>
                @endforeach

                @if(count($topUsers) === 0)
                    <div class="rounded-2xl bg-white p-12 text-center shadow-sm">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                            <i class="fas fa-trophy text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Belum Ada Data</h3>
                        <p class="text-gray-600">Belum ada data leaderboard</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden" />
</x-guest-layout>