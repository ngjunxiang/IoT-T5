<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveImage extends Model
{
    protected $casts = [
        'lightsOn' => 'boolean',
    ];
    protected $fillable = ['imageName', 'numPeopleDetected', 'lightsOn'];


}
