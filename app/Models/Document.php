<?php

namespace App\Models;

use App\Factories\DocumentFactory;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class Document extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public $table = 'documents';

    public static array $searchable = [
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'ext_refs',
        'filename',
        'mimetype',
        'size',
        'hash',
    ];

    protected static function newFactory(): Factory
    {
        return DocumentFactory::new();
    }

    /** @return BelongsToMany<Activity, $this> */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }

    /** @return BelongsToMany<Entity, $this> */
    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'entity_document');
    }

    /** @return BelongsToMany<Application, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class);
    }

    /** @return BelongsToMany<Relation, $this> */
    public function entityRelations(): BelongsToMany
    {
        return $this->belongsToMany(Relation::class);
    }

    /** @return BelongsToMany<DataProcessing, $this> */
    public function dataProcessings(): BelongsToMany
    {
        return $this->belongsToMany(DataProcessing::class);
    }

    /** @return BelongsToMany<LogicalServer, $this> */
    public function logicalServers(): BelongsToMany
    {
        return $this->belongsToMany(LogicalServer::class);
    }

    /** @return BelongsToMany<ExternalConnectedEntity, $this> */
    public function externalConnectedEntities(): BelongsToMany
    {
        return $this->belongsToMany(ExternalConnectedEntity::class);
    }

    /**
     * Relations to the objects a document can be attached to.
     */
    public const OWNER_RELATIONS = [
        'activities',
        'entities',
        'applications',
        'entityRelations',
        'dataProcessings',
        'logicalServers',
        'externalConnectedEntities',
    ];

    /**
     * Tables whose `icon_id` column references a document used as a custom icon.
     */
    public const ICON_TABLES = [
        'admin_users',
        'applications',
        'buildings',
        'clusters',
        'containers',
        'databases',
        'entities',
        'information',
        'logical_servers',
        'peripherals',
        'physical_security_devices',
        'physical_servers',
        'physical_switches',
        'processes',
        'security_devices',
        'sites',
        'storage_devices',
        'workstations',
    ];

    /**
     * Is this document an image used as the custom icon of at least one object?
     * Icons are shown on the maps and in the icon pickers, so they are not sensitive.
     */
    public function isIcon(): bool
    {
        if (! str_starts_with((string) $this->mimetype, 'image/')) {
            return false;
        }

        foreach (self::ICON_TABLES as $table) {
            if (DB::table($table)->where('icon_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is this document attached to at least one object, whatever the user's perimeter?
     */
    public function hasOwners(): bool
    {
        foreach (self::OWNER_RELATIONS as $relation) {
            if ($this->{$relation}()->withoutGlobalScopes()->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Can the user see one of the objects this document is attached to
     * (role permission in the object's perimeter, or cartographer)?
     */
    public function isVisibleBy(User $user): bool
    {
        foreach (self::OWNER_RELATIONS as $relation) {
            foreach ($this->{$relation} as $owner) {
                if (Gate::forUser($user)->allows('show-object', $owner)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Restricts the query to the documents the user may list: documents attached to no
     * object, or attached to at least one object the user sees in the lists (`<type>_access`,
     * perimeters and cartographers through the owners' global scopes).
     *
     * @param  Builder<Document>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if (Gate::forUser($user)->allows('configure')) {
            return;
        }

        $query->where(function (Builder $query) use ($user) {
            $query->where(function (Builder $orphan) {
                foreach (self::OWNER_RELATIONS as $relation) {
                    $orphan->whereDoesntHave($relation, fn (Builder $owner) => $owner->withoutGlobalScopes());
                }
            });

            foreach (self::OWNER_RELATIONS as $relation) {
                $ability = Str::snake(class_basename($this->{$relation}()->getRelated())).'_access';
                if (Gate::forUser($user)->allows($ability)) {
                    $query->orWhereHas($relation);
                }
            }
        });
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size);
        /*
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = $this->size;
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
        */
    }
}
