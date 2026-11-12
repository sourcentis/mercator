@extends('layouts.admin')

@section('title')
    {{ trans('cruds.wan.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('wan_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route("admin.wans.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.wan.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.wan.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.wan.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.wan.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.wan.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.wan.fields.mans') }}
                        </th>
                        <th>
                            {{ trans('cruds.wan.fields.lans') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wans as $key => $wan)
                        <tr data-entry-id="{{ $wan->id }}">
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $wan->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$wan" />
                            </td>
                            <td>
                                {{ $wan->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $wan->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                @foreach($wan->mans as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach($wan->lans as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}</span>
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('wan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.wans.show', $wan->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($wan)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.wans.edit', $wan->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('wan_delete')
                                    <form action="{{ route('admin.wans.destroy', $wan->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $wans])
</div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.wan.title_singular"),
    'URL' => route('admin.wans.massDestroy'),
    'canDelete' => auth()->user()->can('wan_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes']
));
</script>
@endsection
