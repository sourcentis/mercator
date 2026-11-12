@extends('layouts.admin')

@section('title')
    {{ trans('cruds.physicalServer.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('physical_server_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.physical-servers.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.physicalServer.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.physicalServer.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.physicalServer.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.physicalServer.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.physicalServer.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalServer.fields.responsible') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalServer.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalServer.fields.building') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalServer.fields.bay') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.physicalServer.fields.description') }}
                        </th>
                        <th data-column="configuration">
                            {{ trans('cruds.physicalServer.fields.configuration') }}
                        </th>
                        <th data-column="address_ip">
                            {{ trans('cruds.physicalServer.fields.address_ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($physicalServers as $physicalServer)
                        <tr data-entry-id="{{ $physicalServer->id }}"
                            @if (($physicalServer->description==null)||
                                ($physicalServer->configuration==null)||
                                ($physicalServer->site_id==null)||
                                ($physicalServer->responsible==null)
                                )
                                class="table-warning"
                            @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $physicalServer->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$physicalServer" />
                            </td>
                            <td>
                                {{ $physicalServer->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $physicalServer->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $physicalServer->responsible }}
                            </td>
                            <td>
                                @if ($physicalServer->site!=null)
                                    <x-show-link :model="$physicalServer->site" />
                                @endif
                            </td>
                            <td>
                                @if ($physicalServer->building!=null)
                                    <x-show-link :model="$physicalServer->building" />
                                @endif
                            </td>
                            <td>
                                @if ($physicalServer->bay!=null)
                                    <x-show-link :model="$physicalServer->bay" />
                                @endif
                            </td>
                            <td>
                                {!! $physicalServer->description !!}
                            </td>
                            <td>
                                {!! $physicalServer->configuration !!}
                            </td>
                            <td>
                                {{ $physicalServer->address_ip }}
                            </td>
                            <td nowrap>
                                @can('physical_server_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.physical-servers.show', $physicalServer->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($physicalServer)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.physical-servers.edit', $physicalServer->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('physical_server_delete')
                                    <form action="{{ route('admin.physical-servers.destroy', $physicalServer->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $physicalServers])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.physicalServer.title_singular"),
            'URL' => route('admin.physical-servers.massDestroy'),
            'canDelete' => auth()->user()->can('physical_server_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'description', 'configuration', 'address_ip'],
));
    </script>
@endsection
