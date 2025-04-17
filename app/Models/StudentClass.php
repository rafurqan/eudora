<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class StudentClass extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'student_classes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $hidden = [
        'teacher_id'
    ];

    protected $fillable = [
        'id',
        'name',
        'part',
        'capacity',
        'academic_year',
        'teacher_id',
        'education_level_id',
        'status',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function education(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }

}
