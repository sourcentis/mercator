@extends('layouts.admin')

@section('title')
    {{ trans('cruds.physicalRouter.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('physical_router_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.physical-routers.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.physicalRouter.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.physicalRouter.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.physicalRouter.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.physicalRouter.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.physicalRouter.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalRouter.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalRouter.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalRouter.fields.building') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalRouter.fields.bay') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalRouter.fields.routers') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($physicalRouters as $physicalRouter)
                        <tr data-entry-id="{{ $physicalRouter->id }}"
                            @if (
                                ($physicalRouter->description===null)||
                                ($physicalRouter->type===null)||
                                ($physicalRouter->site_id===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $physicalRouter->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$physicalRouter" />
                            </td>
                            <td>
                                {{ $physicalRouter->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $physicalRouter->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $physicalRouter->description ?? '' !!}
                            </td>
                            <td>
                                @if($physicalRouter->site!=null)
                                    <x-show-link :model="$physicalRouter->site" />
                                @endif
                            </td>
                            <td>
                                @if($physicalRouter->building!=null)
                                    <x-show-link :model="$physicalRouter->building" />
                                @endif
                            </td>
                            <td>
                                @if($physicalRouter->bay!=null)
                                    <x-show-link :model="$physicalRouter->bay" />
                                @endif
                            </td>
                            <td>
                                @foreach($physicalRouter->routers as $router)
                                    <x-show-link :model="$router" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('physical_router_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.physical-routers.show', $physicalRouter->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($physicalRouter)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.physical-routers.edit', $physicalRouter->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('physical_router_delete')
                                    <form action="{{ route('admin.physical-routers.destroy', $physicalRouter->id) }}"
                                          method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
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
        
        @include('partials.pagination-footer', ['paginator' => $physicalRouters])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.physicalRouter.title_singular"),
            'URL' => route('admin.physical-routers.massDestroy'),
            'canDelete' => auth()->user()->can('physical_router_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
