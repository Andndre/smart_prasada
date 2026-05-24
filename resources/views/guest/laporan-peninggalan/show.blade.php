<x-guest-layout>
    <div class="px-6 py-6 bg-primary text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center">
                <button class="back-button mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <h1 class="text-xl font-bold">{{ __('laporan-peninggalan.heritage_detail') }}</h1>
            </div>
        </div>
    </div>

    <div class="px-6 pt-6 pb-24 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
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

            {{-- Main Content --}}
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                {{-- Header --}}
                <div class="flex items-start mb-5">
                    <div class="flex-shrink-0 mr-3">
                        @if($laporan->user->profile_photo)
                            <img src="{{ asset('storage/' . $laporan->user->profile_photo) }}" alt="User"
                                class="w-12 h-12 rounded-full object-cover border border-gray-200">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900">{{ $laporan->nama_peninggalan }}</h2>
                        <p class="text-gray-600">{{ __('laporan-peninggalan.reported_by') }} {{ $laporan->user->name }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $laporan->created_at->format('d M Y, H:i') }}
                            ({{ $laporan->created_at->diffForHumans() }})</p>
                    </div>
                </div>

                {{-- Images Carousel --}}
                @if($laporan->laporanGambar->count() > 0)
                    <div class="mb-6">
                        <div class="gallery-carousel relative rounded-lg overflow-hidden">
                            <div class="gallery-container flex transition-transform duration-300 ease-in-out h-64">
                                @foreach($laporan->laporanGambar as $index => $image)
                                    <div class="gallery-item min-w-full h-full">
                                        <img src="{{ asset('storage/' . $image->path_gambar) }}"
                                            alt="Peninggalan {{ $index + 1 }}" class="w-full h-full object-contain bg-gray-100">
                                    </div>
                                @endforeach
                            </div>

                            @if($laporan->laporanGambar->count() > 1)
                                <button
                                    class="gallery-prev absolute left-0 top-1/2 -translate-y-1/2 bg-black/50 text-white w-10 h-10 rounded-full flex items-center justify-center">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button
                                    class="gallery-next absolute right-0 top-1/2 -translate-y-1/2 bg-black/50 text-white w-10 h-10 rounded-full flex items-center justify-center">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <div class="gallery-dots absolute bottom-2 left-0 right-0 flex justify-center space-x-1">
                                    @foreach($laporan->laporanGambar as $index => $image)
                                        <button
                                            class="gallery-dot w-2 h-2 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white/50' }}"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Location --}}
                <div class="mb-6">
                    <h3 class="text-gray-700 font-medium mb-2">Lokasi</h3>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="flex items-start mb-3">
                            <i class="fas fa-map-marker-alt text-red-500 mt-1 mr-2"></i>
                            <p class="text-gray-600">{{ $laporan->alamat }}</p>
                        </div>
                        <div id="map" class="h-48 w-full rounded-lg"></div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <h3 class="text-gray-700 font-medium mb-2">{{ __('laporan-peninggalan.description') }}</h3>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-600 whitespace-pre-line">{{ $laporan->deskripsi }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-between border-t border-gray-100 pt-4">
                    <div class="flex space-x-4">
                        <form id="like-form"
                            action="{{ route('guest.laporan-peninggalan.like', $laporan->laporan_id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="flex items-center space-x-1 text-gray-700 hover:text-primary transition-colors">
                                <i id="like-icon" class="{{ $userLiked ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                <span id="like-count" class="text-sm">{{ $laporan->like_count }}</span>
                            </button>
                        </form>
                        <button id="comment-button"
                            class="flex items-center space-x-1 text-gray-700 hover:text-primary transition-colors">
                            <i class="far fa-comment"></i>
                            <span class="text-sm">{{ $laporan->laporanKomentar->count() }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Comments Section --}}
            <div id="comments" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('laporan-peninggalan.comments') }}</h3>

                {{-- Comment Form --}}
                <form action="{{ route('guest.laporan-peninggalan.comment', $laporan->laporan_id) }}" method="POST"
                    class="mb-6">
                    @csrf
                    <div class="flex space-x-3">
                        <div class="flex-shrink-0">
                            @if(auth()->user()->profile_photo)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="User"
                                    class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <textarea id="komentar" name="komentar" rows="2"
                                class="w-full px-3 py-2 border {{ $errors->has('komentar') ? 'border-red-500' : 'border-gray-300' }} rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                                placeholder="{{ __('laporan-peninggalan.write_comment') }}..."></textarea>
                            @error('komentar')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            <button type="submit"
                                class="mt-2 px-4 py-1 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors text-sm">
                                {{ __('laporan-peninggalan.send') }}
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Comment List --}}
                <div class="space-y-4">
                    @forelse($laporan->laporanKomentar->sortByDesc('created_at') as $comment)
                        <div class="flex space-x-3">
                            <div class="flex-shrink-0">
                                @if($comment->user->profile_photo)
                                    <img src="{{ asset('storage/' . $comment->user->profile_photo) }}" alt="User"
                                        class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-500"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-3">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-medium text-gray-800">{{ $comment->user->name }}</h4>
                                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 text-gray-600">{{ $comment->komentar }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500">
                            <p>{{ __('laporan-peninggalan.no_comments_yet') }}.</p>
                            <p class="text-sm">{{ __('laporan-peninggalan.be_the_first_to_comment') }}!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Bottom Navigation --}}
        <x-bottom-nav class="md:hidden" />

        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        @endpush

        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Initialize map
                    const map = L.map('map').setView([{{ $laporan->lat }}, {{ $laporan->lng }}], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // Add marker for the location
                    L.marker([{{ $laporan->lat }}, {{ $laporan->lng }}]).addTo(map)
                        .bindPopup('{{ $laporan->nama_peninggalan }}');

                    // Handle image gallery carousel
                    const galleryContainer = document.querySelector('.gallery-container');
                    const galleryPrev = document.querySelector('.gallery-prev');
                    const galleryNext = document.querySelector('.gallery-next');
                    const galleryDots = document.querySelectorAll('.gallery-dot');

                    if (galleryContainer) {
                        const itemCount = document.querySelectorAll('.gallery-item').length;
                        let currentIndex = 0;

                        function goToSlide(index) {
                            if (index < 0) index = itemCount - 1;
                            if (index >= itemCount) index = 0;
                            currentIndex = index;

                            galleryContainer.style.transform = `translateX(-${currentIndex * 100}%)`;

                            // Update dots
                            galleryDots.forEach((dot, i) => {
                                dot.classList.toggle('bg-white', i === currentIndex);
                                dot.classList.toggle('bg-white/50', i !== currentIndex);
                            });
                        }

                        if (galleryPrev) {
                            galleryPrev.addEventListener('click', () => {
                                goToSlide(currentIndex - 1);
                            });
                        }

                        if (galleryNext) {
                            galleryNext.addEventListener('click', () => {
                                goToSlide(currentIndex + 1);
                            });
                        }

                        galleryDots.forEach((dot, i) => {
                            dot.addEventListener('click', () => {
                                goToSlide(i);
                            });
                        });
                    }

                    // Handle like action with AJAX
                    const likeForm = document.getElementById('like-form');
                    const likeIcon = document.getElementById('like-icon');
                    const likeCount = document.getElementById('like-count');

                    if (likeForm) {
                        likeForm.addEventListener('submit', function (e) {
                            e.preventDefault();

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
                    }

                    // Scroll to comment form when comment button is clicked
                    const commentButton = document.getElementById('comment-button');
                    if (commentButton) {
                        commentButton.addEventListener('click', () => {
                            document.getElementById('komentar').scrollIntoView({ behavior: 'smooth' });
                            document.getElementById('komentar').focus();
                        });
                    }
                });
            </script>
        @endpush
</x-guest-layout>