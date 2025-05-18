<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class StudentContact extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'student_contacts';
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
        'aggregate_id',
        'aggregate_type',
        'contact_type_id',
        'value',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContactType::class, 'contact_type_id');
    }

}
