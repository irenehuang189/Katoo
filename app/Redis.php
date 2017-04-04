<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Redis extends Model
{
    protected $fillable = ['key', 'value'];
}
