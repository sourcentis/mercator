@props([
    'id',
    'order',
    'title',
    'maxPageLength',
    'canDelete',
    'hasButtons' => true,
    'serverSidePagination' => false,
])

@if ($serverSidePagination)
function paginationPerPage(perPage) {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
@endif

document.addEventListener("DOMContentLoaded", function () {

table = $('{{ $id }}').DataTable({
        keys: true,
        stateSave: true,
        responsive: true,
        colReorder: true,
		autoWidth: true,
        columnDefs: [
            // Première colonne -> sélections
            {
                targets: 0,
                orderable: false,
                render: DataTable.render.select(),
            },
            // Dernière colonne alignée à droite
            {
                targets: -1,
                // className: 'dt-body-right'
            }
        ],
        @if ($serverSidePagination)
        paging: false,
        searching: false,
        info: false,
        @endif
        layout:
        {
            @if ($serverSidePagination)
            topStart: function() {
                @php
                    $opts = $perPageOptions ?? [10, 25, 50, 100, 250];
                    $cur  = (int) request('per_page', 50);
                @endphp
                var wrap = document.createElement('div');
                wrap.style.cssText = 'display:flex;align-items:center;gap:0.4rem;font-size:0.75rem';
                var sel = document.createElement('select');
                sel.className = 'form-select form-select-sm';
                sel.style.cssText = 'font-size:0.75rem;width:auto';
                sel.addEventListener('change', function() { paginationPerPage(this.value); });
                @foreach ($opts as $opt)
                sel.add(new Option('{{ $opt }}', '{{ $opt }}', false, {{ $cur === $opt ? 'true' : 'false' }}));
                @endforeach
                var lbl = document.createElement('span');
                lbl.textContent = '{{ trans("global.elements_per_page") }}';
                wrap.appendChild(sel);
                wrap.appendChild(lbl);
                return wrap;
            },
            topEnd: function() {
                var currentSearch = '{{ addslashes(request("search", "")) }}';
                var wrap = document.createElement('div');
                wrap.style.cssText = 'display:inline-flex;align-items:center;gap:0.4rem';
                var lbl = document.createElement('span');
                lbl.textContent = '{{ trans("global.search") }} :';
                lbl.style.cssText = 'font-size:0.75rem;white-space:nowrap';
                var inner = document.createElement('div');
                inner.style.cssText = 'position:relative;display:inline-flex;align-items:center';
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm';
                input.style.cssText = 'font-size:0.75rem;width:200px;padding-right:1.6rem';
                input.value = currentSearch;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = '&times;';
                btn.title = '{{ trans("global.delete") }}';
                btn.style.cssText = 'position:absolute;right:0.35rem;background:none;border:none;cursor:pointer;font-size:1.1rem;line-height:1;color:#adb5bd;padding:0;display:' + (currentSearch ? 'block' : 'none');
                btn.addEventListener('click', function() {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('search');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
                input.addEventListener('input', function() {
                    btn.style.display = this.value ? 'block' : 'none';
                });
                input.addEventListener('keydown', function(e) {
                    if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) {
                        e.stopPropagation();
                        return;
                    }
                    if (e.key !== 'Enter') return;
                    var url = new URL(window.location.href);
                    this.value ? url.searchParams.set('search', this.value) : url.searchParams.delete('search');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
                inner.appendChild(input);
                inner.appendChild(btn);
                wrap.appendChild(lbl);
                wrap.appendChild(inner);
                return wrap;
            },
            @endif
            keys: {
                columns: ':not(:first-child)',
            },
        },

        select: {
            style: 'multi',
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
        @if (isset($maxPageLength))
        lengthMenu: [
            [10, 25, 50, 100, {{ $maxPageLength }}],
            [10, 25, 50, 100, {{ $maxPageLength }}],
        ],
        @endif
        @if ($hasButtons)
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
              title: "Mercator - {{ $title }} - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'excel',
              title: "Mercator - {{ $title }} - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                  columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'pdf',
              test: 'PDF',
              title: "Mercator - {{ $title }} - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
            {
              extend: 'print',
              title: "Mercator - {{ $title }} - {{ Carbon\Carbon::today()->format('Ymd') }}",
              className: 'btn-default',
              exportOptions: {
                columns: ':not(:first-child):not(:last-child)'
              }
          },
          @if ($canDelete)
          {
                text: "{{ trans('global.datatables.delete') }}",
                className: 'btn-danger',
                action:
                    function (e, dt, node, config) {
                      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                          return $(entry).data('entry-id')
                      });

                      if (ids.length === 0) {
                        alert("{{ trans('global.datatables.zero_selected') }}")
                      }

                      else if (confirm("{{ trans('global.areYouSure') }}")) {
                        $.ajax({
                          method: 'POST',
                          headers: {'x-csrf-token': _token},
                          url: "{{ $URL }}",
                          data: { ids: ids, _method: 'DELETE' }})
                          .done(function ()
                              { location.reload() })
                        }
                    }
                }
            @endif
            ],
          @endif
        }
    );
    table
        .buttons(0, null)
        .container()
        .prependTo(table.table().container());
});
