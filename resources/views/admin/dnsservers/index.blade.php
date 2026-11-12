@extends('layouts.admin')

@section('title')
    {{ trans('cruds.dnsserver.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('dnsserver_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route("admin.dnsservers.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.dnsserver.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.dnsserver.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class=" table table-bordered table-striped table-hover datatable datatable-Dnsserver">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.dnsserver.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.dnsserver.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.dnsserver.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.dnsserver.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.dnsserver.fields.address_ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dnsservers as $dnsserver)
                        <tr data-entry-id="{{ $dnsserver->id }}"
                        @if ($dnsserver->address_ip==null)
                            class="table-warning"
                        @endif
                            >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $dnsserver->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$dnsserver" />
                            </td>
                            <td>
                                {{ $dnsserver->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $dnsserver->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                              {!! $dnsserver->description !!}
                            </td>
                            <td>
                                {{ $dnsserver->address_ip ?? '' }}
                            </td>
                            <td>
                                @can('dnsserver_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.dnsservers.show', $dnsserver->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($dnsserver)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.dnsservers.edit', $dnsserver->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('dnsserver_delete')
                                    <form action="{{ route('admin.dnsservers.destroy', $dnsserver->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $dnsservers])
</div>
</div>



@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.dnsserver.title_singular"),
    'URL' => route('admin.dnsservers.massDestroy'),
    'canDelete' => auth()->user()->can('dnsserver_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
</script>
@endsection
