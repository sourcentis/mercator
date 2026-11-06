<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\SiteFactory;
use App\Traits\Auditable;
use App\Traits\Blameable;
use App\Traits\HasCartographers;
use App\Traits\HasIcon;
use App\Traits\HasPerimeter;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Site
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property string|null $description
 * @property int|null $icon_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Site extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'sites';

    public static string $prefix = 'SITE_';

    public static string $icon = '/images/site.png';

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'attributes',
        'icon_id',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static array $searchable = [
        'name',
        'description',
        'type',
        'attributes',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return SiteFactory::new();
    }

    /** @return HasMany<Building, $this> */
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Bay, $this> */
    public function bays(): HasMany
    {
        return $this->hasMany(Bay::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<PhysicalServer, $this> */
    public function physicalServers(): HasMany
    {
        return $this->hasMany(PhysicalServer::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Workstation, $this> */
    public function workstations(): HasMany
    {
        return $this->hasMany(Workstation::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<StorageDevice, $this> */
    public function storageDevices(): HasMany
    {
        return $this->hasMany(StorageDevice::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Peripheral, $this> */
    public function peripherals(): HasMany
    {
        return $this->hasMany(Peripheral::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Phone, $this> */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class, 'site_id', 'id')->orderBy('name');
    }

    /** @return HasMany<PhysicalSwitch, $this> */
    public function physicalSwitches(): HasMany
    {
        return $this->hasMany(PhysicalSwitch::class, 'site_id', 'id')->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query->whereNotNull('description');
    }
}
