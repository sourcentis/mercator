@extends('layouts.admin')

@section('title')
    {{ trans('cruds.applicationBlock.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('application_block_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.application-blocks.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.applicationBlock.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.applicationBlock.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.applicationBlock.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.applicationBlock.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.applicationBlock.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationBlock.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationBlock.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationBlock.fields.responsible') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicationBlocks as $applicationBlock)
                        <tr data-entry-id="{{ $applicationBlock->id }}"
                        @if (($applicationBlock->description==null)||
                            ($applicationBlock->responsible==null)||
                            ($applicationBlock->applications->count()==0)
                            )
                           class="table-warning"
                        @endif
                          >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $applicationBlock->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$applicationBlock" />
                            </td>
                            <td>
                                {{ $applicationBlock->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $applicationBlock->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $applicationBlock->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($applicationBlock->applications as $application)
                                    <x-show-link :model="$application" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                {{ $applicationBlock->responsible ?? '' }}
                            </td>
                            <td nowrap>
                                @can('application_block_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.application-blocks.show', $applicationBlock->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($applicationBlock)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.application-blocks.edit', $applicationBlock->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('application_block_delete')
                                    <form action="{{ route('admin.application-blocks.destroy', $applicationBlock->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $applicationBlocks])
</div>
</div>
@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.applicationBlock.title_singular"),
    'URL' => route('admin.application-blocks.massDestroy'),
    'canDelete' => auth()->user()->can('application_block_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
</script>
@endsection
