@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.certificate.title_singular') }}
@endsection

@section('content')
<form method="POST" action="{{ route("admin.certificates.store") }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header">
            {{ trans('global.create') }} {{ trans('cruds.certificate.title_singular') }}
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
                    <label class="label-required" for="name">{{ trans('cruds.certificate.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required autofocus/>
                    @if($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.certificate.fields.name_helper') }}</span>
                </div>
            </div>
            @else
                <div class="col-sm-5">
                <div class="form-group">
                    <label class="label-required" for="name">{{ trans('cruds.certificate.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required autofocus/>
                    @if($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.certificate.fields.name_helper') }}</span>
                </div>
            </div>
            @endif
            <div class="col-sm-2">
                <div class="form-group">
                    <label class="label-maturity-2" for="type">{{ trans('cruds.certificate.fields.type') }}</label>
                    <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}" name="type" id="type">
                        <option></option>
                        @foreach($type_list as $t)
                            <option {{ old('type') == $t ? 'selected' : '' }}>{{$t}}</option>
                        @endforeach
                        @if (!$type_list->contains(old('type')))
                            <option {{ old('type') ? 'selected' : ''}}> {{ old('type') }}</option>
                        @endif
                    </select>

                    @if($errors->has('type'))
                        <div class="invalid-feedback">
                            {{ $errors->first('type') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.certificate.fields.type_helper') }}</span>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    <label for="attributes">{{ trans('cruds.certificate.fields.attributes') }}</label>
                    <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                            name="attributes[]" id="attributes" multiple>
                        @foreach($attributes_list as $a)
                            <option {{ in_array($a, old('attributes', [])) ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('attributes'))
                        <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                    @endif
                    <span class="help-block">{{ trans('cruds.certificate.fields.attributes_helper') }}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label class="label-maturity-2" for="description">{{ trans('cruds.certificate.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description') }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.certificate.fields.description_helper') }}</span>
                </div>
            </div>
        </div>
            <div class="row">
                <div class="col-3">
                     <div class="form-group">
                        <label class="label-maturity-2" for="start_validity">{{ trans('cruds.certificate.fields.start_validity') }}</label>
                        <input class="form-control date-input" type="date" name="start_validity" id="start_validity" value="{{ old('start_validity') }}">
                        <span class="help-block">{{ trans('cruds.certificate.fields.start_validity_helper') }}</span>
                    </div>
                </div>
                <div class="col-3">
                     <div class="form-group">
                        <label class="label-maturity-2" for="end_validity">{{ trans('cruds.certificate.fields.end_validity') }}</label>
                        <input class="form-control date-input" type="date" name="end_validity" id="end_validity" value="{{ old('end_validity') }}">
                        <span class="help-block">{{ trans('cruds.certificate.fields.end_validity_helper') }}</span>
                    </div>
                </div>
                <div class="col-3">
                     <div class="form-group">
                        <label for="status">{{ trans('cruds.certificate.fields.status') }}</label>

                        <select class="form-control select2 {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status">
                            <option></option>
                            <option value="0" {{ old('status')==0 ? 'selected' : '' }}>{{ trans('cruds.certificate.fields.status_good') }}</option>
                            <option value="1" {{ old('status')==1 ? 'selected' : '' }}>{{ trans('cruds.certificate.fields.status_revoked') }}</option>
                            <option value="2" {{ old('status')==2 ? 'selected' : '' }}>{{ trans('cruds.certificate.fields.status_unknown') }}</option>
                        </select>

                        <span class="help-block">{{ trans('cruds.certificate.fields.status_helper') }}</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="label-maturity-2" for="logicalServers">{{ trans('cruds.certificate.fields.logical_servers') }}</label>
                <select class="form-control select2 {{ $errors->has('logicalServers') ? 'is-invalid' : '' }}" name="logicalServers[]" id="logicalServers" multiple>
                    @foreach($logicalServers as $id => $name)
                        <option value="{{ $id }}" {{ in_array($id, old('logicalServers', [])) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @if($errors->has('logicalServers'))
                    <div class="invalid-feedback">
                        {{ $errors->first('logicalServers') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.certificate.fields.logical_servers_helper') }}</span>
            </div>

            <div class="form-group">
                <label class="label-maturity-2" for="applications">{{ trans('cruds.certificate.fields.applications') }}</label>
                <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}" name="applications[]" id="applications" multiple>
                    @foreach($applications as $id => $name)
                        <option value="{{ $id }}" {{ in_array($id, old('applications', [])) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @if($errors->has('applications'))
                    <div class="invalid-feedback">
                        {{ $errors->first('applications') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.certificate.fields.applications_helper') }}</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.certificates.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <button id="btn-save" class="btn btn-success" type="submit">
            {{ trans('global.save') }}
        </button>
    </div>
</form>
@endsection
