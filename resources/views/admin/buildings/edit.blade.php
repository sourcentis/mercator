@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $building->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.buildings.update", [$building->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.building.title_singular') }}
            </div>

            <div class="card-body">

                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($building))
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $building->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.building.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $building->name) }}" required
                                   maxlength="32" autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.building.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $building->name) }}" required
                                   maxlength="32" autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.building.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $building->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.type_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.building.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, explode(' ',$building->attributes)) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.building.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $building->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $building->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/building.png') }}"
                                    data-url-template="{{ route('admin.documents.show', ':id') }}"
                                    data-upload="#iconFile">
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="file" id="iconFile" name="iconFile" accept="image/png"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1" for="site_id">{{ trans('cruds.building.fields.site') }}</label>
                            <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                    name="site_id"
                                    id="site_id">
                                <option value="">{{ trans('global.pleaseSelect') }}</option>
                                @foreach($sites as $id => $name)
                                    <option value="{{ $id }}" {{ ($building->site ? $building->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.site_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="building_id">{{ trans('cruds.building.fields.parent') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="building_id"
                                    id="building_id">
                                <option value="">{{ trans('global.pleaseSelect') }}</option>
                                @foreach($buildings as $id => $name)
                                    <option value="{{ $id }}" {{ ($building->building ? $building->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('building_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('building_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.parent_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="buildings">{{ trans('cruds.building.fields.children') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="buildings[]"
                                    id="buildings[]" multiple>
                                <option value="">{{ trans('global.pleaseSelect') }}</option>
                                @foreach($buildings as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('buildings', [])) || $building->buildings->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('buildings'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('buildings') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.building.fields.children_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.buildings.index') }}">
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
    @include('admin.buildings._site-scope-filter', [
        'buildingSiteMap' => $buildingSiteMap,
    ])
@endsection
