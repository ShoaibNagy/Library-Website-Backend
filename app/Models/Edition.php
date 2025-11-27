<?php

namespace App\Models;

use Database\Factories\EditionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'author_id',
        'isbn',
        'publication_date',
        'publisher',
        'language',
    ];

    protected $casts = [
        'publication_date' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function copies()
    {
        return $this->hasMany(Copy::class);
    }

    protected static function newFactory()
    {
        return EditionFactory::new();
    }
}
