<?php

namespace App\Models;

use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\ApplicationFlowFactory;
use App\Scopes\ApplicationFlowPerimeterScope;
use App\Traits\Auditable;
use App\Traits\HasCartographers;
use App\Traits\HasPerimeter;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Flux Applicatif
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $description
 * @property string|null $attributes
 * @property int|null $application_source_id
 * @property int|null $service_source_id
 * @property int|null $module_source_id
 * @property int|null $database_source_id
 * @property int|null $application_dest_id
 * @property int|null $service_dest_id
 * @property int|null $module_dest_id
 * @property int|null $database_dest_id
 * @property bool $crypted
 * @property bool $bidirectional
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ApplicationFlow extends Model implements HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'application_flows';

    public static string $prefix = 'FLOW_';

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
        'application_source_id',
        'service_source_id',
        'module_source_id',
        'database_source_id',
        'application_dest_id',
        'service_dest_id',
        'module_dest_id',
        'database_dest_id',
        'crypted',
        'bidirectional',
    ];

    /**
     * Mapping des champs ID vers les noms de relations pour les sources
     */
    private const SOURCE_RELATIONS = [
        'application_source_id' => 'applicationSource',
        'service_source_id' => 'serviceSource',
        'module_source_id' => 'moduleSource',
        'database_source_id' => 'databaseSource',
    ];

    /**
     * Mapping des champs ID vers les noms de relations pour les destinations
     */
    private const DEST_RELATIONS = [
        'application_dest_id' => 'applicationDest',
        'service_dest_id' => 'serviceDest',
        'module_dest_id' => 'moduleDest',
        'database_dest_id' => 'databaseDest',
    ];

    protected static function newFactory(): Factory
    {
        return ApplicationFlowFactory::new();
    }

    protected static function perimeterScopeClass(): string
    {
        return ApplicationFlowPerimeterScope::class;
    }

    /**
     * Colonnes source/destination pouvant chacune porter un objet d'un
     * périmètre différent du flux lui-même. Utilisé par
     * ApplicationFlowPerimeterScope pour filtrer sur le périmètre actif.
     *
     * @return array<string, string> field => relation name
     */
    public static function perimeterRelations(): array
    {
        return self::SOURCE_RELATIONS + self::DEST_RELATIONS;
    }

    /* '*~-.,¸¸.-~·*'¨¯'*~-.,¸¸.-~·*'¨¯ UIDs ¯¨'*·~-.¸¸,.-~*''*~-.,¸¸.-~·*'¨¯ */

    /**
     * Retourne l'UID de la source (ex: "APP_42", "SRV_15")
     * Utilise le préfixe statique défini dans chaque modèle
     */
    public function sourceId(): ?string
    {
        return $this->getEntityUID(self::SOURCE_RELATIONS);
    }

    /**
     * Retourne l'UID de la destination (ex: "MOD_8", "DB_23")
     * Utilise le préfixe statique défini dans chaque modèle
     */
    public function destId(): ?string
    {
        return $this->getEntityUID(self::DEST_RELATIONS);
    }

    /** @return BelongsToMany<Information, $this> */
    public function informations(): BelongsToMany
    {
        return $this->belongsToMany(Information::class, 'application_flow_information', 'flux_id', 'information_id');
    }

    /* '*~-.,¸¸.-~·*'¨¯'*~-.,¸¸.-~·*'¨¯ Relations ¯¨'*·~-.¸¸,.-~*''*~-.,¸¸.-~·*'¨¯ */

    /** @return BelongsTo<Application, $this> */
    public function applicationSource(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_source_id');
    }

    /** @return BelongsTo<ApplicationService, $this> */
    public function serviceSource(): BelongsTo
    {
        return $this->belongsTo(ApplicationService::class, 'service_source_id');
    }

    /** @return BelongsTo<ApplicationModule, $this> */
    public function moduleSource(): BelongsTo
    {
        return $this->belongsTo(ApplicationModule::class, 'module_source_id');
    }

    /** @return BelongsTo<Database, $this> */
    public function databaseSource(): BelongsTo
    {
        return $this->belongsTo(Database::class, 'database_source_id');
    }

    /** @return BelongsTo<Application, $this> */
    public function applicationDest(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_dest_id');
    }

    /** @return BelongsTo<ApplicationService, $this> */
    public function serviceDest(): BelongsTo
    {
        return $this->belongsTo(ApplicationService::class, 'service_dest_id');
    }

    /** @return BelongsTo<ApplicationModule, $this> */
    public function moduleDest(): BelongsTo
    {
        return $this->belongsTo(ApplicationModule::class, 'module_dest_id');
    }

    /** @return BelongsTo<Database, $this> */
    public function databaseDest(): BelongsTo
    {
        return $this->belongsTo(Database::class, 'database_dest_id');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->orWhere(fn ($q) => $q
                ->whereNotNull('application_source_id')
                ->whereNotNull('module_source_id')
                ->whereNotNull('database_source_id'))
            ->orWhere(fn ($q) => $q
                ->whereNotNull('application_dest_id')
                ->whereNotNull('module_dest_id')
                ->whereNotNull('database_dest_id'));
    }
}
