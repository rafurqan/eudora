<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class RolePermission extends Model
{
    use HasFactory, Notifiable, HasUuids;
    protected $table = 'role_permissions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'role_id',
        'permission_id',
        'created_at',
        'created_by_id',
        'updated_at',
        'updated_by_id'
    ];
}


