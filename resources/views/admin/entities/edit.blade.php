@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $entity->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.entities.update", [$entity->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.entity.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($entity))
                <div class="col-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $entity->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.entity.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $entity->name) }}" required
                                   maxlength="64" autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.entity.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $entity->name) }}" required
                                   maxlength="64" autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif

                    <div class="col-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.entity.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$entityTypes->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($entityTypes as $t)
                                    <option {{ (old('type') ? old('type') : $entity->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            <span class="help-block">{{ trans('cruds.entity.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.entity.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $entity->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.entity.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $entity->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $entity->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/entity.png') }}"
                                    data-url-template="{{ route('admin.documents.show', ':id') }}"
                                    data-upload="#iconFile">
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="file" id="iconFile" name="iconFile" accept="image/png"/>
                        </div>
                    </div>
                </div>

                @canAccess(App\Models\Entity::class)
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="processes">{{ trans('cruds.entity.fields.parent_entity') }}</label>
                            <select class="form-control select2 {{ $errors->has('processes') ? 'is-invalid' : '' }}"
                                    name="parent_entity_id" id="parent_entity_id">
                                <option></option>
                                @foreach($entities as $id => $name)
                                    <option value="{{ $id }}" {{ old('entity',$entity->parent_entity_id)==$id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('processes'))
                                <span class="text-danger">{{ $errors->first('parent_entity') }}</span>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.parent_entity_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="childEntities">{{ trans('cruds.entity.fields.subsidiaries') }}</label>
                            <select class="form-control select2 {{ $errors->has('childEntities') ? 'is-invalid' : '' }}"
                                    name="childEntities[]" id="childEntities" multiple>
                                @foreach($entities as $id => $name)
                                    @continue($id == $entity->id)
                                    <option value="{{ $id }}" {{ (in_array($id, old('childEntities', [])) || $entity->entities->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('childEntities'))
                                <div class="invalid-feedback">{{ $errors->first('childEntities') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.subsidiaries_helper') }}</span>
                        </div>
                    </div>
                </div>
                @endcanAccess

                <div class="form-group">
                    <label class="label-maturity-1"
                           for="contact_point">{{ trans('cruds.entity.fields.contact_point') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('contact_point') ? 'is-invalid' : '' }}"
                              name="contact_point"
                              id="contact_point">{{ old('contact_point', $entity->contact_point) }}</textarea>
                    @if($errors->has('contact_point'))
                        <div class="invalid-feedback">
                            {{ $errors->first('contact_point') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.entity.fields.contact_point_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="security_level">{{ trans('cruds.entity.fields.security_level') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('security_level') ? 'is-invalid' : '' }}"
                              name="security_level"
                              id="security_level">{{ old('security_level', $entity->security_level) }}</textarea>
                    @if($errors->has('security_level'))
                        <div class="invalid-feedback">
                            {{ $errors->first('security_level') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.entity.fields.security_level_helper') }}</span>
                </div>


                <div class="row">
                    <div class="col-md-6">

                        <div class="form-group">
                            <label for="applications">{{ trans('cruds.entity.fields.applications_resp') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                    name="respApplications[]" id="respApplications" multiple>
                                @foreach($applications as $id => $application)
                                    <option value="{{ $id }}" {{ (in_array($id, old('respApplications', [])) || $entity->respApplications->contains($id)) ? 'selected' : '' }}>{{ $application }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('respApplications'))
                                <span class="text-danger">{{ $errors->first('respApplications') }}</span>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.applications_resp_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="databases">{{ trans('cruds.entity.fields.databases_resp') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('databases') ? 'is-invalid' : '' }}"
                                    name="databases[]" id="databases" multiple>
                                @foreach($databases as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('databases', [])) || $entity->databases->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('databases'))
                                <span class="text-danger">{{ $errors->first('databases') }}</span>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.databases_resp_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="processes">{{ trans('cruds.entity.fields.processes') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('processes') ? 'is-invalid' : '' }}"
                                    name="processes[]" id="processes" multiple>
                                @foreach($processes as $id => $identificateur)
                                    <option value="{{ $id }}" {{ (in_array($id, old('processes', [])) || $entity->processes->contains($id)) ? 'selected' : '' }}>{{ $identificateur }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('processes'))
                                <span class="text-danger">{{ $errors->first('processes') }}</span>
                            @endif
                            <span class="help-block">{{ trans('cruds.entity.fields.processes_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.entities.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection

