@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.patching') }}
@endsection

@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-sm-3">
        <form method="get" action="/admin/patching/index" name="myform">
            <input type="hidden" name="clear"/>
            <label class="recommended" for="patching_group">{{ trans('cruds.logicalServer.fields.attributes') }}</label>
            <select name="attributes_filter[]" class="form-control select2-free"
                    id="attributes_filter"
                    multiple data-autosubmit data-autosubmit-if-empty="clear">
                @foreach($attributes_filter as $a)
                    @if (!in_array($a, $attributes_list))
                        <option selected>{{$a}}</option>
                    @endif
                @endforeach
                @foreach($attributes_list as $a)
                    <option {{ ($attributes_filter ? in_array($a, $attributes_filter) : false) ? "selected" : "" }}>{{ $a }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="col-md-8">
    </div>
    <div class="col-sm-1">
        <span class="pull-right">
        <label class="recommended" for="patching_group">&nbsp;</label>
        <form method="get" action="/admin/patching/dashboard">
            <button class="btn btn-primary" type="submit">
                Dashboard
            </button>
        </form>
        </spam>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('panel.menu.patching') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-LogicalServer">
                <thead>
                    <tr>
                        <th width="0">

                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.operating_system') }}
                            /
                            CPE
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.application.fields.responsible') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.update_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.next_update') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patches as $patch)
                        <tr data-entry-id="{{ $patch->id }}"
                            @if ($patch->next_update===null)
                                class="table-secondary"
                            @elseif (\Carbon\Carbon::parse($patch->next_update)->isPast())
                                class="table-danger"
                            @else
                                class="table-success"
                            @endif
                            >
                            <td>
                            </td>
                            <td>
                                <x-show-link :model="$patch" />
                            </td>
                            <td>
                                @if ($patch->type === "SRV")
                                {{ $patch->version ?? '' }}
                                @else
                                {{ $patch->vendor ?? '' }} :
                                {{ $patch->product ?? '' }} :
                                {{ $patch->version ?? '' }}
                                @endif
                            </td>
                            <td>
                            @if ($patch->type==="SRV")
                              @foreach($patch->applications as $application)
                                <x-show-link :model="$application" />
                                  @if(!$loop->last)
                                  ,
                                  @endif
                              @endforeach
                            @else
                                -
                            @endif
                            </td>
                            <td>
                            @if ($patch->type=="SRV")
                                @php($responsibles = array())
                                @foreach($patch->applications as $application)
                                    @if ($application->responsible!=null)
                                        @foreach(explode(",",$application->responsible) as $responsible)
                                            @if (!in_array($responsible, $responsibles))
                                                @php(array_push($responsibles, $responsible))
                                            @endif
                                        @endforeach
                                     @endif
                                @endforeach
                                @foreach($responsibles as $responsible)
                                    {{ $responsible }}
                                    @if (!$loop->last)
                                    ,
                                    @endif
                                @endforeach
                            @else
                                {{ $patch->responsible }}
                            @endif
                            </td>
                            <td>
                                {{ $patch->attributes ?? '' }}
                            </td>
                            <td>
                                {{ $patch->update_date }}
                            </td>
                            <td>
                                {{ $patch->next_update }}
                            </td>
                            <td>
                                @if ($patch->type=="SRV")
                                    @can('logical_server_show')
                                        <a class="btn btn-xs btn-primary" href="{{ route('admin.patching.edit.server', $patch->id) }}">
                                            {{ trans('global.patch') }}
                                        </a>
                                    @endcan
                                @else
                                @can('application_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.patching.edit.application', $patch->id) }}">
                                        {{ trans('global.patch') }}
                                    </a>
                                @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
document.addEventListener("DOMContentLoaded", function () {

table = $('#table').DataTable({
        keys: true,
        stateSave: true,
        responsive: true,
        colReorder: true,
		autoWidth: true,
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                render: DataTable.render.select(),
            }],
        layout:
        {
    		paging: true,
            keys: {
                columns: ':not(:first-child)',
            },
        },

        select: {
            style: 'os',
            selector: 'td:first-child',
            headerCheckbox: 'select-page',
            items: 'row'
        },

        @if (isset($order))
        order: {!! $order !!},
        @else
        order: [[1, 'asc']],
        @endif
        pageLength: 100,

        }
    );
    table
        .buttons(0, null)
        .container()
        .prependTo(table.table().container());
});

</script>
@endsection
