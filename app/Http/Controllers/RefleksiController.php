<?php

namespace App\Http\Controllers;

use App\Models\JawabanRefleksi;
use App\Models\PertanyaanRefleksi;
use App\Models\VirtualMuseum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RefleksiController extends Controller
{
    /**
     * Batas panjang satu jawaban. Cukup untuk beberapa paragraf refleksi siswa,
     * tapi tetap membatasi kolom TEXT dari kiriman yang tidak masuk akal.
     */
    private const MAKS_PANJANG_JAWABAN = 2000;

    /**
     * Modul refleksi — dijalankan di layar biasa setelah sesi VR, bukan di dalamnya.
     * Modul kelima blueprint (hal. 11).
     *
     * Sama seperti fase di dalam VR, halaman ini tidak mengunci apa pun: ia bisa
     * dibuka tanpa sesi VR yang sudah selesai, dan tidak menjadi gerbang ke mana pun.
     */
    public function show(Request $request, int $museum_id): View
    {
        $museum = VirtualMuseum::with('situsPeninggalan')->findOrFail($museum_id);

        $pertanyaan = PertanyaanRefleksi::where('museum_id', $museum_id)
            ->orderBy('urutan')
            ->orderBy('pertanyaan_id')
            ->get();

        return view('guest.refleksi.show', [
            'museum' => $museum,
            'pertanyaan' => $pertanyaan,
            'kodeResponden' => $request->query('kode'),
            'sesiId' => $request->query('sesi'),
            'maksPanjang' => self::MAKS_PANJANG_JAWABAN,
        ]);
    }

    public function store(Request $request, int $museum_id): RedirectResponse
    {
        $museum = VirtualMuseum::findOrFail($museum_id);

        $idPertanyaan = PertanyaanRefleksi::where('museum_id', $museum_id)
            ->pluck('pertanyaan_id')
            ->all();

        $validated = $request->validate([
            'kode_responden' => 'nullable|string|max:100',
            'sesi_id' => 'nullable|uuid',
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable|string|max:'.self::MAKS_PANJANG_JAWABAN,
        ]);

        $now = now();
        $baris = [];

        foreach ($validated['jawaban'] as $pertanyaanId => $jawaban) {
            // Pertanyaan dari museum lain diabaikan diam-diam: form hanya pernah
            // merender milik museum ini, jadi sisanya pasti kiriman yang dikarang.
            if (! in_array((int) $pertanyaanId, $idPertanyaan, true)) {
                continue;
            }
            if (blank($jawaban)) {
                continue;
            }

            $baris[] = [
                'pertanyaan_id' => (int) $pertanyaanId,
                'user_id' => Auth::id(),
                'museum_id' => $museum->museum_id,
                'kode_responden' => $validated['kode_responden'] ?? null,
                'sesi_id' => $validated['sesi_id'] ?? null,
                'jawaban' => $jawaban,
                'created_at' => $now,
            ];
        }

        if ($baris) {
            JawabanRefleksi::insert($baris);
        }

        $redirectParams = ['museum' => $museum->museum_id];
        if ($request->filled('kiosk') || session('is_kiosk_session')) {
            if ($request->filled('kiosk')) {
                $redirectParams['kiosk'] = $request->input('kiosk');
            }
            if ($request->filled('kode_akhir')) {
                $redirectParams['kode_akhir'] = $request->input('kode_akhir');
            }
            if ($request->filled('kode_responden')) {
                $redirectParams['kode'] = $request->input('kode_responden');
            }
        }

        return redirect()->route('refleksi.selesai', $redirectParams);
    }

    /**
     * Museum dibawa lewat query string, bukan segmen rute, supaya halaman ini tetap
     * bisa dibuka tanpa konteks apa pun — tautannya lalu turun ke beranda.
     */
    public function selesai(Request $request): View
    {
        $museum = VirtualMuseum::with('situsPeninggalan')->find($request->query('museum'));
        $kodeResponden = $request->query('kode');
        $kodeAkhir = $request->query('kode_akhir');
        $isKiosk = $request->boolean('kiosk') || (bool) session('is_kiosk_session');

        $kodeBerikutnya = null;
        $urlBerikutnya = null;
        if ($isKiosk && $museum) {
            $kodeBerikutnya = self::hitungKodeBerikutnya($kodeResponden, $kodeAkhir);
            $queryBerikutnya = ['kiosk' => 1];
            if ($kodeBerikutnya) {
                $queryBerikutnya['kode'] = $kodeBerikutnya;
            }
            if ($kodeAkhir) {
                $queryBerikutnya['kode_akhir'] = $kodeAkhir;
            }
            $urlBerikutnya = route('vr.museum', [$museum->situs_id, $museum->museum_id]).'?'.http_build_query($queryBerikutnya);
        }

        return view('guest.refleksi.selesai', [
            'materiId' => $museum?->situsPeninggalan?->materi_id,
            'museum' => $museum,
            'isKiosk' => $isKiosk,
            'kodeResponden' => $kodeResponden,
            'kodeBerikutnya' => $kodeBerikutnya,
            'urlBerikutnya' => $urlBerikutnya,
        ]);
    }

    /**
     * Hitung kode responden berikutnya sesuai deret angka (misal R001 -> R002).
     */
    public static function hitungKodeBerikutnya(?string $kode, ?string $kodeAkhir = null): ?string
    {
        if (! $kode || ! preg_match('/^(.*?)(\d+)$/', $kode, $m)) {
            return null;
        }

        $nextNum = (int) $m[2] + 1;
        $next = $m[1].str_pad((string) $nextNum, strlen($m[2]), '0', STR_PAD_LEFT);

        if ($kodeAkhir && preg_match('/^(.*?)(\d+)$/', $kodeAkhir, $mEnd)) {
            if ($m[1] === $mEnd[1] && $nextNum > (int) $mEnd[2]) {
                return null;
            }
        }

        return $next;
    }

    /**
     * Ekspor jawaban refleksi sebagai CSV baris mentah untuk tim peneliti.
     *
     * Sengaja di sini, bukan menumpang VrEventController — nama controller yang
     * berbohong soal isinya adalah utang yang murah dihindari sekarang.
     */
    public function export(): StreamedResponse
    {
        $kolom = [
            'jawaban_id', 'kode_responden', 'sesi_id', 'user_id', 'nama_user',
            'museum_id', 'nama_museum', 'nilai_karakter', 'pertanyaan', 'jawaban', 'dijawab_pada',
        ];

        return response()->streamDownload(function () use ($kolom) {
            $keluaran = fopen('php://output', 'w');
            fputcsv($keluaran, $kolom);

            JawabanRefleksi::query()
                ->with(['user:id,name', 'virtualMuseum:museum_id,nama', 'pertanyaan'])
                ->orderBy('kode_responden')
                ->orderBy('created_at')
                ->chunk(500, function ($jawaban) use ($keluaran) {
                    foreach ($jawaban as $baris) {
                        fputcsv($keluaran, [
                            $baris->jawaban_id,
                            $baris->kode_responden,
                            $baris->sesi_id,
                            $baris->user_id,
                            $baris->user?->name,
                            $baris->museum_id,
                            $baris->virtualMuseum?->nama,
                            $baris->pertanyaan?->nilai_karakter?->value,
                            $baris->pertanyaan?->pertanyaan,
                            $baris->jawaban,
                            $baris->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($keluaran);
        }, 'jawaban-refleksi-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
