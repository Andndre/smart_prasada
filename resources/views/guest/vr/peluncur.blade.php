<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <p class="text-sm font-medium uppercase tracking-wide text-purple-600">Peluncur Sesi VR</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $museum->nama }}</h1>
                @if ($situs)
                    <p class="mt-1 text-gray-600">{{ $situs->nama }}</p>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="space-y-4">
                    <div>
                        <label for="kode" class="mb-1 block text-sm font-medium text-gray-700">Kode responden pertama</label>
                        <input type="text" id="kode" value="R001" autocomplete="off"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 font-mono focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Kode ini yang menyambungkan data VR dan refleksi ke angket serta pretest.
                            Tanpa kode, seluruh sesi tercatat anonim.
                        </p>
                    </div>

                    <div>
                        <label for="kode-akhir" class="mb-1 block text-sm font-medium text-gray-700">
                            Kode terakhir <span class="text-gray-400">(opsional)</span>
                        </label>
                        <input type="text" id="kode-akhir" placeholder="R020" autocomplete="off"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 font-mono focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Isi kalau kodenya berurutan. Tombol "Responden berikutnya" di headset jadi
                            satu ketukan tanpa mengetik. Kosongkan kalau kodenya tidak berurutan.
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" id="kiosk" checked
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        Mode kiosk — sembunyikan navigasi aplikasi di halaman VR
                    </label>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="text-center">
                    <div id="qr-code" class="mx-auto flex justify-center"></div>
                    <p class="mt-4 break-all font-mono text-xs text-gray-500" id="tautan-teks"></p>
                    <button type="button" id="btn-salin"
                        class="mt-3 rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Salin tautan
                    </button>
                </div>

                <p class="mt-6 rounded-md bg-amber-50 p-3 text-xs text-amber-800">
                    Tautan berlaku {{ $ttlMenit }} menit. Setelah dipindai sekali, headset tetap masuk
                    sampai sesinya berakhir — responden berikutnya cukup lewat tombol di headset,
                    tidak perlu memindai ulang.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
    <script>
        (function () {
            const basis = @json(route('vr.museum', [$museum->situs_id, $museum->museum_id]));
            const arToken = @json($arToken);
            const wadah = document.getElementById('qr-code');
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
                wadah.innerHTML = '';
                qr = new QRCode(wadah, {
                    text: tautan,
                    width: 200,
                    height: 200,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                });
            }

            for (const id of ['kode', 'kode-akhir', 'kiosk']) {
                document.getElementById(id).addEventListener('input', perbarui);
                document.getElementById(id).addEventListener('change', perbarui);
            }

            document.getElementById('btn-salin').addEventListener('click', async (e) => {
                await navigator.clipboard?.writeText(bangunTautan());
                e.target.textContent = 'Tersalin ✓';
                setTimeout(() => (e.target.textContent = 'Salin tautan'), 2000);
            });

            perbarui();
        })();
    </script>
</x-app-layout>
