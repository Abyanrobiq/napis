<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'icon',
        'color',
        'target_date',
        'status'
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });

        static::creating(function ($saving) {
            if (auth()->check()) {
                $saving->user_id = auth()->id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Hitung persentase progress
    public function progressPercentage()
    {
        if ($this->target_amount == 0) return 0;
        return min(($this->current_amount / $this->target_amount) * 100, 100);
    }

    // Hitung sisa yang perlu ditabung
    public function remainingAmount()
    {
        return max($this->target_amount - $this->current_amount, 0);
    }

    // Cek apakah sudah tercapai
    public function isCompleted()
    {
        return $this->current_amount >= $this->target_amount;
    }
}
