@extends('layouts.admin')

@section('title')
    {{ trans('cruds.physicalSecurityDevice.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('physical_security_device_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.physical-security-devices.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.physicalSecurityDevice.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.physicalSecurityDevice.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.physicalSecurityDevice.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSecurityDevice.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSecurityDevice.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSecurityDevice.fields.address_ip') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSecurityDevice.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSecurityDevice.fields.building') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSecurityDevice.fields.bay') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.physicalSecurityDevice.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($physicalSecurityDevices as $physicalSecurityDevice)
                        <tr data-entry-id="{{ $physicalSecurityDevice->id }}"
                            @if (($physicalSecurityDevice->description==null)||
                                ($physicalSecurityDevice->type==null)||
                                ($physicalSecurityDevice->site_id==null)||
                                ($physicalSecurityDevice->building_id==null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>
                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $physicalSecurityDevice->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$physicalSecurityDevice" />
                            </td>
                            <td>
                                {{ $physicalSecurityDevice->type ?? '' }}
                            </td>
                            <td>
                                @if($physicalSecurityDevice->attributes)
                                    @foreach(preg_split('/\s+/', trim($physicalSecurityDevice->attributes)) as $a)
                                        @if($a !== '')
                                            <span class="badge badge-info">{{ e($a) }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                {{ $physicalSecurityDevice->address_ip ?? '' }}
                            </td>
                            <td>
                                @if($physicalSecurityDevice->site!=null)
                                    <x-show-link :model="$physicalSecurityDevice->site" />
                                @endif
                            </td>
                            <td>
                                @if($physicalSecurityDevice->building!=null)
                                    <x-show-link :model="$physicalSecurityDevice->building" />
                                @endif
                            </td>
                            <td>
                                @if($physicalSecurityDevice->bay!=null)
                                    <x-show-link :model="$physicalSecurityDevice->bay" />
                                @endif
                            </td>
                            <td>
                                {!! $physicalSecurityDevice->description !!}
                            </td>
                            <td nowrap>
                                @can('physical_security_device_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.physical-security-devices.show', $physicalSecurityDevice->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($physicalSecurityDevice)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.physical-security-devices.edit', $physicalSecurityDevice->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('physical_security_device_delete')
                                    <form action="{{ route('admin.physical-security-devices.destroy', $physicalSecurityDevice->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $physicalSecurityDevices])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.physicalSecurityDevice.title_singular"),
            'URL' => route('admin.physical-security-devices.massDestroy'),
            'canDelete' => auth()->user()->can('physical_security_device_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'description'],
));
    </script>
@endsection
