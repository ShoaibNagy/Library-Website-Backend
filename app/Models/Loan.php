<?php

namespace App\Models;

use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'copy_id',
        'checkout_date',
        'due_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'checkout_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function copy()
    {
        return $this->belongsTo(Copy::class);
    }

    public function isOverdue()
    {
        return $this->status === 'active' && now()->isAfter($this->due_date);
    }

    protected static function newFactory()
    {
        return LoanFactory::new();
    }
}
