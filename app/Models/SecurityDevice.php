<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\SecurityDeviceFactory;
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\SecurityDevice
 */
class SecurityDevice extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'security_devices';

    public static string $prefix = 'LSECURITY_';

    public static string $icon = '/images/securitydevice.png';

    public static array $searchable = [
        'name',
        'type',
        'attributes',
        'description',
        'address_ip',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'address_ip',
        'attributes',
        'description',
        'icon_id',
    ];

    protected static function newFactory(): Factory
    {
        return SecurityDeviceFactory::new();
    }

    /** @return BelongsToMany<PhysicalSecurityDevice, $this> */
    public function physicalSecurityDevices(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalSecurityDevice::class)->orderBy('name');
    }

    /** @return BelongsToMany<Application, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query->whereNotNull('description');
    }
}
