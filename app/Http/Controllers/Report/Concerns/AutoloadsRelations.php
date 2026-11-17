<?php

namespace App\Http\Controllers\Report\Concerns;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

trait AutoloadsRelations
{
    /**
     * Report views render the _details partial of every object they list, each
     * partial touching a different set of relations (perimeter, location, links...).
     * Rather than maintaining an exhaustive with() list per collection, let Eloquent
     * eager-load any relation the first time it is accessed on one model, for the
     * whole collection at once (nested relations included).
     */
    protected function autoloadRelations(?Collection ...$collections): void
    {
        foreach ($collections as $collection) {
            if ($collection === null) {
                continue;
            }
            ($collection instanceof EloquentCollection ? $collection : new EloquentCollection($collection->all()))
                ->withRelationshipAutoloading();
        }
    }
}
