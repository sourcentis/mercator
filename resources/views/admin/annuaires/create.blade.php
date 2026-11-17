@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.annuaire.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.annuaires.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.create') }} {{ trans('cruds.annuaire.title_singular') }}
            </div>
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
                <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.annuaire.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', '') }}" maxlength="32" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.annuaire.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.annuaire.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', '') }}" maxlength="32" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.annuaire.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.annuaire.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                <option></option>
                                @if (!$type_list->contains(old('type', '')))
                                    <option>{{ old('type', '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.annuaire.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.annuaire.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', [])) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.annuaire.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="description">{{ trans('cruds.annuaire.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description" id="description">{{ old('description') }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.annuaire.fields.description_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1" for="solution">{{ trans('cruds.annuaire.fields.solution') }}</label>
                    <input class="form-control {{ $errors->has('solution') ? 'is-invalid' : '' }}" type="text"
                           name="solution" id="solution" value="{{ old('solution', '') }}">
                    @if($errors->has('solution'))
                        <div class="invalid-feedback">
                            {{ $errors->first('solution') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.annuaire.fields.solution_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="zone_admin_id">{{ trans('cruds.annuaire.fields.zone_admin') }}</label>
                    <select class="form-control select2 {{ $errors->has('zone_admin') ? 'is-invalid' : '' }}"
                            name="zone_admin_id" id="zone_admin_id">
                        @foreach($zone_admins as $id => $zone_admin)
                            <option value="{{ $id }}" {{ old('zone_admin_id') == $id ? 'selected' : '' }}>{{ $zone_admin }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('zone_admin'))
                        <div class="invalid-feedback">
                            {{ $errors->first('zone_admin') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.annuaire.fields.zone_admin_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-2"
                           for="application_id">{{ trans('cruds.annuaire.fields.application') }}</label>
                    <select class="form-control select2 {{ $errors->has('application_id') ? 'is-invalid' : '' }}"
                            name="application_id" id="application_id">
                        @foreach($applications as $id => $application)
                            <option value="{{ $id }}" {{ old('application_id') == $id ? 'selected' : '' }}>{{ $application }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('application_id'))
                        <div class="invalid-feedback">
                            {{ $errors->first('application_id') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.annuaire.fields.application_helper') }}</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.annuaires.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
