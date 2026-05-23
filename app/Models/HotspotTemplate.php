<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotspotTemplate extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_NAVIGATION = 'navigation';

    const TYPE_INFO = 'info';

    const TYPE_TEXT = 'text';

    const TYPE_COMPASS = 'compass';

    protected $table = 'templat_hotspot';

    protected $primaryKey = 'templat_hotspot_id';

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

    protected $appends = ['id'];

    protected $hidden = ['templat_hotspot_id'];

    /**
     * Accessor for 'id' to maintain frontend compatibility.
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->templat_hotspot_id,
            set: fn ($value) => $this->templat_hotspot_id = $value,
        );
    }

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
