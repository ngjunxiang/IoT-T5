<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveImage extends Model
{
    protected $fillable = ['imageName', 'numPeopleDetected'];
}
