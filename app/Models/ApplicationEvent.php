<?php

namespace App\Models;

use App\Factories\ApplicationEventFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEvent extends Model
{
    use HasFactory;

    public $table = 'application_events';

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'ext_refs',
        'user_id',
        'application_id',
        'message',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory(): Factory
    {
        return ApplicationEventFactory::new();
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
