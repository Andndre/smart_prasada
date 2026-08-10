<?php

namespace App\Http\Controllers;

use App\Helper\TokenHelper;
use App\Models\VirtualMuseum;
use Illuminate\Support\Facades\Auth;
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

        // Token dibuat HANYA untuk akun yang sedang login. Jangan pernah menerima
        // user_id dari parameter — itu akan mengubah halaman ini jadi mesin
        // pembuat sesi untuk akun mana pun.
        $arToken = TokenHelper::generate(Auth::id(), self::TOKEN_TTL_MENIT);

        return view('guest.vr.peluncur', [
            'museum' => $museum,
            'situs' => $museum->situsPeninggalan,
            'arToken' => $arToken,
            'ttlMenit' => self::TOKEN_TTL_MENIT,
        ]);
    }
}
