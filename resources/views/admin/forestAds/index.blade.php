@extends('layouts.admin')

@section('title')
    {{ trans('cruds.forestAd.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('forest_ad_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.forest-ads.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.forestAd.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.forestAd.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.forestAd.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.forestAd.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.forestAd.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.forestAd.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.forestAd.fields.zone_admin') }}
                        </th>
                        <th>
                            {{ trans('cruds.forestAd.fields.domains') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($forestAds as $key => $forestAd)
                        <tr data-entry-id="{{ $forestAd->id }}"
                            @if (
                                ($forestAd->description===null)||
                                ($forestAd->zone_admin_id===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $forestAd->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$forestAd" />
                            </td>
                            <td>
                                {{ $forestAd->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $forestAd->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $forestAd->description ?? '' !!}
                            </td>
                            <td>
                                @if ($forestAd->zoneAdmin!=null)
                                    <x-show-link :model="$forestAd->zoneAdmin" />
                                @endif
                            </td>
                            <td>
                                @foreach($forestAd->domains as $domain)
                                    <x-show-link :model="$domain" />
                                    @if ($forestAd->domains->last()!=$domain)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('forest_ad_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.forest-ads.show', $forestAd->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($forestAd)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.forest-ads.edit', $forestAd->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('forest_ad_delete')
                                    <form action="{{ route('admin.forest-ads.destroy', $forestAd->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $forestAds])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.forestAd.title_singular"),
            'URL' => route('admin.forest-ads.massDestroy'),
            'canDelete' => auth()->user()->can('forest_ad_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
