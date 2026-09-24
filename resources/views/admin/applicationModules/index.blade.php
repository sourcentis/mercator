@extends('layouts.admin')

@section('title')
    {{ trans('cruds.applicationModule.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('application_module_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.application-modules.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.applicationModule.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.applicationModule.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.applicationModule.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.applicationModule.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.applicationModule.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationModule.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationModule.fields.entities') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationModule.fields.services') }}
                        </th>
			<th data-column="comments">
			    {{ trans('cruds.applicationModule.fields.comments') }}
			</th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($applicationModules as $key => $applicationModule)
                        <tr data-entry-id="{{ $applicationModule->id }}"
                            @if ($applicationModule->description==null)
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $applicationModule->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$applicationModule" />
                            </td>
                            <td>
                                {{ $applicationModule->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $applicationModule->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $applicationModule->description !!}
                            </td>
                            <td>
                                @foreach($applicationModule->entities as $entity)
                                    <x-show-link :model="$entity" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($applicationModule->applicationServices as $service)
                                    <x-show-link :model="$service" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>
			    <td>
			        {!! nl2br(e($applicationModule->comments)) !!}
			    </td>
                            <td nowrap>
                                @can('application_module_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.application-modules.show', $applicationModule->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($applicationModule)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.application-modules.edit', $applicationModule->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('application_module_delete')
                                    <form action="{{ route('admin.application-modules.destroy', $applicationModule->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $applicationModules])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.applicationModule.title_singular"),
            'URL' => route('admin.application-modules.massDestroy'),
            'canDelete' => auth()->user()->can('application_module_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'comments'],
));
    </script>
@endsection
