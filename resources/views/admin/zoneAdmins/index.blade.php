@extends('layouts.admin')

@section('title')
    {{ trans('cruds.zoneAdmin.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('zone_admin_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.zone-admins.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.zoneAdmin.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.zoneAdmin.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.zoneAdmin.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.zoneAdmin.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.zoneAdmin.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.zoneAdmin.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.zoneAdmin.fields.annuaires') }}
                        </th>
                        <th>
                            {{ trans('cruds.zoneAdmin.fields.forests') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($zoneAdmins as $key => $zoneAdmin)
                        <tr data-entry-id="{{ $zoneAdmin->id }}"
                            @if (
                                ($zoneAdmin->description===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $zoneAdmin->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$zoneAdmin" />
                            </td>
                            <td>
                                {{ $zoneAdmin->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $zoneAdmin->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $zoneAdmin->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($zoneAdmin->annuaires as $annuaire)
                                    <x-show-link :model="$annuaire" />{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </td>
                            <td>
                                @foreach($zoneAdmin->forestAds as $forestAd)
                                    <x-show-link :model="$forestAd" />{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('zone_admin_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.zone-admins.show', $zoneAdmin->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($zoneAdmin)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.zone-admins.edit', $zoneAdmin->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('zone_admin_delete')
                                    <form action="{{ route('admin.zone-admins.destroy', $zoneAdmin->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $zoneAdmins])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.zoneAdmin.title_singular"),
            'URL' => route('admin.zone-admins.massDestroy'),
            'canDelete' => auth()->user()->can('zone_admin_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
