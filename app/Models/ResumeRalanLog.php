<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeRalanLog extends Model
{
    protected $table = 'resume_ralan_logs';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'action',
        'description',
        'user_id',
    ];
}
