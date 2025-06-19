<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class RegistrationCodeReservation extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'registration_code',
        'reserved_at',
        'used',
        'created_by_id',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'used' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}

