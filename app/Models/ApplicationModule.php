<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\ApplicationModuleFactory;
use App\Traits\Auditable;
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
 * App\ApplicationModule
 */
class ApplicationModule extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'application_modules';

    public static string $prefix = 'MOD_';

    public static string $icon = '/images/module.png';

    public static array $searchable = [
        'name',
        'description',
        'comments',
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
        'attributes',
        'description',
        'comments',
        'vendor',
        'product',
        'version',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return ApplicationModuleFactory::new();
    }

    /** @return HasMany<ApplicationFlow, $this> */
    public function moduleSourceFluxes(): HasMany
    {
        return $this->hasMany(ApplicationFlow::class, 'module_source_id', 'id')->orderBy('name');
    }

    /** @return HasMany<ApplicationFlow, $this> */
    public function moduleDestFluxes(): HasMany
    {
        return $this->hasMany(ApplicationFlow::class, 'module_dest_id', 'id')->orderBy('name');
    }

    /** @return BelongsToMany<ApplicationService, $this> */
    public function applicationServices(): BelongsToMany
    {
        return $this->belongsToMany(ApplicationService::class)->orderBy('name');
    }

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class)->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel2(Builder $query): Builder
    {
        return $query->whereNotNull('description');
    }
}
