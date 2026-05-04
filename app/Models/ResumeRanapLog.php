<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeRanapLog extends Model
{
    protected $table = 'resume_ranap_logs';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'action',
        'description',
        'user_id',
    ];
}
