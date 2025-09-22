<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignDokumen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sign_dokumen';

    // Primary key UUID, bukan increment
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'nik',
        'dokumen_asli',
        'id_dokumen_ttd',
        'image_ttd',
    ];
}
