<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TransportationMode extends Model
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'transportation_modes';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $hidden = [
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];

}
