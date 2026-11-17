<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SavedQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'query',
        'is_public',
        'user_id',
    ];

    protected $casts = [
        'query' => 'array',
        'is_public' => 'boolean',
    ];

    // ─── Relations ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ────────────────────────────────────────────────

    /**
     * Requêtes visibles par l'utilisateur courant :
     * les siennes + toutes les publiques.
     */
    public function scopeVisibleBy($query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_public', true);
        });
    }

    // ─── Helpers ───────────────────────────────────────────────

    /**
     * Même règle que scopeVisibleBy, pour une requête déjà chargée.
     */
    public function isVisibleBy(int $userId): bool
    {
        return $this->is_public || $this->user_id === $userId;
    }

    /**
     * Retourne une copie du DSL avec un nouveau nom.
     * Utilisé pour la duplication.
     */
    public function duplicate(): static
    {
        return new self()->fill([
            'name' => $this->name.' (copie)',
            'description' => $this->description,
            'query' => $this->query,
            'is_public' => false,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Retourne le mode de sortie du DSL (graph|list).
     */
    public function getOutputAttribute(): string
    {
        return $this->query['output'] ?? 'list';
    }

    /**
     * Retourne l'entité source du DSL.
     */
    public function getFromAttribute(): string
    {
        return $this->query['from'] ?? '';
    }
}
