@extends('layouts.admin')

@section('title')
    {{ trans('cruds.bay.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('bay_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.bays.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.bay.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.bay.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.bay.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.bay.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.bay.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.bay.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.bay.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.bay.fields.building') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bays as $bay)
                        <tr data-entry-id="{{ $bay->id }}"
                            @if(
                                ($bay->description===null)||
                                ($bay->site_id===null)||
                                ($bay->building_id===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $bay->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$bay" />
                            </td>
                            <td>
                                {{ $bay->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $bay->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $bay->description ?? '' !!}
                            </td>
                            <td>
                                @if ($bay->site!=null)
                                    <x-show-link :model="$bay->site" />
                                @endif
                            </td>
                            <td>
                                @if ($bay->building!=null)
                                    <x-show-link :model="$bay->building" />
                                @endif
                            </td>
                            <td nowrap>
                                @can('bay_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.bays.show', $bay->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($bay)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.bays.edit', $bay->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('bay_delete')
                                    <form action="{{ route('admin.bays.destroy', $bay->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $bays])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.bay.title_singular"),
            'URL' => route('admin.bays.massDestroy'),
            'canDelete' => auth()->user()->can('bay_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
