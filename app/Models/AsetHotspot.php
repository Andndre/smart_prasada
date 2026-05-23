<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetHotspot extends Model
{
    protected $table = 'aset_hotspots';
    protected $primaryKey = 'aset_id';

    protected $fillable = [
        'nama',
        'file_path',
        'tipe',
    ];
}
