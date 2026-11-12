@extends('layouts.admin')

@section('title')
    {{ trans('cruds.networkSwitch.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('network_switch_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.network-switches.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.networkSwitch.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.networkSwitch.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class=" table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.networkSwitch.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.networkSwitch.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.networkSwitch.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.networkSwitch.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.networkSwitch.fields.vlans') }}
                        </th>
                        <th>
                            {{ trans('cruds.networkSwitch.fields.ip') }}
                        </th>
                        <th>
                            {{ trans('cruds.networkSwitch.fields.physical_switches') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($networkSwitches as $networkSwitch)
                        <tr data-entry-id="{{ $networkSwitch->id }}"
                            @if (
                                ($networkSwitch->description===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $networkSwitch->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$networkSwitch" />
                            </td>
                            <td>
                                {{ $networkSwitch->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $networkSwitch->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $networkSwitch->description ?? '' !!}
                            </td>
                            <td>
                                @foreach($networkSwitch->vlans as $vlan)
                                    <x-show-link :model="$vlan" />
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                {{ $networkSwitch->ip ?? '' }}
                            </td>
                            <td>
                                @foreach($networkSwitch->physicalSwitches as $physicalSwitch)
                                    <x-show-link :model="$physicalSwitch" />
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('network_switch_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.network-switches.show', $networkSwitch->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($networkSwitch)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.network-switches.edit', $networkSwitch->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('network_switch_delete')
                                    <form action="{{ route('admin.network-switches.destroy', $networkSwitch->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $networkSwitches])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.networkSwitch.title_singular"),
            'URL' => route('admin.network-switches.massDestroy'),
            'canDelete' => auth()->user()->can('network_switch_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
