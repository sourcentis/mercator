@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $subnetwork->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.subnetworks.update", [$subnetwork->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.subnetwork.title_singular') }}
            </div>
            <div class="card-body">
                    <div class="row">
                        @if (auth()->user()->canChoosePerimeterOf($subnetwork))
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $subnetwork->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                            <div class="form-group">
                                <label class="label-required" for="name">{{ trans('cruds.subnetwork.fields.name') }}</label>
                                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                       name="name"
                                       id="name" value="{{ old('name', $subnetwork->name) }}" required autofocus/>
                                @if($errors->has('name'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.subnetwork.fields.name_helper') }}</span>
                            </div>
                        </div>
            @else
                <div class="col-md-5">
                            <div class="form-group">
                                <label class="label-required" for="name">{{ trans('cruds.subnetwork.fields.name') }}</label>
                                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                       name="name"
                                       id="name" value="{{ old('name', $subnetwork->name) }}" required autofocus/>
                                @if($errors->has('name'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.subnetwork.fields.name_helper') }}</span>
                            </div>
                        </div>
            @endif

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type">{{ trans('cruds.subnetwork.fields.type') }}</label>
                                <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                        name="type" id="type">
                                    @if (!$type_list->contains(old('type')))
                                        <option> {{ old('type') }}</option>
                                    @endif
                                    @foreach($type_list as $t)
                                        <option {{ (old('type') ? old('type') : $subnetwork->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('type'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('type') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.subnetwork.fields.type_helper') }}</span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="attributes">{{ trans('cruds.subnetwork.fields.attributes') }}</label>
                                <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                        name="attributes[]" id="attributes" multiple>
                                    @foreach($attributes_list as $a)
                                        <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, explode(' ',$subnetwork->attributes)) ? 'selected' : '' }}>{{$a}}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('attributes'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('attributes') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.subnetwork.fields.attributes_helper') }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.subnetwork.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description" rows="4"
                                    id="description">{{ old('description', $subnetwork->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.description_helper') }}</span>
                        </div>
                    </div>
                </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.subnetwork.title_connexion') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="network_id">{{ trans('cruds.subnetwork.fields.network') }}</label>
                            <select class="form-control select2 {{ $errors->has('network_id') ? 'is-invalid' : '' }}"
                                    name="network_id" id="network_id">
                                @foreach($networks as $id => $name)
                                    <option value="{{ $id }}" {{ ($subnetwork->network ? $subnetwork->network->id : old('network_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('network_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('network_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.network_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="subnetwork_id">{{ trans('cruds.subnetwork.fields.subnetwork') }}</label>
                            <select class="form-control select2 {{ $errors->has('subnetwork_id') ? 'is-invalid' : '' }}"
                                    name="subnetwork_id" id="subnetwork_id">
                                <option></option>
                                @foreach($subnetworks as $id => $name)
                                    <option value="{{ $id }}" {{ ($subnetwork->subnetwork ? $subnetwork->subnetwork->id : old('subnetwork_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('subnetwork_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('subnetwork_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.subnetwork_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.subnetwork.title_addressing') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="address">{{ trans('cruds.subnetwork.fields.address') }}</label>
                            <input class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                   type="text" name="address" id="address"
                                   value="{{ old('address', $subnetwork->address) }}">
                            @if($errors->has('address'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('address') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.address_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="label-maturity-1" for="default_gateway">
                                {{ trans('cruds.subnetwork.fields.default_gateway') }}
                            </label>
                            <input
                                    class="form-control {{ $errors->has('default_gateway') ? 'is-invalid' : '' }}"
                                    type="text"
                                    name="default_gateway"
                                    id="default_gateway"
                                    value="{{ old('default_gateway', $subnetwork->default_gateway) }}"
                                    placeholder="{{ __('ex: 10.42.0.1') }}"
                            />
                            @if($errors->has('default_gateway'))
                                <div class="invalid-feedback">{{ $errors->first('default_gateway') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.default_gateway_helper') }}</span>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gateway_id">{{ trans('cruds.subnetwork.fields.gateway') }}</label>
                            <select class="form-control select2 {{ $errors->has('gateway_id') ? 'is-invalid' : '' }}"
                                    name="gateway_id" id="gateway_id">
                                @foreach($gateways as $id => $gateway)
                                    <option value="{{ $id }}" {{ ($subnetwork->gateway ? $subnetwork->gateway->id : old('gateway_id')) == $id ? 'selected' : '' }}>{{ $gateway }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('gateway_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('gateway_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.gateway_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="form-group">
                                <label class="label-maturity-1"
                                       for="vlan_id">{{ trans('cruds.subnetwork.fields.vlan') }}</label>
                                <select class="form-control select2 {{ $errors->has('vlan_id') ? 'is-invalid' : '' }}"
                                        name="vlan_id" id="vlan_id">
                                    @foreach($vlans as $id => $vlan)
                                        <option value="{{ $id }}" {{ ($subnetwork->vlan ? $subnetwork->vlan->id : old('vlan_id')) == $id ? 'selected' : '' }}>{{ $vlan }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('vlan_id'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('vlan_id') }}
                                    </div>
                                @endif
                                <span class="help-block">{{ trans('cruds.subnetwork.fields.vlan_helper') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="ip_allocation_type">{{ trans('cruds.subnetwork.fields.ip_allocation_type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('ip_allocation_type') ? 'is-invalid' : '' }}"
                                    name="ip_allocation_type" id="ip_allocation_type">
                                @if (!$ip_allocation_type_list->contains(old('ip_allocation_type')))
                                    <option> {{ old('ip_allocation_type') }}</option>
                                @endif
                                @foreach($ip_allocation_type_list as $t)
                                    <option {{ (old('ip_allocation_type') ? old('ip_allocation_type') : $subnetwork->ip_allocation_type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('ip_allocation_type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('ip_allocation_type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.ip_allocation_type_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.subnetwork.title_classification') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="zone">{{ trans('cruds.subnetwork.fields.zone') }}</label>
                            <select class="form-control select2-free {{ $errors->has('zone') ? 'is-invalid' : '' }}"
                                    name="zone" id="zone">
                                @if (!$zone_list->contains(old('zone')))
                                    <option> {{ old('zone') }}</option>
                                @endif
                                @foreach($zone_list as $z)
                                    <option {{ (old('zone') ? old('zone') : $subnetwork->zone) == $z ? 'selected' : '' }}>{{$z}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('zone'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('zone') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.zone_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="dmz">{{ trans('cruds.subnetwork.fields.dmz') }}</label>
                            <select class="form-control select2-free {{ $errors->has('dmz') ? 'is-invalid' : '' }}"
                                    name="dmz" id="dmz">
                                @if (!$dmz_list->contains(old('dmz')))
                                    <option> {{ old('dmz') }}</option>
                                @endif
                                @foreach($dmz_list as $z)
                                    <option {{ (old('dmz') ? old('dmz') : $subnetwork->dmz) == $z ? 'selected' : '' }}>{{$z}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('dmz'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dmz') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.dmz_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="wifi">{{ trans('cruds.subnetwork.fields.wifi') }}</label>
                            <select class="form-control select2-free {{ $errors->has('wifi') ? 'is-invalid' : '' }}"
                                    name="wifi" id="wifi">
                                @if (!$wifi_list->contains(old('wifi')))
                                    <option> {{ old('wifi') }}</option>
                                @endif
                                @foreach($wifi_list as $w)
                                    <option {{ (old('wifi') ? old('wifi') : $subnetwork->wifi) == $w ? 'selected' : '' }}>{{$w}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('wifi'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('wifi') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.wifi_helper') }}</span>
                        </div>
                    </div>

                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.subnetwork.title_governance') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="responsible_exp">{{ trans('cruds.subnetwork.fields.responsible_exp') }}</label>
                            <select class="form-control select2-free {{ $errors->has('responsible_exp') ? 'is-invalid' : '' }}"
                                    name="responsible_exp" id="responsible_exp">
                                @if (!$responsible_exp_list->contains(old('responsible_exp')))
                                    <option> {{ old('responsible_exp') }}</option>
                                @endif
                                @foreach($responsible_exp_list as $t)
                                    <option {{ (old('responsible_exp') ? old('responsible_exp') : $subnetwork->responsible_exp) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('responsible_exp'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('responsible_exp') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.subnetwork.fields.responsible_exp_helper') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.subnetworks.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
