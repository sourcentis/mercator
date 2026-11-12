@extends('layouts.admin')

@section('title')
    {{ trans('cruds.macroProcessus.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('macro_processus_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.macro-processuses.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.macroProcessus.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.macroProcessus.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.macroProcessus.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.macroProcessus.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.macroProcessus.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.macroProcessus.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.macroProcessus.fields.owner') }}
                        </th>
                        <th>
                            {{ trans('cruds.macroProcessus.fields.processes') }}
                        </th>
                        <th data-column="io_elements">
                            {{ trans('cruds.macroProcessus.fields.io_elements') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($macroProcessuses as $key => $macroProcessus)
                        <tr data-entry-id="{{ $macroProcessus->id }}"
                            @if(($macroProcessus->description==null)||
                                ($macroProcessus->io_elements==null)||
                                ((auth()->user()->granularity>=2)&&
                                    (
                                    ($macroProcessus->security_need_c==null)||
                                    ($macroProcessus->security_need_i==null)||
                                    ($macroProcessus->security_need_a==null)||
                                    ($macroProcessus->security_need_t==null)
                                    )
                                )||
                                    (
                                    (auth()->user()->granularity>=2) &&
                                    ($macroProcessus->owner==null)
                                    )
                                )
                                    class="table-warning"
                            @endif
                            >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $macroProcessus->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$macroProcessus" />
                            </td>
                            <td>
                                {{ $macroProcessus->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $macroProcessus->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $macroProcessus->description ?? '' !!}
                            </td>

                            <td>
                                {{ $macroProcessus->owner }}
                            </td>

                            <td>
                                @foreach($macroProcessus->processes as $process)
                                    <x-show-link :model="$process" />
                                    @if(!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                {{ $macroProcessus->io_elements }}
                            </td>
                            <td nowrap>
                                @can('macro_processus_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.macro-processuses.show', $macroProcessus->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($macroProcessus)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.macro-processuses.edit', $macroProcessus->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('macro_processus_delete')
                                    <form action="{{ route('admin.macro-processuses.destroy', $macroProcessus->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $macroProcessuses])
</div>
</div>



@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.macroProcessus.title_singular"),
    'URL' => route('admin.macro-processuses.massDestroy'),
    'canDelete' => auth()->user()->can('site_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'io_elements'],
));
</script>
@endsection
