@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ trans('cruds.physicalLink.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.physical-links.update', [$link->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.physicalLink.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.physicalLink.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$types->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($types as $type)
                                    <option {{ (old('type') ? old('type') : $link->type) == $type ? 'selected' : '' }}>{{$type}}</option>
                                @endforeach
                            </select>
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div style="width: 120px; flex: 0 0 120px;">
                        <div class="form-group">
                            <label for="color">{{ trans('cruds.physicalLink.fields.color') }}</label>
                            <input type="color" name="color" value="{{ $link->color ?? '#FFFFFF' }}" class="form-control form-control-color">
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.color_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.physicalLink.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a, old('attributes'))) || in_array($a, explode(' ', $link->attributes ?? '')) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.physicalLink.fields.src') }}</label>
                            <select class="form-control select2 {{ $errors->has('src_id') ? 'is-invalid' : '' }}"
                                    name="src_id" id="src_id" autofocus>
                                <option>...</option>
                                @foreach($devices as $id => $name)
                                    <option value="{{ $id }}" {{ (old('src_id') ? old('src_id') : $link->sourceId()) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('src_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('src_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.src_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-1">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.physicalLink.fields.src_port') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="src_port" id="src_port" value="{{ old('src_port', $link->src_port) }}">
                            @if($errors->has('src_port'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('src_port') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.src_port_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.physicalLink.fields.dest') }}</label>
                            <select class="form-control select2 {{ $errors->has('dest_id') ? 'is-invalid' : '' }}"
                                    name="dest_id" id="dest_id">
                                <option>...</option>
                                @foreach($devices as $id => $name)
                                    <option value="{{ $id }}" {{ (old('dest_id') ? old('dest_id') : $link->destinationId()) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('dest_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.dest_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-1">
                        <div class="form-group">
                            <label class="label-required"
                                   for="name">{{ trans('cruds.physicalLink.fields.dest_port') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="dest_port" id="dest_port" value="{{ old('dest_port', $link->dest_port) }}">
                            @if($errors->has('dest_port'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest_port') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.dest_port_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="description">{{ trans('cruds.physicalLink.fields.description') }}</label>
                            <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                      name="description" id="description">{{ old('description', $link->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.description_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.physical-links.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
