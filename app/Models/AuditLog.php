<?php

namespace App\Models;

use App\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * App\AuditLog
 */
class AuditLog extends Model
{
    use HasFactory;

    public $table = 'audit_logs';

    protected $fillable = [
        'description',
        'subject_id',
        'subject_type',
        'user_id',
        'properties',
        'host',
    ];

    protected $casts = [
        'properties' => 'collection',
    ];

    protected static function newFactory(): Factory
    {
        return AuditLogFactory::new();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subjectURL(): string
    {
        return AuditLog::url($this->subject_type, (string) $this->subject_id);
    }

    public static function URL(string $subject_type, string $subject_id): string
    {
        // For non regression
        if ($subject_type === 'App\Models\MApplication') {
            $subject_type = 'App\Models\Application';
        }

        return '/admin/'.
            Str::plural(Str::snake(Str::afterLast($subject_type, '\\'), '-')).
            '/'.$subject_id;
    }
}
