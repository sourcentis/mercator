<?php

namespace App\Models;

use App\Factories\CPEVendorFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\CPEVendor
 */
class CPEVendor extends Model
{
    use HasFactory;

    public $table = 'cpe_vendors';

    public $timestamps = false;

    public static array $searchable = [
    ];

    protected array $dates = [
    ];

    protected $fillable = [
        'part',
        'name',
    ];

    protected static function newFactory(): Factory
    {
        return CPEVendorFactory::new();
    }

    /** @return BelongsToMany<CPEProduct, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(CPEProduct::class)->orderBy('name');
    }
}
