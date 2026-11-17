@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $networkSwitch->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.network-switches.update", [$networkSwitch->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.networkSwitch.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($networkSwitch))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $networkSwitch->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.networkSwitch.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name"
                                   id="name" value="{{ old('name', $networkSwitch->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.networkSwitch.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.networkSwitch.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name"
                                   id="name" value="{{ old('name', $networkSwitch->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.networkSwitch.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.networkSwitch.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $networkSwitch->type ?? '')))
                                    <option>{{ old('type', $networkSwitch->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $networkSwitch->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.networkSwitch.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.networkSwitch.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $networkSwitch->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.networkSwitch.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label class="label-maturity-1"
                               for="description">{{ trans('cruds.networkSwitch.fields.description') }}</label>
                        <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                  name="description"
                                  id="description">{{ old('description', $networkSwitch->description) }}</textarea>
                        @if($errors->has('description'))
                            <div class="invalid-feedback">
                                {{ $errors->first('description') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.networkSwitch.fields.description_helper') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="vlans">{{ trans('cruds.networkSwitch.fields.vlans') }}</label>
                            <select class="form-control select2 {{ $errors->has('vlans') ? 'is-invalid' : '' }}"
                                    name="vlans[]" id="vlans" multiple>
                                @foreach($vlans as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('vlans', [])) || $networkSwitch->vlans->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('vlans'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('vlans') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.networkSwitch.fields.vlans_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="ip">{{ trans('cruds.networkSwitch.fields.ip') }}</label>
                            <input class="form-control {{ $errors->has('ip') ? 'is-invalid' : '' }}" type="text"
                                   name="ip"
                                   id="ip" value="{{ old('ip', $networkSwitch->ip) }}">
                            @if($errors->has('ip'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('ip') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.networkSwitch.fields.ip_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="physicalSwitches">{{ trans('cruds.networkSwitch.fields.physical_switches') }}</label>
                        <select class="form-control select2 {{ $errors->has('physicalSwitches') ? 'is-invalid' : '' }}"
                                name="physicalSwitches[]" id="physicalSwitches" multiple>
                            @foreach($physicalSwitches as $id => $name)
                                <option value="{{ $id }}" {{ (in_array($id, old('physicalSwitches', [])) || $networkSwitch->physicalSwitches->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('physicalSwitches'))
                            <div class="invalid-feedback">
                                {{ $errors->first('physicalSwitches') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.networkSwitch.fields.physical_switches_helper') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.network-switches.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
