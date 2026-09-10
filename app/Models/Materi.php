<?php

namespace App\Models;

use Database\Factories\MateriFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Materi extends Model
{
    /** @use HasFactory<MateriFactory> */
    use HasFactory;

    /**
     * @var mixed|true
     */
    public mixed $is_completed;

    /**
     * @var mixed|true
     */
    public mixed $is_available;

    protected $table = 'materi';

    protected $primaryKey = 'materi_id';

    protected $fillable = [
        'era_id',
        'bab',
        'judul',
        'deskripsi',
        'urutan',
        'gambar_sampul',
    ];

    protected $casts = [
        'era_id' => 'integer',
        'bab' => 'integer',
        'urutan' => 'integer',
    ];

    public function shouldIncrementProgress(User $user, int $progress): bool
    {
        if (config('app.demo_mode', false)) {
            return false;
        }

        $materiLevel = $this->getLinearLevel();

        return $materiLevel > $user->level_sekarang ||
            ($materiLevel == $user->level_sekarang && $progress > $user->progress_level_sekarang);
    }

    /**
     * Linear position for progress gating. Sorted by era, then bab, then urutan.
     */
    public function getLinearLevel(): int
    {
        $orderedIds = self::orderedMateriIds();
        $index = $orderedIds->search($this->materi_id);

        if ($index === false) {
            return 1;
        }

        return $index + 1;
    }

    public static function orderedMateriIds(): Collection
    {
        return self::query()
            ->leftJoin('era', 'materi.era_id', '=', 'era.era_id')
            ->orderByRaw('CASE WHEN materi.era_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('era.urutan')
            ->orderBy('materi.bab')
            ->orderBy('materi.urutan')
            ->orderBy('materi.materi_id')
            ->pluck('materi.materi_id');
    }

    // Relationships
    public function era(): BelongsTo
    {
        return $this->belongsTo(Era::class, 'era_id', 'era_id');
    }

    public function pretest(): HasMany
    {
        return $this->hasMany(Pretest::class, 'materi_id', 'materi_id');
    }

    public function posttest(): HasMany
    {
        return $this->hasMany(Posttest::class, 'materi_id', 'materi_id');
    }

    public function jawabanUser(): HasMany
    {
        return $this->hasMany(JawabanUser::class, 'materi_id', 'materi_id');
    }

    public function ebook(): HasMany
    {
        return $this->hasMany(Ebook::class, 'materi_id', 'materi_id');
    }

    public function situsPeninggalan(): HasMany
    {
        return $this->hasMany(SitusPeninggalan::class, 'materi_id', 'materi_id');
    }

    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class, 'materi_id', 'materi_id');
    }
}
