@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $physicalSwitch->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.physical-switches.update", [$physicalSwitch->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.physicalSwitch.title_singular') }}
            </div>
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($physicalSwitch))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $physicalSwitch->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.physicalSwitch.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $physicalSwitch->name) }}" required
                                   maxlength="64" autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.name_helper') }}</span>
                        </div>

                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.physicalSwitch.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $physicalSwitch->name) }}" required
                                   maxlength="64" autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.name_helper') }}</span>
                        </div>

                    </div>
            @endif
                    <div class="col-sm-2">

                        <div class="form-group">
                            <label for="type"
                                   class="label-maturity-1">{{ trans('cruds.physicalSwitch.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $physicalSwitch->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.physicalSwitch.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, array_filter(explode(' ', (string) $physicalSwitch->attributes))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-9">

                        <div class="form-group">
                            <label for="description"
                                   class="label-maturity-1">{{ trans('cruds.physicalSwitch.fields.description') }}</label>
                            <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                      name="description"
                                      id="description">{{ old('description', $physicalSwitch->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.description_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $physicalSwitch->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/switch.png') }}"
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
                <div class="form-group">
                    <label for="databases">{{ trans('cruds.physicalSwitch.fields.network_switches') }}</label>
                    <select class="form-control select2 {{ $errors->has('physical_routers') ? 'is-invalid' : '' }}"
                            name="networkSwitches[]" id="networkSwitches" multiple>
                        @foreach($networkSwitches as $id => $name)
                            <option value="{{ $id }}" {{ (in_array($id, old('networkSwitches', [])) || $physicalSwitch->networkSwitches->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('networkSwitches'))
                        <div class="invalid-feedback">
                            {{ $errors->first('networkSwitches') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.physicalSwitch.fields.network_switches_helper') }}</span>
                </div>
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.physical_infrastructure.title_short") }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="site_id" class="label-maturity-1">{{ trans('cruds.physicalSwitch.fields.site') }}</label>
                            <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                    name="site_id" id="site_id">
                                @foreach($sites as $id => $site)
                                    <option value="{{ $id }}" {{ ($physicalSwitch->site ? $physicalSwitch->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $site }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.site_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="building_id">{{ trans('cruds.physicalSwitch.fields.building') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="building_id" id="building_id">
                                @foreach($buildings as $id => $building)
                                    <option value="{{ $id }}" {{ ($physicalSwitch->building ? $physicalSwitch->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $building }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('building'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('building') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.building_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="bay_id">{{ trans('cruds.physicalSwitch.fields.bay') }}</label>
                            <select class="form-control select2 {{ $errors->has('bay') ? 'is-invalid' : '' }}"
                                    name="bay_id" id="bay_id">
                                @foreach($bays as $id => $bay)
                                    <option value="{{ $id }}" {{ ($physicalSwitch->bay ? $physicalSwitch->bay->id : old('bay_id')) == $id ? 'selected' : '' }}>{{ $bay }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('bay'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('bay') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalSwitch.fields.bay_helper') }}</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-switches.index') }}">
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
