@extends('layouts.admin')

@section('title')
    {{ trans('cruds.trash.title') }}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-trash3 me-1"></i>{{ trans('cruds.trash.title') }}
    </div>
    <div class="card-body">
        <p class="text-muted">{{ trans('cruds.trash.help') }}</p>

        @if (count($rows) === 0)
            <p class="mb-0">{{ trans('cruds.trash.empty') }}</p>
        @else
            <div class="col-lg-8 px-0">
                <table class="table table-bordered table-hover mb-0" id="trash-overview">
                    <thead>
                        <tr>
                            <th style="width: 30%">{{ trans('cruds.trash.view') }}</th>
                            <th>{{ trans('cruds.trash.object_type') }}</th>
                            <th class="text-end text-nowrap" style="width: 1%">{{ trans('cruds.trash.deleted_count') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $view => $types)
                            @foreach ($types as $row)
                                <tr>
                                    @if ($loop->first)
                                        <th rowspan="{{ count($types) }}" class="align-top fw-bold">{{ $view }}</th>
                                    @endif
                                    <td><a href="{{ $row['url'] }}">{{ $row['label'] }}</a></td>
                                    <td class="text-end">{{ $row['count'] }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
