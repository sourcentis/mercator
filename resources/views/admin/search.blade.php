@extends('layouts.admin')

@section('title')
    {{ trans('global.search') }}
@endsection

@section('content')

@if (empty($searchableData))
    <div class="card mt-2">
        <div class="card-header">Empty</div>
    </div>
@else

    @php $curModel = null; @endphp

    @foreach ($searchableData as $item)

        {{-- Nouveau groupe : ferme la card précédente et ouvre la suivante --}}
        @if ($curModel !== $item['model'])

            @if ($curModel !== null)
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @php $curModel = $item['model']; @endphp

            <div class="card mt-2">
                <div class="card-header">
                    {{ $item['name'] }}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover datatable datatable-{{ $curModel }}">
                            <thead>
                                <tr>
                                    @foreach ($item['fields_formated'] as $field)
                                        <th>{{ $field }}</th>
                                    @endforeach
                                    <th style="display:none;"></th>
                                </tr>
                            </thead>
                            <tbody>

        @endif

        {{-- Ligne de données --}}
        <tr>
            @foreach ($item['fields'] as $field)
                <td>
                    @if ($loop->first)
                        @canShow($item['instance'])
                            <a href="{{ $item['url'] }}">{!! clean((string) $item['data'][$field]) !!}</a>
                        @else
                            {!! clean((string) $item['data'][$field]) !!}
                        @endcanShow
                    @else
                        {!! clean((string) $item['data'][$field]) !!}
                    @endif
                </td>
            @endforeach
            <td style="display:none;"></td>
        </tr>

    @endforeach

    {{-- Fermeture de la dernière card --}}
    @if ($curModel !== null)
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

@endif

@endsection


@section('scripts')
@parent
<script @nonce>
$(function () {
    const dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

    $.extend(true, $.fn.dataTable.defaults, {
        orderCellsTop: true,
        order: [[1, 'asc']],
        pageLength: 100,
        stateSave: true,
        lengthMenu: [10, 50, 100, 500],
    });

    @php $curModel = null; @endphp
    @foreach ($searchableData as $item)
        @if ($curModel !== $item['model'])
            $('.datatable-{{ $item['model'] }}:not(.ajaxTable)').DataTable({ buttons: dtButtons });
            @php $curModel = $item['model']; @endphp
        @endif
    @endforeach
});
</script>
@endsection
