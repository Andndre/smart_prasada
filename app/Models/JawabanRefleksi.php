<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanRefleksi extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'jawaban_refleksi';

    protected $primaryKey = 'jawaban_id';

    protected $fillable = [
        'pertanyaan_id',
        'user_id',
        'museum_id',
        'kode_responden',
        'sesi_id',
        'jawaban',
    ];

    protected $casts = [
        'pertanyaan_id' => 'integer',
        'user_id' => 'integer',
        'museum_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanRefleksi::class, 'pertanyaan_id', 'pertanyaan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function virtualMuseum(): BelongsTo
    {
        return $this->belongsTo(VirtualMuseum::class, 'museum_id', 'museum_id');
    }
}
