@extends('layouts.admin')

@section('title')
    {{ trans('cruds.operation.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('operation_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.operations.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.operation.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.operation.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.operation.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.operation.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.operation.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.operation.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.operation.fields.process') }}
                        </th>
                        <th>
                            {{ trans('cruds.operation.fields.tasks') }}
                        </th>
                        <th>
                            {{ trans('cruds.operation.fields.actors') }}
                        </th>
                        <th>
                            {{ trans('cruds.operation.fields.activities') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operations as $key => $operation)
                        <tr data-entry-id="{{ $operation->id }}"
                         @if ($operation->description===null) class="table-warning" @endif >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $operation->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$operation" />
                            </td>
                            <td>
                                {{ $operation->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $operation->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $operation->description ?? '' !!}
                            </td>
                            <td>
                                @if ($operation->process!=null)
                                    <x-show-link :model="$operation->process" />
                                @endif
                            </td>
                            <td>
                                @foreach($operation->tasks as $task)
                                    <x-show-link :model="$task" />@if (!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($operation->actors as $actor)
                                    <x-show-link :model="$actor" />@if (!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($operation->activities as $activity)
                                    <x-show-link :model="$activity" />@if (!$loop->last), @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('operation_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.operations.show', $operation->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($operation)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.operations.edit', $operation->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('operation_delete')
                                    <form action="{{ route('admin.operations.destroy', $operation->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $operations])
</div>
</div>



@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.operation.title_singular"),
    'URL' => route('admin.operations.massDestroy'),
    'canDelete' => auth()->user()->can('operation_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
</script>
@endsection
