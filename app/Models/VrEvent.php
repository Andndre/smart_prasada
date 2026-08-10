<?php

namespace App\Models;

use App\Enums\JenisEventVr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VrEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'vr_event';

    protected $primaryKey = 'event_id';

    protected $fillable = [
        'sesi_id',
        'user_id',
        'museum_id',
        'kode_responden',
        'jenis',
        'mesh_name',
        'detail',
        'offset_ms',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'museum_id' => 'integer',
        'jenis' => JenisEventVr::class,
        'detail' => 'array',
        'offset_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function virtualMuseum(): BelongsTo
    {
        return $this->belongsTo(VirtualMuseum::class, 'museum_id', 'museum_id');
    }
}
