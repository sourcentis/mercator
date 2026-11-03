<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\ProcessFactory;
use App\Traits\Auditable;
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
use Illuminate\Support\Collection;

/**
 * App\Process
 *
 * @property string|null $type
 * @property string|null $attributes
 */
class Process extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'processes';

    public static string $prefix = 'PROCESS_';

    public static string $icon = '/images/process.png';

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'attributes',
        'icon_id',
        'description',
        'in_out',
        'security_need_c',
        'security_need_i',
        'security_need_a',
        'security_need_t',
        'security_need_auth',
        'owner',
        'macroprocess_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static array $searchable = [
        'name',
        'description',
        'in_out',
        'owner',
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
        return ProcessFactory::new();
    }

    /** @return BelongsToMany<Information, $this> */
    public function information(): BelongsToMany
    {
        return $this->belongsToMany(Information::class)->orderBy('name');
    }

    /** @return BelongsToMany<Application, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->orderBy('name');
    }

    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class)->orderBy('name');
    }

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class)->orderBy('name');
    }

    /** @return HasMany<Operation, $this> */
    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class, 'process_id', 'id')->orderBy('name');
    }

    /** @return BelongsToMany<DataProcessing, $this> */
    public function dataProcesses(): BelongsToMany
    {
        return $this->belongsToMany(DataProcessing::class, 'data_processing_process')->orderBy('name');
    }

    /** @return BelongsTo<MacroProcessus, $this> */
    public function macroProcess(): BelongsTo
    {
        return $this->belongsTo(MacroProcessus::class, 'macroprocess_id');
    }

    /** @return BelongsToMany<SecurityControl, $this> */
    public function securityControls(): BelongsToMany
    {
        return $this->belongsToMany(SecurityControl::class, 'security_control_process')->orderBy('name');
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
            ->whereNotNull('in_out')
            ->whereNotNull('owner');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel2(Builder $query): Builder
    {
        return $query->maturityLevel1()
            ->whereNotNull('name')
            ->whereNotNull('macroprocess_id')
            ->whereNotNull('security_need_c')
            ->whereNotNull('security_need_i')
            ->whereNotNull('security_need_a')
            ->whereNotNull('security_need_t');
    }
}
