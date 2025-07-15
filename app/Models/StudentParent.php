<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class StudentParent extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'student_parents';
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
        'parent_type_id',
        'full_name',
        'nik',
        'aggregate_id',
        'aggregate_type',
        'birth_year',
        'education_level_id',
        'occupation',
        'income_range_id',
        'phone',
        'email',
        'address',
        'is_main_contact',
        'is_emergency_contact',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }

    public function incomeRange(): BelongsTo
    {
        return $this->belongsTo(IncomeRange::class, 'income_range_id');
    }

    public function parentType(): BelongsTo
    {
        return $this->belongsTo(ParentType::class, 'parent_type_id');
    }

}
