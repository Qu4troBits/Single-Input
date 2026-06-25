<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLogModel extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'method',
        'path',
        'route_name',
        'payload_hash',
        'created_at',
    ];
}
