<?php

namespace App\Models;

use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\PhysicalRouterFactory;
use App\Traits\Auditable;
use App\Traits\Blameable;
use App\Traits\HasCartographers;
use App\Traits\HasPerimeter;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\PhysicalRouter
 */
class PhysicalRouter extends Model implements HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'physical_routers';

    public static string $prefix = 'PHYS_ROUTER_';

    public static string $icon = '/images/router.png';

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'description',
        'type',
        'attributes',
        'site_id',
        'building_id',
        'bay_id',
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
        return PhysicalRouterFactory::new();
    }

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /** @return BelongsTo<Building, $this> */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    /** @return BelongsTo<Bay, $this> */
    public function bay(): BelongsTo
    {
        return $this->belongsTo(Bay::class, 'bay_id');
    }

    /** @return BelongsToMany<Router, $this> */
    public function routers(): BelongsToMany
    {
        return $this->belongsToMany(Router::class)->orderBy('name');
    }

    /** @return BelongsToMany<Vlan, $this> */
    public function vlans(): BelongsToMany
    {
        return $this->belongsToMany(Vlan::class)->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereNotNull('type')
            ->whereNotNull('site_id');
    }
}
