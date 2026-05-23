<?php

namespace App\Http\Controllers;

use App\Models\AsetHotspot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AsetHotspotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = AsetHotspot::latest()->get();
        
        $assets->transform(function($asset) {
            $asset->url = Storage::disk('public')->url($asset->file_path);
            return $asset;
        });
        
        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'file' => 'required|file|mimes:png,jpg,jpeg,svg,webm,mp4|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($request->nama) . '-' . time() . '.' . $extension;
        
        // Determine type based on extension
        $tipe = in_array(strtolower($extension), ['webm', 'mp4']) ? 'video' : 'image';
        
        $path = $file->storeAs('hotspots/assets', $filename, 'public');

        $asset = AsetHotspot::create([
            'nama' => $request->nama,
            'file_path' => $path,
            'tipe' => $tipe,
        ]);

        // When stored in public disk, Storage::disk('public')->url() gives the correct URL
        $asset->url = Storage::disk('public')->url($asset->file_path);

        return response()->json([
            'status' => 'success',
            'message' => 'Aset berhasil diunggah',
            'data' => $asset
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $asset = AsetHotspot::findOrFail($id);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($asset->file_path)) {
            Storage::disk('public')->delete($asset->file_path);
        }
        
        $asset->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Aset berhasil dihapus'
        ]);
    }
}
