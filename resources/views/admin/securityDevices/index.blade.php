@extends('layouts.admin')

@section('title')
    {{ trans('cruds.securityDevice.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('security_device_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.security-devices.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.securityDevice.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.securityDevice.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.securityDevice.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.securityDevice.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.securityDevice.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.securityDevice.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.securityDevice.fields.physical_security_devices') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.securityDevice.fields.description') }}
                        </th>
                        <th data-column="address_ip">
                            {{ trans('cruds.securityDevice.fields.address_ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($securityDevices as $securityDevice)
                        <tr data-entry-id="{{ $securityDevice->id }}"
                            @if (
                                ($securityDevice->description===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $securityDevice->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$securityDevice" />
                            </td>
                            <td>
                                {{ $securityDevice->type ?? '' }}
                            </td>
                            <td>
                                @php
                                    foreach(explode(" ",$securityDevice->attributes) as $a)
                                        echo "<div class='badge badge-info'>$a</div> ";
                                @endphp
                            </td>
                            <td>
                                @foreach($securityDevice->applications as $application)
                                    <x-show-link :model="$application" />
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                @foreach($securityDevice->physicalSecurityDevices as $device)
                                    <x-show-link :model="$device" />
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                {!! $securityDevice->description !!}
                            </td>
                            <td>
                                {{ $securityDevice->address_ip }}
                            </td>
                            <td nowrap>
                                @can('security_device_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.security-devices.show', $securityDevice->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($securityDevice)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.security-devices.edit', $securityDevice->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('security_device_delete')
                                    <form action="{{ route('admin.security-devices.destroy', $securityDevice->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $securityDevices])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.securityDevice.title_singular"),
            'URL' => route('admin.security-devices.massDestroy'),
            'canDelete' => auth()->user()->can('security_device_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'description', 'address_ip'],
));
    </script>
@endsection
