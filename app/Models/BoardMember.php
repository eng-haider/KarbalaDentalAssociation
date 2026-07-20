<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardMember extends Model
{
    protected $fillable = ['name', 'position', 'photo', 'sort_order'];
}
