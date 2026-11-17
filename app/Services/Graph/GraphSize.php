<?php

namespace App\Services\Graph;

use Countable;

/**
 * Server-side guard against graphs too large for client-side Graphviz layout.
 *
 * The node count is estimated before the DOT source is built, from the sizes of
 * the node collections handed to a GraphBuilder (one DOT node per object), so
 * that oversized graphs are neither built nor sent to the browser.
 */
class GraphSize
{
    /**
     * Configured limit; 0 means no limit.
     */
    public static function maxNodes(): int
    {
        return max(0, (int) config('mercator.parameters.max_nodes', 500));
    }

    /**
     * Estimated number of nodes: the sum of the sizes of the node collections.
     */
    public static function count(Countable|array ...$collections): int
    {
        return array_sum(array_map(fn ($collection) => count($collection), $collections));
    }

    /**
     * @return array{count: int, max: int}|null null when the graph may be rendered
     */
    public static function tooLarge(int $count): ?array
    {
        $max = self::maxNodes();

        return $max > 0 && $count > $max ? ['count' => $count, 'max' => $max] : null;
    }
}
