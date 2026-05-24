<x-elearning-layout>
    {{-- Header Section --}}
    <div class="bg-primary px-6 py-6 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <button class="back-button rounded-full p-2 transition-colors hover:bg-white/10">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <div class="mx-4 flex-1 text-center">
                    <h1 class="text-lg font-bold">{{ $ebook->judul }}</h1>
                    <p class="text-sm opacity-90">
                        {{ $ebook->materi->judul }}
                        @if ($ebook->materi->bab)
                            &mdash; Bab {{ $ebook->materi->bab }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('profile.edit') }}"
                    class="h-12 w-12 overflow-hidden rounded-full border-2 border-white/30 transition-colors hover:border-white/50">
                    @if (auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile Picture"
                            class="h-full w-full object-cover" />
                    @else
                        <img src="{{ asset('images/placeholder/profile-picture.png') }}" alt="Profile Picture"
                            class="h-full w-full object-cover" />
                    @endif
                </a>
            </div>
        </div>
    </div>

    {{-- E-book Viewer Section --}}
    <div class="min-h-screen bg-white">
        <div class="container mx-auto px-2 py-3 md:px-4 md:py-6">
            {{-- Flipbook Container --}}
            <div id="flipbook-container"
                class="relative flex min-h-[85vh] w-full flex-col items-center justify-center overflow-hidden rounded-xl bg-gray-100 shadow-lg md:min-h-[80vh] md:rounded-2xl">
                <!-- Loading Spinner -->
                <div class="loading-container absolute inset-0 z-40 flex items-center justify-center bg-white/80">
                    <div class="text-center">
                        <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-b-2 border-blue-600"></div>
                        <p class="text-gray-600">{{ __('elearning.ebook.memuat_ebook') }}</p>
                    </div>
                </div>
                {{-- Flipbook Element --}}
                <div class="flex w-full items-center justify-center">
                    <div id="flipbook" class="flipbook relative hidden">
                        <!-- Overlay hanya menutupi flipbook -->
                        <div id="flipbook-start-overlay"
                            class="absolute left-0 top-0 z-50 flex h-full w-full cursor-pointer select-none flex-col items-center justify-center bg-black/60 text-center text-white"
                            style="backdrop-filter: blur(2px);">
                            <div>
                                <div class="mb-2 text-2xl font-bold">{{ __('elearning.ebook.klik_untuk_mulai') }}</div>
                                <div class="mb-4 text-base">{{ __('elearning.ebook.ebook_tampil_fullscreen') }}</div>
                                <button
                                    class="rounded-lg bg-blue-600 px-6 py-3 text-lg font-semibold text-white transition hover:bg-blue-700">{{ __('elearning.ebook.mulai_membaca') }}</button>
                            </div>
                        </div>
                        <!-- PageFlip container -->
                        <div id="pageflip-viewer" class="h-full w-full"></div>
                        <!-- Fullscreen controls (bottom, small, horizontal) -->
                        <div id="fullscreen-controls"
                            class="absolute bottom-4 left-1/2 z-50 flex hidden -translate-x-1/2 items-center gap-3">
                            <button id="fullscreen-prev"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-xl text-white transition hover:bg-black/70 focus:outline-none"
                                style="backdrop-filter: blur(2px);">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div id="fullscreen-page-indicator"
                                class="pointer-events-none select-none rounded-lg bg-white/80 px-1 py-1 text-base font-semibold text-black shadow">
                            </div>
                            <button id="fullscreen-next"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-xl text-white transition hover:bg-black/70 focus:outline-none"
                                style="backdrop-filter: blur(2px);">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Controls --}}
            <div class="mt-3 flex items-center justify-center space-x-2 md:mt-6 md:space-x-4">
                <button id="prev-page"
                    class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white transition-colors hover:bg-blue-700 disabled:bg-gray-300 md:px-4 md:py-2 md:text-base"
                    disabled>
                    <i class="fas fa-chevron-left mr-1 md:mr-2"></i>
                    <span class="hidden sm:inline">{{ __('elearning.ebook.sebelumnya') }}</span>
                    <span class="sm:hidden">Prev</span>
                </button>

                <div class="flex items-center space-x-1 md:space-x-2">
                    <span class="text-xs text-gray-600 md:text-sm">{{ __('elearning.ebook.halaman') }}</span>
                    <span id="current-page" class="text-sm font-semibold md:text-base">1</span>
                    <span class="text-xs text-gray-600 md:text-sm">{{ __('elearning.ebook.dari') }}</span>
                    <span id="total-pages" class="text-sm font-semibold md:text-base">-</span>
                </div>

                <button id="next-page"
                    class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white transition-colors hover:bg-blue-700 md:px-4 md:py-2 md:text-base">
                    <span class="hidden sm:inline">{{ __('elearning.ebook.selanjutnya') }}</span>
                    <span class="sm:hidden">Next</span>
                    <i class="fas fa-chevron-right ml-1 md:ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- PDF.js & Turn.js Libraries --}}
    <!-- JANGAN ADA JQUERY LAIN DI LAYOUT/HEADER -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"></script>
    @vite(['resources/js/ebook.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initEbookPageFlip({
                pdfUrl: @json(asset('storage/' . $ebook->path_file)),
                totalPages: {{ $ebook->jumlah_halaman ?? 'null' }},
                materiUrl: @json(route('guest.elearning.materi', $ebook->materi_id)),
                ebookId: {{ $ebook->ebook_id }},
                csrfToken: @json(csrf_token())
            });
        });
    </script>

    <style>
        /* Fade in animation for dialog */
        .animate-fadeIn {
            animation: fadeIn 0.3s ease;
        }

        /* Flipbook Styles for Single Page */
        .flipbook {
            margin: 20px auto;
            width: 100%;
            max-width: 900px;
            aspect-ratio: 210/297;
            min-height: 320px;
            background: white;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .flipbook .page {
            background-color: white;
            background-size: 100% 100%;
            border: 1px solid #ccc;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            width: 100%;
            height: 100%;
            aspect-ratio: 210/297;
            overflow: hidden;
        }

        .flipbook .page canvas,
        .flipbook .page img {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            margin: 0 auto;
            background: white;
        }

        .flipbook .even {
            background: linear-gradient(135deg, #fff 0%, #f9f9f9 100%);
        }

        .flipbook .odd {
            background: linear-gradient(135deg, #fff 0%, #f9f9f9 100%);
        }

        /* Container improvements for single page */
        #flipbook-container {
            padding: 20px;
            min-height: 85vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Responsive adjustments for single page */
        @media (max-width: 1024px) {
            .flipbook {
                max-width: 100vw;
                min-height: 220px;
            }

            #flipbook-container {
                min-height: 85vh;
            }
        }

        @media (max-width: 768px) {
            .flipbook {
                max-width: 100vw;
                min-height: 180px;
            }

            #flipbook-container {
                min-height: 90vh;
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .flipbook {
                max-width: 100vw;
                min-height: 120px;
            }

            #flipbook-container {
                min-height: 95vh;
                padding: 4px;
            }
        }

        /* Fullscreen styles for single page */
        #flipbook-container:fullscreen {
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        #flipbook-container:fullscreen .flipbook {
            margin: 0;
            width: 80vw !important;
            height: 95vh !important;
        }

        /* Mobile fullscreen adjustments */
        @media (max-width: 768px) {
            #flipbook-container:fullscreen .flipbook {
                width: 95vw !important;
                height: 98vh !important;
            }
        }

        /* Loading animation for pages */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page canvas {
            animation: fadeIn 0.4s ease-out;
        }

        /* Turn.js custom styles for single page */
        .turn-page {
            background-color: #fafafa;
            border-radius: 8px;
        }

        .shadow {
            -webkit-transition: -webkit-box-shadow 0.5s;
            -moz-transition: -moz-box-shadow 0.5s;
            -o-transition: box-shadow 0.5s;
            transition: box-shadow 0.5s;
        }

        /* Simple viewer improvements */
        #simple-canvas {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.2);
            max-width: 100%;
            height: auto;
        }

        /* Mobile canvas improvements */
        @media (max-width: 480px) {
            #simple-canvas {
                border-radius: 8px;
                border-width: 1px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            }
        }

        /* Page turning effect improvements */
        .flipbook .turn-page-wrapper {
            border-radius: 8px;
            overflow: hidden;
        }
    </style>
</x-elearning-layout>