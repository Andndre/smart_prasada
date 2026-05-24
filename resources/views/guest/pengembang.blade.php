@php use Carbon\Carbon; @endphp
<x-guest-layout>
    {{-- Header Section --}}
    <div class="px-6 py-6 bg-primary text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <x-application-logo class="w-12 h-12"/>
                <x-user-profile-navbar/>
            </div>

            <div class="pt-8 pb-8">
                <h1 class="text-xl font-bold">{{ __('app.developer_info') }}</h1>
                <p class="text-sm opacity-90">{{ __('app.about_developer') }}</p>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="px-6 py-8 pb-24 bg-gray-50 rounded-t-3xl -mt-6 relative">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-6">
                {{-- Developer Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full overflow-hidden border-4 border-primary/20">
                            <img src="{{ asset('images/wayan-pardi.png') }}" alt="Developer"
                                 class="w-full h-full object-cover"/>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.developer_name') }}</h3>
                        <p class="text-gray-600 text-sm">{{ __('app.developer_role') }}</p>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('app.contact_info') }}</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 py-2">
                            <i class="fas fa-phone text-primary w-5"></i>
                            <span class="text-gray-700">+62 812-3443-1643</span>
                        </div>
                        <div class="flex items-center space-x-3 py-2">
                            <i class="fas fa-envelope text-primary w-5"></i>
                            <span class="text-gray-700">wayan.pardi@undiksha.ac.id</span>
                        </div>
                        <div class="flex items-center space-x-3 py-2">
                            <i class="fas fa-briefcase text-primary w-5"></i>
                            <span class="text-gray-700">{{ __('app.lecturer') }}</span>
                        </div>
                        <div class="flex items-start space-x-3 py-2">
                            <i class="fas fa-map-marker-alt text-primary w-5 mt-1"></i>
                            <div class="text-gray-700 flex-1">
                                <div>Perumahan Graha Adi Terrace Blok H Nomor 4 Banyuning, Buleleng, Bali</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Developer History --}}
                @if($riwayatPengembang->count() > 0)
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('app.development_history') }}</h3>
                        <div class="space-y-4">
                            @foreach($riwayatPengembang as $riwayat)
                                <div class="border-l-4 border-primary pl-4 py-1">
                                    <h4 class="font-medium text-gray-800">{{ $riwayat->judul }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ Carbon::parse($riwayat->tahun)->format('Y') }}
                                        @if($riwayat->tahun_selesai)
                                            - {{ Carbon::parse($riwayat->tahun_selesai)->format('Y') }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden"/>
</x-guest-layout>
