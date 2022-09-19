<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefreshedScan extends Model
{
    use HasFactory;

    protected $table = 'refresh_scans';

    protected $casts = [
        'document' => 'object'
    ];

    protected $fillable = [
        'document',
    ];

    public function page()
    {
        return $this->hasOne(Page::class, 'scanId');
    }
}
