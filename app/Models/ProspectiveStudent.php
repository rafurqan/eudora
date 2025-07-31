<?php

namespace App\Models;

use App\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class ProspectiveStudent extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'prospective_students';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = ['document_status', 'photo_url', 'student_id'];

    protected $hidden = [
        'nationality_id',
        'religion_id',
        'special_need_id',
        'special_condition_id',
        'transportation_mode_id'
    ];

    protected $fillable = [
        'id',
        'registration_code',
        'full_name',
        'nickname',
        'religion_id',
        'gender',
        'village_id',
        'birth_place',
        'nationality_id',
        'birth_date',
        'entry_year',
        'nisn',
        'street',
        'email',
        'phone',
        'child_order',
        'family_status',
        'special_need_id',
        'special_condition_id',
        'transportation_mode_id',
        'photo_filename',
        'health_condition',
        'hobby',
        'special_need',
        'additional_information',
        'has_kip',
        'has_kps',
        'status',
        'eligible_for_kip',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }

    public function specialNeed(): BelongsTo
    {
        return $this->belongsTo(SpecialNeed::class, 'special_need_id');
    }

    public function specialCondition(): BelongsTo
    {
        return $this->belongsTo(SpecialCondition::class, 'special_condition_id');
    }

    public function transportationMode(): BelongsTo
    {
        return $this->belongsTo(TransportationMode::class, 'transportation_mode_id');
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function originSchools(): HasMany
    {
        return $this->hasMany(StudentOriginSchool::class, 'aggregate_id')
            ->where('aggregate_type', ProspectiveStudent::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'aggregate_id')
            ->where('aggregate_type', ProspectiveStudent::class);
    }

    public function parents(): HasMany
    {
        return $this->hasMany(StudentParent::class, 'aggregate_id')
            ->where('aggregate_type', ProspectiveStudent::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(StudentContact::class, 'aggregate_id')
            ->where('aggregate_type', ProspectiveStudent::class);
    }

    public function getDocumentStatusAttribute()
    {
        $requiredDocumentTypeIds = DocumentType::where('is_required', true)->pluck('id');

        if ($requiredDocumentTypeIds->isEmpty()) {
            return 'Lengkap';
        }

        $studentRequiredCount = $this->documents()
            ->whereIn('document_type_id', $requiredDocumentTypeIds)
            ->count();

        return $studentRequiredCount >= $requiredDocumentTypeIds->count() ? 'Lengkap' : 'Belum Lengkap';
    }
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_filename)
            return null;

        return FileHelper::getFileUrl('storage/photos', $this->file_name);
        // return asset("storage/photos/{$this->photo_filename}");
    }

    public function classMemberships()
    {
        return $this->hasMany(ClassMembership::class);
    }

    public function activeClassMembership()
    {
        return $this->hasOne(ClassMembership::class)->whereNull('end_at');
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'entity');
    }
    public function student()
    {
        return $this->hasOne(Student::class, 'prospective_student_id');
    }

    public function getStudentIdAttribute()
    {
        return $this->student?->id;
    }

    public function mainParent()
    {
        return $this->hasOne(StudentParent::class, 'aggregate_id')
            ->where('aggregate_type', self::class)
            ->where('is_main_contact', true);
    }

}
