@extends('layouts.admin')

@section('title')
    {{ trans('cruds.graph.title_singular') }} {{ old('name', $name) }}
@endsection

@section('content')
    <form method="POST" action='{{ route("admin.graphs.update", [$id]) }}' enctype="multipart/form-data" id="grahForm">
        @method('PUT')
        @csrf
        <input name='id' type='hidden' value='{{$id}}' id="id"/>
        <input name='content' type='hidden' value='' id="content"/>
        <div class="card">
            <div class="card-header">
                Cartographier
            </div>
            <div class="card-body" style="padding-top: 0;">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.graph.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $name) }}" required
                                   maxlength="64"/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.graph.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row resizable-div" id="editor">
                    <div class="col-lg-12">
                        <table width="100%">
                            <tr>
                                <td width="320">
                                    <div class="form-group">
                                        <label for="title">Filtre</label>
                                        <select class="form-control select2" id="filters" multiple>
                                            <option value="1">{{ trans("cruds.report.cartography.ecosystem") }}</option>
                                            <option value="2">{{ trans("cruds.report.cartography.information_system") }}</option>
                                            <option value="3">{{ trans("cruds.report.cartography.applications") }}</option>
                                            <option value="9">{{ trans("cruds.applicationFlow.title") }}</option>
                                            <option value="4">{{ trans("cruds.report.cartography.administration") }}</option>
                                            <option value="5">{{ trans("cruds.report.cartography.logical_infrastructure") }}</option>
                                            <option value="10">{{ trans("cruds.logicalFlow.title") }}</option>
                                            <option value="6">{{ trans("cruds.report.cartography.physical_infrastructure") }}</option>
                                            <option value="8">{{ trans("cruds.physicalLink.title") }}</option>
                                        </select>
                                        <span class="help-block">{{ trans("cruds.report.explorer.filter_helper") }}</span>
                                    </div>
                                </td>
                                <td width=10>
                                </td>
                                <td width="280">
                                    <div class="form-group">
                                        <label for="attr-filter">{{ trans("cruds.report.explorer.attributes") }}</label>
                                        <select class="form-control select2" id="attr-filter" multiple>
                                        </select>
                                        <span class="help-block">{{ trans('cruds.report.explorer.attributes_helper') }}</span>
                                    </div>
                                </td>
                                <td width=10>
                                </td>
                                <td width="320">
                                    <div class="form-group">
                                        <label for="title">Objets</label>
                                        <select class="form-control select2" id="node">
                                            <option></option>
                                            @foreach($nodes as $node)
                                                <option value="{{ $node['id'] }}">{{ $node["label"] }}</option>
                                            @endforeach
                                        </select>
                                        <span class="help-block">{{ trans("cruds.report.explorer.object_helper") }}</span>
                                    </div>
                                </td>
                                <td style="text-align: left; vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div
                                                style="
                                          display: flex;
                                          justify-content: center;
                                          align-items: center;
                                          width: 50px;
                                          height: 40px;
                                          border: 0 solid #007bff;
                                          border-radius: 8px;">
                                            <img id="nodeImage" src=""
                                                 style="width: 32px; cursor: grab;"/>
                                        </div>
                                        <button type="button" id="add-node-btn" class="btn btn-success">
                                            <i class="bi bi-plus-square-fill"></i>&nbsp;{{ trans('global.add') }}
                                        </button>
                                    </div>
                                </td>
                                <td style="vertical-align: top; ">
                                    <div id="maximizeBtn"
                                         style="
                                      text-align: right;
                                      display: flex;
                                      justify-content: right;
                                      font-size: 20px;
                                      align-items: center;
                                      width: 100%;
                                      height: 40px;
                                      border-radius: 8px;
                                      cursor: pointer;
                                      color: #7C123E">&#8613;
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7">
                                    <button type="button" id="delete-btn" class="btn btn-danger">
                                        <i class="bi bi-dash-circle"></i>&nbsp;{{ trans("cruds.report.explorer.delete") }}
                                    </button>
                                    &nbsp;
                                    <button type="button" id="reload-btn" class="btn btn-warning">
                                        <i class="bi bi-arrow-repeat"></i>&nbsp;{{ trans("cruds.report.explorer.reload") }}
                                    </button>
                                    &nbsp;&nbsp;
                                    <div style="display: inline-block;">
                                        <select class="form-control select2" id="depth" data-allow-clear="false">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3" selected>3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                    <button type="button" id="deploy-btn" class="btn btn-info"
                                            data-please-select="{{ trans('cruds.report.explorer.please_select') }}">
                                        <i class="fas fa-star"></i>&nbsp;{{ trans("cruds.report.explorer.deploy") }}
                                    </button>
                                    &nbsp;&nbsp;
                                    <div class="btn-group" role="group" aria-label="Direction">
                                        <input type="radio" class="btn-check" name="direction" id="direction-up" value="up" autocomplete="off">
                                        <label class="btn btn-outline-primary" for="direction-up">
                                            &uarr; {{ trans("cruds.report.explorer.up") }}
                                        </label>

                                        <input type="radio" class="btn-check" name="direction" id="direction-down" value="down" autocomplete="off">
                                        <label class="btn btn-outline-primary" for="direction-down">
                                            &darr; {{ trans("cruds.report.explorer.down") }}
                                        </label>

                                        <input type="radio" class="btn-check" name="direction" id="direction-both" value="both" checked autocomplete="off">
                                        <label class="btn btn-outline-primary" for="direction-both">
                                            &updownarrow; {{ trans("cruds.report.explorer.both") }}
                                        </label>
                                    </div>
                                    &nbsp;
                                    <button id="toggleIP" class="btn btn-outline-secondary" type="button" aria-pressed="false">Show IP</button>
                                    &nbsp;
                                    <button id="toggleAttr" class="btn btn-outline-secondary" type="button" aria-pressed="false">Show Attr</button>
                                </td>
                            </tr>
                        </table>
                        <br>

                        <div id="app-container" style="display: flex;">
                            <div id="sidebar" style="
    width: 60px; /* élargi un peu pour les icônes plus grandes */
    background: #ffffff;
    border-right: 1px solid #ddd;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center; /* centre les icônes */
    gap: 12px; /* espace entre les icônes */
                                ">
                                <i id="saveButton" title="{{ trans('cruds.graph.toolbar.save') }}"
                                   class="mapping-icon bi bi-floppy-fill {{ $id === '-1' ? 'disabled' : ''}}"></i>
                                <i id="undoButton" title="{{ trans('cruds.graph.toolbar.undo') }}" class="mapping-icon bi bi-arrow-counterclockwise"></i>
                                <i id="redoButton" title="{{ trans('cruds.graph.toolbar.redo') }}" class="mapping-icon bi bi-arrow-clockwise"></i>
                                <i id="font-btn" title="{{ trans('cruds.graph.toolbar.text') }}" class="mapping-icon bi bi-fonts" draggable="true"></i>
                                <i id="square-btn" title="{{ trans('cruds.graph.toolbar.rectangle') }}" class="mapping-icon bi bi-square" draggable="true"></i>
                                {{--
                                <i id="group-btn" title="{{ trans('cruds.graph.toolbar.group') }}" class="mapping-icon bi bi-plus-square-dotted"></i>
                                <i id="ungroup-btn" title="{{ trans('cruds.graph.toolbar.ungroup') }}" class="mapping-icon bi bi-dash-square-dotted"></i>
                                --}}
                                <i id="zoom-in-btn" title="{{ trans('cruds.graph.toolbar.zoom_in') }}" class="mapping-icon bi bi-zoom-in"></i>
                                <i id="fit-btn" title="{{ trans('cruds.graph.toolbar.fit') }}" class="mapping-icon bi bi-crosshair"></i>
                                <i id="zoom-out-btn" title="{{ trans('cruds.graph.toolbar.zoom_out') }}" class="mapping-icon bi bi-zoom-out"></i>
                                <i id="physics-btn" title="{{ trans('cruds.graph.toolbar.physics') }}" class="mapping-icon bi bi-magnet" aria-pressed="false"></i>
                                <i id="grid-btn" title="{{ trans('cruds.graph.toolbar.grid') }}" class="mapping-icon bi bi-grid-3x3-gap" aria-pressed="false"></i>
                                <i id="background-btn" title="{{ trans('cruds.graph.toolbar.background') }}" class="mapping-icon bi bi-image"></i>
                                <i id="update-btn" title="{{ trans('cruds.graph.toolbar.update') }}" class="mapping-icon bi bi-lightning-fill"></i>
                                <i id="download-btn" title="{{ trans('cruds.graph.toolbar.export') }}" class="mapping-icon bi bi-download"></i>
                            </div>

                            <!-- Contextual menu for background -->
                            <div id="background-menu"
                                 style="display: none; position: absolute; background: #fff; border: 1px solid #ccc; z-index: 1000; padding: 10px;">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; max-width: 260px;">
                                    @foreach($backgrounds ?? [] as $background)
                                        <img class="background-thumb" data-url="{{ $background }}" src="{{ $background }}"
                                             style="width: 48px; height: 48px; object-fit: cover; cursor: pointer; border: 1px solid #ccc;"/>
                                    @endforeach
                                </div>
                                <hr>
                                <label for="background-input">{{ trans('cruds.graph.menu.select_background') }}</label>
                                <input type="file" id="background-input" accept="image/*" class="d-none"/>
                                <button type="button" id="background-input-btn">{{ trans('global.import') }}</button>
                                <br>
                                <button type="button" id="background-remove-btn">{{ trans('global.delete') }}</button>
                            </div>

                            <!-- Contextual menu for edges -->
                            <div id="edge-context-menu"
                                 style="display: none; position: absolute; background: #fff; border: 1px solid #ccc; z-index: 1000; padding: 10px; flex-direction: column; gap: 6px;">
                                <div style="display: flex; gap: 12px;">
                                    <div id="edge-fill-group" style="display: none; flex-direction: column; align-items: center;">
                                        <label for="edge-fill-select">{{ trans('cruds.graph.menu.fill') }}</label>
                                        <input type="color" id="edge-fill-select" value="#ffffff">
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <label for="edge-color-select">{{ trans('cruds.graph.menu.border') }}</label>
                                        <input type="color" id="edge-color-select" name="favorite-color" value="#ff0000">
                                    </div>
                                    <div id="edge-text-group" style="display: none; flex-direction: column; align-items: center;">
                                        <label for="edge-text-color-select">{{ trans('cruds.graph.menu.text') }}</label>
                                        <input type="color" id="edge-text-color-select" value="#000000">
                                    </div>
                                </div>
                                <div>
                                    <label for="edge-dash-select" style="margin-right: 5px;">{{ trans('cruds.graph.menu.line') }}</label>
                                    <select id="edge-dash-select">
                                        <option value="solid">&#x2014;&#x2014;&#x2014; {{ trans('cruds.graph.menu.line_solid') }}</option>
                                        <option value="dashed">- - - {{ trans('cruds.graph.menu.line_dashed') }}</option>
                                        <option value="dotted">&#xB7;&#xB7;&#xB7; {{ trans('cruds.graph.menu.line_dotted') }}</option>
                                        <option value="dash-dot">-&#xB7;-&#xB7; {{ trans('cruds.graph.menu.line_dash_dot') }}</option>
                                    </select>
                                    <select id="edge-thickness-select">
                                        <option value="1">1 px</option>
                                        <option value="2">2 px</option>
                                        <option value="3">3 px</option>
                                        <option value="4">4 px</option>
                                        <option value="5">5 px</option>
                                    </select>
                                </div>
                                <div>
                                    <button id="apply-edge-style">{{ trans('global.apply') }}</button>
                                </div>
                            </div>

                            <!-- Contextual menu for text -->
                            <div id="text-context-menu"
                                 style="display: none; position: absolute; background: #fff; border: 1px solid #ccc; z-index: 1000; padding: 10px; flex-direction: column; gap: 6px;">
                                <div>
                                    <label for="text-font-select" style="margin-right: 5px;">{{ trans('cruds.graph.menu.font') }}</label>
                                    <select id="text-font-select">
                                        <option>Arial</option>
                                        <option>Helvetica</option>
                                        <option>Times New Roman</option>
                                        <option>Courier New</option>
                                        <option>Verdana</option>
                                        <option>Georgia</option>
                                    </select>
                                    <label for="text-size-select" style="margin-right: 5px;">{{ trans('cruds.graph.menu.size') }}</label>
                                    <select id="text-size-select">
                                        <option value="8">8 px</option>
                                        <option value="10">10 px</option>
                                        <option value="12">12 px</option>
                                        <option value="14">14 px</option>
                                        <option value="16">16 px</option>
                                        <option value="18">18 px</option>
                                        <option value="20">20 px</option>
                                        <option value="22">22 px</option>
                                        <option value="24">24 px</option>
                                        <option value="26">26 px</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="text-color-select" style="margin-right: 5px;">{{ trans('global.color') }}</label>
                                    <input type="color" id="text-color-select" name="favorite-color" value="#ff0000">
                                    <button type="button" id="text-bold-select" class="button" style="font-weight: bold;">B</button>
                                    <button type="button" id="text-italic-select" class="button" style="font-style: italic;">I</button>
                                    <button type="button" id="text-underline-select" class="button" style="text-decoration: underline;">U</button>
                                </div>
                                <div>
                                    <button id="apply-text-style">{{ trans('global.apply') }}</button>
                                </div>
                            </div>

                            <div id="graph-container" tabindex="-1"
                                 style="position: relative; overflow: hidden; width: 100%; cursor: default; touch-action: none; outline: none;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="form-group" id="graph-buttons">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.graphs.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection

