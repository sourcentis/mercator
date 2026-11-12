@extends('layouts.admin')

@section('title')
    {{ trans('cruds.peripheral.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('peripheral_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.peripherals.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.peripheral.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.peripheral.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.peripheral.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.peripheral.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.peripheral.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.domain') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.provider') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.responsible') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.address_ip') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.building') }}
                        </th>
                        <th>
                            {{ trans('cruds.peripheral.fields.bay') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.peripheral.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($peripherals as $peripheral)
                        <tr data-entry-id="{{ $peripheral->id }}"
                            @if (
                                    ($peripheral->description===null)||
                                    ($peripheral->site_id===null)||
                                    ($peripheral->building_id===null)||
                                    ($peripheral->responsible===null)
                                    )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $peripheral->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$peripheral" />
                            </td>
                            <td>
                                {{ $peripheral->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $peripheral->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $peripheral->domain->name ?? '' }}
                            </td>
                            <td>
                                {{ $peripheral->provider->name ?? '' }}
                            </td>
                            <td>
                                {{ $peripheral->responsible ?? '' }}
                            </td>
                            <td>
                                {{ $peripheral->address_ip ?? '' }}
                            </td>
                            <td>
                                {{ $peripheral->site->name ?? '' }}
                            </td>
                            <td>
                                {{ $peripheral->building->name ?? '' }}
                            </td>
                            <td>
                                {{ $peripheral->bay->name ?? '' }}
                            </td>
                            <td>
                                {!! $peripheral->description !!}
                            </td>
                            <td nowrap>
                                @can('peripheral_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.peripherals.show', $peripheral->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($peripheral)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.peripherals.edit', $peripheral->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('peripheral_delete')
                                    <form action="{{ route('admin.peripherals.destroy', $peripheral->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $peripherals])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.peripheral.title_singular"),
            'URL' => route('admin.peripherals.massDestroy'),
            'canDelete' => auth()->user()->can('peripheral_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'description'],
));
    </script>
@endsection
