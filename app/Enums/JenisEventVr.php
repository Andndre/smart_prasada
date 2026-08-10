<?php

namespace App\Enums;

/**
 * Jenis runtime event yang dicatat selama sesi VR.
 *
 * Wajib per proposal hal. 22 ("pencatatan runtime events untuk kebutuhan pengujian
 * fungsional, usability evaluation, dan validasi sistem pada TKT 5-6") dan jadi sumber
 * bukti luaran wajib #1.
 *
 * Dikunci sebagai enum supaya beda ejaan dari sisi klien tidak memecah data penelitian
 * setelah ratusan responden selesai diuji.
 */
enum JenisEventVr: string
{
    case SesiMulai = 'sesi_mulai';
    case FaseBerubah = 'fase_berubah';
    case Teleport = 'teleport';
    case ObjekDilihat = 'objek_dilihat';
    case PanelDibuka = 'panel_dibuka';
    case PanelDitutup = 'panel_ditutup';
    case ObjekDigenggam = 'objek_digenggam';
    case ObjekDilepas = 'objek_dilepas';
    case PuzzleBenar = 'puzzle_benar';
    case SesiSelesai = 'sesi_selesai';

    public function label(): string
    {
        return match ($this) {
            self::SesiMulai => 'Sesi dimulai',
            self::FaseBerubah => 'Transisi fase',
            self::Teleport => 'Berpindah posisi',
            self::ObjekDilihat => 'Objek dipandang',
            self::PanelDibuka => 'Panel info dibuka',
            self::PanelDitutup => 'Panel info ditutup',
            self::ObjekDigenggam => 'Objek digenggam',
            self::ObjekDilepas => 'Objek dilepas',
            self::PuzzleBenar => 'Objek terpasang benar',
            self::SesiSelesai => 'Sesi selesai',
        };
    }
}
