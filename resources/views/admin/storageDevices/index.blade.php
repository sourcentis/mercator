@extends('layouts.admin')

@section('title')
    {{ trans('cruds.storageDevice.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('storage_device_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.storage-devices.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.storageDevice.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.storageDevice.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.storageDevice.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.storageDevice.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.storageDevice.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.storageDevice.fields.address_ip') }}
                        </th>
                        <th>
                            {{ trans('cruds.storageDevice.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.storageDevice.fields.building') }}
                        </th>
                        <th>
                            {{ trans('cruds.storageDevice.fields.bay') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.storageDevice.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($storageDevices as $storageDevice)
                        <tr data-entry-id="{{ $storageDevice->id }}"
                            @if (
                                    ($storageDevice->description===null)||
                                    ($storageDevice->site_id===null)||
                                    ($storageDevice->building_id===null)
                                    )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $storageDevice->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$storageDevice" />
                            </td>
                            <td>
                                {{ $storageDevice->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $storageDevice->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $storageDevice->address_ip ?? '' }}
                            </td>
                            <td>
                                @if ($storageDevice->site!==null)
                                    <x-show-link :model="$storageDevice->site" />
                                @endif
                            </td>
                            <td>
                                @if ($storageDevice->building!==null)
                                    <x-show-link :model="$storageDevice->building" />
                                @endif
                            </td>
                            <td>
                                @if ($storageDevice->bay!==null)
                                    <x-show-link :model="$storageDevice->bay" />
                                @endif
                            </td>
                            <td>
                                {!! $storageDevice->description !!}
                            </td>
                            <td nowrap>
                                @can('storage_device_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.storage-devices.show', $storageDevice->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($storageDevice)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.storage-devices.edit', $storageDevice->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('storage_device_delete')
                                    <form action="{{ route('admin.storage-devices.destroy', $storageDevice->id) }}"
                                          method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
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
        
        @include('partials.pagination-footer', ['paginator' => $storageDevices])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.storageDevice.title_singular"),
            'URL' => route('admin.storage-devices.massDestroy'),
            'canDelete' => auth()->user()->can('storage_device_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes', 'description'],
));
    </script>
@endsection
