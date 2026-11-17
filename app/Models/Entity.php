<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\EntityFactory;
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Entity
 */
class Entity extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'entities';

    public static string $prefix = 'ENTITY_';

    public static string $icon = '/images/entity.png';

    public static array $searchable = [
        'name',
        'description',
        'security_level',
        'contact_point',
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
        'icon_id',
        'description',
        'security_level',
        'contact_point',
        'parent_entity_id',
    ];

    protected static function newFactory(): Factory
    {
        return EntityFactory::new();
    }

    /** @return HasMany<Database, $this> */
    public function databases(): HasMany
    {
        return $this->hasMany(Database::class, 'entity_resp_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Application, $this> */
    public function respApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'entity_resp_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Relation, $this> */
    public function sourceRelations(): HasMany
    {
        return $this->hasMany(Relation::class, 'source_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Relation, $this> */
    public function destinationRelations(): HasMany
    {
        return $this->hasMany(Relation::class, 'destination_id', 'id')->orderBy('name');
    }

    /** @return BelongsToMany<Application, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->orderBy('name');
    }

    /** @return BelongsToMany<Process, $this> */
    public function processes(): BelongsToMany
    {
        return $this->belongsToMany(Process::class)->orderBy('name');
    }

    /** @return BelongsTo<Entity, $this> */
    public function parentEntity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'parent_entity_id');
    }

    /** @return HasMany<Entity, $this> */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class, 'parent_entity_id', 'id')->orderBy('name');
    }

    public function isExternal(): bool
    {
        return in_array('extern', explode(' ', (string) $this->getAttribute('attributes')), true);
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereNotNull('security_level')
            ->whereNotNull('contact_point')
            ->whereExists(fn ($q) => $q
                ->from('entity_process')
                ->whereColumn('entity_process.entity_id', 'entities.id'));
    }
}
