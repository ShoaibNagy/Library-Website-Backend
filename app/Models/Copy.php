<?php

namespace App\Models;

use Database\Factories\CopyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copy extends Model
{
    use HasFactory;

    protected $fillable = [
        'edition_id',
        'barcode',
        'status',
        'condition',
    ];

    protected $casts = [
        'status' => 'string',
        'condition' => 'string',
    ];

    public function edition()
    {
        return $this->belongsTo(Edition::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    protected static function newFactory()
    {
        return CopyFactory::new();
    }
}
