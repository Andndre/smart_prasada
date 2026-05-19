<x-guest-layout>
    <div class="px-6 py-6 bg-primary text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <button class="back-button mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <h1 class="text-xl font-bold">{{ __('laporan-peninggalan.report_heritage') }}</h1>
            </div>
            <a href="{{ route('guest.laporan-peninggalan.create') }}"
               class="bg-white text-primary px-4 py-2 rounded-lg font-medium text-sm flex items-center">
                <i class="fas fa-plus mr-2"></i>
                {{ __('laporan-peninggalan.report') }}
            </a>
        </div>
    </div>

    <div class="px-6 pt-6 pb-24 bg-gray-50 min-h-screen">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Reports List --}}
        <div class="space-y-4">
            @forelse ($laporan as $item)
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    {{-- Header --}}
                    <div class="flex items-start mb-3">
                        {{-- User Profile Photo --}}
                        <div class="flex-shrink-0 mr-3">
                            @if($item->user->profile_photo)
                                <img src="{{ asset('storage/' . $item->user->profile_photo) }}" alt="User"
                                     class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $item->nama_peninggalan }}</h3>
                                    <p class="text-sm text-gray-600">Oleh {{ $item->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="px-2 py-1 bg-gray-100 text-xs text-gray-600 rounded-md">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ \Illuminate\Support\Str::limit($item->alamat, 20) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description (truncated) --}}
                    <p class="text-sm text-gray-700 mb-3">
                        {{ \Illuminate\Support\Str::limit($item->deskripsi, 150) }}
                        @if(strlen($item->deskripsi) > 150)
                            <a href="{{ route('guest.laporan-peninggalan.show', $item->laporan_id) }}"
                               class="text-primary hover:underline">{{ __('laporan-peninggalan.read_more') }}</a>
                        @endif
                    </p>

                    {{-- Images --}}
                    @if($item->laporanGambar->count() > 0)
                        <div class="mb-3 flex space-x-2 overflow-x-auto pb-2">
                            @foreach($item->laporanGambar as $image)
                                <a href="{{ asset('storage/' . $image->path_gambar) }}" target="_blank"
                                   class="flex-shrink-0">
                                    <img src="{{ asset('storage/' . $image->path_gambar) }}" alt="Peninggalan"
                                         class="h-24 w-32 object-cover rounded-lg">
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <div class="flex space-x-6">
                            <form class="like-form" data-id="{{ $item->laporan_id }}"
                                  action="{{ route('guest.laporan-peninggalan.like', $item->laporan_id) }}"
                                  method="POST">
                                @csrf
                                <button type="submit"
                                        class="flex items-center space-x-1 text-gray-700 hover:text-primary transition-colors">
                                    <i class="like-icon {{ $item->laporanSuka->where('user_id', auth()->id())->count() > 0 ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                    <span class="like-count text-xs">{{ $item->like_count }}</span>
                                </button>
                            </form>
                            <a href="{{ route('guest.laporan-peninggalan.show', $item->laporan_id) }}#comments"
                               class="flex items-center space-x-1 text-gray-700 hover:text-primary transition-colors">
                                <i class="far fa-comment mr-1"></i>
                                <span class="text-xs">{{ $item->comment_count }}</span>
                            </a>
                        </div>
                        <a href="{{ route('guest.laporan-peninggalan.show', $item->laporan_id) }}"
                           class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-sm text-gray-700 rounded-md transition-colors">
                            {{ __('laporan-peninggalan.show_detail') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <i class="fas fa-info-circle text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('laporan-peninggalan.no_heritage_yet') }}</h3>
                    <p class="text-gray-600 mb-4"> Jadilah yang pertama melaporkan peninggalan.</p>
                    <a href="{{ route('guest.laporan-peninggalan.create') }}"
                       class="inline-block px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium">
                        {{ __('laporan-peninggalan.be_the_first_to_report') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $laporan->links() }}
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden"/>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Handle like functionality with AJAX
                const likeForms = document.querySelectorAll('.like-form');

                likeForms.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const reportId = this.dataset.id;
                        const likeIcon = this.querySelector('.like-icon');
                        const likeCount = this.querySelector('.like-count');

                        fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({})
                        })
                            .then(response => response.json())
                            .then(data => {
                                // Toggle like icon
                                if (data.action === 'liked') {
                                    likeIcon.classList.remove('far');
                                    likeIcon.classList.add('fas');
                                } else {
                                    likeIcon.classList.remove('fas');
                                    likeIcon.classList.add('far');
                                }

                                // Update like count
                                likeCount.textContent = data.like_count;
                            })
                            .catch(error => console.error('Error:', error));
                    });
                });
            });
        </script>
    @endpush
</x-guest-layout>
