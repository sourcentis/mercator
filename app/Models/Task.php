<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\TaskFactory;
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
use Illuminate\Support\Collection;

/**
 * App\Task
 *
 * @property string|null $type
 * @property string|null $attributes
 */
class Task extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, Blameable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'tasks';

    public static string $prefix = 'TASK_';

    public static string $icon = '/images/task.png';

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
        return TaskFactory::new();
    }

    /** @return BelongsToMany<Operation, $this> */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class)->orderBy('name');
    }

    public function graphs(): Collection
    {
        return Graph::bpmnGraphsReferencing($this->getUID());
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel3(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereExists(fn ($q) => $q
                ->from('operation_task')
                ->whereColumn('operation_task.task_id', 'tasks.id'));
    }
}
