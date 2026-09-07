<?php

namespace App\Http\Controllers;

use App\Helper\TokenHelper;
use App\Models\User;
use App\Models\VirtualMuseum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class VrPeluncurController extends Controller
{
    /**
     * Masa berlaku token peluncur. Lebih panjang dari default 10 menit karena
     * fasilitator perlu waktu berjalan dari laptop ke headset dan memasangkannya
     * ke siswa. Token hanya dipakai sekali untuk menukar sesi; setelah itu
     * cookie sesi yang bekerja, jadi masa berlaku panjang tidak memperluas paparan.
     */
    private const TOKEN_TTL_MENIT = 30;

    /**
     * Halaman peluncur fasilitator: menghasilkan QR/tautan sesi VR yang membawa
     * kode responden.
     *
     * Ini satu-satunya tempat yang mengirimkan `?kode=`. Tanpanya seluruh data
     * `vr_event` dan `jawaban_refleksi` tetap anonim, dan pencatatan runtime jadi
     * sia-sia untuk validasi TKT 5-6.
     */
    public function show(int $museum_id): View
    {
        $museum = VirtualMuseum::with('situsPeninggalan')->findOrFail($museum_id);
        $currentUser = Auth::user();

        // Keamanan Kiosk: Jangan pernah membiarkan akun admin menjadi sesi headset.
        // Jika admin yang membuka, alihkan ke akun kiosk khusus (role 'user')
        // agar browser headset tidak memiliki privilese admin jika siswa keluar scene.
        $isKioskAccount = ($currentUser->role === 'admin') || request()->boolean('use_kiosk_account');
        $sessionUser = $isKioskAccount ? User::getOrCreateKioskUser() : $currentUser;

        $arToken = TokenHelper::generate($sessionUser->id, self::TOKEN_TTL_MENIT);

        // Generate PIN 4-digit unik untuk pairing cepat dari browser Meta Quest 2
        do {
            $pin = (string) random_int(1000, 9999);
        } while (Cache::has('kiosk_pin_'.$pin));

        Cache::put('kiosk_pin_'.$pin, [
            'museum_id' => $museum->museum_id,
            'situs_id' => $museum->situs_id,
            'user_id' => $sessionUser->id,
            'arToken' => $arToken,
            'kode' => 'R001',
            'kode_akhir' => null,
            'kiosk' => true,
        ], now()->addMinutes(self::TOKEN_TTL_MENIT));

        return view('guest.vr.peluncur', [
            'museum' => $museum,
            'situs' => $museum->situsPeninggalan,
            'arToken' => $arToken,
            'ttlMenit' => self::TOKEN_TTL_MENIT,
            'sessionUser' => $sessionUser,
            'isKioskAccount' => $isKioskAccount,
            'isAdminLaunching' => $currentUser->role === 'admin',
            'pin' => $pin,
        ]);
    }
}
