@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $workstation->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.workstations.update", [$workstation->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.workstation.title_singular') }}
            </div>
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($workstation))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $workstation->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.workstation.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $workstation->name) }}" required
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.workstation.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $workstation->name) }}" required
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.workstation.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $workstation->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.workstation.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, array_filter(explode(' ', (string) $workstation->attributes))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label class="label-maturity-1"
                                       for="description">{{ trans('cruds.workstation.fields.description') }}</label>
                                <textarea
                                        class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                        name="description"
                                        id="description">{{ old('description', $workstation->description) }}</textarea>
                                @if($errors->has('description'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('description') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                                <select id="iconSelect"
                                        name="iconSelect"
                                        class="form-control js-icon-picker"
                                        data-icons='@json($icons)'
                                        data-selected="{{ $workstation->icon_id ?? '-1' }}"
                                        data-default-img="{{ asset('images/workstation.png') }}"
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
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                Model / Configuration
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.workstation.fields.manufacturer') }}</label>
                            <select class="form-control select2-free {{ $errors->has('manufacturer') ? 'is-invalid' : '' }}"
                                    name="manufacturer" id="manufacturer">
                                @if (!$type_list->contains(old('manufacturer')))
                                    <option> {{ old('manufacturer') }}</option>
                                @endif
                                @foreach($manufacturer_list as $manufacturer)
                                    <option {{ (old('manufacturer') ? old('manufacturer') : $workstation->manufacturer) == $manufacturer ? 'selected' : '' }}>{{$manufacturer}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('manufacturer'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('manufacturer') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.manufacturer_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.workstation.fields.model') }}</label>
                            <select class="form-control select2-free {{ $errors->has('model') ? 'is-invalid' : '' }}"
                                    name="model" id="model">
                                @if (!$model_list->contains(old('model')))
                                    <option> {{ old('model') }}</option>
                                @endif
                                @foreach($model_list as $model)
                                    <option {{ (old('model') ? old('model') : $workstation->model) == $model ? 'selected' : '' }}>{{$model}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('model'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('model') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.model_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="serial_number">{{ trans('cruds.workstation.fields.serial_number') }}</label>
                            <input class="form-control {{ $errors->has('serial_number') ? 'is-invalid' : '' }}"
                                   type="text" name="serial_number" id="serial_number"
                                   value="{{ old('serial_number', $workstation->serial_number) }}"/>
                            @if($errors->has('serial_number'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('serial_number') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.serial_number_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="cpu">{{ trans('cruds.workstation.fields.cpu') }}</label>
                            <select class="form-control select2-free {{ $errors->has('cpu') ? 'is-invalid' : '' }}"
                                    name="cpu" id="cpu">
                                @if (!$type_list->contains(old('cpu')))
                                    <option> {{ old('cpu') }}</option>
                                @endif
                                @foreach($cpu_list as $c)
                                    <option {{ (old('cpu') ? old('cpu') : $workstation->cpu) == $c ? 'selected' : '' }}>{{$c}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('cpu'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('cpu') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.cpu_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="memory">{{ trans('cruds.workstation.fields.memory') }}</label>
                            <input class="form-control {{ $errors->has('memory') ? 'is-invalid' : '' }}" type="text"
                                   name="memory" id="memory" value="{{ old('memory', $workstation->memory) }}">
                            @if($errors->has('memory'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('memory') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.memory_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="disk">{{ trans('cruds.workstation.fields.disk') }}</label>
                            <input class="form-control {{ $errors->has('disk') ? 'is-invalid' : '' }}" type="text"
                                   name="disk" id="disk" value="{{ old('disk', $workstation->disk) }}">
                            @if($errors->has('disk'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('disk') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.disk_helper') }}</span>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="entity_id">{{ trans('cruds.workstation.fields.entity') }}</label>
                            <select class="form-control select2 {{ $errors->has('entity') ? 'is-invalid' : '' }}"
                                    name="entity_id" id="entity_id">
                                @foreach($entities as $id => $name)
                                    <option value="{{ $id }}" {{ ($workstation->entity ? $workstation->entity->id : old("entity_id")) == $id ? "selected" : " " }} > {{ $name }} </option>
                                @endforeach
                            </select>
                            @if($errors->has('entity'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('entity') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.entity_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="entity_id">{{ trans('cruds.workstation.fields.domain') }}</label>
                            <select class="form-control select2 {{ $errors->has('domain') ? 'is-invalid' : '' }}"
                                    name="domain_id" id="domain_id">
                                @foreach($domains as $id => $name)
                                    <option value="{{ $id }}" {{ ($workstation->domain ? $workstation->domain->id : old("domain_id")) == $id ? "selected" : " " }} > {{ $name }} </option>
                                @endforeach
                            </select>
                            @if($errors->has('domain'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('domain') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.domain_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="entity_id">{{ trans('cruds.workstation.fields.user') }}</label>
                            <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}"
                                    name="user_id" id="user_id">
                                @foreach($users as $id => $user_id)
                                    <option value="{{ $id }}" {{ ($workstation->user ? $workstation->user->id : old("user_id")) == $id ? "selected" : " " }} > {{ $user_id }} </option>
                                @endforeach
                            </select>
                            @if($errors->has('user_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('user_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.user_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="memory">{{ trans('cruds.workstation.fields.other_user') }}</label>
                            <input class="form-control {{ $errors->has('other_user') ? 'is-invalid' : '' }}" type="text"
                                   name="other_user" id="other_user"
                                   value="{{ old('other_user', $workstation->other_user) }}">
                            @if($errors->has('other_user'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('other_user') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.other_user_helper') }}</span>
                        </div>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="status">{{ trans('cruds.workstation.fields.status') }}</label>
                                <select class="form-control select2-free {{ $errors->has('status') ? 'is-invalid' : '' }}"
                                        name="status" id="status">
                                    @if (!$type_list->contains(old('status')))
                                        <option> {{ old('status') }}</option>
                                    @endif
                                    @foreach($status_list as $status)
                                        <option {{ (old('status') ? old('status') : $workstation->status) == $status ? 'selected' : '' }}>{{$status}}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('status'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('status') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.workstation.fields.status_helper') }}</span>
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
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="operating_system">{{ trans('cruds.workstation.fields.operating_system') }}</label>
                            <select class="form-control select2-free {{ $errors->has('operating_system') ? 'is-invalid' : '' }}"
                                    name="operating_system" id="operating_system">
                                @if (!$type_list->contains(old('operating_system')))
                                    <option> {{ old('operating_system') }}</option>
                                @endif
                                @foreach($operating_system_list as $os)
                                    <option {{ (old('operating_system') ? old('operating_system') : $workstation->operating_system) == $os ? 'selected' : '' }}>{{$os}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('operating_system'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('operating_system') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.operating_system_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg">
                        <div class="form-group">
                            <label for="applications">{{ trans('cruds.workstation.fields.applications') }}</label>
                            <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                    name="applications[]" id="applications" multiple>
                                @foreach($application_list as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('applications', [])) || $workstation->applications->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('applications'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('applications') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.applications_helper') }}</span>
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
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="network_id">{{ trans('cruds.workstation.fields.network') }}</label>
                            <select class="form-control select2 {{ $errors->has('entity') ? 'is-invalid' : '' }}"
                                    name="network_id" id="network_id">
                                @foreach($networks as $id => $name)
                                    <option value="{{ $id }}" {{ ($workstation->network ? $workstation->network->id : old("network_id")) == $id ? "selected" : " " }} > {{ $name }} </option>
                                @endforeach
                            </select>
                            @if($errors->has('network'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('network') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.network_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="address_ip">{{ trans('cruds.workstation.fields.address_ip') }}</label>
                            <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text"
                                   name="address_ip" id="address_ip"
                                   value="{{ old('address_ip', $workstation->address_ip) }}">
                            @if($errors->has('address_ip'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('address_ip') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.address_ip_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="address_ip">{{ trans('cruds.workstation.fields.mac_address') }}</label>
                            <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text"
                                   name="mac_address" id="mac_address"
                                   value="{{ old('mac_address', $workstation->mac_address) }}">
                            @if($errors->has('mac_address'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('mac_address') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.mac_address_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="operating_system">{{ trans('cruds.workstation.fields.network_port_type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('network_port_type') ? 'is-invalid' : '' }}"
                                    name="network_port_type" id="network_port_type">
                                @if (!$type_list->contains(old('network_port_type')))
                                    <option> {{ old('network_port_type') }}</option>
                                @endif
                                @foreach($network_port_type_list as $port)
                                    <option {{ (old('network_port_type') ? old('network_port_type') : $workstation->network_port_type) == $port ? 'selected' : '' }}>{{$port}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('network_port_type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('network_port_type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.network_port_type_helper') }}</span>
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
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="site_id">{{ trans('cruds.workstation.fields.site') }}</label>
                            <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                    name="site_id" id="site_id">
                                @foreach($sites as $id => $site)
                                    <option value="{{ $id }}" {{ ($workstation->site ? $workstation->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $site }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.site_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="building_id">{{ trans('cruds.workstation.fields.building') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="building_id" id="building_id">
                                @foreach($buildings as $id => $building)
                                    <option value="{{ $id }}" {{ ($workstation->building ? $workstation->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $building }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('building'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('building') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.workstation.fields.building_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.workstations.index') }}">
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
    ])
@endsection

