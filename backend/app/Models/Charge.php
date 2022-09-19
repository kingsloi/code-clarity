<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $casts = [
        'qty' => 'integer',
        'amount' => 'double',
        'order' => 'integer',
    ];

    protected $fillable = [
        'pageId',
        'date',
        'revCode',
        'procedureCode',
        'description',
        'qty',
        'amount',
        'order'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'pageId');
    }
}
