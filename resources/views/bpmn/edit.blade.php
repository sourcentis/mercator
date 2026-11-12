@extends('layouts.admin')
@section('content')
    <form method="POST" action='{{ route("admin.bpmn.update", [$id]) }}' enctype="multipart/form-data" id="graph-form">
        @method('PUT')
        @csrf
        <input name='id' type='hidden' value='{{$id}}' id="id""/>
        <input name='class' type='hidden' value='2'/>
        <input name='content' type='hidden' value='' id="content"/>
        <div class="card" >
            <div class="card-header">
                BPMN
            </div>
            <div class="card-body p-0 ps-2" id="editor">
                <div class="row">
                    <div class="col-md-5"  style="border-left:3px solid #ddd; padding-left:68px;">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.bpmn.fields.name') }}</label>
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
                    <div class="col-md-3 ps-0">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.bpmn.fields.type') }}</label>
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
                    <div class="col-md-4">
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
                    </div>
                </div>

                <div id="app-container" class="bpmn-app p-0">
                    <div id="bpmn-sidebar" class="bpmn-sidebar p-1">

                    <i title="{{ trans('cruds.bpmn.buttons.state') }}" id="state-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="state-node">&#xE845;</i>
                    <i title="{{ trans('cruds.bpmn.buttons.task') }}" id="task-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="task-node">&#xE821;</i>
                    <i title="{{ trans('cruds.bpmn.buttons.gateway') }}" id="gateway-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="gateway-node">&#xE834;</i>
                    <i title="{{ trans('cruds.bpmn.buttons.data') }}" id="data-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="data-node">&#xE84B;</i>
                    <i title="{{ trans('cruds.bpmn.buttons.lane') }}" id="lane-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="lane-node">&#xE85C;</i>
                    <i title="{{ trans('cruds.bpmn.buttons.activities') }}" id="activities-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="activities-node">&#xE861</i>
                    <i title="{{ trans('cruds.bpmn.buttons.annotation') }}" id="annotation-btn" class="mapping-icon bpmn-icon" draggable="true" data-node-type="annotation-node">&#xE86B;</i>
                    <i title="{{ trans('cruds.bpmn.buttons.conversation') }}" id="conversation-btn" class="mapping-icon bi bi-hexagon" draggable="true" data-node-type="conversation-node"></i>
                    ---
                    <i id="zoom-in-btn" title="{{ trans('cruds.bpmn.buttons.zoom_in') }}" class="mapping-icon bi bi-zoom-in"></i>
                    <i id="zoom-out-btn" title="{{ trans('cruds.bpmn.buttons.zoom_out') }}" class="mapping-icon bi bi-zoom-out"></i>
                    ---
                    <u id="save-btn" title="{{ trans('cruds.bpmn.buttons.save') }}" class="mapping-icon bi bi-floppy-fill"></u>
                    <label for="file-input" title="{{ trans('cruds.bpmn.buttons.import') }}"
                           class="mapping-icon bi bi-box-arrow-in-down">
                        <input type="file" id="file-input" accept=".bpmn,.xml"/>
                    </label>
                    <i id="download-svg" title="{{ trans('cruds.bpmn.buttons.download_svg') }}" class="mapping-icon bi bi-card-image"></i>
                    <i id="download-btn" title="{{ trans('cruds.bpmn.buttons.export') }}" class="mapping-icon bi bi-box-arrow-down"></i>

                </div>

                <div id="graph-container" tabindex="0"
                     style="
                        flex: 1;
                        min-width: 0;
                        min-height: 0;
                        height: 800px;
                        position: relative;
                        overflow: auto;
                        background-color: #fff;
                        background-image:
                            linear-gradient(to right, rgba(0,0,0,0.06) 1px, transparent 1px),
                            linear-gradient(to bottom, rgba(0,0,0,0.06) 1px, transparent 1px);
                        background-size: 20px 20px;"
                ></div>
                <div class="status" id="status"></div>

                <!-- The contextual vertex/edge menu is no longer markup here:
                     @sourcentis/bpmn-editor builds and manages its own inside
                     #graph-container (same icons, same actions). -->
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-no-cancel" class="btn btn-default" href="{{ route('admin.bpmn.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <button id="btn-save" class="btn btn-danger" type="submit">
            {{ trans('global.save') }}
        </button>
    </div>
</form>
@endsection

@section('styles')
@vite('resources/css/mapping.css')

<style>

@font-face {
    font-family: "bpmn";
    src: url("/build/fonts/bpmn.ttf") format("truetype");
    font-display: block;
}

.bpmn-icon {
    font-family: "bpmn";
    font-size: 20px;
    line-height: 1;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
}

/* Bootstrap icons (conversation, zoom, save, import, export…) don't get a
   font-size from .mapping-icon, so they render smaller than the custom
   "bpmn" glyphs above — bump them to match. */
.mapping-icon.bi {
    font-size: 22px;
}

#conversation-btn::before {
    transform: rotate(30deg);
}

/* Le card-body doit pouvoir étirer son contenu */
.card-body {
    display: flex;
    flex-direction: column;
    min-height: 0; /* critique pour que les enfants puissent scroller/étirer */
}

/* 2) Zone app (sidebar + graphe) prend tout l’espace dispo du card-body */
.bpmn-app {
    display: flex;
    flex: 1;
    min-height: 0;      /* critique */
    height: 100%;       /* si le parent le permet */
}

/* 3) Sidebar propre (au lieu du inline style) */
.bpmn-sidebar {
    width: 60px;
    background: #fff;
    border-right: 1px solid #ddd;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

/* 4) Graph : occupe tout, et on autorise le déplacement “feuille” via scroll si besoin.
   Les dimensions/overflow/fond sont posés en style inline sur le div (voir markup
   ci-dessus) plutôt qu'ici : resources/css/mapping.css (chargé sur cette page pour
   le legacy mxGraph, cf. #graph-container qu'il redéfinit pour la cartographie
   réseau) a une règle #graph-container de même spécificité — seul un style inline
   est garanti de gagner face à elle quel que soit l'ordre de chargement Vite. */

/* 5) Supprimer le ring/bord bleu quand le container a le focus */
#graph-container:focus,
#graph-container:focus-visible {
    outline: none !important;
    box-shadow: none !important;
}


.toolbar {
    background: white;
    padding: 12px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    gap: 10px;
    align-items: center;
}

button {
    padding: 8px 16px;
    background: #2196F3;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

button:hover {
    background: #1976D2;
}

button:active {
    background: #1565C0;
}

input[type="file"] {
    display: none;
}

.file-label {
    padding: 8px 16px;
    background: #4CAF50;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.file-label:hover {
    background: #45a049;
}

.info {
    margin-left: auto;
    color: #666;
    font-size: 13px;
}


.status {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #333;
    color: white;
    padding: 12px 20px;
    border-radius: 4px;
    opacity: 0;
    transition: opacity 0.3s;
    z-index: 1000;
}

.status.show {
    opacity: 1;
}

/* The contextual vertex/edge menu (formerly .vertex-menu / .menu-break /
   .color-palette / .color-swatch here) is now built and styled entirely by
   @sourcentis/bpmn-editor itself (bpmn-editor-* prefixed classes), so that
   CSS no longer lives in this page. */

.hidden { display: none !important; }

</style>

@endsection

@section('scripts')
@vite('resources/BPMN/bpmn.ts')

<script @nonce>
document.addEventListener("DOMContentLoaded", function () {

    //--------------------------------------------------------------
    // Save graph
    //--------------------------------------------------------------
    const form = document.getElementById('graph-form');
    form.addEventListener('submit', function (event) {
        console.log("save called !");
        // Prevent the form from submitting immediately
        event.preventDefault();

        document.getElementById('content').value = getXMLGraph();

        // Now submit the form
        form.submit();
    });

    //--------------------------------------------------------------
    // Chargement du graphe
    //--------------------------------------------------------------
    @if (!empty($content))
        loadGraph(@json($content));
    @endif

    //--------------------------------------------------------------
    // Maximisation
    document.getElementById('maximizeBtn').addEventListener('click', function () {
        const div = document.getElementById('editor');
        const sidebar = document.getElementById('bpmn-sidebar');
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
    });

});
</script>
@endsection
