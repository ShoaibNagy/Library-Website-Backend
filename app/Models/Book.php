<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'publication_year',
        'authors',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'authors' => 'array',
    ];

    public function editions()
    {
        return $this->hasMany(Edition::class);
    }

    protected static function newFactory()
    {
        return BookFactory::new();
    }
}
