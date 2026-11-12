@extends('layouts.admin')

@section('title')
    {{ trans('cruds.site.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('site_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.sites.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.site.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.site.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.site.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.site.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.site.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.site.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.site.fields.buildings') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sites as $site)
                        <tr data-entry-id="{{ $site->id }}"
                            @if (
                                ($site->description===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $site->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$site" />
                            </td>
                            <td>
                                {{ $site->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", $site->attributes) as $attribute) {
                                    echo "<span class='badge badge-info'>";
                                    echo $attribute;
                                    echo "</span> ";
                                }
                                ?>
                            </td>
                            <td>
                                {!! $site->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($site->buildings as $building)
                                    <x-show-link :model="$building" />
                                    @if ($site->buildings->last()!=$building)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('site_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.sites.show', $site->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($site)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.sites.edit', $site->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('site_delete')
                                    <form action="{{ route('admin.sites.destroy', $site->id) }}" method="POST"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
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
        
        @include('partials.pagination-footer', ['paginator' => $sites])
</div>
    </div>
@endsection

@section('scripts')
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.site.title_singular"),
    'URL' => route('admin.sites.massDestroy'),
    'canDelete' => auth()->user()->can('site_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
</script>
@endsection
