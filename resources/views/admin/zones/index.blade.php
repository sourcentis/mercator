@extends('layouts.admin')

@section('title')
    {{ trans('cruds.zone.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('zone_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.zones.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.zone.title_singular') }}
                </a>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.zone.title') }}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10"></th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>{{ trans('cruds.zone.fields.name') }}</th>
                        <th>{{ trans('cruds.zone.fields.type') }}</th>
                        <th>{{ trans('cruds.zone.fields.attributes') }}</th>
                        <th>{{ trans('cruds.zone.fields.description') }}</th>
                        <th>{{ trans('cruds.zone.fields.buildings') }}</th>
                        <th>{{ trans('cruds.zone.fields.parent_zones') }}</th>
                        <th>{{ trans('cruds.zone.fields.child_zones') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($zones as $zone)
                        <tr data-entry-id="{{ $zone->id }}"
                            @if($zone->description === null) class="table-warning" @endif>
                            <td></td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $zone->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$zone" />
                            </td>
                            <td>{{ $zone->type ?? '' }}</td>
                            <td>
                            @foreach(explode(" ",$zone->attributes) as $attribute)
                                <span class="badge badge-info">{{ $attribute }}</span>
                            @endforeach
                            </td>
                            <td>{!! $zone->description ?? '' !!}</td>
                            <td>
                            @foreach($zone->buildings as $building)
                                <x-show-link :model="$building" />{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            </td>
                            <td>
                            @foreach($zone->parentZones as $parentZone)
                                <x-show-link :model="$parentZone" />{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            </td>
                            <td>
                            @foreach($zone->childZones as $childZone)
                                <x-show-link :model="$childZone" />{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            </td>
                            <td nowrap>
                                @can('zone_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.zones.show', $zone->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan
                                @canEdit($zone)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.zones.edit', $zone->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit
                                @can('zone_delete')
                                    <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST"
                                          data-confirm="{{ trans('global.areYouSure') }}"
                                          style="display: inline-block;">
                                        @method('DELETE')
                                        @csrf
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
        
        @include('partials.pagination-footer', ['paginator' => $zones])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', [
            'id'        => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'hiddenColumns' => ['perimeter'],
            'title'     => trans('cruds.zone.title_singular'),
            'URL'       => route('admin.zones.massDestroy'),
            'canDelete' => auth()->user()->can('zone_delete') ? true : false,
            'serverSidePagination' => true,
        ])
    </script>
@endsection
