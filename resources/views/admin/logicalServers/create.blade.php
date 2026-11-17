@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.logicalServer.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.logical-servers.store") }}" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('global.create') }} {{ trans('cruds.logicalServer.title_singular') }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->hasMultiplePerimeters())
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', auth()->user()->activeOrDefaultPerimeterId()) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-1">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.logicalServer.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', '') }}" required maxlength="64"
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.logicalServer.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', '') }}" required maxlength="64"
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="operating_system">{{ trans('cruds.logicalServer.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="form-check">
                            <label for="active">{{ trans('cruds.logicalServer.fields.active') }}</label>
                            <div class="form-switch">
                                <input class="form-check-input" type="checkbox" id="active"
                                       name="active" {{ old('active', true) ? "checked" : "" }}>
                                <label class="form-check-label"
                                       for="active">{{ trans('cruds.logicalServer.fields.active_helper') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.logicalServer.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', []), true) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.attributes_helper') }}</span>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-9">
                        <div class="form-group">
                            <label for="description">{{ trans('cruds.logicalServer.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description" id="description">{{ old('description') }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="-1"
                                    data-default-img="{{ asset('images/lserver.png') }}"
                                    data-url-template="{{ route('admin.documents.show', ':id') }}"
                                    data-upload="#iconFile">
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="file" id="iconFile" name="iconFile" accept="image/png"/>
                        </div>
                    </div>
                </div>
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.logical_infrastructure.title_short") }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="operating_system">{{ trans('cruds.logicalServer.fields.operating_system') }}</label>
                            <select class="form-control select2-free {{ $errors->has('operating_system') ? 'is-invalid' : '' }}"
                                    name="operating_system" id="operating_system">
                                @if (!$operating_system_list->contains(old('operating_system')))
                                    <option> {{ old('operating_system') }}</option>
                                @endif
                                @foreach($operating_system_list as $t)
                                    <option {{ old('operating_system') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('operating_system'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('operating_system') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.operating_system_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="install_date">{{ trans('cruds.logicalServer.fields.install_date') }}</label>
                            <input class="date form-control" type="date" name="install_date" id="install_date"
                                   value="{{ old('install_date') }}">
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.install_date_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="update_date">{{ trans('cruds.logicalServer.fields.update_date') }}</label>
                            <input class="date form-control" type="date" id="update_date" name="update_date"
                                   value="{{ old('update_date') }}">
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.update_date_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label class="label-maturity-1"
                                       for="applications">{{ trans('cruds.logicalServer.fields.clusters') }}</label>
                                <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                        name="clusters[]" id="clusters" multiple>
                                    @foreach($clusters as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('clusters', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('clusters'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('clusters') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.clusters_helper') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="environment">{{ trans('cruds.logicalServer.fields.environment') }}</label>
                            <select class="form-control select2-free {{ $errors->has('environment') ? 'is-invalid' : '' }}"
                                    name="environment" id="environment">
                                @if (!$environment_list->contains(old('environment')))
                                    <option> {{ old('environment') }}</option>
                                @endif
                                @foreach($environment_list as $t)
                                    <option {{ old('environment') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('environment'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('environment') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalServer.fields.environment_helper') }}</span>
                        </div>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="address_ip">{{ trans('cruds.logicalServer.fields.address_ip') }}</label>
                                <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}"
                                       type="text"
                                       name="address_ip" id="address_ip" value="{{ old('address_ip', '') }}">
                                @if($errors->has('address_ip'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('address_ip') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.address_ip_helper') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg">
                            <div class="form-group">
                                <label for="net_services">{{ trans('cruds.logicalServer.fields.net_services') }}</label>
                                <input class="form-control {{ $errors->has('net_services') ? 'is-invalid' : '' }}"
                                       type="text" name="net_services" id="net_services"
                                       value="{{ old('net_services', '') }}">
                                @if($errors->has('net_services'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('net_services') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.net_services_helper') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-header">
                    {{ trans("cruds.logicalServer.fields.configuration") }}
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="cpu">{{ trans('cruds.logicalServer.fields.cpu') }}</label>
                                <input class="form-control {{ $errors->has('cpu') ? 'is-invalid' : '' }}" type="text"
                                       name="cpu" id="cpu" value="{{ old('cpu', '') }}">
                                @if($errors->has('cpu'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('cpu') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.cpu_helper') }}</span>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="memory">{{ trans('cruds.logicalServer.fields.memory') }}</label>
                                <input class="form-control {{ $errors->has('memory') ? 'is-invalid' : '' }}" type="text"
                                       name="memory" id="memory" value="{{ old('memory', '') }}">
                                @if($errors->has('memory'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('memory') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.memory_helper') }}</span>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="disk">{{ trans('cruds.logicalServer.fields.disk') }}</label>
                                <input class="form-control {{ $errors->has('disk') ? 'is-invalid' : '' }}" type="text"
                                       name="disk" id="disk" value="{{ old('disk', '') }}">
                                @if($errors->has('disk'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('disk') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.disk_helper') }}</span>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="disk">{{ trans('cruds.logicalServer.fields.disk_used') }}</label>
                                <input class="form-control {{ $errors->has('disk') ? 'is-invalid' : '' }}" type="text"
                                       name="disk_used" id="disk_used" value="{{ old('disk_used', '') }}">
                                @if($errors->has('disk_used'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('disk_used') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.disk_used_helper') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg">
                            <div class="form-group">
                                <label for="configuration">{{ trans('cruds.logicalServer.fields.configuration') }}</label>
                                <textarea
                                        class="form-control ckeditor {{ $errors->has('configuration') ? 'is-invalid' : '' }}"
                                        name="configuration" id="configuration">{{ old('configuration') }}</textarea>
                                @if($errors->has('configuration'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('configuration') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.configuration_helper') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-header">
                    {{ trans("cruds.menu.application.title_short") }}
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label class="label-maturity-1"
                                       for="applications">{{ trans('cruds.logicalServer.fields.applications') }}</label>
                                <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                        name="applications[]" id="applications" multiple>
                                    @foreach($applications as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('applications', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('applications'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('applications') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.applications_helper') }}</span>
                            </div>
                        </div>

                        <div class="col-sm">
                            <div class="form-group">
                                <label for="databases">{{ trans('cruds.logicalServer.fields.databases') }}</label>
                                <select class="form-control select2 {{ $errors->has('databases') ? 'is-invalid' : '' }}"
                                        name="databases[]" id="databases" multiple>
                                    @foreach($databases as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('databases', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('databases'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('databases') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.databases_helper') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-header">
                    {{ trans("cruds.menu.administration.title_short") }}
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="label-maturity-1"
                                       for="domain">{{ trans('cruds.logicalServer.fields.domain') }}</label>
                                <select class="form-control select2 {{ $errors->has('domains') ? 'is-invalid' : '' }}"
                                        name="domain_id" id="domain_id">
                                    <option></option>
                                    @foreach($domains as $id => $name)
                                        <option value="{{ $id }}" {{ $id==old('domain_id', '') ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('domain_id'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('domain_id') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.domain_helper') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-header">
                    {{ trans("cruds.menu.physical_infrastructure.title_short") }}
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="physicalServers">{{ trans('cruds.logicalServer.fields.servers') }}</label>
                                <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                    <span class="btn btn-info btn-xs deselect-all"
                                          style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                                </div>
                                <select class="form-control select2 {{ $errors->has('servers') ? 'is-invalid' : '' }}"
                                        name="physicalServers[]" id="physicalServers" multiple>
                                    @foreach($physicalServers as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('physicalServers', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('servers'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('servers') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.logicalServer.fields.servers_helper') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @can('backup_access')
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-header">
                    {{ trans("cruds.backup.title") }}
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="backup_ids">{{ trans('cruds.backup.title') }}</label>
                                <select class="form-control select2 {{ $errors->has('backup_ids') ? 'is-invalid' : '' }}"
                                        name="backup_ids[]" id="backup_ids" multiple>
                                    @foreach($backups as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('backup_ids', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('backup_ids'))
                                    <div class="invalid-feedback">{{ $errors->first('backup_ids') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="form-group">
                <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.logical-servers.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
                <button id="btn-save" class="btn btn-success" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
    </form>
@endsection

