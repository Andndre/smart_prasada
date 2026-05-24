<?php

namespace App\Http\Controllers\Admin;

use App\Helper\ArPatternHelper;
use App\Http\Controllers\Controller;
use App\Models\AksesSitusUser;
use App\Models\ArMarker;
use App\Models\Ebook;
use App\Models\Era;
use App\Models\KritikSaran;
use App\Models\LaporanPeninggalan;
use App\Models\Materi;
use App\Models\Posttest;
use App\Models\Pretest;
use App\Models\RiwayatPengembang;
use App\Models\SitusPeninggalan;
use App\Models\Tugas;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        // Statistics
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_materi' => Materi::count(),
            'total_situs' => SitusPeninggalan::count(),
            'total_laporan' => LaporanPeninggalan::count(),
            'total_kritik_saran' => KritikSaran::count(),
        ];

        // Recent users (last 10)
        $recentUsers = User::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent reports (last 5)
        $recentReports = LaporanPeninggalan::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent feedback (last 5)
        $recentFeedback = KritikSaran::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Monthly user registrations (last 6 months) - compatible with MySQL and SQLite
        $monthlyUsers = User::where('role', 'user')
            ->where('created_at', '>=', now()->subMonths(6))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($user) {
                return $user->created_at->format('Y-m');
            })
            ->map(fn ($group) => (object) [
                'count' => $group->count(),
                'month' => (int) $group->first()->created_at->format('n'),
                'year' => (int) $group->first()->created_at->format('Y'),
            ])
            ->sortByDesc(fn ($item) => $item->year * 100 + $item->month)
            ->values();

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentReports',
            'recentFeedback',
            'monthlyUsers'
        ));
    }

    /**
     * Display users management
     */
    public function users()
    {
        $users = User::with('logAktivitas')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function showUser($id)
    {
        $user = User::with(['logAktivitas', 'jawabanUser', 'aksesSitusUser', 'kritikSaran', 'laporanPeninggalan'])
            ->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|in:user,admin',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['profile_photo', '_token', '_method', 'password_confirmation']);

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && Storage::exists('public/'.$user->profile_photo)) {
                Storage::delete('public/'.$user->profile_photo);
            }

            $photoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $photoPath;
        }

        $user->update($data);

        return redirect()->route('admin.users')
            ->with('success', 'Pengguna berhasil diperbarui!');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri!');
        }

        // Delete profile photo if exists
        if ($user->profile_photo && Storage::exists('public/'.$user->profile_photo)) {
            Storage::delete('public/'.$user->profile_photo);
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'Pengguna berhasil dihapus!');
    }

    /**
     * Display materi management
     */
    public function materi()
    {
        $materis = Materi::with('era')
            ->leftJoin('era', 'materi.era_id', '=', 'era.era_id')
            ->orderByRaw('CASE WHEN materi.era_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('era.urutan', 'asc')
            ->orderBy('materi.bab', 'asc')
            ->orderBy('materi.urutan', 'asc')
            ->select('materi.*')
            ->paginate(20);

        return view('admin.materi.index', compact('materis'));
    }

    /**
     * Display situs management
     */
    public function situs()
    {
        $situs = SitusPeninggalan::with(['user', 'materi', 'virtualMuseum', 'virtualMuseumObject', 'aksesSitusUser'])
            ->orderBy('situs_id', 'desc')
            ->paginate(20);

        // Calculate statistics
        $stats = [
            'total_situs' => SitusPeninggalan::count(),
            'total_virtual_objects' => VirtualMuseumObject::whereNotNull('situs_id')->count(),
            'total_user_accesses' => AksesSitusUser::count(),
        ];

        return view('admin.situs.index', compact('situs', 'stats'));
    }

    /**
     * Show the form for creating a new situs
     */
    public function createSitus()
    {
        $materis = Materi::with('era')
            ->leftJoin('era', 'materi.era_id', '=', 'era.era_id')
            ->orderByRaw('CASE WHEN materi.era_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('era.urutan')
            ->orderBy('materi.bab')
            ->orderBy('materi.urutan')
            ->select('materi.*')
            ->get();

        return view('admin.situs.create', compact('materis'));
    }

    /**
     * Store a newly created situs in storage
     */
    public function storeSitus(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'deskripsi' => 'required|string',
            'materi_id' => 'required|exists:materi,materi_id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['_token', 'thumbnail']);
        $data['user_id'] = Auth::id(); // Auto-set current user

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('situs-thumbnails', 'public');
            $data['thumbnail'] = $thumbnailPath;
        }

        SitusPeninggalan::create($data);

        return redirect()->route('admin.situs')
            ->with('success', 'Situs peninggalan berhasil ditambahkan.');
    }

    /**
     * Display the specified situs
     */
    public function showSitus($situs_id)
    {
        $situs = SitusPeninggalan::with(['user', 'materi', 'virtualMuseum.virtualMuseumObjects', 'virtualMuseumObject', 'aksesSitusUser'])
            ->findOrFail($situs_id);

        $stats = [
            'virtual_objects' => $situs->virtualMuseumObject->count(),
            'user_accesses' => $situs->aksesSitusUser->count(),
        ];

        return view('admin.situs.show', compact('situs', 'stats'));
    }

    /**
     * Show the form for editing the specified situs
     */
    public function editSitus($situs_id)
    {
        $situs = SitusPeninggalan::findOrFail($situs_id);
        $materis = Materi::with('era')
            ->leftJoin('era', 'materi.era_id', '=', 'era.era_id')
            ->orderByRaw('CASE WHEN materi.era_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('era.urutan')
            ->orderBy('materi.bab')
            ->orderBy('materi.urutan')
            ->select('materi.*')
            ->get();

        return view('admin.situs.edit', compact('situs', 'materis'));
    }

    /**
     * Update the specified situs in storage
     */
    public function updateSitus(Request $request, $situs_id)
    {
        $situs = SitusPeninggalan::findOrFail($situs_id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'deskripsi' => 'required|string',
            'materi_id' => 'required|exists:materi,materi_id',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['_token', '_method', 'remove_thumbnail']);

        // Handle thumbnail removal
        if ($request->has('remove_thumbnail') && $request->remove_thumbnail == '1') {
            // Delete old thumbnail if exists
            if ($situs->thumbnail) {
                $oldThumbnailPath = 'public/'.$situs->thumbnail;
                if (Storage::exists($oldThumbnailPath)) {
                    Storage::delete($oldThumbnailPath);
                    Log::info('Thumbnail lama dihapus', ['path' => $oldThumbnailPath]);
                }
                $data['thumbnail'] = null;
            }
        }
        // Handle new thumbnail upload
        elseif ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($situs->thumbnail) {
                $oldThumbnailPath = 'public/'.$situs->thumbnail;
                if (Storage::exists($oldThumbnailPath)) {
                    Storage::delete($oldThumbnailPath);
                    Log::info('Thumbnail lama dihapus', ['path' => $oldThumbnailPath]);
                }
            }

            // Simpan file baru
            $file = $request->file('thumbnail');
            $filename = 'situs-'.$situs_id.'-'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('situs-thumbnails', $filename, 'public');

            $data['thumbnail'] = $path;
        }

        $situs->update($data);

        return redirect()->route('admin.situs.show', $situs_id)
            ->with('success', 'Situs peninggalan berhasil diperbarui.');
    }

    /**
     * Remove the specified situs from storage
     */
    public function destroySitus($situs_id)
    {
        $situs = SitusPeninggalan::findOrFail($situs_id);
        $nama = $situs->nama;

        // Delete thumbnail if exists
        if ($situs->thumbnail && Storage::exists("public/{$situs->thumbnail}")) {
            Storage::delete("public/{$situs->thumbnail}");
        }

        $situs->delete();

        return redirect()->route('admin.situs')
            ->with('success', "Situs peninggalan '{$nama}' berhasil dihapus.");
    }

    // =======================
    // VIRTUAL MUSEUM MANAGEMENT
    // =======================

    /**
     * Display virtual living museum management
     */
    public function virtualMuseum()
    {
        $museums = VirtualMuseum::with(['situsPeninggalan', 'virtualMuseumObjects'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $stats = [
            'total_museums' => VirtualMuseum::count(),
            'total_objects' => VirtualMuseumObject::whereNotNull('museum_id')->count(),
            'total_situs_with_museum' => SitusPeninggalan::whereHas('virtualMuseum')->count(),
            'total_situs_without_museum' => SitusPeninggalan::whereDoesntHave('virtualMuseum')->count(),
        ];

        return view('admin.virtual-museum.index', compact('museums', 'stats'));
    }

    /**
     * Show the form for creating a new virtual living museum
     */
    public function createVirtualMuseum(Request $request)
    {
        $situsOptions = SitusPeninggalan::orderBy('nama')->get();

        // Pre-select situs if provided in query parameter
        $selectedSitusId = $request->get('situs_id');

        return view('admin.virtual-museum.create', compact('situsOptions', 'selectedSitusId'));
    }

    /**
     * Store a newly created virtual living museum
     */
    public function storeVirtualMuseum(Request $request)
    {
        // Support both chunked upload (via obj_file_path) and standard upload (via obj_file)
        $rules = [
            'situs_id' => 'required|exists:situs_peninggalan,situs_id',
            'nama' => 'required|string|max:255',
        ];

        if ($request->filled('obj_file_path')) {
            // Chunked upload — path already uploaded, no file needed
            $rules['obj_file_path'] = 'required|string|max:500';
        } else {
            // Standard upload
            $rules['obj_file'] = 'required|file|max:307200';
        }

        $request->validate($rules);

        $data = [
            'situs_id' => $request->situs_id,
            'nama' => $request->nama,
        ];

        if ($request->filled('obj_file_path')) {
            // Chunked upload: use the pre-uploaded path
            $data['path_obj'] = $request->obj_file_path;
        } elseif ($request->hasFile('obj_file')) {
            // Standard upload
            $file = $request->file('obj_file');
            $mime = $file->getMimeType();
            if (! str_starts_with($mime, 'model/') && ! \in_array($mime, ['application/octet-stream'])) {
                throw ValidationException::withMessages(['obj_file' => 'File model harus bertipe GLB.']);
            }
            // Stream-based GLB magic bytes check (reads only 4 bytes, not whole file)
            $handle = fopen($file->getRealPath(), 'rb');
            $header = $handle ? fread($handle, 4) : false;
            if ($handle) {
                fclose($handle);
            }
            if ($header !== 'glTF') {
                throw ValidationException::withMessages(['obj_file' => 'File model bukan GLB yang valid.']);
            }
            $extension = $this->safeExtensionFromMime($mime, 'glb');
            $filename = Str::uuid()->toString().'.'.$extension;
            $data['path_obj'] = $file->storeAs('virtual-museum/models', $filename, 'public');
        }

        VirtualMuseum::create($data);

        return redirect()->route('admin.virtual-museum')
            ->with('success', 'Virtual Living Museum berhasil ditambahkan!');
    }

    private function safeExtensionFromMime(string $mime, string $default): string
    {
        static $map = [
            'model/gltf-binary' => 'glb',
            'application/octet-stream' => 'glb',
        ];

        return $map[$mime] ?? $default;
    }

    /**
     * Display the specified virtual living museum
     */
    public function showVirtualMuseum($museum_id)
    {
        $museum = VirtualMuseum::with([
            'situsPeninggalan.materi',
            'virtualMuseumObjects',
        ])->findOrFail($museum_id);

        return view('admin.virtual-museum.show', compact('museum'));
    }

    /**
     * Show the form for editing virtual living museum
     */
    public function editVirtualMuseum($museum_id)
    {
        $museum = VirtualMuseum::with('situsPeninggalan')->findOrFail($museum_id);

        // Get all situs - now supporting multiple museums per situs
        $situsOptions = SitusPeninggalan::orderBy('nama')->get();

        return view('admin.virtual-museum.edit', compact('museum', 'situsOptions'));
    }

    /**
     * Update the specified virtual living museum
     */
    public function updateVirtualMuseum(Request $request, $museum_id)
    {
        $museum = VirtualMuseum::findOrFail($museum_id);

        $rules = [
            'situs_id' => 'required|exists:situs_peninggalan,situs_id',
            'nama' => 'required|string|max:255',
        ];

        if ($request->filled('obj_file_path')) {
            $rules['obj_file_path'] = 'required|string|max:500';
        } else {
            $rules['obj_file'] = 'nullable|file|max:307200';
        }

        $request->validate($rules);

        // Capture original path BEFORE any mutation
        $oldObjPath = $museum->path_obj;

        $data = [
            'situs_id' => $request->situs_id,
            'nama' => $request->nama,
        ];

        $museum->update($data);

        try {
            if ($request->filled('obj_file_path')) {
                // Chunked upload: use pre-uploaded path
                $museum->path_obj = $request->obj_file_path;
                $museum->save();
            } elseif ($request->hasFile('obj_file')) {
                // Standard upload
                $file = $request->file('obj_file');
                $mime = $file->getMimeType();
                if (! str_starts_with($mime, 'model/') && $mime !== 'application/octet-stream') {
                    throw ValidationException::withMessages(['obj_file' => 'File model harus bertipe GLB.']);
                }
                // Stream-based GLB magic bytes check
                $handle = fopen($file->getRealPath(), 'rb');
                $header = $handle ? fread($handle, 4) : false;
                if ($handle) {
                    fclose($handle);
                }
                if ($header !== 'glTF') {
                    throw ValidationException::withMessages(['obj_file' => 'File model bukan GLB yang valid.']);
                }
                $extension = $this->safeExtensionFromMime($mime, 'glb');
                $filename = Str::uuid()->toString().'.'.$extension;
                $path = $file->storeAs('virtual-museum/models', $filename, 'public');
                $museum->path_obj = $path;
                $museum->save();
            }

            // Delete old file only after new upload is saved
            if (! empty($oldObjPath) && $oldObjPath !== $museum->path_obj) {
                if (Storage::disk('public')->exists($oldObjPath)) {
                    Storage::disk('public')->delete($oldObjPath);
                }
            }
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            Log::error('File operation failed after DB update', [
                'museum_id' => $museum_id,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.virtual-museum.show', $museum_id)
            ->with('success', 'Virtual Living Museum berhasil diperbarui!');
    }

    /**
     * Remove the specified virtual living museum
     */
    public function destroyVirtualMuseum($museum_id)
    {
        $museum = VirtualMuseum::findOrFail($museum_id);
        $nama = $museum->nama;

        // Delete DB record first
        $museum->delete();

        // Clean up files only after DB deletion succeeds
        try {
            if ($museum->path_obj && Storage::disk('public')->exists($museum->path_obj)) {
                Storage::disk('public')->delete($museum->path_obj);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete model file after museum deletion', [
                'museum_id' => $museum_id,
                'path_obj' => $museum->path_obj,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.virtual-museum')
            ->with('success', "Virtual Living Museum '{$nama}' berhasil dihapus.");
    }

    /**
     * Show the form for creating a new virtual living museum object
     */
    public function createVirtualMuseumObject($museum_id)
    {
        $museum = VirtualMuseum::with(['situsPeninggalan', 'arMarkers.virtualMuseumObjects'])->findOrFail($museum_id);

        return view('admin.virtual-museum-object.create', compact('museum'));
    }

    /**
     * Store a newly created virtual living museum object
     */
    public function storeVirtualMuseumObject(Request $request, $museum_id)
    {
        $museum = VirtualMuseum::findOrFail($museum_id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_real' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'path_obj' => 'nullable|file|max:307200', // 300MB
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,aac|max:10240', // 10MB
            'path_gambar_marker' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'marker_id' => [
                'nullable',
                'integer',
                Rule::exists('ar_marker', 'marker_id')->where(function ($query) use ($museum_id) {
                    $query->where('museum_id', $museum_id);
                }),
            ],
            'nama_marker' => 'nullable|string|max:255',
        ]);

        $data = [
            'situs_id' => $museum->situs_id,
            'museum_id' => $museum_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ];

        // Handle file uploads
        if ($request->hasFile('gambar_real')) {
            $file = $request->file('gambar_real');
            $filename = time().'_gambar_real_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('virtual-museum/objects/images', $filename, 'public');
            $data['gambar_real'] = $path;
        }

        if ($request->hasFile('path_obj')) {
            $file = $request->file('path_obj');
            $filename = time().'_obj_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('virtual-museum/objects/models', $filename, 'public');
            $data['path_obj'] = $path;
        }

        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $filename = time().'_audio_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('virtual-museum/objects/audio', $filename, 'public');
            $data['path_audio'] = $path;
        }

        $marker = $this->resolveMarkerForObject($request, $museum);
        if ($marker) {
            $data['marker_id'] = $marker->marker_id;
            $data['path_gambar_marker'] = $marker->path_gambar_marker;
            $data['path_patt'] = $marker->path_patt;
        }

        VirtualMuseumObject::create($data);

        return redirect()->route('admin.virtual-museum.show', $museum_id)
            ->with('success', 'Object Virtual Living Museum berhasil ditambahkan!');
    }

    /**
     * Display the specified virtual living museum object
     */
    public function showVirtualMuseumObject($object_id)
    {
        $object = VirtualMuseumObject::with(['situsPeninggalan', 'virtualMuseum.situsPeninggalan', 'arMarker'])
            ->findOrFail($object_id);

        return view('admin.virtual-museum-object.show', compact('object'));
    }

    /**
     * Show the form for editing the specified virtual living museum object
     */
    public function editVirtualMuseumObject($object_id)
    {
        $object = VirtualMuseumObject::with(['situsPeninggalan', 'virtualMuseum.situsPeninggalan', 'arMarker'])
            ->findOrFail($object_id);
        $markers = ArMarker::withCount('virtualMuseumObjects')
            ->where('museum_id', $object->museum_id)
            ->orderBy('created_at')
            ->get();

        return view('admin.virtual-museum-object.edit', compact('object', 'markers'));
    }

    /**
     * Update the specified virtual living museum object
     */
    public function updateVirtualMuseumObject(Request $request, $object_id)
    {
        $object = VirtualMuseumObject::with('arMarker')->findOrFail($object_id);
        $museum = VirtualMuseum::findOrFail($object->museum_id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_real' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'path_obj' => 'nullable|file|max:307200', // 300MB
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,aac|max:10240', // 10MB
            'path_gambar_marker' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'marker_id' => [
                'nullable',
                'integer',
                Rule::exists('ar_marker', 'marker_id')->where(function ($query) use ($object) {
                    $query->where('museum_id', $object->museum_id);
                }),
            ],
            'nama_marker' => 'nullable|string|max:255',
        ]);

        $data = [
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->boolean('remove_audio')) {
            if ($object->path_audio && Storage::disk('public')->exists($object->path_audio)) {
                Storage::disk('public')->delete($object->path_audio);
            }
            $data['path_audio'] = null;
        }

        // Handle file uploads
        if ($request->hasFile('gambar_real')) {
            // Delete old file if it exists
            if ($object->gambar_real && Storage::disk('public')->exists($object->gambar_real)) {
                Storage::disk('public')->delete($object->gambar_real);
            }

            $file = $request->file('gambar_real');
            $filename = time().'_gambar_real_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('virtual-museum/objects/images', $filename, 'public');
            $data['gambar_real'] = $path;
        }

        if ($request->hasFile('path_obj')) {
            // Delete old file if it exists
            if ($object->path_obj && Storage::disk('public')->exists($object->path_obj)) {
                Storage::disk('public')->delete($object->path_obj);
            }

            $file = $request->file('path_obj');
            $filename = time().'_obj_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('virtual-museum/objects/models', $filename, 'public');
            $data['path_obj'] = $path;
        }

        if ($request->hasFile('audio_file')) {
            if ($object->path_audio && Storage::disk('public')->exists($object->path_audio)) {
                Storage::disk('public')->delete($object->path_audio);
            }
            $file = $request->file('audio_file');
            $filename = time().'_audio_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('virtual-museum/objects/audio', $filename, 'public');
            $data['path_audio'] = $path;
        }

        $oldMarker = $object->arMarker;
        $marker = $this->resolveMarkerForObject($request, $museum, $object);

        if ($marker) {
            $data['marker_id'] = $marker->marker_id;
            $data['path_gambar_marker'] = $marker->path_gambar_marker;
            $data['path_patt'] = $marker->path_patt;
        }

        $object->update($data);

        if ($oldMarker && $marker && $oldMarker->marker_id !== $marker->marker_id) {
            $this->cleanupUnusedMarker($oldMarker);
        }

        return redirect()->route('admin.virtual-museum-object.show', $object_id)
            ->with('success', 'Object Virtual Living Museum berhasil diperbarui!');
    }

    /**
     * Remove the specified virtual living museum object
     */
    public function destroyVirtualMuseumObject($object_id)
    {
        $object = VirtualMuseumObject::with('arMarker')->findOrFail($object_id);
        $museum_id = $object->museum_id;
        $nama = $object->nama;
        $oldMarker = $object->arMarker;

        // Delete associated files
        $files = ['gambar_real', 'path_obj', 'path_audio'];
        foreach ($files as $fileField) {
            if ($object->$fileField && Storage::disk('public')->exists($object->$fileField)) {
                Storage::disk('public')->delete($object->$fileField);
            }
        }

        if (! $oldMarker) {
            $this->deletePublicFile($object->path_gambar_marker);
            $this->deletePublicFile($object->path_patt);
        }

        $object->delete();

        if ($oldMarker) {
            $this->cleanupUnusedMarker($oldMarker);
        }

        return redirect()->route('admin.virtual-museum.show', $museum_id)
            ->with('success', "Object Virtual Living Museum '{$nama}' berhasil dihapus.");
    }

    private function storeMarkerAssets($file): array
    {
        $originalName = preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'marker';
        $timestamp = now()->format('YmdHis');

        $markerPath = null;
        $patternPath = null;

        try {
            $sourcePath = $file->getRealPath();
            if ($sourcePath === false) {
                throw ValidationException::withMessages([
                    'path_gambar_marker' => 'File marker tidak valid. Silakan unggah ulang gambar marker.',
                ]);
            }

            $patternContent = ArPatternHelper::encodeImageToPattern($sourcePath);
            $patternPath = "virtual-museum/objects/patterns/{$timestamp}_patt_{$baseName}.patt";
            Storage::disk('public')->put($patternPath, $patternContent);

            $markerPng = ArPatternHelper::buildFullMarkerPng($sourcePath, 0.5, 512, 'black');
            $markerPath = "virtual-museum/objects/markers/{$timestamp}_marker_{$baseName}.png";
            Storage::disk('public')->put($markerPath, $markerPng);
        } catch (\Throwable $exception) {
            $this->deletePublicFile($markerPath);
            $this->deletePublicFile($patternPath);

            Log::error('Gagal membuat AR pattern otomatis', [
                'marker_path' => $markerPath,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'path_gambar_marker' => 'Gagal membuat file pattern otomatis dari gambar marker. Pastikan file gambar valid lalu coba lagi.',
            ]);
        }

        return [
            'path_gambar_marker' => $markerPath,
            'path_patt' => $patternPath,
        ];
    }

    private function resolveMarkerForObject(Request $request, VirtualMuseum $museum, ?VirtualMuseumObject $currentObject = null): ?ArMarker
    {
        $selectedMarkerId = $request->input('marker_id');

        if ($request->hasFile('path_gambar_marker')) {
            $assets = $this->storeMarkerAssets($request->file('path_gambar_marker'));
            $markerName = $request->filled('nama_marker')
                ? $request->input('nama_marker')
                : ('Marker '.$request->input('nama', 'AR'));

            return ArMarker::create([
                'situs_id' => $museum->situs_id,
                'museum_id' => $museum->museum_id,
                'nama' => $markerName,
                'path_gambar_marker' => $assets['path_gambar_marker'],
                'path_patt' => $assets['path_patt'],
            ]);
        }

        if ($selectedMarkerId) {
            return ArMarker::where('museum_id', $museum->museum_id)
                ->findOrFail($selectedMarkerId);
        }

        return $currentObject?->arMarker;
    }

    private function cleanupUnusedMarker(ArMarker $marker): void
    {
        $isStillUsed = VirtualMuseumObject::where('marker_id', $marker->marker_id)->exists();

        if ($isStillUsed) {
            return;
        }

        $this->deletePublicFile($marker->path_gambar_marker);
        $this->deletePublicFile($marker->path_patt);
        $marker->delete();
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Display reports management
     */
    public function reports()
    {
        $reports = LaporanPeninggalan::with(['user', 'laporanGambar'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reports', compact('reports'));
    }

    /**
     * Display the specified report
     */
    public function showReport($id)
    {
        $report = LaporanPeninggalan::with(['user', 'laporanGambar', 'laporanKomentar.user', 'laporanSuka.user'])
            ->findOrFail($id);

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Remove the specified report
     */
    public function destroyReport($id)
    {
        try {
            $report = LaporanPeninggalan::findOrFail($id);
            $nama = $report->nama_peninggalan;

            // Delete associated files
            foreach ($report->laporanGambar as $gambar) {
                if ($gambar->path_gambar && Storage::disk('public')->exists($gambar->path_gambar)) {
                    Storage::disk('public')->delete($gambar->path_gambar);
                }
            }

            $report->delete();

            return redirect()->route('admin.reports')
                ->with('success', "Laporan '{$nama}' berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->route('admin.reports')
                ->with('error', 'Terjadi kesalahan saat menghapus laporan.');
        }
    }

    /**
     * Display feedback management
     */
    public function feedback()
    {
        $feedback = KritikSaran::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.feedback', compact('feedback'));
    }

    /**
     * Delete a feedback
     */
    public function destroyFeedback($id)
    {
        try {
            $feedback = KritikSaran::findOrFail($id);
            $feedback->delete();

            return redirect()->route('admin.feedback')
                ->with('success', 'Feedback berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus feedback', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('admin.feedback')
                ->with('error', 'Gagal menghapus feedback.');
        }
    }

    /**
     * Show the form for creating a new materi
     */
    public function createMateri()
    {
        $eras = Era::orderBy('urutan')->get();

        return view('admin.materi.create', compact('eras'));
    }

    /**
     * Store a newly created materi
     */
    public function storeMateri(Request $request)
    {
        $request->validate([
            'era_id' => 'required|exists:era,era_id',
            'bab' => 'required|integer|min:1',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        // Auto generate urutan (get next available order)
        $nextUrutan = Materi::max('urutan') + 1;

        $data = $request->all();
        $data['urutan'] = $nextUrutan;

        Materi::create($data);

        return redirect()->route('admin.materi')
            ->with('success', 'Materi berhasil ditambahkan!');
    }

    /**
     * Display the specified materi
     */
    public function showMateri($id)
    {
        $materi = Materi::with(['era', 'pretest', 'posttest', 'ebook', 'situsPeninggalan'])
            ->findOrFail($id);

        return view('admin.materi.show', compact('materi'));
    }

    /**
     * Show the form for editing materi
     */
    public function editMateri($id)
    {
        $materi = Materi::findOrFail($id);
        $eras = Era::orderBy('urutan')->get();

        return view('admin.materi.edit', compact('materi', 'eras'));
    }

    /**
     * Update the specified materi
     */
    public function updateMateri(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);

        $request->validate([
            'era_id' => 'required|exists:era,era_id',
            'bab' => 'required|integer|min:1',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        $materi->update($request->only(['era_id', 'bab', 'judul', 'deskripsi']));

        return redirect()->route('admin.materi')
            ->with('success', 'Materi berhasil diperbarui!');
    }

    /**
     * Remove the specified materi
     */
    public function destroyMateri($id)
    {
        $materi = Materi::findOrFail($id);

        // Check if materi has related data
        if (
            $materi->pretest()->exists() || $materi->posttest()->exists() ||
            $materi->ebook()->exists() || $materi->situsPeninggalan()->exists()
        ) {
            return redirect()->route('admin.materi')
                ->with('error', 'Tidak dapat menghapus materi yang memiliki data terkait!');
        }

        $materi->delete();

        return redirect()->route('admin.materi')
            ->with('success', 'Materi berhasil dihapus!');
    }

    /**
     * Update materi order via drag & drop
     */
    public function updateMateriOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:materi,materi_id',
            'items.*.position' => 'required|integer|min:1',
        ]);

        foreach ($request->items as $item) {
            Materi::where('materi_id', $item['id'])
                ->update(['urutan' => $item['position']]);
        }

        return response()->json(['message' => 'Urutan materi berhasil diperbarui!']);
    }

    // ========== PRETEST MANAGEMENT ==========

    /**
     * Display pretest for specific materi
     */
    public function pretest($materi_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $pretests = Pretest::where('materi_id', $materi_id)
            ->orderBy('pretest_id', 'asc')
            ->paginate(20);

        return view('admin.pretest.index', compact('materi', 'pretests'));
    }

    /**
     * Show the form for creating a new pretest
     */
    public function createPretest($materi_id)
    {
        $materi = Materi::findOrFail($materi_id);

        return view('admin.pretest.create', compact('materi'));
    }

    /**
     * Store a newly created pretest
     */
    public function storePretest(Request $request, $materi_id)
    {
        $materi = Materi::findOrFail($materi_id);

        $request->validate([
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string|max:255',
            'pilihan_b' => 'required|string|max:255',
            'pilihan_c' => 'required|string|max:255',
            'pilihan_d' => 'required|string|max:255',
            'pilihan_e' => 'nullable|string|max:255',
            'jawaban_benar' => 'required|in:A,B,C,D,E',
        ]);

        $data = $request->all();
        $data['materi_id'] = $materi_id;

        Pretest::create($data);

        return redirect()->route('admin.pretest', $materi_id)
            ->with('success', 'Soal pretest berhasil ditambahkan!');
    }

    /**
     * Display the specified pretest
     */
    public function showPretest($materi_id, $pretest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $pretest = Pretest::where('materi_id', $materi_id)->findOrFail($pretest_id);

        // Get previous and next pretest questions
        $previousPretest = Pretest::where('materi_id', $materi_id)
            ->where('pretest_id', '<', $pretest_id)
            ->orderBy('pretest_id', 'desc')
            ->first();

        $nextPretest = Pretest::where('materi_id', $materi_id)
            ->where('pretest_id', '>', $pretest_id)
            ->orderBy('pretest_id', 'asc')
            ->first();

        return view('admin.pretest.show', compact('materi', 'pretest', 'previousPretest', 'nextPretest'));
    }

    /**
     * Show the form for editing pretest
     */
    public function editPretest($materi_id, $pretest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $pretest = Pretest::where('materi_id', $materi_id)->findOrFail($pretest_id);

        return view('admin.pretest.edit', compact('materi', 'pretest'));
    }

    /**
     * Update the specified pretest
     */
    public function updatePretest(Request $request, $materi_id, $pretest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $pretest = Pretest::where('materi_id', $materi_id)->findOrFail($pretest_id);

        $request->validate([
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string|max:255',
            'pilihan_b' => 'required|string|max:255',
            'pilihan_c' => 'required|string|max:255',
            'pilihan_d' => 'required|string|max:255',
            'pilihan_e' => 'nullable|string|max:255',
            'jawaban_benar' => 'required|in:A,B,C,D,E',
        ]);

        $pretest->update($request->all());

        return redirect()->route('admin.pretest', $materi_id)
            ->with('success', 'Soal pretest berhasil diperbarui!');
    }

    /**
     * Remove the specified pretest
     */
    public function destroyPretest($materi_id, $pretest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $pretest = Pretest::where('materi_id', $materi_id)->findOrFail($pretest_id);

        $pretest->delete();

        return redirect()->route('admin.pretest', $materi_id)
            ->with('success', 'Soal pretest berhasil dihapus!');
    }

    // ========== POSTTEST MANAGEMENT ==========

    /**
     * Display posttest for specific materi
     */
    public function posttest($materi_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $posttests = Posttest::where('materi_id', $materi_id)
            ->orderBy('posttest_id', 'asc')
            ->paginate(20);

        return view('admin.posttest.index', compact('materi', 'posttests'));
    }

    /**
     * Show the form for creating a new posttest
     */
    public function createPosttest($materi_id)
    {
        $materi = Materi::findOrFail($materi_id);

        return view('admin.posttest.create', compact('materi'));
    }

    /**
     * Store a newly created posttest
     */
    public function storePosttest(Request $request, $materi_id)
    {
        $materi = Materi::findOrFail($materi_id);

        $request->validate([
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string|max:255',
            'pilihan_b' => 'required|string|max:255',
            'pilihan_c' => 'required|string|max:255',
            'pilihan_d' => 'required|string|max:255',
            'pilihan_e' => 'nullable|string|max:255',
            'jawaban_benar' => 'required|in:A,B,C,D,E',
        ]);

        $data = $request->all();
        $data['materi_id'] = $materi_id;

        Posttest::create($data);

        return redirect()->route('admin.posttest', $materi_id)
            ->with('success', 'Soal posttest berhasil ditambahkan!');
    }

    /**
     * Display the specified posttest
     */
    public function showPosttest($materi_id, $posttest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $posttest = Posttest::where('materi_id', $materi_id)->findOrFail($posttest_id);

        // Get previous and next posttest questions
        $previousPosttest = Posttest::where('materi_id', $materi_id)
            ->where('posttest_id', '<', $posttest_id)
            ->orderBy('posttest_id', 'desc')
            ->first();

        $nextPosttest = Posttest::where('materi_id', $materi_id)
            ->where('posttest_id', '>', $posttest_id)
            ->orderBy('posttest_id', 'asc')
            ->first();

        return view('admin.posttest.show', compact('materi', 'posttest', 'previousPosttest', 'nextPosttest'));
    }

    /**
     * Show the form for editing posttest
     */
    public function editPosttest($materi_id, $posttest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $posttest = Posttest::where('materi_id', $materi_id)->findOrFail($posttest_id);

        return view('admin.posttest.edit', compact('materi', 'posttest'));
    }

    /**
     * Update the specified posttest
     */
    public function updatePosttest(Request $request, $materi_id, $posttest_id)
    {
        $materi = Materi::findOrFail($materi_id);
        $posttest = Posttest::where('materi_id', $materi_id)->findOrFail($posttest_id);

        $request->validate([
            'pertanyaan' => 'required|string',
            'pilihan_a' => 'required|string|max:255',
            'pilihan_b' => 'required|string|max:255',
            'pilihan_c' => 'required|string|max:255',
            'pilihan_d' => 'required|string|max:255',
            'pilihan_e' => 'nullable|string|max:255',
            'jawaban_benar' => 'required|in:A,B,C,D,E',
        ]);

        $posttest->update($request->all());

        return redirect()->route('admin.posttest', $materi_id)
            ->with('success', 'Soal posttest berhasil diperbarui!');
    }

    /**
     * Remove the specified posttest
     */
    public function destroyPosttest($materi_id, $posttest_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        $posttest = Posttest::find($posttest_id);
        if (! $posttest) {
            return back()->with('error', 'Soal posttest tidak ditemukan.');
        }
        
        $posttest->delete();

        return redirect()->route('admin.posttest', $materi_id)
            ->with('success', 'Soal posttest berhasil dihapus!');
    }

    /**
     * Display ebook management for a specific materi
     */
    public function ebook($materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        
        $ebooks = Ebook::where('materi_id', $materi_id)->paginate(10);

        return view('admin.ebook.index', compact('materi', 'ebooks'));
    }

    /**
     * Show the form for creating a new ebook
     */
    public function createEbook($materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }

        return view('admin.ebook.create', compact('materi'));
    }

    /**
     * Store a newly created ebook
     */
    public function storeEbook(Request $request, $materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'path_file' => 'required|file|mimes:pdf|max:51200', // 50MB max for PDF
        ]);

        $data = [
            'materi_id' => $materi_id,
            'judul' => $request->judul,
        ];

        // Handle file upload
        if ($request->hasFile('path_file')) {
            $file = $request->file('path_file');
            $filename = time().'_ebook_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('ebooks', $filename, 'public');
            $data['path_file'] = $path;
        }

        Ebook::create($data);

        return redirect()->route('admin.ebook', $materi_id)
            ->with('success', 'E-book berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified ebook
     */
    public function editEbook($materi_id, $ebook_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        $ebook = Ebook::where('materi_id', $materi_id)->where('ebook_id', $ebook_id)->firstOrFail();

        return view('admin.ebook.edit', compact('materi', 'ebook'));
    }

    /**
     * Update the specified ebook
     */
    public function updateEbook(Request $request, $materi_id, $ebook_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        $ebook = Ebook::where('materi_id', $materi_id)->where('ebook_id', $ebook_id)->firstOrFail();

        $request->validate([
            'judul' => 'required|string|max:255',
            'path_file' => 'nullable|file|mimes:pdf|max:51200', // 50MB max for PDF
        ]);

        $data = [
            'judul' => $request->judul,
        ];

        // Handle file upload
        if ($request->hasFile('path_file')) {
            // Delete old file if it exists
            if ($ebook->path_file && Storage::disk('public')->exists($ebook->path_file)) {
                Storage::disk('public')->delete($ebook->path_file);
            }

            $file = $request->file('path_file');
            $filename = time().'_ebook_'.preg_replace('/[^a-zA-Z0-9\._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('ebooks', $filename, 'public');
            $data['path_file'] = $path;
        }

        $ebook->update($data);

        return redirect()->route('admin.ebook', $materi_id)
            ->with('success', 'E-book berhasil diperbarui!');
    }

    /**
     * Remove the specified ebook
     */
    public function destroyEbook($ebook_id)
    {
        $ebook = Ebook::find($ebook_id);
        if (! $ebook) {
            return back()->with('error', 'E-book tidak ditemukan.');
        }
        $materi_id = $ebook->materi_id;

        // Delete associated file
        if ($ebook->path_file && Storage::disk('public')->exists($ebook->path_file)) {
            Storage::disk('public')->delete($ebook->path_file);
        }

        $ebook->delete();

        return redirect()->route('admin.ebook', $materi_id)
            ->with('success', 'E-book berhasil dihapus!');
    }

    // ========== TUGAS MANAGEMENT ==========

    /**
     * Display tugas index page
     */
    public function tugas($materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        $tugas = $materi->tugas()->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.tugas.index', compact('materi', 'tugas'));
    }

    /**
     * Show form to create new tugas
     */
    public function createTugas($materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }

        return view('admin.tugas.create', compact('materi'));
    }

    /**
     * Store new tugas
     */
    public function storeTugas(Request $request, $materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $tugasData = [
            'materi_id' => $materi_id,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
        ];

        // Handle image upload if provided
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('tugas-images', 'public');
            $tugasData['gambar'] = $path;
        }

        $tugas = Tugas::create($tugasData);

        return redirect()->route('admin.tugas', $materi_id)
            ->with('success', 'Tugas berhasil ditambahkan');
    }

    /**
     * Show form to edit tugas
     */
    public function editTugas($materi_id, $tugas_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        $tugas = Tugas::find($tugas_id);
        if (! $tugas) {
            return back()->with('error', 'Tugas tidak ditemukan.');
        }

        return view('admin.tugas.edit', compact('materi', 'tugas'));
    }

    /**
     * Update tugas
     */
    public function updateTugas(Request $request, $materi_id, $tugas_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }
        $tugas = Tugas::find($tugas_id);
        if (! $tugas) {
            return back()->with('error', 'Tugas tidak ditemukan.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $tugas->judul = $validated['judul'];
        $tugas->deskripsi = $validated['deskripsi'];

        // Handle image upload if provided
        if ($request->hasFile('gambar')) {
            // Delete previous image if exists
            if ($tugas->gambar) {
                Storage::disk('public')->delete($tugas->gambar);
            }

            $path = $request->file('gambar')->store('tugas-images', 'public');
            $tugas->gambar = $path;
        }

        $tugas->save();

        return redirect()->route('admin.tugas', $materi_id)
            ->with('success', 'Tugas berhasil diperbarui');
    }

    /**
     * Delete tugas
     */
    public function destroyTugas($materi_id, $tugas_id)
    {
        $tugas = Tugas::where('materi_id', $materi_id)
            ->where('tugas_id', $tugas_id)
            ->firstOrFail();

        // Delete image if exists
        if ($tugas->gambar) {
            Storage::disk('public')->delete($tugas->gambar);
        }

        $tugas->delete();

        return redirect()->route('admin.tugas', $materi_id)
            ->with('success', 'Tugas berhasil dihapus');
    }

    public function riwayatPengembang()
    {
        $riwayatPengembang = RiwayatPengembang::orderBy('tahun', 'desc')->paginate(10);

        return view('admin.riwayat-pengembang.index', compact('riwayatPengembang'));
    }

    public function createRiwayatPengembang()
    {
        return view('admin.riwayat-pengembang.create');
    }

    public function storeRiwayatPengembang()
    {
        $validated = request()->validate([
            'judul' => 'required|string|max:255',
            'tahun' => 'required|date',
            'tahun_selesai' => 'nullable|date|after_or_equal:tahun',
        ]);

        RiwayatPengembang::create($validated);

        return redirect()->route('admin.riwayat-pengembang')
            ->with('success', 'Riwayat pengembang berhasil ditambahkan');
    }

    public function editRiwayatPengembang(string $id)
    {
        $riwayatPengembang = RiwayatPengembang::findOrFail($id);

        return view('admin.riwayat-pengembang.edit', compact('riwayatPengembang'));
    }

    public function updateRiwayatPengembang(string $id)
    {
        $validated = request()->validate([
            'judul' => 'required|string|max:255',
            'tahun' => 'required|date',
            'tahun_selesai' => 'nullable|date|after_or_equal:tahun',
        ]);

        RiwayatPengembang::findOrFail($id)->update($validated);

        return redirect()->route('admin.riwayat-pengembang')
            ->with('success', 'Riwayat pengembang berhasil diperbarui');
    }

    public function destroyRiwayatPengembang(string $id)
    {
        RiwayatPengembang::findOrFail($id)->delete();

        return redirect()->route('admin.riwayat-pengembang')
            ->with('success', 'Riwayat pengembang berhasil dihapus');
    }
}
