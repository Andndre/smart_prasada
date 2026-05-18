<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotTemplate extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_NAVIGATION = 'navigation';
    const TYPE_INFO = 'info';
    const TYPE_TEXT = 'text';
    const TYPE_COMPASS = 'compass';

    protected $fillable = [
        'name',
        'type',
        'file_path',
        'thumbnail_path',
        'is_animated',
        'default_color',
    ];

    protected $casts = [
        'is_animated' => 'boolean',
    ];

    /**
     * Check if this is a navigation template.
     */
    public function isNavigation(): bool
    {
        return $this->type === self::TYPE_NAVIGATION;
    }

    /**
     * Check if this is an info template.
     */
    public function isInfo(): bool
    {
        return $this->type === self::TYPE_INFO;
    }

    /**
     * Get available types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_NAVIGATION,
            self::TYPE_INFO,
            self::TYPE_TEXT,
            self::TYPE_COMPASS,
        ];
    }

    /**
     * Convert to viewer JSON format.
     */
    public function toViewerJson(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'filePath' => $this->file_path,
            'thumbnailPath' => $this->thumbnail_path,
            'isAnimated' => $this->is_animated,
            'defaultColor' => $this->default_color,
        ];
    }
}