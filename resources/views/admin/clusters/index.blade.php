@extends('layouts.admin')

@section('title')
    {{ trans('cruds.cluster.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('cluster_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.clusters.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.cluster.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.cluster.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.cluster.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.cluster.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.cluster.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.cluster.fields.logical_servers') }}
                        </th>&nbsp;
                        <th>
                            {{ trans('cruds.cluster.fields.physical_servers') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.cluster.fields.description') }}
                        </th>
                        <th data-column="address_ip">
                            {{ trans('cruds.cluster.fields.address_ip') }}
                        </th>
                        <th>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($clusters as $cluster)
                        <tr data-entry-id="{{ $cluster->id }}"
                            @if(
                                ($cluster->description==null)||
                                ($cluster->type==null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $cluster->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$cluster" />
                            </td>
                            <td>
                                {{ $cluster->type ?? '' }}
                            </td>
                            <td>
                                @php
                                    foreach(explode(" ",$cluster->attributes) as $a)
                                        echo "<div class='badge badge-info'>$a</div> ";
                                @endphp
                            </td>
                            <td>
                                @foreach($cluster->logicalServers as $logicalServer)
                                    <x-show-link :model="$logicalServer" />
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                                @if (($cluster->logicalServers->count()>0)&&($cluster->routers->count()>0))
                                    ,
                                @endif
                                @foreach($cluster->routers as $router)
                                    <x-show-link :model="$router" />
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($cluster->physicalServers as $physicalServer)
                                    <x-show-link :model="$physicalServer" />
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach

                            </td>
                            <td>
                                {!! $cluster->description !!}
                            </td>
                            <td>
                                {{ $cluster->address_ip }}
                            </td>
                            <td nowrap>
                                @can('cluster_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.clusters.show', $cluster->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($cluster)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.clusters.edit', $cluster->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('cluster_delete')
                                    <form action="{{ route('admin.clusters.destroy', $cluster->id) }}" method="POST"
                                          data-confirm="{{ trans('global.areYouSure') }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $clusters])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.cluster.title_singular"),
            'URL' => route('admin.clusters.massDestroy'),
            'canDelete' => auth()->user()->can('cluster_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'description', 'address_ip'],
));
    </script>
@endsection
