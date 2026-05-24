<x-guest-layout>
    <div class="bg-primary px-6 py-6 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center">
                <button class="back-button mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <h1 class="text-xl font-bold">{{ __('app.feedback_suggestion') }}</h1>
            </div>
        </div>
    </div>

    <div class="min-h-screen bg-gray-50 px-6 pb-24 pt-6">
        <div class="max-w-7xl mx-auto">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-100 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form Section --}}
            <div class="mb-6 rounded-lg bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">{{ __('app.send_feedback') }}</h2>

                <form action="{{ route('guest.kritik-saran.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="pesan"
                            class="mb-1 block text-sm font-medium text-gray-700">{{ __('app.message') }}</label>
                        <textarea id="pesan" name="pesan" rows="4"
                            class="{{ $errors->has('pesan') ? 'border-red-500' : 'border-gray-300' }} w-full rounded-md border px-3 py-2 focus:border-primary focus:outline-none focus:ring-primary"
                            placeholder="{{ __('app.write_feedback_placeholder') }}">{{ old('pesan') }}</textarea>
                        @error('pesan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="hover:bg-primary-dark rounded-md bg-primary px-4 py-2 font-medium text-white transition-colors">
                            {{ __('app.send') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- History Section --}}
            <div class="rounded-lg bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">{{ __('app.feedback_history') }}</h2>

                @if ($kritikSaran->count() > 0)
                    <div class="space-y-4">
                        @foreach ($kritikSaran as $item)
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="flex items-start justify-between">
                                    <p class="text-gray-800">{{ $item->pesan }}</p>
                                    <span
                                        class="text-xs text-gray-500">{{ $item->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                            <i class="fas fa-comment-dots text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Belum Ada Feedback</h3>
                        <p class="text-gray-600">{{ __('app.no_feedback_history') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom Navigation --}}
    <x-bottom-nav class="md:hidden" />
</x-guest-layout>
