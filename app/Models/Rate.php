<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Rate extends Model
{
    use HasFactory;

    protected $table = 'rates';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'child_id' => 'array',
    ];

    protected $fillable = [
        'id',
        'service_id',
        'child_ids',
        'program_id',
        'price',
        'is_active',
        'created_by_id',
        'created_at',
        'updated_at',
    ];


    // Set UUID saat create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relasi: Rate belongs to a Service
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    // OPTIONAL: Bisa buat relasi self-to-self (child_id ke id)
    public function child()
    {
        return $this->belongsTo(Rate::class, 'child_id');
    }
}
