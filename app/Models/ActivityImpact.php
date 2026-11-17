<?php

namespace App\Models;

use App\Factories\ActivityImpactFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Activity
 */
class ActivityImpact extends Model
{
    use HasFactory;

    public $table = 'activity_impact';

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
    ];

    public static array $searchable = [
    ];

    protected static function newFactory(): Factory
    {
        return ActivityImpactFactory::new();
    }

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
