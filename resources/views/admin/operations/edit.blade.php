@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $operation->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.operations.update", [$operation->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.operation.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($operation))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $operation->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.operation.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', $operation->name) }}" malength=64 required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.operation.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', $operation->name) }}" malength=64 required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.operation.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $operation->type ?? '')))
                                    <option>{{ old('type', $operation->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $operation->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.operation.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $operation->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="description">{{ trans('cruds.operation.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description"
                              id="description">{{ old('description', $operation->description) }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.operation.fields.description_helper') }}</span>
                </div>

                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group">
                            <label for="database_source_id">{{ trans('cruds.operation.fields.process') }}</label>
                            <select class="form-control select2 {{ $errors->has('process') ? 'is-invalid' : '' }}"
                                    name="process_id" id="process_id">
                                <option></option>
                                @foreach($processes as $id => $name)
                                    <option value="{{ $id }}" {{ ($operation->process ? $operation->process->id : old('process_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('process_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('process_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.process_helper') }}</span>
                        </div>

                    </div>
                    <div class="col-md-6">

                        <div class="form-group">
                            <label class="label-maturity-3"
                                   for="tasks">{{ trans('cruds.operation.fields.activities') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('activities') ? 'is-invalid' : '' }}"
                                    name="activities[]" id="activities" multiple>
                                @foreach($activities as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('activities', [])) || $operation->activities->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('activities'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('activities') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.activities_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="label-maturity-2"
                                   for="actors">{{ trans('cruds.operation.fields.actors') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('actors') ? 'is-invalid' : '' }}"
                                    name="actors[]" id="actors" multiple>
                                @foreach($actors as $id => $actors)
                                    <option value="{{ $id }}" {{ (in_array($id, old('actors', [])) || $operation->actors->contains($id)) ? 'selected' : '' }}>{{ $actors }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('actors'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('actors') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.actors_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">

                        <div class="form-group">
                            <label for="tasks">{{ trans('cruds.operation.fields.tasks') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('tasks') ? 'is-invalid' : '' }}"
                                    name="tasks[]" id="tasks" multiple>
                                @foreach($tasks as $id => $tasks)
                                    <option value="{{ $id }}" {{ (in_array($id, old('tasks', [])) || $operation->tasks->contains($id)) ? 'selected' : '' }}>{{ $tasks }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('tasks'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('tasks') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.operation.fields.tasks_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.operations.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
