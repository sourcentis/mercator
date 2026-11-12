@extends('layouts.admin')

@section('title')
    {{ trans('cruds.physicalSwitch.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('physical_switch_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.physical-switches.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.physicalSwitch.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.physicalSwitch.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.physicalSwitch.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.physicalSwitch.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.physicalSwitch.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSwitch.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSwitch.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSwitch.fields.building') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSwitch.fields.bay') }}
                        </th>
                        <th>
                            {{ trans('cruds.physicalSwitch.fields.network_switches') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($physicalSwitches as $key => $physicalSwitch)
                        <tr data-entry-id="{{ $physicalSwitch->id }}"
                            @if (
                                ($physicalSwitch->description===null)||
                                ($physicalSwitch->type===null)||
                                ($physicalSwitch->site_id===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $physicalSwitch->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$physicalSwitch" />
                            </td>
                            <td>
                                {{ $physicalSwitch->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $physicalSwitch->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $physicalSwitch->description ?? '' !!}
                            </td>
                            <td>
                                @if($physicalSwitch->site!=null)
                                    <x-show-link :model="$physicalSwitch->site" />
                                @endif
                            </td>
                            <td>
                                @if($physicalSwitch->building!=null)
                                    <x-show-link :model="$physicalSwitch->building" />
                                @endif
                            </td>
                            <td>
                                @if($physicalSwitch->bay!=null)
                                    <x-show-link :model="$physicalSwitch->bay" />
                                @endif
                            </td>
                            <td>
                                @foreach($physicalSwitch->networkSwitches as $networkSwitch)
                                    <x-show-link :model="$networkSwitch" />
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('physical_switch_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.physical-switches.show', $physicalSwitch->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($physicalSwitch)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.physical-switches.edit', $physicalSwitch->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('physical_switch_delete')
                                    <form action="{{ route('admin.physical-switches.destroy', $physicalSwitch->id) }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $physicalSwitches])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.physicalSwitch.title_singular"),
            'URL' => route('admin.physical-switches.massDestroy'),
            'canDelete' => auth()->user()->can('physical_switch_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
