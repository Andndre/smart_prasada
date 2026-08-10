<?php

namespace App\Models;

use App\Enums\NilaiKarakter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertanyaanRefleksi extends Model
{
    use HasFactory;

    protected $table = 'pertanyaan_refleksi';

    protected $primaryKey = 'pertanyaan_id';

    public $timestamps = true;

    protected $fillable = [
        'museum_id',
        'nilai_karakter',
        'pertanyaan',
        'urutan',
    ];

    protected $casts = [
        'museum_id' => 'integer',
        'nilai_karakter' => NilaiKarakter::class,
        'urutan' => 'integer',
    ];

    public function virtualMuseum(): BelongsTo
    {
        return $this->belongsTo(VirtualMuseum::class, 'museum_id', 'museum_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(JawabanRefleksi::class, 'pertanyaan_id', 'pertanyaan_id');
    }
}
