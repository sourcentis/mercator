@extends('layouts.admin')

@section('title')
    {{ trans('cruds.router.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('router_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.routers.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.router.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.router.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.router.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.router.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.router.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.router.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.router.fields.physical_routers') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($routers as $router)
                        <tr data-entry-id="{{ $router->id }}"
                            @if (
                                ($router->description===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $router->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$router" />
                            </td>
                            <td>
                                {{ $router->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $router->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $router->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($router->physicalRouters as $physicalRouter)
                                    <x-show-link :model="$physicalRouter" />
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('router_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.routers.show', $router->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($router)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.routers.edit', $router->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('router_delete')
                                    <form action="{{ route('admin.routers.destroy', $router->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $routers])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.router.title_singular"),
            'URL' => route('admin.routers.massDestroy'),
            'canDelete' => auth()->user()->can('router_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes'],
));
    </script>
@endsection
