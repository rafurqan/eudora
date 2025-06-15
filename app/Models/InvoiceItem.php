<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $table = 'invoice_item';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'invoice_id',
        'rate_id',
        'frequency',
        'amount_rate',
        'created_at',
        'updated_at',
        'created_by_id',
        'updated_by_id'
    ];
}
