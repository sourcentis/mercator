@extends('layouts.admin')

@section('title')
    {{ trans('cruds.cartographer.title') }} {{ trans('global.list') }}
@endsection

@section('content')
<div style="margin-bottom: 10px;" class="row">
    @can('cartographer_create')
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.cartographers.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.cartographer.title_singular') }}
        </a>
    </div>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('cruds.cartographer.title') }}
    </div>
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>{{ trans('cruds.cartographer.fields.user') }}</th>
                        <th>{{ trans('cruds.cartographer.fields.role') }}</th>
                        <th>{{ trans('cruds.cartographer.fields.type') }}</th>
                         <th>{{ trans('cruds.cartographer.fields.object') }}</th>
                         <th>{{ trans('cruds.cartographer.fields.last_updated') }}</th>
                         <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartographers as $cartographer)
                    @php
                        $showRoute = $routes[$cartographer->cartographiable_type] ?? null;
                    @endphp
                    <tr data-entry-id="{{ $cartographer->id }}">
                        <td></td>

                        {{-- Utilisateur --}}
                        <td>
                            @if($cartographer->user)
                                <x-show-link :model="$cartographer->user" />
                            @else
                                -
                            @endif
                        </td>

                        {{-- Rôle --}}
                        <td>
                            @if($cartographer->role)
                                <x-show-link :model="$cartographer->role" :label="$cartographer->role->title ?? ''" />
                            @else
                                -
                            @endif
                        </td>


                        {{-- Type --}}
                        <td>{{ $models[$cartographer->cartographiable_type] ?? $cartographer->cartographiable_type }}</td>

                        {{-- Objet (lien vers la fiche) --}}
                        <td>
                            @if($cartographer->cartographiable)
                                @php($label = $cartographer->cartographiable->name ?? '(id:'.$cartographer->cartographiable_id.')')
                                @if($showRoute)
                                    <a href="{{ route($showRoute, $cartographer->cartographiable_id) }}">{{ $label }}</a>
                                @else
                                    {{ $label }}
                                @endif
                            @else
                                <em class="text-muted">(objet supprimé)</em>
                            @endif
                        </td>

                        {{-- Dernière modification de l'objet --}}
                        <td>
                            @can('audit_log_show')
                            @if($cartographer->cartographiable)
                            <a href="{{ route('admin.audit-logs.history', ['type' => $cartographer->cartographiable_type, 'id' => $cartographer->cartographiable_id]) }}">
                            {{ $cartographer->cartographiable?->updated_at?->format(trans('global.timestamp')) ?? '-' }}
                            </a>
                            @endif
                            @endcan
                        </td>

                        {{-- Actions --}}
                        <td nowrap>
                            @can('cartographer_create')
                            <a class="btn btn-xs btn-info"
                               href="{{ $cartographer->user_id
                                    ? route('admin.cartographers.create', ['user_id' => $cartographer->user_id])
                                    : route('admin.cartographers.create', ['role_id' => $cartographer->role_id]) }}">
                                {{ trans('global.edit') }}
                            </a>
                            @endcan
                            @can('cartographer_delete')
                            <form action="{{ route('admin.cartographers.destroy', $cartographer->id) }}" method="POST"
                                  onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                  style="display: inline-block;">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="submit" class="btn btn-xs btn-danger"
                                       value="{{ trans('global.delete') }}">
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @include('partials.pagination-footer', ['paginator' => $cartographers])
    </div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', [
    'id'                  => '#dataTable',
    'title'               => trans('cruds.cartographer.title_singular'),
    'URL'                 => route('admin.cartographers.massDestroy'),
    'canDelete'           => (bool) auth()->user()->can('cartographer_delete'),
    'serverSidePagination' => true,
])
</script>
@endsection
