<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestDate extends Model
{
    protected $fillable = ['test_date'];
    
    protected $casts = [
        'test_date' => 'date',
    ];
}
