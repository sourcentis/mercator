<?php

namespace App\Models;

use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\PhoneFactory;
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Phone
 */
class Phone extends Model implements HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'phones';

    public static string $prefix = 'PHONE_';

    public static string $icon = '/images/phone.png';

    public static array $searchable = [
        'name',
        'description',
        'address_ip',
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
        'description',
        'type',
        'attributes',
        'address_ip',
        'site_id',
        'building_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return PhoneFactory::new();
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

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('type')
            ->whereNotNull('description')
            ->whereNotNull('site_id');
    }
}
