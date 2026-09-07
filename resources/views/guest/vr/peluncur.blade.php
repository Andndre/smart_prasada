<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumb Navigasi --}}
            <nav class="mb-4 flex items-center space-x-2 text-sm text-gray-500">
                @if ($situs)
                    <a href="{{ route('guest.situs.detail', $situs->situs_id) }}"
                        class="flex items-center transition-colors hover:text-purple-600">
                        <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                        Kembali ke {{ $situs->nama }}
                    </a>
                @elseif (auth()->user()?->role === 'admin')
                    <a href="{{ route('admin.virtual-museum.show', $museum->museum_id) }}"
                        class="flex items-center transition-colors hover:text-purple-600">
                        <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                        Kembali ke Detail Museum (Admin)
                    </a>
                @else
                    <a href="{{ route('guest.home') }}"
                        class="flex items-center transition-colors hover:text-purple-600">
                        <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                        Kembali ke Beranda
                    </a>
                @endif
            </nav>

            <div class="mb-6">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-700">
                        <i class="fas fa-vr-cardboard mr-1"></i> Peluncur Sesi VR
                    </span>
                    @if ($isKioskAccount)
                        <span
                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            <i class="fas fa-shield-alt mr-1"></i> Kiosk Sandbox Aktif
                        </span>
                    @endif
                </div>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $museum->nama }}</h1>
                @if ($situs)
                    <p class="mt-0.5 text-sm text-gray-600">{{ $situs->nama }}</p>
                @endif
            </div>

            {{-- Kartu Status Keamanan Akun Sesi --}}
            <div
                class="{{ $isKioskAccount ? 'border-emerald-200 bg-emerald-50/70' : 'border-blue-200 bg-blue-50/70' }} mb-6 rounded-xl border p-4 shadow-sm">
                <div class="flex items-start space-x-3">
                    <div
                        class="{{ $isKioskAccount ? 'bg-emerald-200 text-emerald-700' : 'bg-blue-200 text-blue-700' }} mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full">
                        <i class="fas {{ $isKioskAccount ? 'fa-shield-halved' : 'fa-user-check' }} text-sm"></i>
                    </div>
                    <div class="flex-1 text-xs">
                        <h4 class="font-semibold text-gray-900">
                            Akun Sesi Headset: <span class="font-mono text-purple-700">{{ $sessionUser->name }}</span>
                            ({{ $sessionUser->email }})
                        </h4>
                        @if ($isAdminLaunching)
                            <p class="mt-1 leading-relaxed text-emerald-800">
                                <strong>Perlindungan Privilese Admin:</strong> Anda terdeteksi login sebagai
                                Administrator. Untuk mencegah celah keamanan di headset Meta Quest yang dipakai siswa,
                                token AR diterbitkan secara otomatis menggunakan <strong>Akun Kiosk Terisolasi</strong>
                                (role non-admin). Hak akses admin Anda tetap aman.
                            </p>
                        @else
                            <p class="mt-1 leading-relaxed text-gray-600">
                                Sesi VR pada headset akan diautentikasi di bawah akun ini selama {{ $ttlMenit }}
                                menit. Responden bergantian menggunakan akun bersama ini tanpa perlu login ulang.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kartu PIN Masuk Cepat Meta Quest 2 (Pairing Code) --}}
            <div
                class="mb-6 rounded-2xl border-2 border-purple-300 bg-gradient-to-br from-purple-50 via-white to-indigo-50/50 p-6 shadow-sm">
                <div class="flex flex-col items-center justify-between gap-5 sm:flex-row">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-full bg-purple-600 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-white">
                                <i class="fas fa-key mr-1.5"></i> PIN Pairing
                            </span>
                            <span class="text-xs font-semibold text-purple-700">Khusus Meta Quest 2 / Tanpa Scanner
                                QR</span>
                        </div>
                        <h3 class="mt-1.5 text-lg font-bold text-gray-900">Masuk Cepat dari Headset VR</h3>
                        <p class="mt-1 text-xs leading-relaxed text-gray-600">
                            Buka browser Meta Quest 2 ke alamat bookmark: <br>
                            <a href="{{ url('/kiosk') }}" target="_blank"
                                class="font-mono text-sm font-bold text-purple-700 underline">{{ url('/kiosk') }}</a>
                            <br><span class="text-gray-500">Lalu masukkan 4 digit PIN di samping ini untuk langsung
                                masuk.</span>
                        </p>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="flex items-center gap-2" title="Ketik 4 angka ini di Meta Quest">
                            @foreach (str_split($pin) as $digit)
                                <div
                                    class="flex h-14 w-12 items-center justify-center rounded-xl border-2 border-purple-400 bg-white font-mono text-3xl font-black text-purple-700 shadow-md">
                                    {{ $digit }}
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="btn-salin-pin"
                            class="mt-2 flex items-center text-xs font-semibold text-purple-600 transition hover:text-purple-800">
                            <i class="fas fa-copy mr-1"></i> <span id="label-salin-pin">Salin PIN
                                ({{ $pin }})</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Meja Pengisian Refleksi Siswa (Laptop / Tablet Fasilitator) --}}
            <div class="mb-6 rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-teal-50/40 p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-white">
                                <i class="fas fa-clipboard-check mr-1.5"></i> Meja Refleksi
                            </span>
                            <span class="text-xs font-semibold text-emerald-800">Khusus Laptop Guru / Tablet Siswa</span>
                        </div>
                        <h3 class="mt-1.5 text-lg font-bold text-gray-900">Form Refleksi Pasca-VR di Laptop Ini</h3>
                        <p class="mt-1 text-xs leading-relaxed text-gray-600">
                            Setelah siswa melepas headset, klik tombol di samping untuk membuka lembar refleksi di laptop ini agar siswa dapat mengetik dengan keyboard fisik yang nyaman.
                        </p>
                    </div>

                    <div class="flex flex-col sm:items-end gap-2 shrink-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-medium text-gray-600">Kode Siswa:</span>
                            <div class="flex items-center rounded-lg border border-gray-300 bg-white p-0.5 shadow-sm">
                                <button type="button" id="btn-prev-kode-refleksi" class="px-2 py-1 text-xs text-gray-500 hover:text-purple-600 transition" title="Kode sebelumnya">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="text" id="kode-refleksi" value="R001"
                                    class="w-20 border-none p-0 text-center font-mono text-sm font-bold text-emerald-700 focus:ring-0">
                                <button type="button" id="btn-next-kode-refleksi" class="px-2 py-1 text-xs text-gray-500 hover:text-purple-600 transition" title="Kode berikutnya">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <a id="btn-buka-refleksi" href="#" target="_blank"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-emerald-700 hover:shadow-lg">
                            <i class="fas fa-pen-to-square mr-2"></i>
                            <span id="label-buka-refleksi">Buka Form Refleksi (R001)</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-base font-semibold text-gray-900">Pengaturan Responden & Mode Sesi</h3>
                <div class="space-y-4">
                    <div>
                        <label for="kode" class="mb-1 block text-sm font-medium text-gray-700">Kode responden
                            pertama</label>
                        <input type="text" id="kode" value="R001" autocomplete="off"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2 font-mono text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Kode ini yang menyambungkan rekaman interaksi VR dan refleksi ke angket responden.
                        </p>
                    </div>

                    <div>
                        <label for="kode-akhir" class="mb-1 block text-sm font-medium text-gray-700">
                            Kode terakhir <span class="font-normal text-gray-400">(opsional untuk deret
                                berurutan)</span>
                        </label>
                        <input type="text" id="kode-akhir" placeholder="R030" autocomplete="off"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2 font-mono text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Jika diisi, tombol "Responden berikutnya" di headset akan otomatis berpindah ke kode
                            selanjutnya dengan 1 ketukan tanpa mengetik.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <label class="flex cursor-pointer items-start gap-2.5 text-sm text-gray-800">
                            <input type="checkbox" id="kiosk" checked
                                class="mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <div>
                                <span class="font-medium">Mode Kiosk Aktif</span>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    Sembunyikan bilah navigasi aplikasi di dalam VR, kunci panel selesai untuk mencegah
                                    kontaminasi kode antar-responden, dan aktifkan kontinuitas pergantian responden
                                    otomatis.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="text-center">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Scan QR Code di Browser
                        Headset VR</p>
                    <div id="qr-code"
                        class="mx-auto inline-block flex justify-center rounded-lg border border-gray-100 bg-white p-3 shadow-sm">
                    </div>
                    <p class="mx-auto mt-4 max-w-lg break-all rounded border border-gray-200 bg-gray-50 p-2.5 font-mono text-xs text-gray-500"
                        id="tautan-teks"></p>

                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <button type="button" id="btn-salin"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                            <i class="fas fa-copy mr-1.5"></i>
                            <span id="label-salin">Salin tautan</span>
                        </button>
                        <a id="btn-buka-langsung" href="#" target="_blank"
                            class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-purple-700">
                            <i class="fas fa-external-link-alt mr-1.5"></i>
                            Buka Sesi di Tab Baru
                        </a>
                    </div>
                </div>

                <div
                    class="mt-6 flex items-start space-x-2.5 rounded-lg border border-amber-200 bg-amber-50 p-3.5 text-xs text-amber-900">
                    <i class="fas fa-info-circle mt-0.5 shrink-0 text-amber-600"></i>
                    <div>
                        <strong>Masa Berlaku Token:</strong> Tautan berlaku selama {{ $ttlMenit }} menit untuk
                        proses handoff. Setelah headset memindai sekali dan sesi login terbentuk di browser headset,
                        headset dapat dipakai bergantian sepanjang hari tanpa perlu memindai QR code lagi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
    <script>
        (function() {
            const basis = @json(route('vr.museum', [$museum->situs_id, $museum->museum_id]));
            const arToken = @json($arToken);
            const wadah = document.getElementById('qr-code');
            const btnBuka = document.getElementById('btn-buka-langsung');
            let qr = null;

            function bangunTautan() {
                const url = new URL(basis, location.origin);
                url.searchParams.set('arToken', arToken);

                const kode = document.getElementById('kode').value.trim();
                if (kode) url.searchParams.set('kode', kode);

                const akhir = document.getElementById('kode-akhir').value.trim();
                if (akhir) url.searchParams.set('kode_akhir', akhir);

                if (document.getElementById('kiosk').checked) url.searchParams.set('kiosk', '1');

                return url.toString();
            }

            function perbarui() {
                const tautan = bangunTautan();
                document.getElementById('tautan-teks').textContent = tautan;
                btnBuka.href = tautan;

                wadah.innerHTML = '';
                qr = new QRCode(wadah, {
                    text: tautan,
                    width: 200,
                    height: 200,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                });
            }

            const pin = @json($pin);
            const updatePinUrl = @json(route('vr.peluncur.pin.update'));
            const csrfToken = @json(csrf_token());

            let syncTimer = null;

            function sinkronkanPinKeServer() {
                clearTimeout(syncTimer);
                syncTimer = setTimeout(async () => {
                    try {
                        await fetch(updatePinUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                pin: pin,
                                kode: document.getElementById('kode').value.trim(),
                                kode_akhir: document.getElementById('kode-akhir').value
                                    .trim(),
                                kiosk: document.getElementById('kiosk').checked ? 1 : 0
                            })
                        });
                    } catch (err) {
                        console.error('Gagal sinkronisasi PIN:', err);
                    }
                }, 300);
            }

            for (const id of ['kode', 'kode-akhir', 'kiosk']) {
                document.getElementById(id).addEventListener('input', () => {
                    perbarui();
                    sinkronkanPinKeServer();
                });
                document.getElementById(id).addEventListener('change', () => {
                    perbarui();
                    sinkronkanPinKeServer();
                });
            }

            document.getElementById('btn-salin').addEventListener('click', async () => {
                const label = document.getElementById('label-salin');
                await navigator.clipboard?.writeText(bangunTautan());
                label.textContent = 'Tersalin ✓';
                setTimeout(() => (label.textContent = 'Salin tautan'), 2000);
            });

            document.getElementById('btn-salin-pin')?.addEventListener('click', async () => {
                const label = document.getElementById('label-salin-pin');
                await navigator.clipboard?.writeText(pin);
                label.textContent = 'PIN Tersalin ✓';
                setTimeout(() => (label.textContent = `Salin PIN (${pin})`), 2000);
            });

            // Logika Meja Pengisian Refleksi Siswa di Laptop
            const refleksiBasis = @json(route('refleksi.show', $museum->museum_id));
            const inputRefleksi = document.getElementById('kode-refleksi');
            const btnBukaRefleksi = document.getElementById('btn-buka-refleksi');
            const labelBukaRefleksi = document.getElementById('label-buka-refleksi');
            const btnPrevRefleksi = document.getElementById('btn-prev-kode-refleksi');
            const btnNextRefleksi = document.getElementById('btn-next-kode-refleksi');

            function perbaruiTautanRefleksi() {
                if (!inputRefleksi || !btnBukaRefleksi) return;
                const kode = inputRefleksi.value.trim();
                const url = new URL(refleksiBasis, location.origin);
                if (kode) url.searchParams.set('kode', kode);
                btnBukaRefleksi.href = url.toString();
                if (labelBukaRefleksi) {
                    labelBukaRefleksi.textContent = kode ? `Buka Form Refleksi (${kode})` : 'Buka Form Refleksi';
                }
            }

            function geserKode(step) {
                if (!inputRefleksi) return;
                const val = inputRefleksi.value.trim();
                const cocok = /^(.*?)(\d+)$/.exec(val);
                if (!cocok) return;
                const [, awalan, angka] = cocok;
                const baru = Math.max(1, Number(angka) + step);
                inputRefleksi.value = awalan + String(baru).padStart(angka.length, '0');
                inputRefleksi.dataset.touched = 'true';
                perbaruiTautanRefleksi();
            }

            inputRefleksi?.addEventListener('input', () => {
                inputRefleksi.dataset.touched = 'true';
                perbaruiTautanRefleksi();
            });

            btnPrevRefleksi?.addEventListener('click', () => geserKode(-1));
            btnNextRefleksi?.addEventListener('click', () => geserKode(1));

            document.getElementById('kode')?.addEventListener('change', (e) => {
                if (inputRefleksi && !inputRefleksi.dataset.touched) {
                    inputRefleksi.value = e.target.value.trim();
                    perbaruiTautanRefleksi();
                }
            });

            perbarui();
            perbaruiTautanRefleksi();
        })();
    </script>
</x-app-layout>
