<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassMembership extends Model
{
    protected $table = 'class_memberships';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_class_id',
        'student_id',
        'prospective_student_id',
        'reason',
        'start_at',
        'end_at',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    // Relasi ke student
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi ke calon siswa
    public function prospectiveStudent(): BelongsTo
    {
        return $this->belongsTo(ProspectiveStudent::class);
    }

    // Relasi ke kelas
    public function studentClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class);
    }

    // Relasi ke user yang membuat (opsional)
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
