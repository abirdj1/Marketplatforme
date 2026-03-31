<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
   protected $fillable = [
    'title',
    'slug',
    'summary',
    'status',
    'file_path',
    'preview_path',
];
}
