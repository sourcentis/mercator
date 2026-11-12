@extends('layouts.admin')

@section('title')
    {{ trans('cruds.vlan.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('vlan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.vlans.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.vlan.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.vlan.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.vlan.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.vlan.fields.vlan_id') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.vlan.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.vlan.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.vlan.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.vlan.fields.subnetworks') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vlans as $key => $vlan)
                        <tr data-entry-id="{{ $vlan->id }}">
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $vlan->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$vlan" />
                            </td>
                            <td>
                                {{ $vlan->vlan_id }}
                            </td>
                            <td>
                                {{ $vlan->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $vlan->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $vlan->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($vlan->subnetworks as $subnetwork)
                                    <x-show-link :model="$subnetwork" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @can('vlan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.vlans.show', $vlan->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($vlan)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.vlans.edit', $vlan->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('vlan_delete')
                                    <form action="{{ route('admin.vlans.destroy', $vlan->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $vlans])
</div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.vlan.title_singular"),
    'URL' => route('admin.vlans.massDestroy'),
    'canDelete' => auth()->user()->can('vlan_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes']
));
</script>
@endsection
