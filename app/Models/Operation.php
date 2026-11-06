<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\OperationFactory;
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\Operation
 *
 * @property string|null $type
 * @property string|null $attributes
 */
class Operation extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'operations';

    public static string $prefix = 'OPERATION_';

    public static string $icon = '/images/operation.png';

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
        'process_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function newFactory(): Factory
    {
        return OperationFactory::new();
    }

    /** @return BelongsTo<Process, $this> */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->orderBy('name');
    }

    /** @return BelongsToMany<Actor, $this> */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class)->orderBy('name');
    }

    /** @return BelongsToMany<Task, $this> */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)->orderBy('name');
    }

    public function graphs(): Collection
    {
        return Graph::bpmnGraphsReferencing($this->getUID());
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query->whereNotNull('description');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel2(Builder $query): Builder
    {
        return $query->maturityLevel1()
            ->whereExists(fn ($q) => $q
                ->from('actor_operation')
                ->whereColumn('actor_operation.operation_id', 'operations.id'));
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel3(Builder $query): Builder
    {
        return $query->maturityLevel2()
            ->whereExists(fn ($q) => $q
                ->from('operation_task')
                ->whereColumn('operation_task.operation_id', 'operations.id'));
    }
}
