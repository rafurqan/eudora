<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class StudentOriginSchool extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'student_origin_schools';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $hidden = [
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    protected $fillable = [
        'id',
        'name',
        'education_id',
        'school_type_id',
        'school_name',
        'npsn',
        'aggregate_id',
        'aggregate_type',
        'address_name',
        'graduation_year',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function education(): BelongsTo
    {
        return $this->belongsTo(Education::class, 'education_id', 'id');
    }

    public function schoolType(): BelongsTo
    {
        return $this->belongsTo(SchoolType::class, 'school_type_id', 'id');
    }
}
