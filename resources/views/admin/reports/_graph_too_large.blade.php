{{-- Graphe trop grand : le DOT n'a pas été construit (voir App\Services\Graph\GraphSize) --}}
@if(!empty($graphTooLarge))
    <div class="alert alert-warning m-3" role="alert"
         data-graph-too-large
         data-node-count="{{ $graphTooLarge['count'] }}"
         data-max-nodes="{{ $graphTooLarge['max'] }}">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ trans('global.graph_too_large', ['count' => $graphTooLarge['count'], 'max' => $graphTooLarge['max']]) }}
    </div>
@endif
