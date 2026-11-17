@extends('layouts.admin')

@section('title')
    {{ $user->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.users.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        @canEdit($user)
            <a class="btn btn-info" href="{{ route('admin.users.edit', $user->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('user_delete')
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                  data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.user.title') }}
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.user.fields.login') }}
                    </th>
                    <td>
                        {{ $user->login }}
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.user.fields.name') }}
                    </th>
                    <td>
                        {{ $user->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.user.fields.email') }}
                    </th>
                    <td>
                        {{ $user->email }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.user.fields.email_verified_at') }}
                    </th>
                    <td>
                        {{ $user->email_verified_at }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.user.fields.roles') }}
                    </th>
                    <td>
                        @foreach($user->roles as $key => $roles)
                            <span class="label label-info">{{ $roles->title }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.user.fields.granularity') }}
                    </th>
                    <td>
                        {{ $user->granularity }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>


    @php
        $directEntries = $user->cartographerEntries->filter(fn($e) => $e->cartographiable !== null);
        $allRoleEntries = $roleCartographers->filter(fn($e) => $e->cartographiable !== null);
        $total = $directEntries->count() + $allRoleEntries->count();
    @endphp

    @if($total > 0)
    <div class="card mt-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>
                <i class="bi bi-pin-map-fill me-2"></i>{{ trans('cruds.cartographer.title') }}
            </span>
            <small class="text-muted">{{ $total }} objet(s)</small>
        </div>


        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th>{{ trans('cruds.cartographer.fields.type') }}</th>
                        <th>{{ trans('cruds.cartographer.fields.object') }}</th>
                        <th>Via</th>
                        <th>{{ trans('cruds.cartographer.fields.last_updated') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($directEntries->sortBy('cartographiable_type') as $entry)
                    @php($showRoute = $routes[$entry->cartographiable_type] ?? null)
                    <tr>
                        <td>{{ $models[$entry->cartographiable_type] ?? $entry->cartographiable_type }}</td>
                        <td>
                            @if($showRoute)
                                <a href="{{ route($showRoute, $entry->cartographiable_id) }}">
                                    {{ $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')' }}
                                </a>
                            @else
                                {{ $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')' }}
                            @endif
                        </td>
                        <td><span class="badge bg-primary">direct</span></td>
                        <td>{{ $entry->cartographiable?->updated_at?->format(trans('global.timestamp')) ?? '-' }}</td>
                        <td>
                            @can('audit_log_show')
                                <a class="btn btn-xs btn-secondary"
                                   href="{{ route('admin.audit-logs.history', ['type' => $entry->cartographiable_type, 'id' => $entry->cartographiable_id]) }}">
                                    {{ trans('global.history') }}
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach

                    @foreach($allRoleEntries->sortBy('cartographiable_type') as $entry)
                    @php($showRoute = $routes[$entry->cartographiable_type] ?? null)
                    <tr>
                        <td>{{ $models[$entry->cartographiable_type] ?? $entry->cartographiable_type }}</td>
                        <td>
                            @if($showRoute)
                                <a href="{{ route($showRoute, $entry->cartographiable_id) }}">
                                    {{ $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')' }}
                                </a>
                            @else
                                {{ $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')' }}
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.roles.show', $entry->role) }}">
                            <span class="badge bg-secondary">
                                {{ $entry->role->title ?? trans('cruds.cartographer.fields.role') }}
                            </span>
                            </a>
                        </td>
                        <td>{{ $entry->cartographiable?->updated_at?->format(trans('global.timestamp')) ?? '-' }}</td>
                        <td>
                            @can('audit_log_show')
                                <a class="btn btn-xs btn-secondary"
                                   href="{{ route('admin.audit-logs.history', ['type' => $entry->cartographiable_type, 'id' => $entry->cartographiable_id]) }}">
                                    {{ trans('global.history') }}
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.users.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
