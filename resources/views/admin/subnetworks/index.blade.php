@extends('layouts.admin')

@section('title')
    {{ trans('cruds.subnetwork.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('subnetwork_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.subnetworks.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.subnetwork.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.subnetwork.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class=" table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.subnetwork.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.network') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.subnetwork') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.address') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.ip_range') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.default_gateway') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.vlan') }}
                        </th>
                        <th>
                            {{ trans('cruds.vlan.fields.vlan_id') }}
                        </th>
                        <th>
                            {{ trans('cruds.subnetwork.fields.zone') }}
                        </th>
                        <th data-column="responsible_exp">
                            {{ trans('cruds.subnetwork.fields.responsible_exp') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subnetworks as $key => $subnetwork)
                        <tr data-entry-id="{{ $subnetwork->id }}"
                            @if(
                              ($subnetwork->description==null)||
                              ($subnetwork->address==null)||
                              ($subnetwork->default_gateway==null)||
                              ($subnetwork->ip_allocation_type==null)||
                              ($subnetwork->vlan_id==null)||
                              ($subnetwork->responsible_exp==null)||
                              ($subnetwork->wifi==null)
                              )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $subnetwork->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$subnetwork" />
                            </td>
                            <td>
                                {{ $subnetwork->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", $subnetwork->attributes) as $attribute) {
                                    echo "<span class='badge badge-info'>";
                                    echo $attribute;
                                    echo "</span> ";
                                }
                                ?>
                            </td>
                            <td>
                                {!! $subnetwork->description ?? '' !!}
                            </td>
                            <td>
                                @if ($subnetwork->network!=null)
                                    <x-show-link :model="$subnetwork->network" />
                                @endif
                            </td>
                            <td>
                                @if ($subnetwork->subnetwork!=null)
                                    <x-show-link :model="$subnetwork->subnetwork" />
                                @endif
                            </td>
                            <td>
                                {{ $subnetwork->address ?? '' }}
                            </td>
                            <td>
                                {{ $subnetwork->ipRange() }}
                            </td>
                            <td>
                                {{ $subnetwork->default_gateway }}
                            </td>
                            <td>
                                @if ($subnetwork->vlan!=null)
                                    <x-show-link :model="$subnetwork->vlan" />
                                @endif
                            </td>
                            <td>
                                @if ($subnetwork->vlan!=null)
                                    <x-show-link :model="$subnetwork->vlan" :label="$subnetwork->vlan->vlan_id" />
                                @endif
                            </td>
                            <td>
                                {{ $subnetwork->zone }}
                            </td>
                            <td>
                                {{ $subnetwork->responsible_exp }}
                            </td>
                            <td nowrap>
                                @can('subnetwork_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.subnetworks.show', $subnetwork->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($subnetwork)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.subnetworks.edit', $subnetwork->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('subnetwork_delete')
                                    <form action="{{ route('admin.subnetworks.destroy', $subnetwork->id) }}"
                                          method="POST" data-confirm="{{ trans('global.areYouSure') }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $subnetworks])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.subnetwork.title_singular"),
            'URL' => route('admin.subnetworks.massDestroy'),
            'canDelete' => auth()->user()->can('subnetwork_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'responsible_exp'],
));
    </script>
@endsection
