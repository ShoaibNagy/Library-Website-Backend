<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bio',
        'birth_year',
    ];

    protected $casts = [
        'birth_year' => 'integer',
    ];

    public function editions(): HasMany
    {
        return $this->hasMany(Edition::class);
    }

    protected static function newFactory()
    {
        return AuthorFactory::new();
    }
}
