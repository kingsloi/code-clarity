<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $casts = [
        'amount' => 'double',
        'order' => 'integer',
    ];

    protected $fillable = [
        'date',
        'description',
        'amount',
        'order'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'pageId');
    }
}
