<?php

namespace App\Models;

use App\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

/**
 * App\Permission
 */
class Permission extends Model
{
    use HasFactory;

    public $table = 'permissions';

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'ext_refs',
        'title',
        'module',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory(): Factory
    {
        return PermissionFactory::new();
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('permissions_roles_map');
        });

        static::deleted(function () {
            Cache::forget('permissions_roles_map');
        });
    }

    /**
     * Relation users
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
