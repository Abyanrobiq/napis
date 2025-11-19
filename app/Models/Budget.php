<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['user_id', 'category_id', 'amount', 'spent', 'period_start', 'period_end'];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });

        static::creating(function ($budget) {
            if (auth()->check()) {
                $budget->user_id = auth()->id();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function remaining()
    {
        return $this->amount - $this->spent;
    }
}
