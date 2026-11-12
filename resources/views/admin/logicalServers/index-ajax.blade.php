@extends('layouts.admin')

@section('title')
    {{ trans('cruds.logicalServer.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('logical_server_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.logical-servers.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.logicalServer.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.logicalServer.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>
                            {{ trans('cruds.logicalServer.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.logicalServer.fields.description') }}
                        </th>
                        <th>
                        </th>
                </thead>
                <tbody>
                    <!-- data goes here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
var translations = {
    view: "{{ trans('global.view') }}",
    delete: "{{ trans('global.delete') }}",
    edit: "{{ trans('global.edit') }}",
    areYouSure : "{{ trans('global.areYouSure') }}",
    title : "{{ trans('cruds.site.title_singular') }}",
};
document.addEventListener("DOMContentLoaded", function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        keys: true,
        stateSave: true,
        colReorder: true,
		autoWidth: true,
        pageLength: 100,

        layout:
        {
    		paging: true,
            keys: {
                columns: ':not(:first-child)',
            },
        },
        ajax: {
            "url": "/admin/logical-servers-data",
            "type": "POST"
        },
        columns: [
            {
                data: "name",
                render: function (data, type, row) {
                        return '<a href="/admin/logical-servers/' + row.id + '">'+row.name+'</a>';
                    },
            },
            { data: "type" },
            {
                data: "attributes",
                render: function (data, type, row) {
                    if (row.attributes!=null)
                        return row.attributes.split(" ").map(word => `<span class="badge badge-info">${word.trim()}</span>`).join(" ");
                    return null;
                },
            },
            {
                data: "description",
                render: function (data, type, row) {
                    var sutsut = '<div>' + row.description + '</div>';
                    return $(sutsut).text();
                }
            },
            {
                data: null,
                name: "actions",
                render: function (data, type, row) {
                    result =  '<a href="/admin/logical-servers/'+row.id+'" class="btn btn-xs btn-primary">'+translations.view+'</a>';
                    result += '<a href="/admin/logical-servers/'+row.id+'/edit" class="btn btn-xs btn-info">'+translations.edit+'</a>';
                    result += '<form action="/admin/logical-servers/'+row.id+'" method="POST" onsubmit=\'return confirm("'+translations.areYouSure+'");\' style="display: inline-block;">';
                    result += '<input type="hidden" name="_method" value="DELETE">';
                    result += '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">';
                    result += '<input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">';
                    result += '</form>';
                    return result;
                }
            },
        ],
        dom: 'Blfrtip',
        buttons: [
            {
              extend: 'colvis',
              columns: ':not(:first-child):not(:last-child)'
            },
            {
              extend: 'copy',
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'csv',
              title: "Mercator - translations.title - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'excel',
              title: "Mercator - " + translations.title + " - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                  columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'pdf',
              test: 'PDF',
              title: "Mercator - " + translations.title + " - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'print',
              title: "Mercator - " + translations.title + " - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
      ]

    });
    table
        .buttons(0, null)
        .container()
        .prependTo(table.table().container());

});
</script>
@endsection
