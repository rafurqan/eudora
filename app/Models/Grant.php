<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Grant extends Model
{
    use HasFactory;
    protected $table = 'grants';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'donor_id',
        'donation_type_id',
        'is_active',
        'description',
        'total_funds',
        'grant_expiration_date',
        'notes',
        'created_by_id',
        'updated_by_id',
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
}
