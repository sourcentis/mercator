<?php

namespace App\Models;

use App\Factories\CPEVersionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\CPEProduct
 */
class CPEVersion extends Model
{
    use HasFactory;

    public $table = 'cpe_versions';

    public $timestamps = false;

    public static array $searchable = [
    ];

    protected array $dates = [
    ];

    protected $fillable = [
        'cpe_product_id',
        'name',
    ];

    protected static function newFactory(): Factory
    {
        return CPEVersionFactory::new();
    }

    /** @return BelongsTo<CPEProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CPEProduct::class, 'cpe_product_id');
    }
}
