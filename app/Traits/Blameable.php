<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Records the user who created, last updated and soft-deleted the model
 * (created_by / updated_by / deleted_by → users.id).
 *
 * A null value means the write was made by the system (console, sync, job).
 * audit_logs remains the source of truth for the full history: these columns
 * are a denormalized summary.
 *
 * Listeners use the "-ing" events, while Auditable uses the "-ed" events, so
 * the audit entry contains the blame columns.
 */
trait Blameable
{
    public static function bootBlameable(): void
    {
        static::creating(function (Model $model): void {
            $userId = Auth::id();
            // Always overwrite: neither replicate() nor an injected value
            // may determine the author.
            $model->setAttribute('created_by', $userId);
            $model->setAttribute('updated_by', $userId);
            $model->setAttribute('deleted_by', null);
        });

        static::updating(function (Model $model): void {
            // Only fired when the model is dirty.
            $model->setAttribute('updated_by', Auth::id());
        });

        static::deleting(function (Model $model): void {
            /** @phpstan-ignore method.notFound */
            if ($model->isForceDeleting()) {
                return;
            }
            $userId = Auth::id();
            // runSoftDelete() only writes deleted_at and updated_at: deleted_by
            // is written separately, without touching updated_at and without
            // firing any event.
            $model->newQueryWithoutScopes()
                ->whereKey($model->getKey())
                ->toBase()
                ->update(['deleted_by' => $userId]);
            $model->setAttribute('deleted_by', $userId);
            $model->syncOriginalAttribute('deleted_by');
        });

        static::restoring(function (Model $model): void {
            $model->setAttribute('deleted_by', null);
        });
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        /** @phpstan-ignore method.notFound */
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        /** @phpstan-ignore method.notFound */
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function deleter(): BelongsTo
    {
        /** @phpstan-ignore method.notFound */
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }
}
