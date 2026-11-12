@extends('layouts.admin')

@section('title')
    {{ trans('cruds.workstation.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('workstation_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.workstations.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.workstation.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.workstation.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.workstation.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.workstation.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.workstation.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.workstation.fields.status') }}
                        </th>
                        <th>
                            {{ trans('cruds.workstation.fields.serial_number') }}
                        </th>
                        <th>
                            {{ trans('cruds.workstation.fields.user') }}
                        </th>
                        <th>
                            {{ trans('cruds.workstation.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.workstation.fields.building') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.workstation.fields.description') }}
                        </th>
                        <th data-column="manufacturer">
                            {{ trans('cruds.workstation.fields.manufacturer') }}
                        </th>
                        <th data-column="model">
                            {{ trans('cruds.workstation.fields.model') }}
                        </th>
                        <th data-column="address_ip">
                            {{ trans('cruds.workstation.fields.address_ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($workstations as $workstation)
                        <tr data-entry-id="{{ $workstation->id }}"
                            @if (
                                ($workstation->description===null)||
                                ($workstation->site_id===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $workstation->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$workstation" />
                            </td>
                            <td>
                                {{ $workstation->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $workstation->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $workstation->status ?? '' }}
                            </td>
                            <td>
                                {{ $workstation->serial_number ?? '' }}
                            </td>
                            <td>
                                @if ($workstation->user!==null)
                                    <x-show-link :model="$workstation->user" :label="$workstation->user->user_id ?? ''" />
                                @endif
                            </td>
                            <td>
                                @if ($workstation->site!=null)
                                    <x-show-link :model="$workstation->site" />
                                @endif
                            </td>
                            <td>
                                @if ($workstation->building!=null)
                                    <x-show-link :model="$workstation->building" />
                                @endif
                            </td>
                            <td>
                                {!! $workstation->description !!}
                            </td>
                            <td>
                                {{ $workstation->manufacturer }}
                            </td>
                            <td>
                                {{ $workstation->model }}
                            </td>
                            <td>
                                {{ $workstation->address_ip }}
                            </td>
                            <td nowrap>
                                @can('workstation_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.workstations.show', $workstation->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($workstation)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.workstations.edit', $workstation->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('workstation_delete')
                                    <form action="{{ route('admin.workstations.destroy', $workstation->id) }}"
                                          method="POST" data-confirm="{{ trans('global.areYouSure') }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $workstations])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.workstation.title_singular"),
            'URL' => route('admin.workstations.massDestroy'),
            'canDelete' => auth()->user()->can('workstation_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'description', 'manufacturer', 'model', 'address_ip'],
));
    </script>
@endsection
