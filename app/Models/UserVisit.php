<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVisit extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'device',
        'platform',
        'browser',
        'referer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
