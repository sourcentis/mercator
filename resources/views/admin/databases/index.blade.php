@extends('layouts.admin')

@section('title')
    {{ trans('cruds.database.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('database_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.databases.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.database.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.database.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.database.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.database.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.database.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.database.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.database.fields.informations') }}
                        </th>
                        <th>
                            {{ trans('cruds.database.fields.applications') }}
                        </th>
                        <th data-column="responsible">
                            {{ trans('cruds.database.fields.responsible') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($databases as $key => $database)
                        <tr data-entry-id="{{ $database->id }}"
                          @if(($database->description==null)||
                              ($database->entity_resp_id==null)||
                              ($database->responsible==null)||
                              ($database->type==null)||
                              ((auth()->user()->granularity>=2)&&
                                    (
                                    ($database->security_need_c==null)||
                                    ($database->security_need_i==null)||
                                    ($database->security_need_a==null)||
                                    ($database->security_need_t==null)
                                    )
                                )
                              )
                          class="table-warning"
                          @endif
                          >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $database->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$database" />
                            </td>
                            <td>
                                {{ $database->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $database->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $database->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($database->informations as $information)
                                    <x-show-link :model="$information" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                @foreach($database->applications as $application)
                                    <x-show-link :model="$application" />
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                {{ $database->responsible }}
                            </td>
                            <td nowrap>
                                @can('database_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.databases.show', $database->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($database)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.databases.edit', $database->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('database_delete')
                                    <form action="{{ route('admin.databases.destroy', $database->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $databases])
</div>
</div>
@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.database.title_singular"),
    'URL' => route('admin.databases.massDestroy'),
    'canDelete' => auth()->user()->can('database_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'responsible'],
));
</script>
@endsection
