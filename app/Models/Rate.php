<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Rate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rates';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'child_ids' => 'array',
        'price' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $fillable = [
        'id',
        'service_id',
        'child_ids',
        'program_id',
        'price',
        'is_active',
        'created_by_id',
        'updated_by_id',
        'code',
        'description',
        'category',
        'frequency',
        'applies_to',
        'created_at',
        'updated_at'
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

    // Relasi dengan children rates
    public function children()
    {
        return $this->hasMany(Rate::class, 'id', 'child_ids');
    }

    // Relasi dengan program
    // public function program()
    // {
    //     return $this->belongsTo(Program::class, 'program_id');
    // }

    // Relasi dengan user yang membuat
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // Relasi dengan user yang mengupdate
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
