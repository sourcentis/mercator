@extends('layouts.admin')

@section('title')
    {{ trans('cruds.wifiTerminal.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('wifi_terminal_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.wifi-terminals.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.wifiTerminal.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.wifiTerminal.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.wifiTerminal.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.wifiTerminal.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.wifiTerminal.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.wifiTerminal.fields.address_ip') }}
                        </th>
                        <th>
                            {{ trans('cruds.wifiTerminal.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.wifiTerminal.fields.building') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.wifiTerminal.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($wifiTerminals as $wifiTerminal)
                        <tr data-entry-id="{{ $wifiTerminal->id }}"
                            @if (
                                ($wifiTerminal->description===null)||
                                ($wifiTerminal->type===null)||
                                ($wifiTerminal->site_id===null)||
                                ($wifiTerminal->building_id===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $wifiTerminal->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$wifiTerminal" />
                            </td>
                            <td>
                                {{ $wifiTerminal->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $wifiTerminal->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $wifiTerminal->address_ip }}
                            </td>
                            <td>
                                @if ($wifiTerminal->site!==null)
                                    <x-show-link :model="$wifiTerminal->site" />
                                @endif
                            </td>
                            <td>
                                @if ($wifiTerminal->building!==null)
                                    <x-show-link :model="$wifiTerminal->building" />
                                @endif
                            </td>
                            <td>
                                {!! $wifiTerminal->description !!}
                            </td>
                            <td nowrap>
                                @can('wifi_terminal_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.wifi-terminals.show', $wifiTerminal->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($wifiTerminal)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.wifi-terminals.edit', $wifiTerminal->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('wifi_terminal_delete')
                                    <form action="{{ route('admin.wifi-terminals.destroy', $wifiTerminal->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $wifiTerminals])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.wifiTerminal.title_singular"),
            'URL' => route('admin.wifi-terminals.massDestroy'),
            'canDelete' => auth()->user()->can('wifi_terminal_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes', 'description'],
));
    </script>
@endsection
