<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class City extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function subDistricts()
    {
        return $this->hasMany(SubDistrict::class);
    }
}
