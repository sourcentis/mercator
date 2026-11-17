<?php

namespace App\Models;

use App\Factories\RelationValueFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\RelationValue
 */
class RelationValue extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    public $table = 'relation_values';

    public static array $searchable = [
    ];

    // For PostgreSQL
    protected $primaryKey = null;

    protected array $dates = [
        'date_price',
    ];

    protected $fillable = [
    ];

    protected static function newFactory(): Factory
    {
        return RelationValueFactory::new();
    }

    public function getDatePriceAttribute($value)
    {
        return $value;
    }

    public function setDatePriceAttribute($value): void
    {
        $this->attributes['date_price'] = $value;
    }

    /** @return BelongsTo<Relation, $this> */
    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'relation_id');
    }
}
