@extends('layouts.admin')

@section('title')
    {{ trans('cruds.lan.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('lan_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.lans.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.lan.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.lan.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.lan.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.lan.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.lan.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.lan.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($lans as $lan)
                        <tr data-entry-id="{{ $lan->id }}"
                            @if (
                                ($lan->description===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $lan->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$lan" />
                            </td>
                            <td>
                                {{ $lan->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $lan->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $lan->description }}
                            </td>
                            <td nowrap>
                                @can('lan_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.lans.show', $lan->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($lan)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.lans.edit', $lan->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('lan_delete')
                                    <form action="{{ route('admin.lans.destroy', $lan->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $lans])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.lan.title_singular"),
            'URL' => route('admin.lans.massDestroy'),
            'canDelete' => auth()->user()->can('lan_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
