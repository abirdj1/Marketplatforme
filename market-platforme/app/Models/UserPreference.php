<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'favorite_sectors',
        'favorite_geographies',
        'favorite_periods',
    ];

    protected function casts(): array
    {
        return [
            'favorite_sectors' => 'array',
            'favorite_geographies' => 'array',
            'favorite_periods' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
