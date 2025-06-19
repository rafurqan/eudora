<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    public function subDistrict()
    {
        return $this->belongsTo(SubDistrict::class);
    }
}
