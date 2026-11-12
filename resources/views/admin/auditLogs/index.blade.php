@extends('layouts.admin')

@section('title')
    {{ trans('cruds.auditLog.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.auditLog.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
        {{-- Barre de recherche --}}
          <form method="GET" action="{{ route('admin.audit-logs.index') }}" id="filterForm" class="mb-3">
             <div class="row">

                <div class="col-1">
                    <div class="input-group input-group-sm">
                        <select name="per_page"
                                id="per_page"
                                class="form-control form-control-sm"
                                data-autosubmit>
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}" @selected($option == $perPage)>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                        <span class="input-group-text">{{ __('lignes') }}</span>
                    </div>
                </div>

                <div class="col-7">
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="{{ __('Rechercher dans les logs…') }}"
                               value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            {{ __('Rechercher') }}
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                                {{ __('Réinitialiser') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            </form>
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10"></th>
                        <th>id</th>
                        <th>{{ trans('cruds.auditLog.fields.description') }}</th>
                        <th>{{ trans('cruds.auditLog.fields.subject_type') }}</th>
                        <th>{{ trans('cruds.auditLog.fields.subject_id') }}</th>
                        <th>{{ trans('cruds.auditLog.fields.user_id') }}</th>
                        <th>{{ trans('cruds.auditLog.fields.host') }}</th>
                        <th>{{ trans('global.created_at') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr data-entry-id="{{ $log->id }}">
                            <td></td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}">{{ $log->id }}</a>
                            </td>
                            <td>{{ $log->description }}</td>
                            <td>{{ Str::afterLast($log->subject_type, '\\') }}</td>
                            <td>
                            @if($log->subject_id!==null)
                                <a href="{{ \App\Models\AuditLog::URL($log->subject_type, $log->subject_id) }}">
                                    {{ $log->subject_id }}
                                </a>
                            @else
                                N/A
                            @endif
                            </td>
                            <td>
                                @can('user_show')
                                    <a href="{{ route('admin.users.show', $log->user_id) }}">
                                        {{ $log->name }}
                                    </a>
                                @else
                                    {{ $log->name }}
                                @endcan
                            </td>
                            <td>{{ $log->host }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td nowrap>
                                <a class="btn btn-xs btn-primary" href="{{ route('admin.audit-logs.show', $log->id) }}">
                                    {{ trans('global.view') }}
                                </a>
                                @if($log->subject_id!==null)
                                <a class="btn btn-xs btn-secondary"
                                   href="{{ route('admin.audit-logs.history', ['type' => $log->subject_type, 'id' => $log->subject_id]) }}">
                                    {{ trans('global.history') }}
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Laravel (garde le paramètre search) --}}
            <div class="mt-3">
                {{ $logs->appends(['search' => request('search')])->links() }}
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable-paginate', array(
            'id' => '#dataTable',
            'order' => "[1, 'desc']"
        ))
    </script>
@endsection
