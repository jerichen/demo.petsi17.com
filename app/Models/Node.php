<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $fillable = ['zxid', 'epoch', 'vote_for', 'state', 'alive'];

    protected $casts = [
        'alive' => 'boolean',
    ];
}
