<?php

namespace App\Models;

use App\Contracts\HasIconContract;
use App\Contracts\HasPrefix;
use App\Contracts\HasUniqueIdentifierContract;
use App\Factories\SubnetworkFactory;
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Subnetwork
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $attributes
 * @property string|null $description
 * @property string|null $address
 * @property string|null $default_gateway
 * @property string|null $ip_allocation_type
 * @property int|null $vlan_id
 * @property string|null $zone
 * @property string|null $dmz
 * @property string|null $wifi
 * @property string|null $responsible_exp
 * @property int|null $gateway_id
 * @property int|null $network_id
 * @property int|null $subnetwork_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Subnetwork extends Model implements HasIconContract, HasPrefix, HasUniqueIdentifierContract
{
    use Auditable, HasFactory, HasIcon, HasUniqueIdentifier, SoftDeletes;
    use HasCartographers;
    use HasPerimeter;

    public $table = 'subnetworks';

    public static string $prefix = 'SUBNETWORK_';

    public static string $icon = '/images/network.png';

    protected $fillable = [
        'perimeter_id',
        'ext_refs',
        'name',
        'type',
        'attributes',
        'description',
        'address',
        'default_gateway',
        'ip_allocation_type',
        'vlan_id',
        'zone',
        'dmz',
        'wifi',
        'responsible_exp',
        'gateway_id',
        'network_id',
        'subnetwork_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static array $searchable = [
        'name',
        'description',
        'responsible_exp',
        'address',
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
        return SubnetworkFactory::new();
    }

    /** @return HasMany<Network, $this> */
    public function connectedSubnetsNetworks(): HasMany
    {
        return $this->hasMany(Network::class, 'connected_subnets_id', 'id')->orderBy('name');
    }

    /** @return HasMany<Subnetwork, $this> */
    public function connectedSubnetsSubnetworks(): HasMany
    {
        return $this->hasMany(Subnetwork::class, 'connected_subnets_id', 'id')->orderBy('name');
    }

    /** @return BelongsTo<Network, $this> */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class, 'network_id');
    }

    /** @return BelongsTo<Subnetwork, $this> */
    public function connectedSubnets(): BelongsTo
    {
        return $this->belongsTo(Subnetwork::class, 'connected_subnets_id');
    }

    /** @return BelongsTo<Gateway, $this> */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }

    /**
     * Get the Vlan that this subnetwork is associated with.
     *
     * @return BelongsTo<Vlan, $this> Relationship to the Vlan model.
     */
    public function vlan(): BelongsTo
    {
        return $this->belongsTo(Vlan::class, 'vlan_id');
    }

    /**
     * Get the parent subnetwork that this subnetwork belongs to.
     *
     * @return BelongsTo<Subnetwork, $this> Relation to the parent Subnetwork model.
     */
    public function subnetwork(): BelongsTo
    {
        return $this->belongsTo(Subnetwork::class, 'subnetwork_id');
    }

    /**
     * Get the child subnetworks that this subnetwork belongs to.
     *
     * @return HasMany<Subnetwork, $this> Relation to the parent Subnetwork model.
     */
    public function subnetworks(): HasMany
    {
        return $this->hasMany(Subnetwork::class, 'subnetwork_id', 'id');
    }

    /**
     * Produce a human-readable IP range for this subnetwork's CIDR address.
     *
     * Returns the inclusive range represented by the model's `address` property:
     * - For IPv4 CIDRs returns "start - end".
     * - For IPv6 CIDRs returns "first -> last".
     * - Returns `null` when `address` is null and "N/A" when `address` is missing a prefix or not a valid IP/CIDR.
     *
     * @return string|null The IP range string, `null` if no address is set, or `"N/A"` for invalid/unparsable addresses.
     */
    public function ipRange(): ?string
    {
        // no address
        if ($this->address === null) {
            return null;
        }

        // Explode range
        $subnetParts = explode('/', $this->address);

        if (count($subnetParts) < 2) {
            return 'N/A';
        }

        // IPv4
        if (filter_var($subnetParts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($subnetParts[1] >= 32) {
                return $subnetParts[0].' - '.$subnetParts[0];
            }

            $ip = ip2long($subnetParts[0]);
            $mask = ~1 << 32 - intval($subnetParts[1]) - 1;
            $start = long2ip($ip & $mask);
            $end = long2ip($ip | ~$mask);

            return $start.' - '.$end;
        }

        // IPv6
        if (filter_var($subnetParts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Split in address and prefix length
            [$addr_given_str, $prefixlen] = explode('/', $this->address);

            // Parse the address into a binary string
            $addr_given_bin = inet_pton($addr_given_str);

            // Convert the binary string to a string with hexadecimal characters
            $addr_given_hex = bin2hex($addr_given_bin);

            // Overwriting first address string to make sure notation is optimal
            $addr_given_str = inet_ntop($addr_given_bin);

            // Calculate the number of 'flexible' bits
            $flexbits = 128 - intval($prefixlen);

            // Build the hexadecimal strings of the first and last addresses
            $addr_hex_first = $addr_given_hex;
            $addr_hex_last = $addr_given_hex;

            // We start at the end of the string (which is always 32 characters long)
            $pos = 31;
            while ($flexbits > 0) {
                // Get the characters at this position
                $orig_first = substr($addr_hex_first, $pos, 1);
                $orig_last = substr($addr_hex_last, $pos, 1);

                // Convert them to an integer
                $origval_first = hexdec($orig_first);
                $origval_last = hexdec($orig_last);

                // First address: calculate the subnet mask. min() prevents the comparison from being negative
                $mask = 0xF << min(4, $flexbits);

                // AND the original against its mask
                $new_val_first = $origval_first & $mask;

                // Last address: OR it with (2^flexbits)-1, with flexbits limited to 4 at a time
                $new_val_last = $origval_last | pow(2, min(4, $flexbits)) - 1;

                // Convert them back to hexadecimal characters
                $new_first = dechex($new_val_first);
                $new_last = dechex($new_val_last);

                // And put those character back in their strings
                $addr_hex_first = substr_replace($addr_hex_first, $new_first, $pos, 1);
                $addr_hex_last = substr_replace($addr_hex_last, $new_last, $pos, 1);

                // We processed one nibble, move to previous position
                $flexbits -= 4;
                $pos -= 1;
            }

            // Convert the hexadecimal strings to a binary string
            $addr_bin_first = hex2bin($addr_hex_first);
            $addr_bin_last = hex2bin($addr_hex_last);

            // And create an IPv6 address from the binary string
            $addr_str_first = inet_ntop($addr_bin_first);
            $addr_str_last = inet_ntop($addr_bin_last);

            // Report to user
            return $addr_str_first.' -> '.$addr_str_last;
        }

        // Si le subnet n'est ni IPv4 ni IPv6
        return 'N/A';
    }

    /**
     * Does the given IP match the CIDR prefix
     */
    public function contains(?string $ip): bool
    {
        if ($ip === null) {
            return false;
        }

        $cidr = $this->address;

        if ((str_contains($ip, '.') && str_contains($cidr, '.')) ||
              (str_contains($ip, ':') && str_contains($cidr, ':'))) {
            // Get mask bits
            [$net, $maskBits] = explode('/', $cidr);
            $maskBits = (int) $maskBits;

            // Size
            $size = strpos($ip, ':') === false ? 4 : 16;

            // Convert to binary
            $ip = inet_pton(trim($ip));
            $net = inet_pton($net);
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

    /** @param Builder<static> $query */
    public function scopeMaturityLevel1(Builder $query): Builder
    {
        return $query
            ->whereNotNull('description')
            ->whereNotNull('address')
            ->whereNotNull('default_gateway')
            ->whereNotNull('ip_allocation_type')
            ->whereNotNull('responsible_exp')
            ->whereNotNull('dmz')
            ->whereNotNull('wifi')
            ->whereNotNull('vlan_id');
    }

    /**
     * Get the mask length (prefix length) from the CIDR address.
     *
     * @return int The mask length (e.g., 24 for "192.168.1.0/24"), or 0 if invalid/missing.
     */
    public function getMaskLength(): int
    {
        // No address defined
        if ($this->address === null) {
            return 0;
        }

        // Split CIDR notation
        $parts = explode('/', $this->address);

        // Invalid format (no prefix length)
        if (count($parts) < 2) {
            return 0;
        }

        // Return the mask length as integer
        return (int) $parts[1];
    }
}
