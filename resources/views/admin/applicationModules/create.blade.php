@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.applicationModule.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.application-modules.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.create') }} {{ trans('cruds.applicationModule.title_singular') }}
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
                            <label class="label-required" for="name">{{ trans('cruds.applicationModule.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', '') }}" maxlength="64" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationModule.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.applicationModule.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', '') }}" maxlength="64" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationModule.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.applicationModule.fields.type') }}</label>
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
                            <span class="help-block">{{ trans('cruds.applicationModule.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.applicationModule.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', [])) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationModule.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label-maturity-2"
                           for="description">{{ trans('cruds.applicationModule.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description" id="description">{!! old('description') !!}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.applicationModule.fields.description_helper') }}</span>
                </div>
		<div class="form-group">
		    <label for="comments">{{ trans('cruds.applicationModule.fields.comments') }}</label>
		    <textarea
		        class="form-control {{ $errors->has('comments') ? 'is-invalid' : '' }}"
		        name="comments"
		        id="comments"
		        rows="5">{{ old('comments', $applicationModule->comments ?? '') }}</textarea>
		    @if($errors->has('comments'))
		        <div class="invalid-feedback">
		            {{ $errors->first('comments') }}
		        </div>
		    @endif
		    <span class="help-block">{{ trans('cruds.applicationModule.fields.comments_helper') }}</span>
		</div>
                <div class="form-group">
                    <label for="entities">{{ trans('cruds.applicationModule.fields.entities') }}</label>
                    <select class="form-control select2 {{ $errors->has('entities') ? 'is-invalid' : '' }}"
                            name="entities[]" id="entities" multiple>
                        @foreach($entities as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, old('entities', [])) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('entities'))
                        <div class="invalid-feedback">
                            {{ $errors->first('entities') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.applicationModule.fields.entities_helper') }}</span>
                </div>


                <div class="form-group">
                    <label for="services">{{ trans('cruds.applicationModule.fields.services') }}</label>
                    <select class="form-control select2 {{ $errors->has('services') ? 'is-invalid' : '' }}"
                            name="services[]" id="services" multiple>
                        @foreach($services as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, old('services', [])) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('services'))
                        <div class="invalid-feedback">
                            {{ $errors->first('services') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.applicationModule.fields.services_helper') }}</span>
                </div>
            </div>

            <!------------------------------------------------------------------------------------------------------------->
            {{-- Common Platform Enumeration --}}
            <!------------------------------------------------------------------------------------------------------------->
            @include('partials.cpe-selector', [
                'part'    => 'a',
                'vendor'  => '',
                'product' => '',
                'version' => ''
            ])
            <!---------------------------------------------------------------------------------------------------->

        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.application-modules.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
