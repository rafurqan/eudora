<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'is_active',
        'created_by_id',
        'created_at',
        'updated_at',
        'updated_by_id'
    ];

    // Set UUID saat membuat entri baru
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID akan dibuat otomatis jika belum ada ID
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relasi: Satu service punya banyak rate
    public function rates()
    {
        return $this->hasMany(Rate::class, 'service_id');
    }
}
