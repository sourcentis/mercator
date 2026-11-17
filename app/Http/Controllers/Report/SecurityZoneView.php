<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\AutoloadsRelations;
use App\Models\Cartographer;
use App\Models\Zone;
use App\Services\Graph\GraphSize;
use App\Services\Graph\SecurityZoneGraphBuilder;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityZoneView extends Controller
{
    use AutoloadsRelations;

    public function generate(Request $request)
    {
        $allowed = Gate::allows('zone_access') || Cartographer::canAccess(Zone::class);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->has('filter')) {
            $selectedIds = array_values(array_filter(array_map('intval', (array) $request->input('zones', []))));
            $request->session()->put('security_zone_filter', $selectedIds);
        } else {
            $raw = $request->session()->get('security_zone_filter', []);
            $selectedIds = is_array($raw) ? $raw : [];
        }

        $allZones = Cartographer::scopedQuery(Zone::query())->orderBy('name')->pluck('name', 'id');

        $query = Cartographer::scopedQuery(Zone::with('parentZones', 'childZones', 'buildings', 'adminUsers')->orderBy('name'));
        if (! empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }
        $zones = $query->get();

        $buildings = $zones->flatMap(fn ($z) => $z->buildings)->unique('id')->sortBy('name');
        $adminUsers = $zones->flatMap(fn ($z) => $z->adminUsers)->unique('id')->sortBy('user_id');

        $this->autoloadRelations($zones, $buildings, $adminUsers);

        $graphBuilder = new SecurityZoneGraphBuilder;
        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count($zones, $buildings, $adminUsers));
        $dotSrc = $graphTooLarge ? '' : $graphBuilder->buildDot($zones, $buildings, $adminUsers);
        $imageManifest = $graphBuilder->imageManifest();

        return view('admin/reports/security_zones', compact(
            'allZones',
            'selectedIds',
            'zones',
            'buildings',
            'adminUsers',
            'dotSrc',
            'graphTooLarge',
            'imageManifest',
        ));
    }
}
