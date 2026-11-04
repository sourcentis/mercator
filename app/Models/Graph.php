<?php

namespace App\Models;

use App\Contracts\HasPrefix;
use App\Factories\GraphFactory;
use App\Traits\Auditable;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Graph extends Model implements HasPrefix
{
    use Auditable, HasFactory, HasUniqueIdentifier, SoftDeletes;

    public $table = 'graphs';

    public static string $prefix = 'GRAPH_';

    public static array $searchable = [
        'name',
        'type',
    ];

    protected array $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'class',
        'type',
        'content',
    ];

    /**
     * BPMN graphs indexed by the UIDs they reference ("#UID" in their content),
     * built with a single query the first time it is needed. Reports render the
     * details of hundreds of BPMN objects, each asking for its graphs: one LIKE
     * query per object was the dominant cost of those pages.
     *
     * @var array<string, list<array{id: int, name: string}>>|null
     */
    private static ?array $bpmnIndex = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::resetBpmnIndex());
        static::deleted(fn () => self::resetBpmnIndex());
    }

    /**
     * BPMN graphs (class 2) whose content references the object with this UID.
     *
     * @return Collection<int, Graph>
     */
    public static function bpmnGraphsReferencing(string $uid): Collection
    {
        if (self::$bpmnIndex === null) {
            self::$bpmnIndex = [];
            foreach (self::query()->select('id', 'name', 'content')->where('class', '=', 2)->cursor() as $graph) {
                preg_match_all('/"#([^"]*)(?=")/', (string) $graph->content, $matches);
                foreach (array_unique($matches[1]) as $ref) {
                    self::$bpmnIndex[$ref][] = ['id' => $graph->id, 'name' => $graph->name];
                }
            }
        }

        return collect(self::$bpmnIndex[$uid] ?? [])
            ->map(fn (array $attributes) => (new self)->forceFill($attributes)->syncOriginal());
    }

    /**
     * Called once per application boot (see AppServiceProvider::boot()) and whenever
     * a graph changes, so the index never serves stale content.
     */
    public static function resetBpmnIndex(): void
    {
        self::$bpmnIndex = null;
    }

    protected static function newFactory(): Factory
    {
        return GraphFactory::new();
    }
}
