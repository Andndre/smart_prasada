<?php

namespace App\Http\Controllers;

use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MapsController extends Controller
{
    public function view(Request $request)
    {
        try {
            $userAuth = Auth::user();
            $user = User::findOrFail($userAuth->id);

            $progress = $user->progress_level_sekarang ?? 0;
            $level = $user->level_sekarang ?? 0;

            // Mendapatkan semua situs
            $allSitus = SitusPeninggalan::all();

            // Mengidentifikasi situs yang telah dibuka oleh user
            $unlockedSitusIds = SitusPeninggalan::whereHas('materi', function ($query) use ($level) {
                $query->where('urutan', '<=', $level);
            })->pluck('situs_id')->toArray();

            return view('guest.maps.view', compact('allSitus', 'unlockedSitusIds'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal memuat halaman peta.');
        }
    }

    public function peninggalan(Request $request)
    {
        try {
            $user = Auth::user();
            $searchQuery = $request->query('q', '');

            $level = $user->level_sekarang ?? 0;

            // Get unlocked situs IDs (based on materi sequence)
            $unlockedSitusIds = SitusPeninggalan::whereHas('materi', function ($query) use ($level) {
                $query->where('urutan', '<=', $level);
            })->pluck('situs_id')->toArray();

            // Get all situs that have museums OR objects
            $situsQuery = SitusPeninggalan::where(function ($query) {
                $query->whereHas('virtualMuseum')
                    ->orWhereHas('virtualMuseumObject');
            });

            // Apply search filter
            if ($searchQuery) {
                $situsQuery->where(function ($query) use ($searchQuery) {
                    $query->where('nama', 'like', "%{$searchQuery}%")
                        ->orWhere('deskripsi', 'like', "%{$searchQuery}%");
                });
            }

            $situs = $situsQuery->get()->map(function ($s) use ($unlockedSitusIds) {
                $s->is_unlocked = in_array($s->situs_id, $unlockedSitusIds);
                // Count museums and objects for display
                $s->museum_count = $s->virtualMuseum->count();
                $s->object_count = $s->virtualMuseumObject->count();
                return $s;
            });

            return view('guest.maps.peninggalan', [
                'situs' => $situs,
                'searchQuery' => $searchQuery,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal memuat halaman peninggalan.');
        }
    }
}
