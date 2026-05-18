<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotspot extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_NAVIGATION = 'navigation';
    const TYPE_INFO = 'info';
    const TYPE_TEXT = 'text';
    const TYPE_COMPASS = 'compass';

    protected $fillable = [
        'scene_id',
        'label',
        'position_x',
        'position_y',
        'position_z',
        'rotation_x',
        'rotation_y',
        'rotation_z',
        'target_scene_id',
        'color',
        'order',
        'type',
        'modal_title',
        'modal_content',
        'modal_image',
        'template_id',
        'animation_config',
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'position_z' => 'float',
        'rotation_x' => 'float',
        'rotation_y' => 'float',
        'rotation_z' => 'float',
        'order' => 'integer',
        'animation_config' => 'array',
    ];

    /**
     * Get the scene that owns this hotspot.
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /**
     * Get the target scene (for navigation hotspots).
     */
    public function targetScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'target_scene_id');
    }

    /**
     * Get the template used by this hotspot.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(HotspotTemplate::class);
    }

    /**
     * Check if this is a navigation hotspot.
     */
    public function isNavigation(): bool
    {
        return $this->type === self::TYPE_NAVIGATION;
    }

    /**
     * Check if this is an info hotspot.
     */
    public function isInfo(): bool
    {
        return $this->type === self::TYPE_INFO;
    }

    /**
     * Get position as A-Frame formatted string.
     */
    public function getPositionAttribute(): string
    {
        return "{$this->position_x} {$this->position_y} {$this->position_z}";
    }

    /**
     * Get rotation as A-Frame formatted string.
     */
    public function getRotationAttribute(): string
    {
        return "{$this->rotation_x} {$this->rotation_y} {$this->rotation_z}";
    }

    /**
     * Convert to viewer JSON format for A-Frame consumption.
     */
    public function toViewerJson(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type ?? self::TYPE_NAVIGATION,
            'position' => $this->position,
            'rotation' => $this->rotation,
            'targetScene' => $this->target_scene_id,
            'color' => $this->color,
            'modalTitle' => $this->modal_title,
            'modalContent' => $this->modal_content,
            'modalImage' => $this->modal_image,
            'template' => $this->relationLoaded('template')
                ? $this->template?->toViewerJson()
                : null,
        ];
    }
}