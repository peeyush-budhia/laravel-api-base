<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable as AuditableContract;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission implements AuditableContract
{
    use Auditable;
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;
}
