<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Page;

class Statement extends Model
{
    use HasFactory;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $casts = [
        'totalCharges' => 'double',
        'totalBalance' => 'double',
        'totalPayments' => 'double',
        'totalPages' => 'integer',
    ];

    protected $fillable = [
        'accountId',
        'visitId',
        'accountClass',
        'attendingPhysician',
        'serviceDate',
        'totalCharges',
        'totalBalance',
        'totalPayments',
        'totalPages',
        'order'
    ];

    public function pages()
    {
        return $this->hasMany(Page::class, 'statementId');
    }

    public static function boot()
    {
        parent::boot();

        // all new statements contain at least one page
        static::created(function($statement) {
            Page::create([
                'pageNumber' => 1,
                'statementId' => $statement->id
            ]);
        });
    }
}
