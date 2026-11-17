<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\ManFactory;
use App\Traits\Auditable;
use App\Traits\Blameable;
use App\Traits\HasCartographers;
use App\Traits\HasIcon;
use App\Traits\HasPerimeter;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Man
 */
class Man extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'mans';

    public static string $prefix = 'MAN_';

    public static string $icon = '/images/vlan.png';

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
        'parent_man_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return ManFactory::new();
    }

    /** @return BelongsToMany<Wan, $this> */
    public function wans(): BelongsToMany
    {
        return $this->belongsToMany(Wan::class)->orderBy('name');
    }

    /** @return BelongsToMany<Lan, $this> */
    public function lans(): BelongsToMany
    {
        return $this->belongsToMany(Lan::class)->orderBy('name');
    }

    /** @return BelongsTo<Man, $this> */
    public function parentMan(): BelongsTo
    {
        return $this->belongsTo(Man::class, 'parent_man_id');
    }

    /** @return HasMany<Man, $this> */
    public function mans(): HasMany
    {
        return $this->hasMany(Man::class, 'parent_man_id', 'id')->orderBy('name');
    }
}
