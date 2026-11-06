<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\ZoneFactory;
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
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Zone extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'zones';

    public static string $prefix = 'ZONE_SEC_';

    public static string $icon = '/images/zone.png';

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

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'attributes',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return ZoneFactory::new();
    }

    /** @return BelongsToMany<Zone, $this> */
    public function childZones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'zone_zone', 'zone_id', 'related_zone_id')
            ->orderBy('name');
    }

    /** @return BelongsToMany<Zone, $this> */
    public function parentZones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'zone_zone', 'related_zone_id', 'zone_id')
            ->orderBy('name');
    }

    /** @return BelongsToMany<Building, $this> */
    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class)->orderBy('name');
    }

    /** @return BelongsToMany<AdminUser, $this> */
    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class)->orderBy('user_id');
    }
}
