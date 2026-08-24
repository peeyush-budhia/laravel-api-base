<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable as AuditableContract;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole implements AuditableContract
{
    use Auditable;
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;
}
