<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\NetworkFactory;
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
 * App\Network
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property string|null $description
 * @property string|null $protocol_type
 * @property string|null $responsible
 * @property string|null $responsible_sec
 * @property int|null $security_need_c
 * @property int|null $security_need_i
 * @property int|null $security_need_a
 * @property int|null $security_need_t
 * @property int|null $security_need_auth
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Network extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'networks';

    public static string $prefix = 'NETWORK_';

    public static string $icon = '/images/cloud.png';

    public static array $searchable = [
        'name',
        'description',
        'protocol_type',
        'responsible',
        'responsible_sec',
        'type',
        'attributes',
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
        'protocol_type',
        'responsible',
        'responsible_sec',
        'security_need_c',
        'security_need_i',
        'security_need_a',
        'security_need_t',
        'security_need_auth',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return NetworkFactory::new();
    }

    /** @return HasMany<ExternalConnectedEntity, $this> */
    public function externalConnectedEntities(): HasMany
    {
        return $this->hasMany(ExternalConnectedEntity::class, 'network_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Subnetwork, $this> */
    public function subnetworks(): HasMany
    {
        return $this->hasMany(Subnetwork::class, 'network_id', 'id')->orderBy('name');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereNotNull('protocol_type')
            ->whereNotNull('responsible')
            ->whereNotNull('responsible_sec')
            ->whereNotNull('security_need_c')
            ->whereNotNull('security_need_i')
            ->whereNotNull('security_need_a')
            ->whereNotNull('security_need_t');
    }
}
