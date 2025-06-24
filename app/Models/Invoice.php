<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $table = 'invoice';
    public $incrementing = false;
    protected $keyType = 'string';

    public function entity()
    {
        return $this->morphTo();
    }

    protected $fillable = [
        'id',
        'student_class',
        'entity_id',
        'entity_type',
        'code',
        'publication_date',
        'due_date',
        'notes',
        'status',
        'total',
        'delivered_wa',
        'invoice_type',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
}
