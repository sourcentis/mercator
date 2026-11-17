@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $physicalServer->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.physical-servers.update", [$physicalServer->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.physicalServer.title_singular') }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($physicalServer))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $physicalServer->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.physicalServer.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $physicalServer->name) }}" required
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.physicalServer.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $physicalServer->name) }}" required
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.physicalServer.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $physicalServer->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.physicalServer.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, array_filter(explode(' ', (string) $physicalServer->attributes))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-9">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.physicalServer.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $physicalServer->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $physicalServer->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/server.png') }}"
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
                {{ trans('cruds.physicalServer.fields.configuration') }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="cpu">{{ trans('cruds.physicalServer.fields.cpu') }}</label>
                            <input class="form-control {{ $errors->has('cpu') ? 'is-invalid' : '' }}" type="text"
                                   name="cpu" id="cpu" value="{{ old('cpu', $physicalServer->cpu) }}">
                            @if($errors->has('cpu'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('cpu') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.cpu_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="memory">{{ trans('cruds.physicalServer.fields.memory') }}</label>
                            <input class="form-control {{ $errors->has('memory') ? 'is-invalid' : '' }}" type="text"
                                   name="memory" id="memory" value="{{ old('memory', $physicalServer->memory) }}">
                            @if($errors->has('memory'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('memory') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.memory_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="disk">{{ trans('cruds.physicalServer.fields.disk') }}</label>
                            <input class="form-control {{ $errors->has('disk') ? 'is-invalid' : '' }}" type="text"
                                   name="disk" id="disk" value="{{ old('disk', $physicalServer->disk) }}">
                            @if($errors->has('disk'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('disk') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.disk_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="disk_used">{{ trans('cruds.physicalServer.fields.disk_used') }}</label>
                            <input class="form-control {{ $errors->has('disk_used') ? 'is-invalid' : '' }}" type="text"
                                   name="disk_used" id="disk_used"
                                   value="{{ old('disk_used', $physicalServer->disk_used) }}">
                            @if($errors->has('disk_used'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('disk_used') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.disk_used_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="configuration">{{ trans('cruds.physicalServer.fields.configuration') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('configuration') ? 'is-invalid' : '' }}"
                                    name="configuration"
                                    id="configuration">{{ old('configuration', $physicalServer->configuration) }}</textarea>
                            @if($errors->has('configuration'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('configuration') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.configuration_helper') }}</span>
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
                    <div class="col-lg">
                        <div class="form-group">
                            <label for="applications">{{ trans('cruds.physicalServer.fields.applications') }}</label>
                            <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                    name="applications[]" id="applications" multiple>
                                @foreach($application_list as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('applications', [])) || $physicalServer->applications->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('applications'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('applications') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.applications_helper') }}</span>
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
                            <label
                                    for="operating_system">{{ trans('cruds.physicalServer.fields.operating_system') }}</label>
                            <select class="form-control select2-free {{ $errors->has('operating_system') ? 'is-invalid' : '' }}"
                                    name="operating_system" id="operating_system">
                                @if (!$operating_system_list->contains(old('operating_system')))
                                    <option> {{ old('operating_system') }}</option>
                                @endif
                                @foreach($operating_system_list as $t)
                                    <option {{ (old('operating_system') ? old('operating_system') : $physicalServer->operating_system) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('operating_system'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('operating_system') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.operating_system_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="install_date">{{ trans('cruds.physicalServer.fields.install_date') }}</label>
                            <input class="form-control datetime" type="date" name="install_date" id="install_date"
                                   value="{{ old('install_date', $physicalServer->install_date) }}">
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.install_date_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="update_date">{{ trans('cruds.physicalServer.fields.update_date') }}</label>
                            <input class="datetime form-control" type="date" id="update_date" name="update_date"
                                   value="{{ old('update_date', $physicalServer->update_date) }}">
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.update_date_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="responsible">{{ trans('cruds.physicalServer.fields.responsible') }}</label>
                            <select class="form-control select2-free {{ $errors->has('responsible') ? 'is-invalid' : '' }}"
                                    name="responsible" id="responsible">
                                @if (!$responsible_list->contains(old('responsible')))
                                    <option> {{ old('responsible') }}</option>
                                @endif
                                @foreach($responsible_list as $t)
                                    <option {{ (old('responsible') ? old('responsible') : $physicalServer->responsible) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('responsible'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('responsible') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.responsible_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label
                                    for="address_ip">{{ trans('cruds.physicalServer.fields.address_ip') }}</label>
                            <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text"
                                   name="address_ip" id="address_ip"
                                   value="{{ old('address_ip', $physicalServer->address_ip) }}">
                            @if($errors->has('address_ip'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('address_ip') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.address_ip_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="clusters">{{ trans('cruds.physicalServer.fields.clusters') }}</label>
                            <select class="form-control select2 {{ $errors->has('clusters') ? 'is-invalid' : '' }}"
                                    name="clusters[]" id="clusters" multiple>
                                @foreach($clusters as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('clusters', [])) || $physicalServer->clusters->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('clusters'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('clusters') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.clusters_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label for="applications">{{ trans('cruds.physicalServer.fields.logical_servers') }}</label>
                            <select class="form-control select2 {{ $errors->has('logicalServers') ? 'is-invalid' : '' }}"
                                    name="logicalServers[]" id="logicalServers" multiple>
                                @foreach($logical_server_list as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('logicalServers', [])) || $physicalServer->logicalServers->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('logicalServers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('logicalServers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.logical_servers_helper') }}</span>
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="site_id">{{ trans('cruds.physicalServer.fields.site') }}</label>
                            <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                    name="site_id" id="site_id">
                                <option></option>
                                @foreach($sites as $id => $site)
                                    <option value="{{ $id }}" {{ ($physicalServer->site ? $physicalServer->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $site }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.site_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label
                                   for="building_id">{{ trans('cruds.physicalServer.fields.building') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="building_id" id="building_id">
                                <option></option>
                                @foreach($buildings as $id => $building)
                                    <option value="{{ $id }}" {{ ($physicalServer->building ? $physicalServer->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $building }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('building'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('building') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.building_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bay_id">{{ trans('cruds.physicalServer.fields.bay') }}</label>
                            <select class="form-control select2 {{ $errors->has('bay') ? 'is-invalid' : '' }}"
                                    name="bay_id" id="bay_id">
                                <option></option>
                                @foreach($bays as $id => $bay)
                                    <option value="{{ $id }}" {{ ($physicalServer->bay ? $physicalServer->bay->id : old('bay_id')) == $id ? 'selected' : '' }}>{{ $bay }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('bay'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('bay') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalServer.fields.bay_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-servers.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection

@section('scripts')
    @parent
    @include('partials.location-cascade', [
        'buildingSiteMap' => $buildingSiteMap,
        'bayBuildingMap' => $bayBuildingMap ?? null,
    ])
@endsection
