<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\BackupFactory;
use App\Traits\Auditable;
use App\Traits\Blameable;
use App\Traits\HasCartographers;
use App\Traits\HasIcon;
use App\Traits\HasPerimeter;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model implements HasIconContract, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    protected $table = 'backups';

    public static string $prefix = 'BACKUP_';

    public static string $icon = '/images/backup.png';

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'attributes',
        'description',
        'backup_frequency',
        'backup_cycle',
        'backup_retention',
    ];

    public static array $searchable = ['name', 'type', 'attributes', 'description'];

    protected $casts = [
        'backup_frequency' => 'integer',
        'backup_cycle' => 'integer',
        'backup_retention' => 'integer',
    ];

    protected static function newFactory(): Factory
    {
        return BackupFactory::new();
    }

    /** @return BelongsToMany<LogicalServer, $this> */
    public function logicalServers(): BelongsToMany
    {
        return $this->belongsToMany(LogicalServer::class, 'backup_logical_server');
    }

    /** @return BelongsToMany<StorageDevice, $this> */
    public function storageDevices(): BelongsToMany
    {
        return $this->belongsToMany(StorageDevice::class, 'backup_storage_device');
    }
}
