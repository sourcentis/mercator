<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\InformationFactory;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Information
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property int|null $icon_id
 * @property string|null $description
 * @property string|null $owner
 * @property string|null $administrator
 * @property string|null $storage
 * @property int|null $security_need_c
 * @property int|null $security_need_i
 * @property int|null $security_need_a
 * @property int|null $security_need_t
 * @property int|null $security_need_auth
 * @property string|null $sensitivity
 * @property string|null $constraints
 * @property string|null $retention_period
 * @property string|null $final_disposition
 * @property string|null $retention_notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Information extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'information';

    public static string $prefix = 'INFO_';

    public static string $icon = '/images/information.png';

    public static array $searchable = [
        'name',
        'description',
        'owner',
        'constraints',
        'type',
        'attributes',
        'retention_period',
        'final_disposition',
        'retention_notes',
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
        'icon_id',
        'description',
        'owner',
        'administrator',
        'storage',
        'security_need_c',
        'security_need_i',
        'security_need_a',
        'security_need_t',
        'security_need_auth',
        'sensitivity',
        'constraints',
        'retention_period',
        'final_disposition',
        'retention_notes',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return InformationFactory::new();
    }

    /** @return BelongsToMany<Database, $this> */
    public function databases(): BelongsToMany
    {
        return $this->belongsToMany(Database::class)->orderBy('name');
    }

    /** @return BelongsToMany<Process, $this> */
    public function processes(): BelongsToMany
    {
        return $this->belongsToMany(Process::class)->orderBy('name');
    }

    /** @return BelongsToMany<ApplicationFlow, $this> */
    public function fluxes(): BelongsToMany
    {
        return $this->belongsToMany(ApplicationFlow::class, 'application_flow_information', 'information_id', 'flux_id');
    }

    /**
     * Informations membres de cette catégorie.
     * Une information "catégorie" regroupe plusieurs informations enfants.
     *
     * @return BelongsToMany<Information, $this>
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            Information::class,
            'information_information',
            'information_id',
            'child_information_id'
        )->orderBy('name');
    }

    /**
     * Catégories (informations parentes) auxquelles appartient cette information.
     *
     * @return BelongsToMany<Information, $this>
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            Information::class,
            'information_information',
            'child_information_id',
            'information_id'
        )->orderBy('name');
    }

    public function graphs(): Collection
    {
        return Graph::bpmnGraphsReferencing($this->getUID());
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereNotNull('owner')
            ->whereNotNull('administrator')
            ->whereNotNull('storage');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel2(Builder $query): Builder
    {
        return $query->maturityLevel1()
            ->whereNotNull('security_need_c')
            ->whereNotNull('security_need_i')
            ->whereNotNull('security_need_a')
            ->whereNotNull('security_need_t')
            ->whereNotNull('sensitivity');
    }
}
