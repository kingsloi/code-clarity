<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $casts = [
        'pageNumber' => 'integer',
        'scanId' => 'integer',
        'order' => 'integer',
    ];

    protected $fillable = [
        'pageNumber',
        'statementId',
        'scanId',
        'order'
    ];

    public function statement()
    {
        return $this->belongsTo(Statement::class, 'statementId');
    }

    public function scan()
    {
        return $this->belongsTo(Scan::class, 'scanId');
    }

    public function charges()
    {
        return $this->hasMany(Charge::class, 'pageId');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'pageId');
    }
}