@section('styles')
    @vite('resources/css/mapping.css')

    <style>
        .button {
            width: 35px;
            cursor: pointer;
            border: 2px solid #ccc;
            background-color: #f0f0f0;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .button.selected {
            background-color: #aaa;
        }

        #physics-btn[aria-pressed="true"] {
            color: #7C123E;
        }
    </style>
@endsection

@section('scripts')
    <script @nonce>
        // TODO : optimize me
        let _nodes = new Map();
        @foreach($nodes as $node)
        _nodes.set("{{ $node["id"] }}", {
            id: "{{ $node["id"]}}",
            vue: "{{ $node["vue"]}}",
            label: {!! json_encode($node["label"]) !!},
            {!! array_key_exists('title',$node) ? ('title: ' . json_encode($node["title"]) . ',') : "" !!}
            {!! array_key_exists('attributes',$node) ? ('attributes: ' . json_encode($node["attributes"]) . ',') : "" !!}
            order: {{ $node["order"] ?? 0 }},
            image: "{{ $node["image"] }}",
            type: "{{ $node["type"] }}",
            edges: [ <?php
                         foreach ($edges as $edge) {
                             if ($edge["from"] == $node["id"])
                                 echo '{attachedNodeId:"' . $edge["to"] . '"' . ($edge["name"] !== null ? ',name:' . json_encode($edge["name"]) : "") . ',edgeType:"' . $edge["type"] . '", edgeDirection: "TO", bidirectional:' . ($edge["bidirectional"] ? "true" : "false") . ',color:' . json_encode($edge["color"]) . '},';
                             if ($edge["to"] == $node["id"])
                                 echo '{attachedNodeId:"' . $edge["from"] . '"' . ($edge["name"] !== null ? ',name:' . json_encode($edge["name"]) : "") . ',edgeType:"' . $edge["type"] . '", edgeDirection: "FROM", bidirectional:' . ($edge["bidirectional"] ? "true" : "false") . ',color:' . json_encode($edge["color"]) . '},';
                         } ?> ]
        });
        @endforeach

        document.addEventListener("DOMContentLoaded", function () {

            function getAttrFilter() {
                return $('#attr-filter').val() || [];
            }

            function matchesAttrFilter(node, attrFilter) {
                if (attrFilter.length === 0) return true;
                if (!node.attributes) return false;
                const nodeAttrs = node.attributes.split(' ').map(s => s.trim()).filter(Boolean);
                return attrFilter.some(a => nodeAttrs.includes(a));
            }

            // Remplit le filtre de tags à partir des attributs présents dans _nodes
            const tags = new Set();
            for (const [, value] of _nodes) {
                if (!value.attributes) continue;
                value.attributes.split(' ').map(s => s.trim()).filter(Boolean).forEach(t => tags.add(t));
            }
            Array.from(tags).sort().forEach(tag => {
                $('#attr-filter').append('<option value="' + tag + '">' + tag + '</option>');
            });
            $('#attr-filter').trigger('change');

            function apply_filter() {
                // Get current filter
                cur_filter = $('#filters').val();
                const attrFilter = getAttrFilter();

                // filter nodes
                $("#node").empty();
                for (let [node, value] of _nodes) {
                    const matchVue  = cur_filter.length === 0 || cur_filter.includes(value.vue);
                    const matchAttr = matchesAttrFilter(value, attrFilter);
                    if (matchVue && matchAttr) {
                        $("#node").append('<option value="' + value.id + '">' + value.label + '</option>');
                    }
                }
                // clear node
                $('#node').val(null).trigger("change");
                // clear image
                document.getElementById('nodeImage').src = '';
            }

            // clear selections
            $('#filters').val(null).trigger('change');
            $('#node').val(null);

            $('#filters')
                .on('select2:select', function (e) {
                    apply_filter();
                });

            $('#filters')
                .on('select2:unselect', function (e) {
                    apply_filter();
                });

            $('#attr-filter')
                .on('select2:select', function (e) {
                    apply_filter();
                });

            $('#attr-filter')
                .on('select2:unselect', function (e) {
                    apply_filter();
                });

            $('#node')
                .on('select2:select', function (e) {
                    // Get current filter
                    const cur_node = $('#node').val();
                    // console.log(cur_node);
                    document.getElementById('nodeImage').src = _nodes.get(cur_node).image;
                });

            $('#node')
                .on('select2:unselect', function (e) {
                    document.getElementById('nodeImage').src = '';
                });

            //--------------------------------------------------------------
            // Maximisation
            document.getElementById('maximizeBtn').addEventListener('click', function () {
                const div = document.getElementById('editor');
                const sidebar = document.getElementById('sidebar');
                const sidebarFooter = document.querySelector('.sidebar-footer');

                if (div.classList.contains('maximized')) {
                    div.classList.remove('maximized');
                    if (sidebar) sidebar.style.display = 'block';
                    if (sidebarFooter) sidebarFooter.style.display = 'block';
                    document.getElementById('maximizeBtn').innerHTML = "&#8613;";
                } else {
                    div.classList.add('maximized');
                    if (sidebar) sidebar.style.display = 'none';
                    if (sidebarFooter) sidebarFooter.style.display = 'none';
                    document.getElementById('maximizeBtn').innerHTML = "&#8615;";
                }
                fitGraph();
            });
            //--------------------------------------------------------------
            // Chargement du graphe
            loadGraph(@json($content));

            //--------------------------------------------------------------
            // Save graph
            const form = document.getElementById('grahForm');
            form.addEventListener('submit', function (event) {
                console.log("save called !");
                // Prevent the form from submitting immediately
                event.preventDefault();

                document.getElementById('content').value = getXMLGraph();

                // Now submit the form
                form.submit();
            });

            const container = document.getElementById('graph-container');
            const buttons   = document.getElementById('graph-buttons');
            function fitGraph() {
                const top     = container.getBoundingClientRect().top;
                const btnH    = buttons ? buttons.offsetHeight : 0;
                container.style.height = (window.innerHeight - top - btnH - 36) + 'px';
            }
            fitGraph();
            window.addEventListener('load', fitGraph);
            window.addEventListener('resize', fitGraph);

        });
    </script>

    @vite('resources/graphs/map.edit.ts')

@endsection
