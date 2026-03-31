<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
    'name',
    'code',
    'price',
    'interval',
    'allows_download',
    'allows_dashboard',
    'reports_quota',
];
}
