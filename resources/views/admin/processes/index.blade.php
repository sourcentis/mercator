@extends('layouts.admin')

@section('title')
    {{ trans('cruds.process.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('process_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.processes.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.process.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.process.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.process.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.process.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.process.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.process.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.process.fields.operations') }}
                        </th>
                        <th>
                            {{ trans('cruds.process.fields.activities') }}
                        </th>
                        <th>
                            {{ trans('cruds.process.fields.informations') }}
                        </th>
                        <th>
                            {{ trans('cruds.process.fields.macroprocessus') }}
                        </th>
                        <th>
                            {{ trans('cruds.process.fields.owner') }}
                        </th>
                        <th data-column="in_out">
                            {{ trans('cruds.process.fields.in_out') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processes as $key => $process)
                        <tr data-entry-id="{{ $process->id }}"
                            @if(($process->name==null)||
                                ($process->description==null)||
                                ($process->in_out==null)||
                                ((auth()->user()->granularity>=2)&&
                                    (($process->security_need_c==null)||
                                    ($process->security_need_i==null)||
                                    ($process->security_need_a==null)||
                                    ($process->security_need_t==null)))||
                                ($process->owner==null)||
                                ($process->macroprocess_id==null)
                                )
                                                      class="table-warning"
                            @endif

                            >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $process->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$process" />
                            </td>
                            <td>
                                {{ $process->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $process->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! clean($process->description ?? '') !!}
                            </td>
                            <td>
                                @foreach($process->operations as $operation)
                                    <x-show-link :model="$operation" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($process->activities as $activity)
                                    <x-show-link :model="$activity" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($process->information as $info)
                                    <x-show-link :model="$info" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                @if ($process->macroProcess !== null)
                                    <x-show-link :model="$process->macroProcess" />
                                @endif
                            </td>
                            <td>
                                {{ $process->owner ?? '' }}
                            </td>
                            <td>
                                {{ $process->in_out }}
                            </td>
                            <td nowrap>
                                @can('process_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.processes.show', $process->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($process)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.processes.edit', $process->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('process_delete')
                                    <form action="{{ route('admin.processes.destroy', $process->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $processes])
</div>
</div>



@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.process.title_singular"),
    'URL' => route('admin.processes.massDestroy'),
    'canDelete' => auth()->user()->can('process_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'in_out'],
));
</script>
@endsection
