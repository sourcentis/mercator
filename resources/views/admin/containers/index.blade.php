@extends('layouts.admin')

@section('title')
    {{ trans('cruds.container.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('container_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.containers.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.container.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.container.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.container.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.container.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.container.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.container.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.container.fields.logical_servers') }}
                        </th>
                        <th>
                            {{ trans('cruds.container.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.container.fields.databases') }}
                        </th>
                        <th width="10%">
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($containers as $key => $container)
                        <tr data-entry-id="{{ $container->id }}"
                            @if(
                                ($container->description==null)||
                                ($container->type==null)||
                                ($container->applications->count()==0)||
                                ($container->logicalServers->count()==0)
                                )
                                    class="table-warning"
                            @endif
                            >
                            <td></td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $container->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$container" />
                            </td>
                            <td>
                                {{ $container->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $container->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $container->description ?? '' !!}
                            </td>
                            <td>
                              @foreach($container->logicalServers as $logicalServer)
                                <x-show-link :model="$logicalServer" />
                                  @if(!$loop->last)
                                  ,
                                  @endif
                              @endforeach
                            </td>
                            <td>
                              @foreach($container->applications as $application)
                                <x-show-link :model="$application" />
                                  @if(!$loop->last)
                                  ,
                                  @endif
                              @endforeach
                            </td>
                            <td>
                              @foreach($container->databases as $database)
                                <x-show-link :model="$database" />
                                  @if(!$loop->last)
                                  ,
                                  @endif
                              @endforeach
                            </td>
                            <td nowrap>
                                @can('containers_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.containers.show', $container->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($container)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.containers.edit', $container->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('container_delete')
                                    <form action="{{ route('admin.containers.destroy', $container->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $containers])
</div>
</div>
@endsection

@section('scripts')
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.container.title_singular"),
    'URL' => route('admin.containers.massDestroy'),
    'canDelete' => auth()->user()->can('site_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
</script>
@endsection
