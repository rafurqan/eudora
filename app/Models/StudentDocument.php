<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StudentDocument extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'student_documents';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'document_type_id',
        'file_name',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

}
