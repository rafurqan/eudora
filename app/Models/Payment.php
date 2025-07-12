<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $table = 'payment';
    public $incrementing = false;
    protected $keyType = 'string';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    protected $fillable = [
        'id',
        'code',
        'invoice_id',
        'payment_method',
        'payment_date',
        'bank_name',
        'account_name',
        'account_number',
        'reference_number',
        'nominal_payment',
        'notes',
        'status',
        'id_log_grant',
        'id_grant',
        'grant_amount',
        'use_grant',
        'total_payment',
        'created_by_id',
        'updated_by_id',
    ];
}
