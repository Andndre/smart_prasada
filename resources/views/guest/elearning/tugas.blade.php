<x-elearning-layout>
    {{-- Header Section --}}
    <div class="px-6 py-6 bg-primary text-white">
        <div class="flex justify-between items-center mb-6">
            <button class="back-button p-2 hover:bg-white/10 rounded-full transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </button>
            <a href="{{ route('profile.edit') }}" class="w-12 h-12 rounded-full overflow-hidden border-2 border-white/30 hover:border-white/50 transition-colors">
                @if(auth()->user()->profile_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile Picture" class="w-full h-full object-cover" />
                @else
                    <img src="{{ asset('images/placeholder/profile-picture.png') }}" alt="Profile Picture" class="w-full h-full object-cover" />
                @endif
            </a>
        </div>

        <div class="mb-8">
            <div class="flex items-center space-x-2 mb-2">
                <i class="fas fa-tasks text-yellow-400"></i>
                <span class="text-sm opacity-90">{{ __('elearning.tugas.tugas') }}</span>
            </div>
            <h1 class="text-2xl font-bold">{{ $materi->judul }}</h1>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="bg-white rounded-t-3xl -mt-6 relative min-h-screen">
        <div class="px-6 py-8">
            <h2 class="text-xl font-bold text-center text-gray-900 mb-6">{{ __('elearning.tugas.daftar_tugas') }}</h2>

            @if($materi->tugas->count() > 0)
                <div class="space-y-4">
                    @foreach($materi->tugas as $tugas)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-2">{{ $tugas->judul }}</h3>
                                <div class="prose prose-sm max-w-none">
                                    {{ $tugas->deskripsi }}
                                </div>

                                @if($tugas->gambar)
                                    <div class="mt-4">
                                        <img src="{{ $tugas->gambarUrl }}" alt="{{ $tugas->judul }}" class="w-full h-auto rounded-lg">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('elearning.tugas.tidak_ada_tugas') }}</h3>
                    <p class="text-gray-600">{{ __('elearning.tugas.materi_tidak_memiliki_tugas') }}</p>
                </div>
            @endif

            <div class="mt-8 text-center">
                <a href="{{ route('guest.elearning.materi', $materi->materi_id) }}"
                   class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-xl hover:bg-primary-dark transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('elearning.tugas.kembali_ke_materi') }}
                </a>
            </div>
        </div>
    </div>
</x-elearning-layout>