<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NilaiKarakter;
use App\Http\Controllers\Controller;
use App\Models\JawabanRefleksi;
use App\Models\PertanyaanRefleksi;
use App\Models\VirtualMuseum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PertanyaanRefleksiController extends Controller
{
    public function index(int $museum_id): View
    {
        $museum = VirtualMuseum::with('situsPeninggalan')->findOrFail($museum_id);

        $pertanyaan = PertanyaanRefleksi::where('museum_id', $museum_id)
            ->orderBy('urutan')
            ->orderBy('pertanyaan_id')
            ->get();

        $jumlahJawaban = JawabanRefleksi::where('museum_id', $museum_id)->count();

        return view('admin.pertanyaan-refleksi.index', compact('museum', 'pertanyaan', 'jumlahJawaban'));
    }

    public function create(int $museum_id): View
    {
        $museum = VirtualMuseum::findOrFail($museum_id);

        return view('admin.pertanyaan-refleksi.create', compact('museum'));
    }

    public function store(Request $request, int $museum_id): RedirectResponse
    {
        $museum = VirtualMuseum::findOrFail($museum_id);

        $validated = $request->validate($this->aturan());

        PertanyaanRefleksi::create([
            'museum_id' => $museum->museum_id,
            'nilai_karakter' => $validated['nilai_karakter'],
            'pertanyaan' => $validated['pertanyaan'],
            'urutan' => $validated['urutan'] ?? 0,
        ]);

        return redirect()->route('admin.pertanyaan-refleksi', $museum->museum_id)
            ->with('success', 'Pertanyaan refleksi berhasil ditambahkan!');
    }

    public function edit(int $pertanyaan_id): View
    {
        $pertanyaan = PertanyaanRefleksi::with('virtualMuseum')->findOrFail($pertanyaan_id);

        return view('admin.pertanyaan-refleksi.edit', compact('pertanyaan'));
    }

    public function update(Request $request, int $pertanyaan_id): RedirectResponse
    {
        $pertanyaan = PertanyaanRefleksi::findOrFail($pertanyaan_id);

        $validated = $request->validate($this->aturan());

        $pertanyaan->update([
            'nilai_karakter' => $validated['nilai_karakter'],
            'pertanyaan' => $validated['pertanyaan'],
            'urutan' => $validated['urutan'] ?? 0,
        ]);

        return redirect()->route('admin.pertanyaan-refleksi', $pertanyaan->museum_id)
            ->with('success', 'Pertanyaan refleksi berhasil diperbarui!');
    }

    public function destroy(int $pertanyaan_id): RedirectResponse
    {
        $pertanyaan = PertanyaanRefleksi::findOrFail($pertanyaan_id);
        $museumId = $pertanyaan->museum_id;
        $pertanyaan->delete();

        return redirect()->route('admin.pertanyaan-refleksi', $museumId)
            ->with('success', 'Pertanyaan refleksi berhasil dihapus!');
    }

    /**
     * Tampilkan rekapitulasi jawaban refleksi siswa untuk museum ini.
     */
    public function hasil(Request $request, int $museum_id): View
    {
        $museum = VirtualMuseum::with('situsPeninggalan')->findOrFail($museum_id);

        $query = JawabanRefleksi::where('museum_id', $museum_id)
            ->with(['pertanyaan', 'user'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->input('search'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('kode_responden', 'like', "%{$term}%")
                        ->orWhere('jawaban', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('pertanyaan_id'), function ($q) use ($request) {
                $q->where('pertanyaan_id', (int) $request->input('pertanyaan_id'));
            })
            ->latest('created_at');

        $jawaban = $query->paginate(20)->withQueryString();

        $totalJawaban = JawabanRefleksi::where('museum_id', $museum_id)->count();
        $totalResponden = JawabanRefleksi::where('museum_id', $museum_id)
            ->whereNotNull('kode_responden')
            ->distinct('kode_responden')
            ->count('kode_responden');
        $pertanyaanList = PertanyaanRefleksi::where('museum_id', $museum_id)
            ->orderBy('urutan')
            ->get();

        return view('admin.pertanyaan-refleksi.hasil', [
            'museum' => $museum,
            'jawaban' => $jawaban,
            'totalJawaban' => $totalJawaban,
            'totalResponden' => $totalResponden,
            'pertanyaanList' => $pertanyaanList,
        ]);
    }

    /**
     * Hapus satu baris jawaban refleksi (misal data uji coba).
     */
    public function destroyJawaban(int $jawaban_id): RedirectResponse
    {
        $jawaban = JawabanRefleksi::findOrFail($jawaban_id);
        $museumId = $jawaban->museum_id;
        $jawaban->delete();

        return redirect()->route('admin.hasil-refleksi', $museumId)
            ->with('success', 'Jawaban refleksi berhasil dihapus!');
    }

    /**
     * @return array<string, mixed>
     */
    private function aturan(): array
    {
        return [
            'nilai_karakter' => ['required', Rule::enum(NilaiKarakter::class)],
            'pertanyaan' => 'required|string',
            'urutan' => 'nullable|integer|min:0',
        ];
    }
}
