<?php

namespace App\Http\Controllers;

use App\Enums\JenisEventVr;
use App\Models\VrEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VrEventController extends Controller
{
    /**
     * Batas jumlah event per pengiriman. Klien menyangga lalu mengirim borongan,
     * jadi satu request wajar memuat puluhan event — tapi tetap dibatasi agar
     * request bengkak tidak bisa dipakai membanjiri tabel.
     */
    private const MAKS_EVENT_PER_BATCH = 200;

    /**
     * Terima satu batch runtime event dari scene VR.
     *
     * Dipanggil lewat navigator.sendBeacon(), yang tidak bisa menyetel header —
     * token CSRF ikut di dalam body dan diperiksa Laravel seperti biasa. Tidak ada
     * rute yang dikecualikan dari perlindungan CSRF.
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'sesi_id' => 'required|uuid',
            'museum_id' => 'required|integer|exists:virtual_museum,museum_id',
            'kode_responden' => 'nullable|string|max:100',
            'events' => 'required|array|max:'.self::MAKS_EVENT_PER_BATCH,
            'events.*.jenis' => ['required', Rule::enum(JenisEventVr::class)],
            'events.*.offset_ms' => 'required|integer|min:0',
            'events.*.mesh_name' => 'nullable|string|max:255',
            'events.*.detail' => 'nullable|array',
        ]);

        $now = now();
        $userId = Auth::id();

        $rows = array_map(fn (array $event): array => [
            'sesi_id' => $validated['sesi_id'],
            'user_id' => $userId,
            'museum_id' => $validated['museum_id'],
            'kode_responden' => $validated['kode_responden'] ?? null,
            'jenis' => $event['jenis'],
            'mesh_name' => $event['mesh_name'] ?? null,
            'detail' => isset($event['detail']) ? json_encode($event['detail']) : null,
            'offset_ms' => $event['offset_ms'],
            'created_at' => $now,
        ], $validated['events']);

        VrEvent::insert($rows);

        return response()->noContent();
    }

    /**
     * Ekspor seluruh event sebagai CSV baris mentah untuk diolah tim peneliti.
     *
     * Sengaja bukan rekap: bentuk rekap yang dibutuhkan analisis belum diketahui, dan
     * baris mentah bisa diolah sendiri di SPSS/Excel.
     */
    public function export(): StreamedResponse
    {
        $perangkatPerSesi = $this->perangkatPerSesi();

        $kolom = [
            'sesi_id', 'kode_responden', 'user_id', 'nama_user', 'museum_id', 'nama_museum',
            'perangkat', 'jenis', 'mesh_name', 'offset_ms', 'detail', 'diterima_server',
        ];

        return response()->streamDownload(function () use ($kolom, $perangkatPerSesi) {
            $keluaran = fopen('php://output', 'w');
            fputcsv($keluaran, $kolom);

            VrEvent::query()
                ->with(['user:id,name', 'virtualMuseum:museum_id,nama'])
                ->orderBy('sesi_id')
                ->orderBy('offset_ms')
                ->chunk(500, function ($events) use ($keluaran, $perangkatPerSesi) {
                    foreach ($events as $event) {
                        fputcsv($keluaran, [
                            $event->sesi_id,
                            $event->kode_responden,
                            $event->user_id,
                            $event->user?->name,
                            $event->museum_id,
                            $event->virtualMuseum?->nama,
                            $perangkatPerSesi[$event->sesi_id] ?? null,
                            $event->jenis->value,
                            $event->mesh_name,
                            $event->offset_ms,
                            $event->detail ? json_encode($event->detail) : null,
                            $event->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($keluaran);
        }, 'vr-events-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Perangkat hanya tercatat di detail event SesiMulai. Analisis hampir selalu
     * dipilah per perangkat, jadi resolusikan sekali lalu isi di setiap baris CSV.
     *
     * @return array<string, string|null> sesi_id => perangkat
     */
    private function perangkatPerSesi(): array
    {
        return VrEvent::query()
            ->where('jenis', JenisEventVr::SesiMulai)
            ->get(['sesi_id', 'detail'])
            ->mapWithKeys(fn (VrEvent $event): array => [
                $event->sesi_id => $event->detail['perangkat'] ?? null,
            ])
            ->all();
    }
}
