@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $router->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.routers.update", [$router->id]) }}">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.router.title_singular') }}
            </div>
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($router))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $router->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.router.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $router->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.router.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.router.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $router->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.router.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">

                        <div class="form-group">
                            <label for="type">{{ trans('cruds.router.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $router->type ?? '')))
                                    <option>{{ old('type', $router->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $router->type) == $type ? 'selected' : '' }}>{{$type}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.router.fields.type_helper') }}</span>
                        </div>

                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.router.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $router->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.router.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1" for="description">{{ trans('cruds.router.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description"
                              id="description">{{ old('description', $router->description) }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.router.fields.description_helper') }}</span>
                </div>
                <div class="form-group">
                    <label for="ip_addresses">{{ trans('cruds.router.fields.ip_addresses') }}</label>
                    <textarea class="form-control {{ $errors->has('ip_addresses') ? 'is-invalid' : '' }}"
                              name="ip_addresses"
                              id="ip_addresses">{{ old('ip_addresses', $router->ip_addresses) }}</textarea>
                    @if($errors->has('ip_addresses'))
                        <div class="invalid-feedback">
                            {{ $errors->first('ip_addresses') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.router.fields.ip_addresses_helper') }}</span>
                </div>
                <div class="form-group">
                    <label for="rules">{{ trans('cruds.router.fields.rules') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('rules') ? 'is-invalid' : '' }}" name="rules"
                              id="rules">{{ old('rules', $router->rules) }}</textarea>
                    @if($errors->has('rules'))
                        <div class="invalid-feedback">
                            {{ $errors->first('rules') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.router.fields.rules_helper') }}</span>
                </div>

                <div class="form-group">
                    <label for="physicalRouters">{{ trans('cruds.router.fields.physical_routers') }}</label>
                    <select class="form-control select2 {{ $errors->has('physical_routers') ? 'is-invalid' : '' }}"
                            name="physicalRouters[]" id="physicalRouters" multiple>
                        @foreach($physical_routers as $id => $name)
                            <option value="{{ $id }}" {{ (in_array($id, old('physicalRouters', [])) || $router->physicalRouters->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('physicalRouters'))
                        <div class="invalid-feedback">
                            {{ $errors->first('physicalRouters') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.router.fields.physical_routers_helper') }}</span>
                </div>

            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.routers.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
