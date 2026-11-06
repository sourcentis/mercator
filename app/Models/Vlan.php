<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\VlanFactory;
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Vlan
 */
class Vlan extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'vlans';

    public static string $prefix = 'VLAN_';

    public static string $icon = '/images/vlan.png';

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'attributes',
        'vlan_id',
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
        return VlanFactory::new();
    }

    /**
     * Get the physical routers associated with this VLAN.
     *
     * @return BelongsToMany<PhysicalRouter, $this> Belongs-to-many relationship for PhysicalRouter models, ordered by `name`.
     */
    public function physicalRouters(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalRouter::class)->orderBy('name');
    }

    /**
     * Network switches associated with this VLAN, ordered by name.
     *
     * @return BelongsToMany<NetworkSwitch, $this> Many-to-many relation yielding NetworkSwitch models ordered by `name`.
     */
    public function networkSwitches(): BelongsToMany
    {
        return $this->belongsToMany(NetworkSwitch::class)->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query->whereNotNull('description');
    }

    /**
     * Retrieve subnetworks that belong to this VLAN, ordered by `name`.
     *
     * @return HasMany<Subnetwork, $this> Subnetwork models associated with this VLAN, ordered by name.
     */
    public function subnetworks(): HasMany
    {
        return $this->hasMany(Subnetwork::class, 'vlan_id', 'id')->orderBy('name');
    }
}
