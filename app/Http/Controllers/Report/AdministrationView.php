<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\AutoloadsRelations;
use App\Models\AdminUser;
use App\Models\Annuaire;
use App\Models\Cartographer;
use App\Models\Domain;
use App\Models\ForestAd;
use App\Models\ZoneAdmin;
use App\Services\Graph\AdministrationGraphBuilder;
use App\Services\Graph\GraphSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdministrationView extends Controller
{
    use AutoloadsRelations;

    /**
     * Build the administration report view populated with zones, annuaires, forests, domains, and admin users.
     *
     * Aborts with HTTP 403 Forbidden if the current user is denied the 'reports_access' gate.
     *
     * Filter: optional list of zones (`zones[]`). When set, only those zones are shown, with
     * their annuaires and forests, the domains of those forests and the users of those domains.
     *
     * @return View The rendered 'admin/reports/administration' view with keys:
     *              - 'zones' => collection of ZoneAdmin
     *              - 'annuaires' => collection of Annuaire
     *              - 'forests' => collection of ForestAd
     *              - 'domains' => collection of Domain
     *              - 'adminUsers' => collection of AdminUser
     *              - 'all_zones' => zone names indexed by id (filter options)
     *              - 'selectedZones' => ids of the selected zones
     */
    public function generate(Request $request): View
    {
        $allowed = Gate::allows('explore_access') || Cartographer::canAccessAny([ZoneAdmin::class, Annuaire::class, ForestAd::class, Domain::class]);
        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $all_zones = Cartographer::scopedQuery(ZoneAdmin::query())->orderBy('name')->pluck('name', 'id');

        // Zones sélectionnées : on ne garde que des ids connus (et visibles)
        $selectedZones = collect((array) $request->input('zones', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $all_zones->has($id))
            ->unique()
            ->values()
            ->all();

        $zoneQuery = ZoneAdmin::query();
        $annuaireQuery = Annuaire::query()->with(['zoneAdmin', 'application']);
        $forestQuery = ForestAd::query();
        $domainQuery = Domain::query();
        $adminUserQuery = AdminUser::query();

        if ($selectedZones !== []) {
            $zoneQuery->whereIn('id', $selectedZones);
            $annuaireQuery->whereIn('zone_admin_id', $selectedZones);
            $forestQuery->whereIn('zone_admin_id', $selectedZones);
        }

        $zones = Cartographer::scopedQuery($zoneQuery)->get();
        $annuaires = Cartographer::scopedQuery($annuaireQuery)->get();
        $forests = Cartographer::scopedQuery($forestQuery)->get();

        if ($selectedZones !== []) {
            // Domaines des forêts retenues, puis utilisateurs de ces domaines
            $domainQuery->whereHas('forestAds', fn ($q) => $q->whereIn('forest_ads.id', $forests->pluck('id')));
        }
        $domains = Cartographer::scopedQuery($domainQuery)->get();

        if ($selectedZones !== []) {
            $adminUserQuery->whereIn('domain_id', $domains->pluck('id'));
        }
        $adminUsers = Cartographer::scopedQuery($adminUserQuery)->get();

        $this->autoloadRelations($zones, $annuaires, $forests, $domains, $adminUsers);

        // Compte les nœuds avant de construire le DOT : un graphe trop grand n'est ni construit ni envoyé
        // (+ les applications des annuaires, ajoutées par AdministrationGraphBuilder)
        $graphTooLarge = GraphSize::tooLarge(GraphSize::count(
            $zones, $annuaires, $forests, $domains, $adminUsers,
            $annuaires->pluck('application')->filter()->unique('id')
        ));

        $graphBuilder = new AdministrationGraphBuilder;

        return view('admin/reports/administration')
            ->with('all_zones', $all_zones)
            ->with('selectedZones', $selectedZones)
            ->with('zones', $zones)
            ->with('annuaires', $annuaires)
            ->with('forests', $forests)
            ->with('domains', $domains)
            ->with('adminUsers', $adminUsers)
            ->with('graphTooLarge', $graphTooLarge)
            ->with('dotSrc', $graphTooLarge ? '' : $graphBuilder->buildDot($zones, $annuaires, $forests, $domains, $adminUsers))
            ->with('imageManifest', $graphBuilder->imageManifest());
    }
}
