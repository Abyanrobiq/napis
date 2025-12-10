<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    protected static function booted()
    {
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });

        static::creating(function ($setting) {
            if (auth()->check()) {
                $setting->user_id = auth()->id();
            }
        });
    }

    public static function get($key, $default = null)
    {
        // Only get user-specific setting, no fallback to other users
        $setting = self::where('key', $key)->where('user_id', auth()->id())->first();
        
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        try {
            return self::withoutGlobalScope('user')->updateOrCreate(
                ['key' => $key, 'user_id' => auth()->id()],
                ['value' => $value]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation for old schema
            if ($e->getCode() == 23000) {
                // Try to update existing record first
                $existing = self::withoutGlobalScope('user')->where('key', $key)->first();
                if ($existing) {
                    $existing->update([
                        'value' => $value,
                        'user_id' => auth()->id()
                    ]);
                    return $existing;
                }
            }
            throw $e;
        }
    }
}
