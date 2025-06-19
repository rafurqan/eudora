<?php

namespace App\Models;

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
    protected $appends = ['document_status', 'photo_url'];

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
        // Gunakan documents_count jika tersedia (hasil dari withCount)
        if (array_key_exists('documents_count', $this->attributes)) {
            return $this->documents_count >= 1 ? 'Lengkap' : 'Belum Lengkap';
        }

        // Jika belum tersedia, fallback ke count manual (hanya untuk jaga-jaga)
        return $this->documents()->count() >= 1 ? 'Lengkap' : 'Belum Lengkap';
    }
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_filename)
            return null;

        return asset("storage/photos/{$this->photo_filename}");
    }

    public function classMemberships()
    {
        return $this->hasMany(ClassMembership::class);
    }

    public function activeClassMembership()
    {
        return $this->hasOne(ClassMembership::class)->whereNull('end_at');
    }
}
