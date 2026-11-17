<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Cartographer;
use App\Models\Entity;
use App\Models\Relation;
use App\Services\Graph\EcosystemGraphBuilder;
use App\Services\Graph\GraphSize;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EcosystemView extends Controller
{
    /*
    * Ecosystem View
    *
    * Filtre : une liste d'entités de départ. Sans sélection, tout l'écosystème est
    * affiché ; sinon, seules les entités de départ, leurs voisins directs et les
    * relations qui les relient sont affichés.
    */
    public function generate(Request $request)
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([Entity::class, Relation::class]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Relations consumed by admin/entities/_details and admin/relations/_details,
        // eager-loaded up front to avoid per-row N+1 queries.
        $entities = Cartographer::scopedQuery(Entity::query())
            ->with([
                'perimeter', 'parentEntity', 'entities', 'processes', 'respApplications', 'databases',
                'sourceRelations.destination', 'destinationRelations.source',
            ])
            ->orderBy('name')
            ->get();

        $relations = Cartographer::scopedQuery(Relation::query())
            ->with(['perimeter', 'source', 'destination'])
            ->orderBy('name')
            ->get();

        $all_entities = $entities->pluck('name', 'id');

        // Entités de départ : on ne garde que des ids connus (et visibles)
        $selectedEntities = collect((array) $request->input('entities', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $all_entities->has($id))
            ->unique()
            ->values();

        if ($selectedEntities->isNotEmpty()) {
            $selected = $selectedEntities->flip();

            // Connexions des entités de départ avec leurs voisins
            $relations = $relations
                ->filter(fn (Relation $relation) => $selected->has($relation->source_id)
                    || $selected->has($relation->destination_id))
                ->values();

            $ids = $selectedEntities
                ->concat($relations->pluck('source_id'))
                ->concat($relations->pluck('destination_id'))
                ->flip();

            $entities = $entities
                ->filter(fn (Entity $entity) => $ids->has($entity->id))
                ->values();
        }

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count($entities));

        $graphBuilder = new EcosystemGraphBuilder;

        return view('admin/reports/ecosystem')
            ->with('all_entities', $all_entities)
            ->with('selectedEntities', $selectedEntities->all())
            ->with('entities', $entities)
            ->with('relations', $relations)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $graphTooLarge ? '' : $graphBuilder->buildDot($entities, $relations))
            ->with('imageManifest', $graphBuilder->imageManifest($entities));
    }
}
