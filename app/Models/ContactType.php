<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ContactType extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'contact_types';
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
        'name',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

}
