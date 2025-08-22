<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaftNode extends Model
{
    protected $fillable = ['zxid', 'term', 'vote_for', 'state', 'alive'];

    protected $casts = [
        'alive' => 'boolean',
    ];
}
