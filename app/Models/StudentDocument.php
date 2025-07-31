<?php

namespace App\Models;

use App\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'student_documents';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $appends = ['file_url'];

    protected $hidden = [
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    protected $fillable = [
        'id',
        'name',
        'aggregate_id',
        'aggregate_type',
        'document_type_id',
        'file_name',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_name) {
            return null;
        }

        return FileHelper::getFileUrl('documents/prospective_students', $this->file_name);
    }
}
