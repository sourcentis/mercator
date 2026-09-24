<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\ApplicationServiceFactory;
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
 * App\ApplicationService
 */
class ApplicationService extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'application_services';

    public static string $prefix = 'APPSERV_';

    public static string $icon = '/images/applicationservice.png';

    public static array $searchable = [
        'name',
        'description',
        'comments',
        'exposition',
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
        'exposition',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return ApplicationServiceFactory::new();
    }

    /** @return HasMany<ApplicationFlow, $this> */
    public function serviceSourceFluxes(): HasMany
    {
        return $this->hasMany(ApplicationFlow::class, 'service_source_id', 'id')->orderBy('name');
    }

    /** @return HasMany<ApplicationFlow, $this> */
    public function serviceDestFluxes(): HasMany
    {
        return $this->hasMany(ApplicationFlow::class, 'service_dest_id', 'id')->orderBy('name');
    }

    /** @return BelongsToMany<Application, $this> */
    public function servicesApplications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->orderBy('name');
    }

    /** @return BelongsToMany<ApplicationModule, $this> */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(ApplicationModule::class)->orderBy('name');
    }

    /** @return BelongsToMany<Application, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel2(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereExists(fn ($q) => $q
                ->from('application_application_service')
                ->whereColumn('application_application_service.application_service_id', 'application_services.id'));
    }
}
