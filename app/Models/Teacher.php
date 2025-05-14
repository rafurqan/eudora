<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Teacher extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'teachers';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'nip',
        'birth_place',
        'birth_date',
        'education_level_id',
        'graduated_from',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id', 'id');
    }

}
