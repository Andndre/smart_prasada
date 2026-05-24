<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\HotspotTemplate;
use App\Models\Scene;
use App\Models\SitusPeninggalan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PanoramaController extends Controller
{
    /**
     * Display the VR Panorama Editor view.
     */
    public function editor(Request $request, int $situsId)
    {
        $situs = SitusPeninggalan::findOrFail($situsId);
        return view('admin.panorama.editor', compact('situs'));
    }

    /**
     * Display a listing of scenes for a specific situs.
     */
    public function index(int $situsId): JsonResponse
    {
        $situs = SitusPeninggalan::findOrFail($situsId);
        $scenes = $situs->scenes()->with('hotspots')->orderBy('order')->get();

        return response()->json([
            'situs' => [
                'id' => $situs->situs_id,
                'name' => $situs->nama,
            ],
            'scenes' => $scenes,
        ]);
    }

    /**
     * Store a newly created scene.
     */
    public function storeScene(Request $request): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $data = $request->validate([
            'situs_id' => 'required|exists:situs_peninggalan,situs_id',
            'name' => 'required|string|max:255',
            'image' => 'required|string',
            'camera_x' => 'nullable|numeric',
            'camera_y' => 'nullable|numeric',
            'camera_z' => 'nullable|numeric',
            'scene_type' => ['nullable', Rule::in(['panorama', 'virtual_museum'])],
        ]);

        // Auto-calculate order as max(order) + 1 for this situs
        $data['order'] = Scene::where('situs_id', $data['situs_id'])->max('order') + 1;
        $data['scene_type'] = $data['scene_type'] ?? Scene::TYPE_PANORAMA;

        $scene = Scene::create($data);
        $scene->load('hotspots');

        return response()->json($scene, 201);
    }

    /**
     * Update the specified scene.
     */
    public function updateScene(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $scene = Scene::findOrFail($id);
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'camera_x' => 'nullable|numeric',
            'camera_y' => 'nullable|numeric',
            'camera_z' => 'nullable|numeric',
            'order' => 'nullable|integer',
            'scene_type' => ['nullable', Rule::in(['panorama', 'virtual_museum'])],
            'hotspot_defaults' => 'nullable|array',
        ]);

        $scene->update($data);

        return response()->json($scene->load('hotspots'));
    }

    /**
     * Remove the specified scene.
     */
    public function destroyScene(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $scene = Scene::findOrFail($id);
        $scene->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Reorder scenes for a situs.
     */
    public function reorderScenes(Request $request): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:adegan,adegan_id',
        ]);

        foreach ($data['order'] as $index => $sceneId) {
            Scene::where('adegan_id', $sceneId)->update(['order' => $index]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Hotspot CRUD ───────────────────────────────────────────────

    /**
     * Store a newly created hotspot.
     */
    public function storeHotspot(Request $request): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $data = $request->validate([
            'scene_id' => 'required|exists:adegan,adegan_id',
            'label' => 'nullable|string|max:255',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'position_z' => 'nullable|numeric',
            'rotation_x' => 'nullable|numeric',
            'rotation_y' => 'nullable|numeric',
            'rotation_z' => 'nullable|numeric',
            'target_scene_id' => 'nullable|exists:adegan,adegan_id',
            'type' => ['nullable', Rule::in(['navigation', 'info', 'text'])],
            'modal_title' => 'nullable|string|max:255',
            'modal_content' => 'nullable|string',
            'modal_image' => 'nullable|string',
            'template_id' => 'nullable|exists:templat_hotspot,templat_hotspot_id',
            'animation_config' => 'nullable|array',
        ]);

        // Set defaults
        $data['type'] = $data['type'] ?? Hotspot::TYPE_NAVIGATION;

        // Map request inputs to database columns
        $data['adegan_id'] = $data['scene_id'];
        unset($data['scene_id']);

        if (\array_key_exists('target_scene_id', $data)) {
            $data['target_adegan_id'] = $data['target_scene_id'];
            unset($data['target_scene_id']);
        }
        if (\array_key_exists('template_id', $data)) {
            $data['templat_hotspot_id'] = $data['template_id'];
            unset($data['template_id']);
        }

        $data['order'] = Hotspot::where('adegan_id', $data['adegan_id'])->max('order') + 1;

        // Apply hotspot defaults from scene if not provided
        $scene = Scene::find($data['adegan_id']);
        if ($scene?->hotspot_defaults) {
            $defaults = $scene->hotspot_defaults;
            $data['position_x'] ??= ($defaults['position_x'] ?? 0);
            $data['position_y'] ??= ($defaults['position_y'] ?? -0.5);
            $data['position_z'] ??= ($defaults['position_z'] ?? -4);
        }

        $hotspot = Hotspot::create($data);
        $hotspot->load('targetScene', 'template');

        return response()->json($hotspot, 201);
    }

    /**
     * Update the specified hotspot.
     */
    public function updateHotspot(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $hotspot = Hotspot::findOrFail($id);
        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'position_z' => 'nullable|numeric',
            'rotation_x' => 'nullable|numeric',
            'rotation_y' => 'nullable|numeric',
            'rotation_z' => 'nullable|numeric',
            'target_scene_id' => 'nullable|exists:adegan,adegan_id',
            'order' => 'nullable|integer',
            'type' => ['nullable', Rule::in(['navigation', 'info', 'text'])],
            'modal_title' => 'nullable|string|max:255',
            'modal_content' => 'nullable|string',
            'modal_image' => 'nullable|string',
            'template_id' => 'nullable|exists:templat_hotspot,templat_hotspot_id',
            'animation_config' => 'nullable|array',
        ]);

        if (\array_key_exists('target_scene_id', $data)) {
            $data['target_adegan_id'] = $data['target_scene_id'];
            unset($data['target_scene_id']);
        }
        if (\array_key_exists('template_id', $data)) {
            $data['templat_hotspot_id'] = $data['template_id'];
            unset($data['template_id']);
        }

        $hotspot->update($data);

        return response()->json($hotspot->load('targetScene', 'template'));
    }

    /**
     * Remove the specified hotspot.
     */
    public function destroyHotspot(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $hotspot = Hotspot::findOrFail($id);
        $hotspot->delete();

        return response()->json(['ok' => true]);
    }

    // ── Hotspot Templates CRUD ─────────────────────────────────────

    /**
     * Display a listing of hotspot templates.
     */
    public function indexTemplates(): JsonResponse
    {
        $templates = HotspotTemplate::all();

        return response()->json($templates);
    }

    /**
     * Store a newly created template.
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['navigation', 'info', 'text'])],
            'file_path' => 'required|string',
            'thumbnail_path' => 'nullable|string',
            'is_animated' => 'nullable|boolean',
            'default_color' => 'nullable|string',
        ]);

        $data['is_animated'] = $data['is_animated'] ?? false;

        $template = HotspotTemplate::create($data);

        return response()->json($template, 201);
    }

    /**
     * Update the specified template.
     */
    public function updateTemplate(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $template = HotspotTemplate::findOrFail($id);
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => ['nullable', Rule::in(['navigation', 'info', 'text'])],
            'file_path' => 'nullable|string',
            'thumbnail_path' => 'nullable|string',
            'is_animated' => 'nullable|boolean',
            'default_color' => 'nullable|string',
        ]);

        $template->update($data);

        return response()->json($template);
    }

    /**
     * Remove the specified template.
     */
    public function destroyTemplate(Request $request, int $id): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(406, 'Expected JSON request');
        }

        $template = HotspotTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['ok' => true]);
    }

    // ── Image Upload ────────────────────────────────────────────────

    /**
     * Upload panorama or hotspot image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:51200',
            'type' => ['nullable', Rule::in(['panorama', 'hotspot'])],
        ]);

        $type = $request->input('type', 'panorama');
        $directory = $type === 'panorama' ? 'panoramas' : 'hotspot-images';

        // Generate UUID filename
        $extension = $request->file('file')->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        $path = $request->file('file')->storeAs($directory, $filename, 'public');
        $url = "/storage/{$path}";

        return response()->json([
            'url' => $url,
            'path' => $path,
            'filename' => $filename,
        ]);
    }

    // ── Public Viewer API ───────────────────────────────────────────

    /**
     * Get tour data for public viewer.
     */
    public function viewer(int $situsId): JsonResponse
    {
        $situs = SitusPeninggalan::findOrFail($situsId);

        return response()->json($situs->toViewerJson());
    }
}
