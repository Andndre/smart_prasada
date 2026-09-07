<x-app-layout tanpa-navigasi>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-900 px-4 py-8 text-white">
        <div class="w-full max-w-md">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <div
                    class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl border border-purple-500/30 bg-purple-600/30 text-purple-400 shadow-inner">
                    <i class="fas fa-vr-cardboard text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Mode Kiosk VR</h1>
                <p class="mt-1 text-sm text-slate-400">
                    Masukkan 4 digit PIN yang tampil di layar laptop fasilitator.
                </p>
            </div>

            {{-- Kartu Input PIN --}}
            <div class="rounded-3xl border border-slate-800 bg-slate-800/80 p-6 shadow-2xl backdrop-blur-md">
                @if (session('error'))
                    <div
                        class="mb-5 flex items-center gap-3 rounded-xl border border-red-500/40 bg-red-500/20 p-3 text-sm text-red-300">
                        <i class="fas fa-circle-exclamation text-base text-red-400"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-500/40 bg-red-500/20 p-3 text-sm text-red-300">
                        @foreach ($errors->all() as $err)
                            <p>{{ $err }}</p>
                        @endforeach
                    </div>
                @endif

                <form id="kiosk-form" method="POST" action="{{ route('kiosk.verify') }}">
                    @csrf
                    {{-- Input Nilai PIN Sebenarnya --}}
                    <input type="hidden" name="pin" id="pin-value" value="{{ old('pin') }}">

                    {{-- Tampilan Digit PIN --}}
                    <div class="mb-6 flex justify-center gap-3" id="pin-display">
                        <div
                            class="digit-box flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-slate-700 bg-slate-900/90 font-mono text-3xl font-bold text-purple-400 transition-all duration-150">
                            {{ old('pin') ? substr(old('pin'), 0, 1) : '' }}
                        </div>
                        <div
                            class="digit-box flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-slate-700 bg-slate-900/90 font-mono text-3xl font-bold text-purple-400 transition-all duration-150">
                            {{ old('pin') && strlen(old('pin')) > 1 ? substr(old('pin'), 1, 1) : '' }}
                        </div>
                        <div
                            class="digit-box flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-slate-700 bg-slate-900/90 font-mono text-3xl font-bold text-purple-400 transition-all duration-150">
                            {{ old('pin') && strlen(old('pin')) > 2 ? substr(old('pin'), 2, 1) : '' }}
                        </div>
                        <div
                            class="digit-box flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-slate-700 bg-slate-900/90 font-mono text-3xl font-bold text-purple-400 transition-all duration-150">
                            {{ old('pin') && strlen(old('pin')) > 3 ? substr(old('pin'), 3, 1) : '' }}
                        </div>
                    </div>

                    {{-- Virtual Touch / Laser Keypad (Ramah Controller Meta Quest) --}}
                    <div class="grid grid-cols-3 gap-2.5">
                        @for ($i = 1; $i <= 9; $i++)
                            <button type="button" data-num="{{ $i }}"
                                class="key-btn flex h-14 items-center justify-center rounded-xl bg-slate-700/80 text-xl font-semibold text-white shadow transition hover:bg-slate-600 active:scale-95">
                                {{ $i }}
                            </button>
                        @endfor
                        <button type="button" id="btn-clear"
                            class="key-btn flex h-14 items-center justify-center rounded-xl bg-red-600/30 text-base font-medium text-red-300 shadow transition hover:bg-red-600/50 active:scale-95">
                            <i class="fas fa-backspace"></i>
                        </button>
                        <button type="button" data-num="0"
                            class="key-btn flex h-14 items-center justify-center rounded-xl bg-slate-700/80 text-xl font-semibold text-white shadow transition hover:bg-slate-600 active:scale-95">
                            0
                        </button>
                        <button type="submit" id="btn-submit"
                            class="key-btn flex h-14 items-center justify-center rounded-xl bg-purple-600 text-base font-semibold text-white shadow-lg transition hover:bg-purple-500 active:scale-95 disabled:opacity-50">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Bantuan / Panduan --}}
            <div class="mt-6 text-center text-xs text-slate-500">
                <p>Simpan halaman ini di Bookmark browser Meta Quest 2 agar mudah dibuka kembali setiap sesi
                    pembelajaran.</p>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const pinValueInput = document.getElementById('pin-value');
            const digitBoxes = document.querySelectorAll('#pin-display .digit-box');
            const form = document.getElementById('kiosk-form');
            let currentPin = pinValueInput.value || '';

            function updateDisplay() {
                digitBoxes.forEach((box, index) => {
                    if (index < currentPin.length) {
                        box.textContent = currentPin[index];
                        box.classList.add('border-purple-500', 'bg-purple-950/30');
                        box.classList.remove('border-slate-700');
                    } else {
                        box.textContent = '';
                        box.classList.remove('border-purple-500', 'bg-purple-950/30');
                        box.classList.add('border-slate-700');
                    }
                });
                pinValueInput.value = currentPin;

                // Auto-submit saat 4 digit lengkap
                if (currentPin.length === 4) {
                    setTimeout(() => form.submit(), 150);
                }
            }

            // Tangani tombol angka di layar
            document.querySelectorAll('.key-btn[data-num]').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (currentPin.length < 4) {
                        currentPin += btn.dataset.num;
                        updateDisplay();
                    }
                });
            });

            // Tombol hapus
            document.getElementById('btn-clear').addEventListener('click', () => {
                if (currentPin.length > 0) {
                    currentPin = currentPin.slice(0, -1);
                    updateDisplay();
                }
            });

            // Dukung pengetikan keyboard fisik / virtual OS
            window.addEventListener('keydown', (e) => {
                if (e.key >= '0' && e.key <= '9') {
                    if (currentPin.length < 4) {
                        currentPin += e.key;
                        updateDisplay();
                    }
                } else if (e.key === 'Backspace') {
                    if (currentPin.length > 0) {
                        currentPin = currentPin.slice(0, -1);
                        updateDisplay();
                    }
                } else if (e.key === 'Enter' && currentPin.length === 4) {
                    form.submit();
                }
            });

            updateDisplay();
        })();
    </script>
</x-app-layout>
