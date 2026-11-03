<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\LogicalServerFactory;
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\LogicalServer
 */
class LogicalServer extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'logical_servers';

    public static string $prefix = 'LSERVER_';

    public static string $icon = '/images/lserver.png';

    public static array $searchable = [
        'name',
        'type',
        'description',
        'configuration',
        'net_services',
        'address_ip',
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
        'icon_id',
        'active',
        'description',
        'operating_system',
        'install_date',
        'attributes',
        'patching_frequency',
        'update_date',
        'next_update',
        'address_ip',
        'cpu',
        'memory',
        'disk',
        'disk_used',
        'domain_id',
        'environment',
        'net_services',
        'configuration',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'patching_frequency' => 'integer',
        'install_date' => 'date',
        'update_date' => 'date',
        'next_update' => 'date',
    ];

    protected static function newFactory(): Factory
    {
        return LogicalServerFactory::new();
    }

    /** @return BelongsToMany<Application, $this> */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->orderBy('name');
    }

    /** @return BelongsToMany<PhysicalServer, $this> */
    public function physicalServers(): BelongsToMany
    {
        return $this->belongsToMany(PhysicalServer::class)->orderBy('name');
    }

    /** @return Collection<int, int> */
    public function serverIds(): Collection
    {
        return $this->belongsToMany(PhysicalServer::class)->pluck('id');
    }

    /** @return BelongsToMany<Document, $this> */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)->orderBy('document_id');
    }

    /** @return BelongsToMany<Database, $this> */
    public function databases(): BelongsToMany
    {
        return $this->belongsToMany(Database::class)->orderBy('name');
    }

    /** @return BelongsToMany<Cluster, $this> */
    public function clusters(): BelongsToMany
    {
        return $this->belongsToMany(Cluster::class);
    }

    /** @return BelongsTo<Domain, $this> */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    /** @return BelongsToMany<Certificate, $this> */
    public function certificates(): BelongsToMany
    {
        return $this->belongsToMany(Certificate::class)->orderBy('name');
    }

    /** @return BelongsToMany<Container, $this> */
    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class)->orderBy('name');
    }

    /** @return BelongsToMany<Backup, $this> */
    public function backups(): BelongsToMany
    {
        return $this->belongsToMany(Backup::class, 'backup_logical_server');
    }

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('active', 0)
            ->orWhere(fn ($q1) => $q1
                ->where('active', 1)
                ->whereNotNull('description')
                ->whereNotNull('operating_system')
                ->whereNotNull('environment')
                ->whereNotNull('address_ip')
                ->whereExists(fn ($q2) => $q2
                    ->from('application_logical_server')
                    ->whereColumn('application_logical_server.logical_server_id', 'logical_servers.id'))
                ->where(fn ($q3) => $q3
                    ->whereExists(fn ($q4) => $q4
                        ->from('logical_server_physical_server')
                        ->whereColumn('logical_server_physical_server.logical_server_id', 'logical_servers.id'))
                    ->orWhereExists(fn ($q5) => $q5
                        ->from('cluster_logical_server')
                        ->whereColumn('cluster_logical_server.logical_server_id', 'logical_servers.id')))));
    }
}
