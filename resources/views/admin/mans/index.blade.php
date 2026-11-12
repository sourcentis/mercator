@extends('layouts.admin')

@section('title')
    {{ trans('cruds.man.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('man_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.mans.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.man.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.man.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.man.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.man.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.man.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.man.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.man.fields.wans') }}
                        </th>
                        <th>
                            {{ trans('cruds.man.fields.parent_man') }}
                        </th>
                        <th>
                            {{ trans('cruds.man.fields.lans') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mans as $key => $man)
                        <tr data-entry-id="{{ $man->id }}">
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $man->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$man" />
                            </td>
                            <td>
                                {{ $man->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $man->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                            {!! $man->description !!}
                            </td>
                            <td>
                                @foreach($man->wans as $wan)
                                <x-show-link :model="$wan" />
                                @if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                            @if($man->parentMan!==null)
                                <x-show-link :model="$man->parentMan" />
                            @endif
                            </td>
                            <td>
                                @foreach($man->lans as $lan)
                                <x-show-link :model="$lan" />
                                @if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('man_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.mans.show', $man->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($man)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.mans.edit', $man->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('man_delete')
                                    <form action="{{ route('admin.mans.destroy', $man->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $mans])
</div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.man.title_singular"),
    'URL' => route('admin.mans.massDestroy'),
    'canDelete' => auth()->user()->can('man_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes']
));
</script>
@endsection
