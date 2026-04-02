<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportView extends Model
{
     protected $fillable = [
        'user_id',
        'report_id',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
