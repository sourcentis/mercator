<?php

namespace App\Models;

use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\LogicalFlowFactory;
use App\Traits\Auditable;
use App\Traits\HasCartographers;
use App\Traits\HasPerimeter;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * App\LogicalFlow
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property string|null $description
 * @property string|null $chain
 * @property string|null $interface
 * @property int|null $router_id
 * @property string|null $priority
 * @property string|null $protocol
 * @property string|null $source_ip_range
 * @property string|null $dest_ip_range
 * @property string|null $source_port
 * @property string|null $dest_port
 * @property int|null $logical_server_source_id
 * @property int|null $peripheral_source_id
 * @property int|null $physical_server_source_id
 * @property int|null $storage_device_source_id
 * @property int|null $workstation_source_id
 * @property int|null $physical_security_device_source_id
 * @property int|null $security_device_source_id
 * @property int|null $subnetwork_source_id
 * @property int|null $cluster_source_id
 * @property int|null $logical_server_dest_id
 * @property int|null $peripheral_dest_id
 * @property int|null $physical_server_dest_id
 * @property int|null $storage_device_dest_id
 * @property int|null $workstation_dest_id
 * @property int|null $physical_security_device_dest_id
 * @property int|null $security_device_dest_id
 * @property int|null $subnetwork_dest_id
 * @property int|null $cluster_dest_id
 * @property string|null $users
 * @property string|null $schedule
 * @property string|null $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LogicalFlow extends Model implements HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'logical_flows';

    public static string $prefix = 'LFLOW_';

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
        'chain',
        'interface',
        'router_id',
        'priority',
        'protocol',
        // IP / Range
        'source_ip_range',
        'dest_ip_range',
        'source_port',
        'dest_port',
        // Sources
        'logical_server_source_id',
        'peripheral_source_id',
        'physical_server_source_id',
        'storage_device_source_id',
        'workstation_source_id',
        'physical_security_device_source_id',
        'security_device_source_id',
        'subnetwork_source_id',
        'cluster_source_id',
        // Destinations
        'logical_server_dest_id',
        'peripheral_dest_id',
        'physical_server_dest_id',
        'storage_device_dest_id',
        'workstation_dest_id',
        'physical_security_device_dest_id',
        'security_device_dest_id',
        'subnetwork_dest_id',
        'cluster_dest_id',
        // Others
        'users',
        'schedule',
        'action',
    ];

    /**
     * Mapping des champs ID vers les noms de relations pour les sources
     */
    private const SOURCE_RELATIONS = [
        'logical_server_source_id' => 'logicalServerSource',
        'peripheral_source_id' => 'peripheralSource',
        'physical_server_source_id' => 'physicalServerSource',
        'storage_device_source_id' => 'storageDeviceSource',
        'workstation_source_id' => 'workstationSource',
        'physical_security_device_source_id' => 'physicalSecurityDeviceSource',
        'security_device_source_id' => 'securityDeviceSource',
        'subnetwork_source_id' => 'subnetworkSource',
        'cluster_source_id' => 'clusterSource',
    ];

    /**
     * Mapping des champs ID vers les noms de relations pour les destinations
     */
    private const DEST_RELATIONS = [
        'logical_server_dest_id' => 'logicalServerDest',
        'peripheral_dest_id' => 'peripheralDest',
        'physical_server_dest_id' => 'physicalServerDest',
        'storage_device_dest_id' => 'storageDeviceDest',
        'workstation_dest_id' => 'workstationDest',
        'physical_security_device_dest_id' => 'physicalSecurityDeviceDest',
        'security_device_dest_id' => 'securityDeviceDest',
        'subnetwork_dest_id' => 'subnetworkDest',
        'cluster_dest_id' => 'clusterDest',
    ];

    protected static function newFactory(): Factory
    {
        return LogicalFlowFactory::new();
    }

    /* ⋅.˳˳.⋅ॱ˙˙ॱ⋅.˳˳.⋅ॱ˙˙ॱᐧ.˳˳.⋅  Sources  ⋅.˳˳.⋅ॱ˙˙ॱ⋅.˳˳.⋅ॱ˙˙ॱᐧ.˳˳.⋅ */

    /** @return BelongsTo<LogicalServer, $this> */
    public function logicalServerSource(): BelongsTo
    {
        return $this->belongsTo(LogicalServer::class, 'logical_server_source_id');
    }

    /** @return BelongsTo<Peripheral, $this> */
    public function peripheralSource(): BelongsTo
    {
        return $this->belongsTo(Peripheral::class, 'peripheral_source_id');
    }

    /** @return BelongsTo<PhysicalServer, $this> */
    public function physicalServerSource(): BelongsTo
    {
        return $this->belongsTo(PhysicalServer::class, 'physical_server_source_id');
    }

    /** @return BelongsTo<StorageDevice, $this> */
    public function storageDeviceSource(): BelongsTo
    {
        return $this->belongsTo(StorageDevice::class, 'storage_device_source_id');
    }

    /** @return BelongsTo<Workstation, $this> */
    public function workstationSource(): BelongsTo
    {
        return $this->belongsTo(Workstation::class, 'workstation_source_id');
    }

    /** @return BelongsTo<PhysicalSecurityDevice, $this> */
    public function physicalSecurityDeviceSource(): BelongsTo
    {
        return $this->belongsTo(PhysicalSecurityDevice::class, 'physical_security_device_source_id');
    }

    /** @return BelongsTo<Subnetwork, $this> */
    public function subnetworkSource(): BelongsTo
    {
        return $this->belongsTo(Subnetwork::class, 'subnetwork_source_id');
    }

    /** @return BelongsTo<SecurityDevice, $this> */
    public function securityDeviceSource(): BelongsTo
    {
        return $this->belongsTo(SecurityDevice::class, 'security_device_source_id');
    }

    /** @return BelongsTo<Cluster, $this> */
    public function clusterSource(): BelongsTo
    {
        return $this->belongsTo(Cluster::class, 'cluster_source_id');
    }

    /* ⋅.˳˳.⋅ॱ˙˙ॱ⋅.˳˳.⋅ॱ˙˙ॱᐧ.˳˳.⋅ Destinations ⋅.˳˳.⋅ॱ˙˙ॱ⋅.˳˳.⋅ॱ˙˙ॱᐧ.˳˳.⋅ */

    /** @return BelongsTo<LogicalServer, $this> */
    public function logicalServerDest(): BelongsTo
    {
        return $this->belongsTo(LogicalServer::class, 'logical_server_dest_id');
    }

    /** @return BelongsTo<Peripheral, $this> */
    public function peripheralDest(): BelongsTo
    {
        return $this->belongsTo(Peripheral::class, 'peripheral_dest_id');
    }

    /** @return BelongsTo<PhysicalServer, $this> */
    public function physicalServerDest(): BelongsTo
    {
        return $this->belongsTo(PhysicalServer::class, 'physical_server_dest_id');
    }

    /** @return BelongsTo<StorageDevice, $this> */
    public function storageDeviceDest(): BelongsTo
    {
        return $this->belongsTo(StorageDevice::class, 'storage_device_dest_id');
    }

    /** @return BelongsTo<Workstation, $this> */
    public function workstationDest(): BelongsTo
    {
        return $this->belongsTo(Workstation::class, 'workstation_dest_id');
    }

    /** @return BelongsTo<PhysicalSecurityDevice, $this> */
    public function physicalSecurityDeviceDest(): BelongsTo
    {
        return $this->belongsTo(PhysicalSecurityDevice::class, 'physical_security_device_dest_id');
    }

    /** @return BelongsTo<Subnetwork, $this> */
    public function subnetworkDest(): BelongsTo
    {
        return $this->belongsTo(Subnetwork::class, 'subnetwork_dest_id');
    }

    /** @return BelongsTo<SecurityDevice, $this> */
    public function securityDeviceDest(): BelongsTo
    {
        return $this->belongsTo(SecurityDevice::class, 'security_device_dest_id');
    }

    /** @return BelongsTo<Cluster, $this> */
    public function clusterDest(): BelongsTo
    {
        return $this->belongsTo(Cluster::class, 'cluster_dest_id');
    }

    /* '*~-.,¸¸.-~·*'¨¯'*~-.,¸¸.-~·*'¨¯ Router ¯¨'*·~-.¸¸,.-~*''*~-.,¸¸.-~·*'¨¯ */

    /** @return BelongsTo<Router, $this> */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id');
    }

    /* '*~-.,¸¸.-~·*'¨¯'*~-.,¸¸.-~·*'¨¯ IP ¯¨'*·~-.¸¸,.-~*''*~-.,¸¸.-~·*'¨¯ */

    public function isSource(?string $ip): bool
    {
        return ($this->source_ip_range !== null) &&
            ($ip !== null) &&
            $this->contains($this->source_ip_range, $ip);
    }

    public function isDestination(?string $ip): bool
    {
        return ($this->dest_ip_range !== null) &&
            ($ip !== null) &&
            $this->contains($this->dest_ip_range, $ip);
    }

    /* '*~-.,¸¸.-~·*'¨¯'*~-.,¸¸.-~·*'¨¯ UIDs ¯¨'*·~-.¸¸,.-~*''*~-.,¸¸.-~·*'¨¯ */

    /**
     * Retourne l'UID de la source (ex: "LSERVER_42")
     * Utilise le préfixe statique défini dans chaque modèle
     */
    public function sourceId(): ?string
    {
        return $this->getEntityUID(self::SOURCE_RELATIONS);
    }

    /**
     * Retourne l'UID de la destination (ex: "WORK_15")
     * Utilise le préfixe statique défini dans chaque modèle
     */
    public function destinationId(): ?string
    {
        return $this->getEntityUID(self::DEST_RELATIONS);
    }

    /* '*~-.,¸¸.-~·*'¨¯'*~-.,¸¸.-~·*'¨¯ Private ¯¨'*·~-.¸¸,.-~*''*~-.,¸¸.-~·*'¨¯ */

    /**
     * Vérifie si une IP est contenue dans un CIDR
     * Supporte IPv4 et IPv6
     */
    private function contains(string $cidr, string $ip): bool
    {
        if ((str_contains($ip, '.') && str_contains($cidr, '.')) ||
            (str_contains($ip, ':') && str_contains($cidr, ':'))) {
            // Get mask bits
            $parts = explode('/', $cidr);
            if (count($parts) !== 2) {
                Log::warning('LogicalFlow: invalid CIDR format', ['cidr' => $cidr, 'id' => $this->id]);

                return false;
            }
            [$net, $maskBits] = $parts;
            $maskBits = (int) $maskBits;

            // Size
            $size = ! str_contains($ip, ':') ? 4 : 16;

            // Convert to binary
            $ip = inet_pton(trim($ip));
            $net = inet_pton(trim($net));
            if (! $ip || ! $net) {
                return false;
            }

            // Build mask
            $solid = intdiv(intval($maskBits), 8);
            $solidBits = $solid * 8;
            $mask = str_repeat(chr(255), $solid);
            for ($i = $solidBits; $i < $maskBits; $i += 8) {
                $bits = max(0, min(8, intval($maskBits) - $i));
                $mask .= chr(pow(2, $bits) - 1 << 8 - $bits);
            }
            $mask = str_pad($mask, $size, chr(0));

            // Compare the mask
            return ($ip & $mask) === ($net & $mask);
        }

        return false;
    }
}
