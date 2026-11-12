@extends('layouts.admin')

@section('title')
    {{ trans("cruds.report.explorer.title") }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card explorer-screen" style="height: auto;">
                <div class="card-header">
                    {{ trans("cruds.report.explorer.title") }}
                </div>

                {{-- ─── Contrôles ──────────────────────────────────────────────── --}}
                <div class="card-body" style="flex: 0 0 auto;">
                    <!-- Loading indicator -->
                    <div id="loading-indicator" class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span id="loading-text">{{ trans('cruds.report.explorer.loading') }}</span>
                    </div>

                    <!-- Controls (hidden until data loaded) -->
                    <div id="explorer-controls" style="display: none;">
                        <table width="100%">
                            <tr>
                                <td width="400">
                                    <div class="form-group">
                                        <label for="title">Filtre</label>
                                        <select class="form-control select2" id="filters" multiple>
                                            @canAccessAny(\App\Models\Entity::class, \App\Models\Relation::class)
                                            <option value="1">{{ trans("cruds.report.cartography.ecosystem") }}</option>
                                            @endcanAccessAny
                                            @canAccessAny(\App\Models\Information::class, \App\Models\Process::class, \App\Models\MacroProcessus::class, \App\Models\Activity::class, \App\Models\Operation::class, \App\Models\Task::class, \App\Models\Actor::class)
                                            <option value="2">{{ trans("cruds.report.cartography.information_system") }}</option>
                                            @endcanAccessAny
                                            @canAccessAny(\App\Models\ApplicationBlock::class, \App\Models\Application::class, \App\Models\ApplicationService::class, \App\Models\ApplicationModule::class, \App\Models\Database::class)
                                            <option value="3">{{ trans("cruds.report.cartography.applications") }}</option>
                                            @endcanAccessAny
                                            @canAccessAny(\App\Models\ZoneAdmin::class, \App\Models\Annuaire::class, \App\Models\ForestAd::class, \App\Models\Domain::class, \App\Models\AdminUser::class)
                                            <option value="4">{{ trans("cruds.report.cartography.administration") }}</option>
                                            @endcanAccessAny
                                            @canAccessAny(\App\Models\Network::class, \App\Models\Subnetwork::class, \App\Models\NetworkSwitch::class, \App\Models\Gateway::class, \App\Models\ExternalConnectedEntity::class, \App\Models\Container::class, \App\Models\Cluster::class, \App\Models\LogicalServer::class, \App\Models\Backup::class, \App\Models\SecurityDevice::class, \App\Models\Router::class, \App\Models\Certificate::class, \App\Models\Vlan::class)
                                            <option value="5">{{ trans("cruds.report.cartography.logical_infrastructure") }}</option>
                                            @endcanAccessAny
                                            @canAccess(\App\Models\LogicalFlow::class)
                                            <option value="10">{{ trans("cruds.logicalFlow.title") }}</option>
                                            @endcanAccess
                                            @canAccess(\App\Models\ApplicationFlow::class)
                                            <option value="9">{{ trans("cruds.applicationFlow.title") }}</option>
                                            @endcanAccess
                                            @canAccessAny(\App\Models\Site::class, \App\Models\Building::class, \App\Models\Bay::class, \App\Models\Zone::class, \App\Models\PhysicalServer::class, \App\Models\Workstation::class, \App\Models\Phone::class, \App\Models\Peripheral::class, \App\Models\StorageDevice::class, \App\Models\PhysicalSwitch::class, \App\Models\PhysicalRouter::class, \App\Models\WifiTerminal::class, \App\Models\PhysicalSecurityDevice::class, \App\Models\Wan::class, \App\Models\Man::class, \App\Models\Lan::class)
                                            <option value="6">{{ trans("cruds.report.cartography.physical_infrastructure") }}</option>
                                            @endcanAccessAny
                                            @canAccess(\App\Models\PhysicalLink::class)
                                            <option value="8">{{ trans("cruds.physicalLink.title") }}</option>
                                            @endcanAccess
                                        </select>
                                        <span class="help-block">{{ trans("cruds.report.explorer.filter_helper") }}</span>
                                    </div>
                                </td>
                                <td width=10>
                                </td>
                                <td width="400">
                                    <div class="form-group">
                                        <label for="attr-filter">{{ trans("cruds.report.explorer.attributes") }}</label>
                                        <select class="form-control select2" id="attr-filter" multiple>
                                        </select>
                                        <span class="help-block">{{ trans('cruds.report.explorer.attributes_helper') }}</span>
                                    </div>
                                </td>
                                <td width="10">
                                </td>
                                <td width="400">
                                    <div class="form-group">
                                        <label for="title">{{ trans("cruds.report.explorer.object") }}</label>
                                        <select class="form-control select2" id="node">
                                            <option></option>
                                        </select>
                                        <span class="help-block">{{ trans("cruds.report.explorer.object_helper") }}</span>
                                    </div>
                                </td>
                                <td style="text-align: left; vertical-align: middle;">
<button type="button" class="btn btn-success" onclick="addNode(document.getElementById('node').value)">
<i class="bi bi-plus-square-fill"></i>&nbsp;{{ trans('global.add') }}
</button>
                                </td>
                                <td style="text-align: right; vertical-align: top;">
                                    &nbsp;
                                    <a onclick="needSavePNG=true; network.redraw();document.getElementById('canvasImg').click();"
                                       href="#"><i class="bi bi-camera-fill"></i>
                                        Photo
                                    </a>
                                    <a id="canvasImg" download="filename"></a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5">
<button type="button" class="btn btn-danger" onclick="network.deleteSelected()">
<i class="bi bi-dash-circle"></i>&nbsp;{{ trans("cruds.report.explorer.delete") }}
</button>
&nbsp;
<button type="button" class="btn btn-warning" onclick="network.unselectAll(); nodes.clear(); edges.clear(); network.redraw();">
<i class="bi bi-arrow-repeat"></i>&nbsp;{{ trans("cruds.report.explorer.reload") }}
</button>
&nbsp;&nbsp;
{{ trans("cruds.report.explorer.physical") }} :
<button id="physicsToggle" class="btn btn-success physics-active" type="button">
    <i class="bi bi-play-fill"></i>
</button>
                                    &nbsp;
                                    &nbsp;
                                    <div style="display: inline-block;">
                                    <select class="form-control select2" id="depth" data-allow-clear="false">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3" selected>3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="5">6</option>
                                        <option value="5">7</option>
                                        <option value="5">8</option>
                                        <option value="5">9</option>
                                    </select>
                                    </div>
<button type="button" class="btn btn-info" onclick="deployAll()">
<i class="fas fa-star"></i>&nbsp;{{ trans("cruds.report.explorer.deploy") }}
</button>
                                    &nbsp;
                                    &nbsp;

<div class="btn-group" role="group" aria-label="Direction">
    <input type="radio" class="btn-check" name="direction" id="direction-up" value="up" autocomplete="off">
    <label class="btn btn-outline-primary" for="direction-up">
        ↑ {{ trans("cruds.report.explorer.up") }}
    </label>

    <input type="radio" class="btn-check" name="direction" id="direction-down" value="down" autocomplete="off">
    <label class="btn btn-outline-primary" for="direction-down">
        ↓ {{ trans("cruds.report.explorer.down") }}
    </label>

    <input type="radio" class="btn-check" name="direction" id="direction-both" value="both" checked autocomplete="off">
    <label class="btn btn-outline-primary" for="direction-both">
        ↕ {{ trans("cruds.report.explorer.both") }}
    </label>
    </div>
    &nbsp;
    <button id="toggleIP" class="btn btn-outline-secondary" type="button"
            data-bs-toggle="button">Show IP</button>
    &nbsp;
    <button id="toggleAttr" class="btn btn-outline-secondary" type="button"
            data-bs-toggle="button">Show Attr</button>

                                </td>
                            </tr>
                        </table>
                    </div>
                </div>{{-- /.card-body contrôles --}}

            </div>{{-- /.card contrôles --}}

            {{-- ─── Card graphe (séparée par 10px de fond gris) ────────────────── --}}
            <div class="card mt-2">

                {{-- ─── Zone réseau ─────────────────────────────────────────────── --}}
                <div class="card-body p-0">
                    <div id="mynetwork" style="width: 100%; height: 650px; flex: none; background-color: #ffffff;"></div>
                    {{-- ─── Resize handle ─────────────────────────────────────── --}}
                    <div id="network-resize-handle"
                         style="height: 8px; cursor: ns-resize;
                                background: linear-gradient(to bottom, #dee2e6, #adb5bd);
                                border-top: 1px solid #ced4da;"
                         title="Glisser pour redimensionner"></div>
                </div>

                    {{-- ─── Footer ──────────────────────────────────────────────────── --}}
                    <div class="card-footer">
                        <div class="d-flex align-items-center gap-3">
                            <label class="d-inline-flex align-items-center mb-0">
                                {{ trans("cruds.report.explorer.engine") }} :
                            </label>
                            @foreach(['barnesHut' => 'Barnes-Hut', 'forceAtlas2Based' => 'Force Atlas 2', 'repulsion' => 'Repulsion', 'hierarchicalRepulsion' => 'Hiérarchique'] as $solver => $label)
                                <label class="d-inline-flex align-items-center mb-0">
                                    <input
                                            type="radio"
                                            name="physics-solver"
                                            value="{{ $solver }}"
                                            @checked($solver === 'barnesHut')
                                    >
                                    &nbsp;
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

            </div>{{-- /.card graphe --}}

        </div>{{-- /.col-lg-12 --}}
    </div>{{-- /.row --}}
    <ul id="explore_context"></ul>
@endsection


@section('scripts')

    @vite(['resources/js/vis-network.js'])
    <script @nonce>

        let nodes       = null;
        let edges       = null;
        let network     = null;
        let needSavePNG = false;
        let _nodes      = new Map();

        // ─────────────────────────────────────────────
        // Chargement des données
        // ─────────────────────────────────────────────

        async function loadGraphData() {
            const loadingIndicator = document.getElementById('loading-indicator');
            const loadingText      = document.getElementById('loading-text');
            const controls         = document.getElementById('explorer-controls');

            try {
                loadingText.textContent = 'Chargement des données du graphe...';

                const response = await fetch('{{ route("admin.reports.explore.data") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                loadingText.textContent = 'Construction du graphe...';
                buildNodesMap(data.nodes, data.edges);

                loadingText.textContent = 'Initialisation de la visualisation...';
                populateNodeSelector(data.nodes);
                initializeNetwork();

                loadingIndicator.style.display = 'none';
                controls.style.display         = 'block';

                // La carte de contrôles vient de s'afficher : la position du graphe a
                // changé, on ajuste la hauteur pour remplir l'espace disponible.
                requestAnimationFrame(() => adjustNetworkHeight(true));

                // Populate attr-filter from the attributes already embedded in the response
                (data.attributes ?? []).forEach(attr => {
                    $('#attr-filter').append('<option value="' + attr + '">' + attr + '</option>');
                });
                $('#attr-filter').trigger('change');

                console.log(`✓ Graphe chargé: ${data.nodes.length} nœuds, ${data.edges.length} liens`);

                // Ajouter le/les nœuds passés en paramètre URL
                const urlParams = new URLSearchParams(window.location.search);
                const nodeParam = urlParams.get('node');
                if (nodeParam) {
                    nodeParam.split(',').forEach(nodeId => {
                        const trimmedId = nodeId.trim();
                        if (trimmedId && _nodes.has(trimmedId)) {
                            addNode(trimmedId);
                        }
                    });
                }

            } catch (error) {
                console.error('Erreur lors du chargement:', error);
                loadingIndicator.className  = 'alert alert-danger';
                loadingText.textContent     = `Erreur: ${error.message}`;
            }
        }

        // ─────────────────────────────────────────────
        // Construction de la Map des nœuds
        // ─────────────────────────────────────────────

        function buildNodesMap(nodesData, edgesData) {
            const edgesByNode = new Map();

            edgesData.forEach(edge => {
                // Edge sortant
                if (!edgesByNode.has(edge.from)) edgesByNode.set(edge.from, []);
                edgesByNode.get(edge.from).push({
                    attachedNodeId: edge.to,
                    name:           edge.name,
                    edgeType:       edge.type,
                    edgeDirection:  'TO',
                    bidirectional:  edge.bidirectional,
                    color:          edge.color
                });
                // Edge entrant
                if (!edgesByNode.has(edge.to)) edgesByNode.set(edge.to, []);
                edgesByNode.get(edge.to).push({
                    attachedNodeId: edge.from,
                    name:           edge.name,
                    edgeType:       edge.type,
                    edgeDirection:  'FROM',
                    bidirectional:  edge.bidirectional,
                    color:          edge.color
                });
            });

            nodesData.forEach(node => {
                _nodes.set(node.id, {
                    id:         node.id,
                    vue:        String(node.vue),
                    label:      node.label,
                    title:      node.title || undefined,
                    image:      node.image,
                    type:       node.type,
                    order:      node.order,
                    attributes: node.attributes || null,
                    edges:      edgesByNode.get(node.id) || []
                });
            });
        }

        // ─────────────────────────────────────────────
        // Sélecteur de nœuds
        // ─────────────────────────────────────────────

        function populateNodeSelector(nodesData) {
            const selector = document.getElementById('node');
            selector.innerHTML = '<option></option>';
            nodesData.forEach(node => {
                const option       = document.createElement('option');
                option.value       = node.id;
                option.textContent = node.label;
                selector.appendChild(option);
            });
            // FIX #5 : rafraîchir Select2 après injection des options
            $('#node').trigger('change');
        }

        // ─────────────────────────────────────────────
        // Initialisation vis.js
        // ─────────────────────────────────────────────

        function initializeNetwork() {
            nodes = new vis.DataSet([]);
            edges = new vis.DataSet([]);

            const container = document.getElementById('mynetwork');
            const options = {
                nodes: {
                    shape: 'image',
                    size:  25
                },
                edges: {
                    smooth: { type: 'continuous' }
                },
                interaction: {
                    navigationButtons: true,
                    keyboard:          true
                }
            };

            network = new vis.Network(container, { nodes, edges }, options);
            setupEventHandlers();
        }

        // ─────────────────────────────────────────────
        // Ajout d'un nœud
        // ─────────────────────────────────────────────

        function addNode(id) {
            const newNode = _nodes.get(id);
            if (newNode == null) return;

            if (!nodes.get(newNode.id)) {
                const nodeToAdd = buildVisNode(newNode);
                network.body.data.nodes.add(nodeToAdd);
            }

            const filter = getFilter();
            console.log("filter=", filter);

            for (const edge of newNode.edges) {
                const targetNode = _nodes.get(edge.attachedNodeId);
                if (targetNode === undefined) continue;

                const passesFilter =
                    filter.length === 0
                    || (filter.includes(targetNode.vue) && edge.edgeType !== 'CABLE' && edge.edgeType !== 'FLUX' && edge.edgeType !== 'LFLUX')
                    || (filter.includes("8") && edge.edgeType === 'CABLE')
                    || (filter.includes("9") && edge.edgeType === 'FLUX')
                    || (filter.includes("10") && edge.edgeType === 'LFLUX')
                    || (filter.includes("1") && edge.edgeType === 'FLUX')
                    || (filter.includes("2") && edge.edgeType === 'FLUX');

                if (passesFilter && nodes.get(targetNode.id) != null) {
                    addEdge(newNode.id, targetNode.id);
                }
            }
        }

        // ─────────────────────────────────────────────
        // Déploiement
        // ─────────────────────────────────────────────

        function deployAll() {
            const activeNode = network.getSelectedNodes()[0];
            if (!activeNode) {
                alert("{{ trans("cruds.report.explorer.please_select") }}");
                return;
            }
            const depth        = parseInt(document.getElementById('depth').value);
            const visitedNodes = new Set();
            deployFromNode(activeNode, depth, visitedNodes, getFilter(), getDirection());
        }

        function matchesAttrFilter(node, attrFilter) {
            if (attrFilter.length === 0) return true;
            if (!node.attributes) return false;
            const nodeAttrs = node.attributes.split(' ').map(s => s.trim()).filter(Boolean);
            return attrFilter.some(a => nodeAttrs.includes(a));
        }

        function deployFromNode(nodeId, depth, visitedNodes, filter, direction = 3) {
            if (depth <= 0 || visitedNodes.has(nodeId)) return;
            visitedNodes.add(nodeId);

            const node = _nodes.get(nodeId);
            if (!node) return;

            console.log("filter=", filter);

            const attrFilter = getAttrFilter();

            for (const edge of node.edges) {
                const targetNodeId = edge.attachedNodeId;

                const targetNode = _nodes.get(targetNodeId);
                if (targetNode == null) continue;

                const matchAttr = matchesAttrFilter(targetNode, attrFilter);

                const passesFilter =
                    (
                        filter.length === 0
                        || (filter.includes(targetNode.vue) && edge.edgeType !== 'CABLE' && edge.edgeType !== 'FLUX' && edge.edgeType !== 'LFLUX')
                        || (filter.includes("8") && edge.edgeType === 'CABLE')
                        || (filter.includes("9") && edge.edgeType === 'FLUX')
                        || (filter.includes("10") && edge.edgeType === 'LFLUX')
                        || (filter.includes("1") && edge.edgeType === 'FLUX')
                        || (filter.includes("2") && edge.edgeType === 'FLUX')
                    ) && matchAttr;

                if (!passesFilter) continue;

                // Vérification direction
                if (direction === 1 && node.order <= targetNode.order) continue;
                if (direction === 2 && node.order >= targetNode.order) continue;

                const alreadyDisplayed = nodes.get(targetNodeId) !== null;

                // Ajouter le nœud cible s'il n'est pas déjà affiché
                if (!alreadyDisplayed) {
                    network.body.data.nodes.add(buildVisNode(targetNode));
                }

                // Ajouter l'arête entre le nœud courant et la cible (idempotent, dédupliqué par addEdge/exists)
                addEdge(nodeId, targetNodeId);

                // Ajouter les arêtes vers les voisins déjà présents
                for (const neighborEdge of targetNode.edges) {
                    if (nodes.get(neighborEdge.attachedNodeId) === null) continue;

                    const neighborPassesFilter =
                        filter.length === 0
                        || neighborEdge.edgeType === 'LINK'
                        || (filter.includes("8") && neighborEdge.edgeType === 'CABLE')
                        || (filter.includes("9") && neighborEdge.edgeType === 'FLUX')
                        || (filter.includes("10") && neighborEdge.edgeType === 'LFLUX')
                        || (filter.includes("1") && neighborEdge.edgeType === 'FLUX')
                        || (filter.includes("2") && neighborEdge.edgeType === 'FLUX');

                    if (neighborPassesFilter) {
                        addEdge(targetNodeId, neighborEdge.attachedNodeId);
                    }
                }

                // Continuer la récursion même si le nœud était déjà affiché : il peut
                // avoir des voisins non encore visités dans cette passe de déploiement.
                // `visitedNodes` empêche toute boucle infinie.
                setTimeout(function () {
                    deployFromNode(targetNodeId, depth - 1, visitedNodes, filter, direction);
                }, 500);
            }
        }

        // ─────────────────────────────────────────────
        // Gestion des arêtes
        // ─────────────────────────────────────────────

        const FONT_OPTIONS = {
            align:       'middle',
            background:  'white',
            strokeWidth: 2,
            strokeColor: 'white'
        };

        function getSmooth(sourceNodeId, targetNodeId) {
            const count = edges.get().filter(e =>
                (e.from === sourceNodeId && e.to === targetNodeId) ||
                (e.from === targetNodeId && e.to === sourceNodeId)
            ).length;

            if (count === 0) return { enabled: false };

            return {
                enabled:    true,
                type:       count % 2 === 1 ? 'curvedCW' : 'curvedCCW',
                roundness:  Math.min(Math.ceil(count / 2) * 0.3, 1.0)
            };
        }

        function exists(from, to, label, symmetric = true) {
            return edges.get({
                filter: function (item) {
                    const direct  = (item.from === from) && (item.to === to)   && (label === null || item.label === label);
                    const reverse = (item.from === to)   && (item.to === from) && (label === null || item.label === label);
                    return symmetric ? (direct || reverse) : direct;
                }
            });
        }

        function addEdge(sourceNodeId, targetNodeId) {
            const edgeList = _nodes.get(sourceNodeId).edges;

            for (const edge of edgeList) {
                if (edge.attachedNodeId !== targetNodeId) continue;

                if (edge.edgeType === 'FLUX' || edge.edgeType === 'LFLUX') {
                    const isFrom    = edge.edgeDirection === 'FROM';
                    const [from, to] = isFrom
                        ? [targetNodeId, sourceNodeId]
                        : [sourceNodeId, targetNodeId];

                    if (exists(from, to, edge.name, false).length > 0) continue;

                    edges.add({
                        label:  edge.name,
                        from,
                        to,
                        smooth: getSmooth(from, to),
                        font:   FONT_OPTIONS,
                        color:  { color: '#1a6496', highlight: '#2196F3' },
                        arrows: edge.bidirectional
                            ? { to: { enabled: true, type: 'arrow' }, from: { enabled: true, type: 'arrow' } }
                            : { to: { enabled: true, type: 'arrow' } }
                    });

                } else if (edge.edgeType === 'CABLE') {
                    if (exists(sourceNodeId, targetNodeId, null).length > 0) continue;

                    edges.add({
                        from:   sourceNodeId,
                        to:     targetNodeId,
                        color:  edge.color ?? 'blue',
                        width:  3,
                        smooth: getSmooth(sourceNodeId, targetNodeId)
                    });

                } else if (edge.edgeType === 'LINK') {
                    if (exists(sourceNodeId, targetNodeId, null).length > 0) continue;

                    edges.add({
                        from:   sourceNodeId,
                        to:     targetNodeId,
                        smooth: getSmooth(sourceNodeId, targetNodeId),
                        color:  { color: '#000000', highlight: '#333333' },
                        dashes: [3, 3],
                        width:  2,
                        });

                } else {
                    console.error("Unknown edge type:", edge.edgeType);
                }
            }
        }

        // ─────────────────────────────────────────────
        // Événements réseau
        // ─────────────────────────────────────────────

        function setupEventHandlers() {
            network.on("afterDrawing", function (ctx) {
                if (needSavePNG) {
                    document.getElementById('canvasImg').href = ctx.canvas.toDataURL();
                    needSavePNG = false;
                }
            });

            network.on("oncontext", function (params) {
                console.log('Context menu on node:', params);
                params.event.preventDefault();
                const activeNode = this.getNodeAt(params.pointer.DOM);
                if (activeNode !== undefined) {
                    showContextMenu(params.event.pageX, params.event.pageY, activeNode);
                }
            });

            network.on('doubleClick', function (params) {
                if (params.nodes.length > 0) {
                    deployFromNode(params.nodes[0], 1, new Set(), getFilter(), getDirection());
                }
            });

            document.addEventListener('click', function () {
                const ctx = document.getElementById('explore_context');
                if (ctx) {
                    ctx.style.display = 'none';
                    ctx.innerHTML     = '';
                }
            });
        }

        // ─────────────────────────────────────────────
        // Menu contextuel
        // ─────────────────────────────────────────────

        const contextMenu = document.getElementById("explore_context");

        function displayContextMenu(left, top) {
            contextMenu.style.display = "block";
            contextMenu.style.opacity = "1";
            contextMenu.style.top     = top  + "px";
            contextMenu.style.left    = left + "px";
        }

        function hideContextMenu() {
            contextMenu.style.opacity = "0";
            contextMenu.style.display = "none";
        }

        function showContextMenu(left, top, nodeId) {
            const node = _nodes.get(nodeId);
            const type = node.type;
            const id   = nodeId.split("_").pop();
            contextMenu.innerHTML =
                "<li><a href='/admin/" + type + "/" + id + "'>{{ trans("global.view") }}</a></li>" +
                "<li><a href='/admin/" + type + "/" + id + "/edit'>{{ trans("global.edit") }}</a></li>" +
                "<li id='hideNode' style='color: #167495; cursor: pointer;'><span>{{ trans("global.hide") }}</span></li>";

            displayContextMenu(left, top);

            document.getElementById("hideNode").addEventListener("click", function () {
                network.body.data.nodes.remove(node);
                hideContextMenu();
            });
        }

        // ─────────────────────────────────────────────
        // Filtres
        // ─────────────────────────────────────────────

        function getFilter() {
            return Array.from(document.getElementById('filters').options)
                .filter(o => o.selected)
                .map(o => o.value);
        }

        // 1 up, 2 down, 3 both
        function getDirection() {
            if (document.getElementById('direction-up').checked)   return 1;
            if (document.getElementById('direction-down').checked) return 2;
            return 3;
        }

        function getAttrFilter() {
            return $('#attr-filter').val() || [];
        }

        function apply_filter() {
            const curFilter  = $('#filters').val() || [];
            const attrFilter = getAttrFilter();

            $("#node").empty();
            for (const [, value] of _nodes) {
                const matchVue  = curFilter.length === 0 || curFilter.includes(value.vue);
                const matchAttr = matchesAttrFilter(value, attrFilter);
                if (matchVue && matchAttr) {
                    $("#node").append('<option value="' + value.id + '">' + value.label + '</option>');
                }
            }
            $('#node').val(null).trigger("change");
        }

        // ─────────────────────────────────────────────
        // Helpers
        // ─────────────────────────────────────────────

        function buildVisNode(node) {
            if (getShowIP() && node.title != null) {
                return { ...node, label: node.label + "\n" + node.title };
            }
            if (getShowAttr() && node.attributes != null) {
                return { ...node, label: node.label + "\n" + node.attributes };
            }
            return node;
        }

        function getShowIP() {
            return document.getElementById('toggleIP').classList.contains('active');
        }

        function getShowAttr() {
            return document.getElementById('toggleAttr').classList.contains('active');
        }

        function refreshNodeLabels() {
            if (!nodes) return;
            const showIP   = getShowIP();
            const showAttr = getShowAttr();
            nodes.forEach(function (visNode) {
                const srcNode = _nodes.get(visNode.id);
                if (!srcNode) return;
                let newLabel = srcNode.label;
                if (showIP && srcNode.title) {
                    newLabel += "\n" + srcNode.title;
                } else if (showAttr && srcNode.attributes) {
                    newLabel += "\n" + srcNode.attributes;
                }
                if (visNode.label !== newLabel) {
                    nodes.update({ id: visNode.id, label: newLabel });
                }
            });
        }

        // ─────────────────────────────────────────────
        // Hauteur dynamique de la zone réseau
        // ─────────────────────────────────────────────

        // Passe à true dès que l'utilisateur redimensionne manuellement la zone :
        // on cesse alors tout ajustement automatique pour respecter son choix.
        let userResizedNetwork = false;

        /**
         * Ajuste #mynetwork pour que son bas s'arrête aux 3/4 de la hauteur d'écran
         * (en tenant compte de l'espace déjà pris au-dessus), afin de laisser de la
         * place visible sous la poignée/footer. Ne fait rien si l'utilisateur a déjà
         * redimensionné manuellement.
         *
         * @param {boolean} refit  true pour recadrer le graphe (network.fit) après ajustement.
         */
        function adjustNetworkHeight(refit = false) {
            if (userResizedNetwork) return;

            const networkEl = document.getElementById('mynetwork');
            if (!networkEl) return;

            const top       = networkEl.getBoundingClientRect().top; // position haute stable
            const maxBottom = window.innerHeight - 50;

            networkEl.style.height = Math.max(300, Math.floor(maxBottom - top)) + 'px';

            if (network) {
                network.redraw();
                if (refit) network.fit();
            }
        }

        // ─────────────────────────────────────────────
        // DOMContentLoaded
        // ─────────────────────────────────────────────

        document.addEventListener('DOMContentLoaded', () => {
            loadGraphData();

            $('body').keydown(function (event) {
                if (event.keyCode === 8 || event.keyCode === 46) {
                    network.deleteSelected();
                }
            });

            $('#filters').val(null).trigger('change');
            $('#attr-filter')
                .on('select2:select',   () => apply_filter())
                .on('select2:unselect', () => apply_filter());
            $('#node').val(null);
            $('#filters')
                .on('select2:select',   () => apply_filter())
                .on('select2:unselect', () => apply_filter());

            // ── Toggle physique ───────────────────────────────────────────────
            let physicsEnabled = true;
            const toggleBtn    = document.getElementById('physicsToggle');
            const icon         = toggleBtn.querySelector('i');

            toggleBtn.addEventListener('click', function () {
                physicsEnabled = !physicsEnabled;
                if (physicsEnabled) {
                    toggleBtn.classList.replace('btn-danger',      'btn-success');
                    toggleBtn.classList.replace('physics-inactive','physics-active');
                    icon.className = 'bi bi-play-fill';
                    network.setOptions({ physics: true });
                } else {
                    toggleBtn.classList.replace('btn-success',    'btn-danger');
                    toggleBtn.classList.replace('physics-active', 'physics-inactive');
                    icon.className = 'bi bi-pause-fill';
                    network.setOptions({ physics: false });
                }
            });

            // ── Toggle IP ─────────────────────────────────────────────────────
            const toggleIP   = document.getElementById('toggleIP');
            const toggleAttr = document.getElementById('toggleAttr');

            if (localStorage.getItem('showIP') === 'true') {
                toggleIP.classList.add('active');
            } else if (localStorage.getItem('showAttr') === 'true') {
                toggleAttr.classList.add('active');
            }

            toggleIP.addEventListener('click', function () {
                const isActive = this.classList.contains('active');
                localStorage.setItem('showIP', String(isActive));
                if (isActive) {
                    toggleAttr.classList.remove('active');
                    localStorage.setItem('showAttr', 'false');
                }
                refreshNodeLabels();
            });

            toggleAttr.addEventListener('click', function () {
                const isActive = this.classList.contains('active');
                localStorage.setItem('showAttr', String(isActive));
                if (isActive) {
                    toggleIP.classList.remove('active');
                    localStorage.setItem('showIP', 'false');
                }
                refreshNodeLabels();
            });

            // ── Sélection du moteur physique ─────────────────────────────────
            document.querySelectorAll('input[name="physics-solver"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    if (!network) return;
                    network.setOptions({
                        physics: { solver: this.value }
                    });
                });
            });

            // ── Resize handle ─────────────────────────────────────────────────
            const resizeHandle = document.getElementById('network-resize-handle');
            const networkEl    = document.getElementById('mynetwork');
            let dragging = false, startY = 0, startH = 0;

            resizeHandle.addEventListener('mousedown', e => {
                dragging = true;
                startY   = e.clientY;
                startH   = networkEl.offsetHeight;
                document.body.style.cssText += 'cursor:ns-resize;user-select:none;';
                e.preventDefault();
            });

            document.addEventListener('mousemove', e => {
                if (!dragging) return;
                userResizedNetwork = true;   // l'utilisateur reprend la main : plus d'auto-ajustement
                networkEl.style.height = Math.max(200, startH + (e.clientY - startY)) + 'px';
                if (network) network.redraw();
            });

            // ── Ajustement automatique au redimensionnement de la fenêtre ──────
            let resizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => adjustNetworkHeight(false), 100);
            });

            document.addEventListener('mouseup', () => {
                if (!dragging) return;
                dragging = false;
                document.body.style.cursor     = '';
                document.body.style.userSelect = '';
                if (network) network.fit();
            });
        });

    </script>

    @parent

@endsection