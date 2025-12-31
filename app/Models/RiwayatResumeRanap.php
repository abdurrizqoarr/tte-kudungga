<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatResumeRamap extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'riwayat_resume_ranaps';

    // Primary key UUID, bukan increment
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'nama_penanda_tangan',
    ];
}
