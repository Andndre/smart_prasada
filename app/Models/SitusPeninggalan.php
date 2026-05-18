<?php

namespace App\Models;

use Database\Factories\SitusPeninggalanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property bool|mixed $is_unlocked
 * @property mixed|string $type
 */
class SitusPeninggalan extends Model
{
    /** @use HasFactory<SitusPeninggalanFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'situs_peninggalan';

    protected $primaryKey = 'situs_id';

    protected $fillable = [
        'nama',
        'alamat',
        'deskripsi',
        'lat',
        'lng',
        'materi_id',
        'user_id',
        'thumbnail',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'materi_id' => 'integer',
        'user_id' => 'integer',
    ];

    // Relationships
    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class, 'materi_id', 'materi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function virtualMuseum(): HasMany
    {
        return $this->hasMany(VirtualMuseum::class, 'situs_id', 'situs_id');
    }

    public function virtualMuseumObject(): HasMany
    {
        return $this->hasMany(VirtualMuseumObject::class, 'situs_id', 'situs_id');
    }

    public function arMarkers(): HasMany
    {
        return $this->hasMany(ArMarker::class, 'situs_id', 'situs_id');
    }

    public function aksesSitusUser(): HasMany
    {
        return $this->hasMany(AksesSitusUser::class, 'situs_id', 'situs_id');
    }

    /**
     * Get the scenes for virtual tour.
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class, 'situs_id', 'situs_id')->orderBy('order');
    }

    /**
     * Get panorama scenes only.
     */
    public function panoramaScenes(): HasMany
    {
        return $this->scenes()->where('scene_type', Scene::TYPE_PANORAMA);
    }

    /**
     * Get the URL for the thumbnail image
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return asset('storage/'.$this->thumbnail);
        }

        return asset('images/placeholder/default-situs.png');
    }

    /**
     * Convert to viewer JSON format for 360 panorama consumption.
     */
    public function toViewerJson(): array
    {
        $this->load('panoramaScenes.hotspots');

        return [
            'id' => $this->situs_id,
            'name' => $this->nama,
            'description' => $this->deskripsi,
            'location' => $this->alamat,
            'coverImage' => $this->thumbnail_url,
            'scenes' => $this->panoramaScenes->map(fn (Scene $scene) => $scene->toViewerJson())->toArray(),
        ];
    }
}
