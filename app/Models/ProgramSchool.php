<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProgramSchool extends Model
{
    protected $table = 'program_school';
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'is_active',
        'created_by_id',
        'updated_by_id',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        // Set UUID saat membuat model baru
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
