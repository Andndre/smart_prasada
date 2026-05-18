<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    use HasFactory;

    // Scene type constants
    const TYPE_PANORAMA = 'panorama';
    const TYPE_VIRTUAL_MUSEUM = 'virtual_museum';

    protected $fillable = [
        'situs_id',
        'name',
        'image',
        'camera_x',
        'camera_y',
        'camera_z',
        'order',
        'scene_type',
        'hotspot_defaults',
    ];

    protected $casts = [
        'camera_x' => 'float',
        'camera_y' => 'float',
        'camera_z' => 'float',
        'order' => 'integer',
        'hotspot_defaults' => 'array',
    ];

    /**
     * Get the situs that owns this scene.
     */
    public function situs(): BelongsTo
    {
        return $this->belongsTo(SitusPeninggalan::class, 'situs_id', 'situs_id');
    }

    /**
     * Get the hotspots for this scene.
     */
    public function hotspots(): HasMany
    {
        return $this->hasMany(Hotspot::class)->orderBy('order');
    }

    /**
     * Get the target scene (for navigation hotspots).
     */
    public function targetScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'target_scene_id');
    }

    /**
     * Get camera position as A-Frame formatted string.
     */
    public function getCameraPositionAttribute(): string
    {
        return "{$this->camera_x} {$this->camera_y} {$this->camera_z}";
    }

    /**
     * Check if this is a panorama scene.
     */
    public function isPanorama(): bool
    {
        return $this->scene_type === self::TYPE_PANORAMA;
    }

    /**
     * Check if this is a virtual museum scene.
     */
    public function isVirtualMuseum(): bool
    {
        return $this->scene_type === self::TYPE_VIRTUAL_MUSEUM;
    }

    /**
     * Convert to viewer JSON format for A-Frame consumption.
     */
    public function toViewerJson(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'cameraPosition' => $this->camera_position,
            'sceneType' => $this->scene_type,
            'hotspots' => $this->hotspots->map(fn (Hotspot $hotspot) => $hotspot->toViewerJson())->toArray(),
        ];
    }
}