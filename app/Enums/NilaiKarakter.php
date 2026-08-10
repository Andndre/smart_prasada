<?php

namespace App\Enums;

/**
 * Nilai karakter yang melekat pada sebuah objek peninggalan.
 *
 * PLACEHOLDER: daftar di bawah adalah 6 dimensi Profil Pelajar Pancasila, dipakai
 * sementara agar seluruh jalur (form admin, editor 3D, panel VR, ekspor) bisa diuji
 * end-to-end. Daftar final harus diambil dari Pardi IW, Sendratari LP, Margi IK.
 * "Rekonstruksi nilai-nilai pendidikan karakter pada peninggalan purbakala di Desa
 * Pakraman Selulung, Kintamani, Bangli" (2017) — referensi #1 proposal, meneliti
 * situs yang sama dengan museum uji. Mengganti daftar ini cukup di file ini saja.
 */
enum NilaiKarakter: string
{
    case Religius = 'religius';
    case BerkebinekaanGlobal = 'berkebinekaan_global';
    case GotongRoyong = 'gotong_royong';
    case Mandiri = 'mandiri';
    case BernalarKritis = 'bernalar_kritis';
    case Kreatif = 'kreatif';

    public function label(): string
    {
        return match ($this) {
            self::Religius => 'Beriman & Berakhlak Mulia',
            self::BerkebinekaanGlobal => 'Berkebinekaan Global',
            self::GotongRoyong => 'Bergotong Royong',
            self::Mandiri => 'Mandiri',
            self::BernalarKritis => 'Bernalar Kritis',
            self::Kreatif => 'Kreatif',
        };
    }

    /**
     * @return array<string, string> value => label, untuk mengisi checkbox form
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $nilai) => [$nilai->value => $nilai->label()])
            ->all();
    }

    /**
     * Terjemahkan nilai tersimpan menjadi label tampilan, abaikan nilai asing.
     *
     * @param  array<int, string>|null  $values
     * @return array<int, string>
     */
    public static function labels(?array $values): array
    {
        return collect($values ?? [])
            ->map(fn (string $value) => self::tryFrom($value)?->label())
            ->filter()
            ->values()
            ->all();
    }
}
