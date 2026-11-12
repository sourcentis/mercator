@extends('layouts.admin')

@section('title')
    {{ trans('cruds.module.title') }}
@endsection

@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-6">
        <a id="btn-new" class="btn btn-success" href="{{ route('admin.modules.check') }}">
            Check Licence
        </a>
    </div>
</div>

    <div class="card">
        <div class="card-header">
            License
        </div>
        <div class="card-body">
            <div class="col-4">
                <table class="table table-bordered">
                <tr><th>Status</th><td>{{ $license['status'] }}</td></tr>
                <tr><th>Key</th><td>{{ $license['license_key'] }}</td></tr>
                <tr><th>Type</th><td>{{ $license['type'] }}</td></tr>
                <tr><th>Issued Date</th><td>{{ $license['issued_date'] }}</td></tr>
                <tr><th>Expiration Date</th><td>{{ $license ['expiration_date'] ?? 'Never' }}</td></tr>
                <tr><th>Days Until Expiration </th><td>{{ $license['days_until_expiration'] ?? 'N/A' }}</td></tr>
                <tr><th>Modules</th><td>{{ implode(', ', $license['modules']) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <br>
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.module.title') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th>{{ trans('cruds.module.fields.name') }}</th>
                        <th>{{ trans('cruds.module.fields.label') }}</th>
                        <th>{{ trans('cruds.module.fields.package') }}</th>
                        <th>{{ trans('cruds.module.fields.package_version') }}</th>
                        <th>{{ trans('cruds.module.fields.db_version') }}</th>
                        <th>{{ trans('cruds.module.fields.installed') }}</th>
                        <th>{{ trans('cruds.module.fields.activated') }}</th>
                        <th class="text-end">{{ trans('cruds.module.fields.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($modules as $module)
                        <tr>
                            <td>{{ $module['name'] }}</td>
                            <td>{{ $module['label'] }}</td>
                            <td><code>{{ $module['package'] }}</code></td>
                            <td>{{ $module['version'] ?? '—' }}</td>
                            <td>{{ $module['db_version'] ?? '—' }}</td>
                            <td>
                                @if($module['installed'])
                                    <span class="badge bg-success">{{ trans('cruds.module.labels.installed') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ trans('cruds.module.labels.not_installed') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($module['installed'])
                                    @if($module['enabled'])
                                        <span class="badge bg-secondary">{{ trans('cruds.module.labels.activated') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ trans('cruds.module.labels.deactivated') }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                {{-- Installer --}}
                                @unless($module['installed'])
                                    <form action="{{ route('admin.modules.install', $module['name']) }}" method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">{{ trans('cruds.module.labels.install') }}</button>
                                    </form>
                                @endunless
                                {{-- Activer / désactiver --}}
                                @if($module['installed'])
                                    @if(!$module['enabled'])
                                        <form action="{{ route('admin.modules.enable', $module['name']) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success">{{ trans('cruds.module.labels.activate') }}</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.modules.disable', $module['name']) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-warning">{{ trans('cruds.module.labels.deactivate') }}</button>
                                        </form>
                                    @endif
                                @endif

                                {{-- Désinstaller --}}
                                @if($module['installed'])
                                    <form action="{{ route('admin.modules.uninstall', $module['name']) }}" method="POST"
                                          class="d-inline"
                                          data-confirm="{{ trans('cruds.module.confirm_uninstall') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ trans('cruds.module.labels.uninstall') }}</button>
                                    </form>
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
