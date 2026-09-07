<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class KioskPinController extends Controller
{
    /**
     * Tampilkan antarmuka input PIN mode kiosk untuk Meta Quest 2 dan perangkat VR lainnya.
     */
    public function show(): View
    {
        return view('guest.vr.kiosk-entry');
    }

    /**
     * Verifikasi PIN 4-digit dari browser headset dan arahkan langsung ke scene VR.
     */
    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => 'required|digits:4',
        ], [
            'pin.required' => 'Masukkan 4 digit PIN peluncur sesi.',
            'pin.digits' => 'PIN harus terdiri dari tepat 4 angka.',
        ]);

        $pin = $validated['pin'];
        $cacheKey = 'kiosk_pin_'.$pin;
        $data = Cache::get($cacheKey);

        if (! $data) {
            return back()->withInput()->with('error', 'PIN tidak ditemukan atau sudah kedaluwarsa. Periksa kembali layar laptop fasilitator.');
        }

        $query = [
            'arToken' => $data['arToken'],
        ];

        if (! empty($data['kode'])) {
            $query['kode'] = $data['kode'];
        }

        if (! empty($data['kode_akhir'])) {
            $query['kode_akhir'] = $data['kode_akhir'];
        }

        if (! empty($data['kiosk'])) {
            $query['kiosk'] = '1';
        }

        $targetUrl = route('vr.museum', [$data['situs_id'], $data['museum_id']]).'?'.http_build_query($query);

        return redirect()->to($targetUrl);
    }

    /**
     * Sinkronisasi parameter PIN dari form peluncur fasilitator secara dinamis.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|digits:4',
            'kode' => 'nullable|string|max:50',
            'kode_akhir' => 'nullable|string|max:50',
            'kiosk' => 'required|boolean',
        ]);

        $pin = $validated['pin'];
        $cacheKey = 'kiosk_pin_'.$pin;
        $data = Cache::get($cacheKey);

        if (! $data) {
            return response()->json(['error' => 'PIN tidak ditemukan atau kedaluwarsa'], 404);
        }

        $data['kode'] = $validated['kode'] ?: null;
        $data['kode_akhir'] = $validated['kode_akhir'] ?: null;
        $data['kiosk'] = (bool) $validated['kiosk'];

        // Pertahankan cache selama 30 menit
        Cache::put($cacheKey, $data, now()->addMinutes(30));

        return response()->json([
            'status' => 'ok',
            'pin' => $pin,
            'data' => $data,
        ]);
    }
}
