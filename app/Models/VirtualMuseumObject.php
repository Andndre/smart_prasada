<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property bool|mixed $is_unlocked
 * @property mixed|string $type
 */
class VirtualMuseumObject extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'virtual_museum_object';

    protected $primaryKey = 'object_id';

    protected $fillable = [
        'situs_id',
        'museum_id',
        'marker_id',
        'nama',
        'mesh_name',
        'gambar_real',
        'path_obj',
        'path_gambar_marker',
        'path_patt',
        'deskripsi',
        'path_audio',
    ];

    protected $casts = [
        'situs_id' => 'integer',
        'museum_id' => 'integer',
        'marker_id' => 'integer',
    ];

    // Relationships
    public function situsPeninggalan(): BelongsTo
    {
        return $this->belongsTo(SitusPeninggalan::class, 'situs_id', 'situs_id');
    }

    public function virtualMuseum(): BelongsTo
    {
        return $this->belongsTo(VirtualMuseum::class, 'museum_id', 'museum_id');
    }

    public function arMarker(): BelongsTo
    {
        return $this->belongsTo(ArMarker::class, 'marker_id', 'marker_id');
    }
}
