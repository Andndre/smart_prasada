<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800"/>
                        </a>
                    @else
                        <a href="{{ route('guest.home') }}">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800"/>
                        </a>
                    @endif
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->user()->role === 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <!-- Kelola Dropdown -->
                        <div class="relative h-full flex items-center" x-data="{ open: false }"
                             style="overflow: visible">
                            <button @click="open = !open"
                                    :class="{'text-gray-900 border-b-2 border-blue-500': open || isRouteActive('/admin/users') || isRouteActive('/admin/materi') || isRouteActive('/admin/situs') || isRouteActive('/admin/virtual-living-museum') || isRouteActive('/admin/reports') || isRouteActive('/admin/feedback') || isRouteActive('/admin/riwayat-pengembang') || isRouteActive('/admin/video-peninggalan')}"
                                    x-data="{ isRouteActive(path) { return window.location.pathname.startsWith(path) } }"
                                    class="inline-flex items-center px-1 pt-1 h-full text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                <span>Kelola</span>
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path :class="{'rotate-180': open, 'rotate-0': !open}"
                                          class="transition-transform duration-200" stroke-linecap="round"
                                          stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 mt-80 w-56 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                                 style="min-width: 14rem">
                                <div class="py-1">
                                    <x-dropdown-link :href="route('admin.users')"
                                                     :active="request()->routeIs('admin.users*')">
                                        <i class="fas fa-users mr-2 w-5 text-center"></i> Kelola Pengguna
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.materi')"
                                                     :active="request()->routeIs('admin.materi*')">
                                        <i class="fas fa-book mr-2 w-5 text-center"></i> Kelola Materi
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.situs')"
                                                     :active="request()->routeIs('admin.situs*')">
                                        <i class="fas fa-landmark mr-2 w-5 text-center"></i> Kelola Situs
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.virtual-museum')"
                                                     :active="request()->routeIs('admin.virtual-museum*')">
                                        <i class="fas fa-university mr-2 w-5 text-center"></i> Virtual Living Museum
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.reports')"
                                                     :active="request()->routeIs('admin.reports*')">
                                        <i class="fas fa-file-alt mr-2 w-5 text-center"></i> Kelola Laporan
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.feedback')"
                                                     :active="request()->routeIs('admin.feedback*')">
                                        <i class="fas fa-comment-alt mr-2 w-5 text-center"></i> Kritik & Saran
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.riwayat-pengembang')"
                                                     :active="request()->routeIs('admin.riwayat-pengembang*')">
                                        <i class="fas fa-history mr-2 w-5 text-center"></i> Riwayat Pengembang
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.video-peninggalan.index')"
                                                     :active="request()->routeIs('admin.video-peninggalan*')">
                                        <i class="fas fa-video mr-2 w-5 text-center"></i> Video Peninggalan
                                    </x-dropdown-link>
                                </div>
                            </div>
                        </div>
                    @else
                        <x-nav-link :href="route('guest.home')" :active="request()->routeIs('guest.home')">
                            {{ __('Home') }}
                        </x-nav-link>
                        <x-nav-link :href="route('guest.panduan')" :active="request()->routeIs('guest.panduan')">
                            {{ __('Guide') }}
                        </x-nav-link>
                        <x-nav-link :href="route('guest.pengembang')" :active="request()->routeIs('guest.pengembang')">
                            {{ __('app.developers') }}
                        </x-nav-link>
                        <x-nav-link :href="route('guest.vr.maps')" :active="request()->routeIs('guest.vr.maps')">
                            VR
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users*')">
                    Kelola Pengguna
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.materi')" :active="request()->routeIs('admin.materi*')">
                    Kelola Materi
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.situs')" :active="request()->routeIs('admin.situs*')">
                    Kelola Situs
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.virtual-museum')"
                                       :active="request()->routeIs('admin.virtual-museum*')">
                    Virtual Living Museum
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports*')">
                    Kelola Laporan
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.feedback')" :active="request()->routeIs('admin.feedback*')">
                    Kritik & Saran
                </x-responsive-nav-link>
                {{--                Riwayat pengembang--}}
                <x-responsive-nav-link :href="route('admin.riwayat-pengembang')"
                                       :active="request()->routeIs('admin.riwayat-pengembang*')">
                    Riwayat Pengembang
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.video-peninggalan.index')"
                                       :active="request()->routeIs('admin.video-peninggalan*')">
                    Video Peninggalan
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('guest.home')" :active="request()->routeIs('guest.home')">
                    {{ __('Home') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('guest.panduan')" :active="request()->routeIs('guest.panduan')">
                    {{ __('Guide') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('guest.pengembang')"
                                       :active="request()->routeIs('guest.pengembang')">
                    {{ __('app.developers') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('guest.vr.maps')" :active="request()->routeIs('guest.vr.maps')">
                    VR
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
