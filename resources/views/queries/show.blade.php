@extends('layouts.admin')

@section('title', __('Query Engine'))

@section('content')
@php $canSave = isset($query) && auth()->id() === $query->user_id && auth()->user()->can('query_edit'); @endphp

<div class="card d-flex flex-column" style="min-height:calc(100vh - 180px);">
    <div class="card-header">
        @lang('Query Engine')
        @isset($query) — {{ $query->name }} @endisset
    </div>
    @include('queries._engine', ['readOnly' => ! $canUseEditor])
</div>

{{-- ── Boutons d'action ────────────────────────────────────────────── --}}
<div class="form-group mt-2">
    <a class="btn btn-default" href="{{ route('admin.queries.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
    @isset($query)
        @if($canSave)
            <button class="btn btn-success" id="btn-save">
                <i class="fas fa-save"></i> {{ trans('global.save') }}
            </button>
        @endif
    @else
        <button class="btn btn-success" id="btn-save">
            <i class="fas fa-bookmark"></i> {{ trans('global.save') }}
        </button>
    @endisset
</div>

{{-- ── Formulaire PUT caché (mise à jour) ─────────────────────────── --}}
@isset($query)
    @if($canSave)
        <form id="form-save-update"
              action="{{ route('admin.queries.update', $query) }}"
              method="POST" class="d-none">
            @csrf
            @method('PUT')
            <input type="hidden" name="name"        value="{{ $query->name }}">
            <input type="hidden" name="description" value="{{ $query->description }}">
            <input type="hidden" name="is_public"   value="{{ $query->is_public ? '1' : '0' }}">
            <input type="hidden" name="query_json"  id="form-save-update-query-json">
        </form>
    @endif
@endisset

@endsection

@section('scripts')
@vite(['resources/js/graphviz.js', 'resources/js/sql-parser.js'])
@include('queries.engine-js', [
    'queryDsl'    => $query?->query ?? null,
    'runUrl'      => $canUseEditor ? null : route('admin.queries.run', $query),
    'saveFormId'  => isset($query) && auth()->id() === $query->user_id && auth()->user()->can('query_edit')
                        ? 'form-save-update'
                        : null,
    'createRoute' => !isset($query)
                        ? route('admin.queries.create')
                        : null,
])
@parent
@endsection