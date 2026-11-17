@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $physicalSecurityDevice->name }}
@endsection

@section('content')

    <form method="POST" action="{{ route("admin.physical-security-devices.update", [$physicalSecurityDevice->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.physicalSecurityDevice.title_singular') }}
            </div>
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($physicalSecurityDevice))
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $physicalSecurityDevice->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                        <div class="form-group">
                            <label class="label-required"
                                   for="name">{{ trans('cruds.physicalSecurityDevice.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $physicalSecurityDevice->name) }}"
                                   required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-required"
                                   for="name">{{ trans('cruds.physicalSecurityDevice.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $physicalSecurityDevice->name) }}"
                                   required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="type">{{ trans('cruds.physicalSecurityDevice.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $physicalSecurityDevice->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.physicalSecurityDevice.fields.attributes') }}</label>
                            <select class="form-control select2-free {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes[]" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, explode(' ', $physicalSecurityDevice->attributes)) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-9">

                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.physicalSecurityDevice.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $physicalSecurityDevice->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.description_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $physicalSecurityDevice->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/securitydevice.png') }}"
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
                    <div class="col-md-8">

                        <div class="form-group">
                            <label for="security_devices">{{ trans('cruds.physicalSecurityDevice.fields.security_devices') }}</label>
                            <select class="form-control select2 {{ $errors->has('security_devices') ? 'is-invalid' : '' }}"
                                    name="security_devices[]" id="security_devices" multiple>
                                @foreach($securityDevices as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('security_devices', [])) || $physicalSecurityDevice->securityDevices->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('security_devices'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('security_devices') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.security_devices_helper') }}</span>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="address_ip">{{ trans('cruds.physicalSecurityDevice.fields.address_ip') }}</label>
                            <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text"
                                   name="address_ip" id="address_ip"
                                   value="{{ old('address_ip', $physicalSecurityDevice->address_ip) }}">
                            @if($errors->has('address_ip'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('address_ip') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.address_ip_helper') }}</span>
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
                                   for="site_id">{{ trans('cruds.physicalSecurityDevice.fields.site') }}</label>
                            <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                    name="site_id" id="site_id">
                                @foreach($sites as $id => $site)
                                    <option value="{{ $id }}" {{ ($physicalSecurityDevice->site ? $physicalSecurityDevice->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $site }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.site_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="building_id">{{ trans('cruds.physicalSecurityDevice.fields.building') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="building_id" id="building_id">
                                @foreach($buildings as $id => $building)
                                    <option value="{{ $id }}" {{ ($physicalSecurityDevice->building ? $physicalSecurityDevice->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $building }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('building'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('building') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.building_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bay_id">{{ trans('cruds.physicalSecurityDevice.fields.bay') }}</label>
                            <select class="form-control select2 {{ $errors->has('bay') ? 'is-invalid' : '' }}"
                                    name="bay_id" id="bay_id">
                                @foreach($bays as $id => $bay)
                                    <option value="{{ $id }}" {{ ($physicalSecurityDevice->bay ? $physicalSecurityDevice->bay->id : old('bay_id')) == $id ? 'selected' : '' }}>{{ $bay }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('bay'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('bay') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSecurityDevice.fields.bay_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-security-devices.index') }}">
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
