<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Actor;
use App\Models\Graph;
use App\Models\Information;
use App\Models\MacroProcessus;
use App\Models\Operation;
use App\Models\Process;
use App\Models\Task;
use Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BPMNController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(): View
    {
        abort_if(Gate::denies('graph_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graphs = Graph::query()->orderBy('name')
            ->where('class', '=', 2)
            ->get();

        return view('bpmn.index', compact('graphs'));
    }

    public function create(): View
    {
        abort_if(Gate::denies('graph_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get types
        $type_list = Graph::query()
            ->select('type')
            ->whereNotNull('type')
            ->where('class', '=', 2)
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'bpmn.edit',
            compact('type_list')
        )
            ->with('id', '-1')
            ->with('type', '')
            ->with('name', '')
            ->with('content', '');
    }

    public function clone(Request $request): View
    {
        abort_if(Gate::denies('graph_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get graph
        $graph = Graph::query()->find($request->id);

        // Graph not found
        abort_if($graph === null, Response::HTTP_NOT_FOUND, '404 Not Found');

        abort_if($graph->class !== 2, Response::HTTP_NOT_ACCEPTABLE, '406 Not BPMN');

        // get nodes and edges from the explorer
        [$nodes, $edges] = app('App\Http\Controllers\Admin\ExplorerController')->getData();

        // Get types
        $type_list = Graph::query()
            ->select('type')
            ->whereNotNull('type')
            ->where('class', '=', 2)
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'bpmn.edit',
            compact('type_list', 'nodes', 'edges')
        )
            ->with('id', '-1')
            ->with('name', $graph->name)
            ->with('type', $graph->type)
            ->with('content', $graph->content);
    }

    public function raw(Request $request)
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graph = Graph::query()->find($request['id']);

        abort_if($graph->class !== 2, Response::HTTP_NOT_ACCEPTABLE, '406 Not BPMN');

        // Get types
        $type_list = Graph::query()
            ->select('type')
            ->whereNotNull('type')
            ->where('class', '=', 2)
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // get nodes and edges from the explorer
        // [$nodes, $edges] = app('App\Http\Controllers\Admin\ExplorerController')->getData();

        // return
        return view(
            'bpmn.raw',
            compact('type_list',
                /* 'nodes', 'edges' */)
        )
            ->with('id', $graph->id)
            ->with('name', $graph->name)
            ->with('type', $graph->type)
            ->with('content', $graph->content);
    }

    public function edit(Request $request)
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graph = Graph::query()->find($request['id']);

        abort_if($graph->class !== 2, Response::HTTP_NOT_ACCEPTABLE, '406 Not BPMN');

        // Get types
        $type_list = Graph::query()
            ->select('type')
            ->whereNotNull('type')
            ->where('class', '=', 2)
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // return
        return view(
            'bpmn.edit',
            compact('type_list')
        )
            ->with('id', $graph->id)
            ->with('name', $graph->name)
            ->with('type', $graph->type)
            ->with('content', $graph->content);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->id == '-1') {
            $graph = Graph::query()->create($request->all());
        } else {
            $graph = Graph::query()->find($request->id);
            $graph->update($request->all());
        }
        $graph->class = 2;
        $graph->save();

        // Si c'est une requête AJAX, retourner du JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'graph_id' => $graph->id,
            ]);
        }

        return redirect()->route('admin.bpmn.show', $graph->id);
    }

    public function show(Request $request): View
    {
        abort_if(Gate::denies('graph_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graph = Graph::query()->find($request->id);

        abort_if($graph->class !== 2, Response::HTTP_NOT_ACCEPTABLE, '406 Not a graph');

        // Get the URLS
        if ($graph->content !== null) {
            $urls = $this->getCellUrls($graph->content);
        } else {
            $urls = [];
        }

        // Get the associated objects
        $macroProcessuses = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.MacroProcessus::$prefix))
            ->map(fn ($url) => MacroProcessus::query()->find(substr($url, strlen(MacroProcessus::$prefix) + 1)));

        $processes = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.Process::$prefix))
            ->map(fn ($url) => Process::query()->find(substr($url, strlen(Process::$prefix) + 1)));

        $activities = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.Activity::$prefix))
            ->map(fn ($url) => Activity::query()->find(substr($url, strlen(Activity::$prefix) + 1)));

        $operations = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.Operation::$prefix))
            ->map(fn ($url) => Operation::query()->find(substr($url, strlen(Operation::$prefix) + 1)));

        $tasks = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.Task::$prefix))
            ->map(fn ($url) => Task::query()->find(substr($url, strlen(Task::$prefix) + 1)));

        $actors = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.Actor::$prefix))
            ->map(fn ($url) => Actor::query()->find(substr($url, strlen(Actor::$prefix) + 1)));

        $informations = collect($urls)
            ->filter(fn ($url) => Str::startsWith($url, '#'.Information::$prefix))
            ->map(fn ($url) => Information::query()->find(substr($url, strlen(Information::$prefix) + 1)));

        return view('bpmn.show',
            compact('graph',
                'macroProcessuses',
                'processes',
                'activities',
                'operations',
                'tasks',
                'actors',
                'informations'
            ));
    }

    private function getCellUrls(string $xmlString): array
    {
        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            return [];
        }

        $urls = [];
        foreach ($xml->xpath('//Cell[@url]') as $cell) {
            $urls[] = (string) $cell['url'];
        }

        return $urls;
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('graph_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Graph::query()->findOrFail($request->id)->delete();

        return redirect()->route('admin.bpmn.index');
    }

    /*
     * Get all objects that can be associated with activitis in the graph
     */
    public function objects(): JsonResponse
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /*
        $graphs = Graph::query()
            ->select('id', 'name')
            ->where('class','=', 2)
            ->get()
            ->map(fn ($g) => [
                'id'   => Graph::$prefix . $g->id,
                'name' => $g->name,
                'url' => '/admin/bpmn/' . $g->id
            ]);
        */

        $activities = Activity::query()
            ->select('id', 'name')
            ->get()
            ->map(fn ($a) => [
                'id' => Activity::$prefix.$a->id,
                'name' => $a->name,
                // 'url' => '/admin/activities/' . $a->id
                'url' => '#'.Activity::$prefix.$a->id,
            ]);

        $operations = Operation::query()
            ->select('id', 'name')
            ->get()
            ->map(fn ($o) => [
                'id' => Operation::$prefix.$o->id,
                'name' => $o->name,
                // 'url' => '/admin/operations/' . $o->id
                'url' => '#'.Operation::$prefix.$o->id,
            ]);

        $tasks = Task::query()
            ->select('id', 'name')
            ->get()
            ->map(fn ($t) => [
                'id' => Task::$prefix.$t->id,
                'name' => $t->name,
                // 'url' => '/admin/tasks/' . $t->id
                'url' => '#'.Task::$prefix.$t->id,
            ]);

        $all = $activities
            ->concat($operations)
            ->concat($tasks)
            ->sortBy('name')
            ->values();

        return response()->json($all);
    }

    /*
     * Get all objects that can be associated with activities in the graph
     */
    public function information(): JsonResponse
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information = Information::query()
            ->select('id', 'name')
            ->get()
            ->map(fn ($i) => [
                'id' => Information::$prefix.$i->id,
                'name' => $i->name,
                // 'url' => '/admin/information/' . $i->id
                'url' => '#'.Information::$prefix.$i->id,
            ]);

        $all = $information
            ->sortBy('name')
            ->values();

        return response()->json($all);
    }

    /*
 * Get all actors that can be associated with activities in the graph
 */
    public function actors(): JsonResponse
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $information = Actor::query()
            ->select('id', 'name')
            ->get()
            ->map(fn ($p) => [
                'id' => Actor::$prefix.$p->id,
                'name' => $p->name,
                // 'url' => '/admin/actors/' . $p->id
                'url' => '#'.Actor::$prefix.$p->id,
            ]);

        $all = $information
            ->sortBy('name')
            ->values();

        return response()->json($all);
    }

    /*
    * Get all process that can be associated with conversations in the graph
    */
    public function process(): JsonResponse
    {
        abort_if(Gate::denies('graph_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graphs = Graph::query()
            ->select('id', 'name')
            ->where('class', '=', 2)
            ->get()
            ->map(fn ($g) => [
                'id' => Graph::$prefix.$g->id,
                'name' => $g->name,
                'url' => '/admin/bpmn/'.$g->id,
            ]);

        $process = Process::query()
            ->select('id', 'name')
            ->get()
            ->map(fn ($p) => [
                'id' => Process::$prefix.$p->id,
                'name' => $p->name,
                // 'url' => '/admin/processes/' . $p->id
                'url' => '#'.Process::$prefix.$p->id,
            ])->toArray();

        return response()->json(
            $graphs
                ->concat($process)
                ->sortBy('name')
                ->values()
        );
    }

    /**
     * Retourne le contenu XML d'un diagramme BPMN (utilisé par l'aperçu de l'index).
     */
    public function data(Request $request): JsonResponse
    {
        abort_if(Gate::denies('graph_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $graph = Graph::query()->findOrFail($request->id);

        abort_if($graph->class !== 2, Response::HTTP_NOT_ACCEPTABLE, '406 Not BPMN');

        return response()->json(['content' => $graph->content]);
    }
}
