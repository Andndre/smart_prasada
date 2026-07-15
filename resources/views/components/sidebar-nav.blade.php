<aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-gray-200 bg-white shadow-lg md:flex">
    <div class="flex h-16 items-center justify-between border-b border-gray-200 bg-primary px-4">
        <a href="{{ route('guest.home') }}" class="flex items-center gap-3">
            <x-application-logo class="h-8 w-auto fill-current text-white" />
            <span class="text-lg font-semibold text-white">{{ config('app.name', 'VLM') }}</span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <div class="space-y-1 px-3">
            <a href="{{ route('guest.home') }}"
                class="{{ request()->routeIs('guest.home') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors">
                <i class="fas fa-home w-5 text-center"></i>
                <span>{{ __('app.home') }}</span>
            </a>
            <a href="{{ route('guest.panduan') }}"
                class="{{ request()->routeIs('guest.panduan') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors">
                <i class="fas fa-book w-5 text-center"></i>
                <span>{{ __('app.guide') }}</span>
            </a>
            <a href="{{ route('guest.pengembang') }}"
                class="{{ request()->routeIs('guest.pengembang') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors">
                <i class="fas fa-code w-5 text-center"></i>
                <span>{{ __('app.developer_info') }}</span>
            </a>
            <a href="{{ route('guest.pengaturan') }}"
                class="{{ request()->routeIs('guest.pengaturan') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors">
                <i class="fas fa-cog w-5 text-center"></i>
                <span>{{ __('app.settings') }}</span>
            </a>
        </div>

        <div class="mt-6 space-y-1 px-3">
            <a href="{{ route('guest.vr.maps') }}"
                class="hover:bg-primary-dark flex items-center gap-3 rounded-lg bg-primary px-4 py-3 font-medium text-white shadow-lg transition-colors">
                <i class="fas fa-vr-cardboard w-5 text-center"></i>
                <span>Virtual Museum (VR)</span>
            </a>
        </div>
    </nav>

    <div class="border-t border-gray-200 p-4">
        <div class="mb-4 flex items-center gap-3">
            <x-user-profile-navbar class="h-10 w-10" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-100">
                <i class="fas fa-sign-out-alt"></i>
                <span>{{ __('app.logout') }}</span>
            </button>
        </form>
    </div>
</aside>

<aside x-data="{ open: false }" x-init="$nextTick(() => open = false)" x-cloak
    :class="{ 'translate-x-0': open, '-translate-x-full': !open }"
    class="fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col border-r border-gray-200 bg-white shadow-lg transition-transform duration-300 ease-in-out lg:hidden"
    @toggle-sidebar.window="open = !open">
    <div class="flex h-16 items-center justify-between border-b border-gray-200 bg-primary px-4">
        <a href="{{ route('guest.home') }}" class="flex items-center gap-3">
            <x-application-logo class="h-8 w-auto fill-current text-white" />
            <span class="text-lg font-semibold text-white">{{ config('app.name', 'VLM') }}</span>
        </a>
        <button @click="open = false" class="text-white hover:text-gray-200">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        <div class="space-y-1 px-3">
            <a href="{{ route('guest.home') }}"
                class="{{ request()->routeIs('guest.home') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
                @click="open = false">
                <i class="fas fa-home w-5 text-center"></i>
                <span>{{ __('app.home') }}</span>
            </a>
            <a href="{{ route('guest.panduan') }}"
                class="{{ request()->routeIs('guest.panduan') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
                @click="open = false">
                <i class="fas fa-book w-5 text-center"></i>
                <span>{{ __('app.guide') }}</span>
            </a>
            <a href="{{ route('guest.pengembang') }}"
                class="{{ request()->routeIs('guest.pengembang') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
                @click="open = false">
                <i class="fas fa-code w-5 text-center"></i>
                <span>{{ __('app.developer_info') }}</span>
            </a>
            <a href="{{ route('guest.pengaturan') }}"
                class="{{ request()->routeIs('guest.pengaturan') ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
                @click="open = false">
                <i class="fas fa-cog w-5 text-center"></i>
                <span>{{ __('app.settings') }}</span>
            </a>
        </div>

        <div class="mt-6 space-y-1 px-3">
            <a href="{{ route('guest.vr.maps') }}"
                class="hover:bg-primary-dark flex items-center gap-3 rounded-lg bg-primary px-4 py-3 font-medium text-white shadow-lg transition-colors"
                @click="open = false">
                <i class="fas fa-vr-cardboard w-5 text-center"></i>
                <span>Virtual Museum (VR)</span>
            </a>
        </div>
    </nav>

    <div class="border-t border-gray-200 p-4">
        <div class="mb-4 flex items-center gap-3">
            <x-user-profile-navbar class="h-10 w-10" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-100">
                <i class="fas fa-sign-out-alt"></i>
                <span>{{ __('app.logout') }}</span>
            </button>
        </form>
    </div>
</aside>

<div x-data="{ open: false }" x-init="$nextTick(() => open = false)" x-cloak @toggle-sidebar.window="open = !open">
    <div x-show="open" x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="open = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>
</div>
