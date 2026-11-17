@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $process->name }}
@endsection

@section('content')

    <form method="POST" action="{{ route("admin.processes.update", [$process->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.process.title_singular') }}
            </div>
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($process))
                <div class="col-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $process->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.process.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $process->name) }}" maxlength="64"
                                   required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.process.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $process->name) }}" maxlength="64"
                                   required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.process.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $process->type ?? '')))
                                    <option>{{ old('type', $process->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $process->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.process.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $process->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-9">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.process.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $process->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.description_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $process->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/process.png') }}"
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
                    <div class="form-group">
                        <label class="label-maturity-1" for="in_out">{{ trans('cruds.process.fields.in_out') }}</label>
                        <textarea class="form-control ckeditor {{ $errors->has('in_out') ? 'is-invalid' : '' }}"
                                  name="in_out" id="in_out">{{ old('in_out', $process->in_out) }}</textarea>
                        @if($errors->has('in_out'))
                            <div class="invalid-feedback">
                                {{ $errors->first('in_out') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.process.fields.in_out_helper') }}</span>
                    </div>
                </div>

                <div class="row">

                    <div class="col-4">
                        <div class="form-group">
                            <label class="label-maturity-2"
                                   for="security_need">{{ trans('cruds.process.fields.security_need') }}</label>
                            <table cellspacing="5" cellpadding="5" border="0" width='100%'>
                                <tr>
                                    <td align="right" width="10">
                                        <label for="security_need">{{ trans('global.confidentiality_short') }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_c') ? 'is-invalid' : '' }}"
                                                name="security_need_c" id="security_need_c">
                                            <option value="-1" {{ ($process->security_need_c ? $process->security_need_c : old('security_need_c')) == -1 ? 'selected' : '' }}></option>
                                            <option value="0" {{ ($process->security_need_c ? $process->security_need_c : old('security_need_c')) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ ($process->security_need_c ? $process->security_need_c : old('security_need_c')) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ ($process->security_need_c ? $process->security_need_c : old('security_need_c')) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ ($process->security_need_c ? $process->security_need_c : old('security_need_c')) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ ($process->security_need_c ? $process->security_need_c : old('security_need_c')) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right">
                                        <label for="security_need">{{ trans('global.integrity_short') }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_i') ? 'is-invalid' : '' }}"
                                                name="security_need_i" id="security_need_i">
                                            <option value="-1" {{ ($process->security_need_i ? $process->security_need_i : old('security_need_i')) == -1 ? 'selected' : '' }}></option>
                                            <option value="0" {{ ($process->security_need_i ? $process->security_need_i : old('security_need_i')) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ ($process->security_need_i ? $process->security_need_i : old('security_need_i')) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ ($process->security_need_i ? $process->security_need_i : old('security_need_i')) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ ($process->security_need_i ? $process->security_need_i : old('security_need_i')) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ ($process->security_need_i ? $process->security_need_i : old('security_need_i')) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right">
                                        <label for="security_need">{{ trans('global.availability_short') }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_a') ? 'is-invalid' : '' }}"
                                                name="security_need_a" id="security_need_a">
                                            <option value="-1" {{ ($process->security_need_a ? $process->security_need_a : old('security_need_a')) == -1 ? 'selected' : '' }}></option>
                                            <option value="0" {{ ($process->security_need_a ? $process->security_need_a : old('security_need_a')) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ ($process->security_need_a ? $process->security_need_a : old('security_need_a')) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ ($process->security_need_a ? $process->security_need_a : old('security_need_a')) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ ($process->security_need_a ? $process->security_need_a : old('security_need_a')) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ ($process->security_need_a ? $process->security_need_a : old('security_need_a')) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right">
                                        <label for="security_need">{{ trans('global.tracability_short') }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_c') ? 'is-invalid' : '' }}"
                                                name="security_need_t" id="security_need_t">
                                            <option value="-1" {{ ($process->security_need_t ? $process->security_need_t : old('security_need_t')) == -1 ? 'selected' : '' }}></option>
                                            <option value="0" {{ ($process->security_need_t ? $process->security_need_t : old('security_need_t')) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ ($process->security_need_t ? $process->security_need_t : old('security_need_t')) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ ($process->security_need_t ? $process->security_need_t : old('security_need_t')) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ ($process->security_need_t ? $process->security_need_t : old('security_need_t')) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ ($process->security_need_t ? $process->security_need_t : old('security_need_t')) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    @if (config('mercator-config.parameters.security_need_auth'))
                                        <td align="right">
                                            <label for="security_need">{{ trans('global.authenticity_short') }}</label>
                                        </td>
                                        <td width="120">
                                            <select class="form-control select2 risk{{ $errors->has('security_need_auth') ? 'is-invalid' : '' }}"
                                                    name="security_need_auth" id="security_need_auth">
                                                <option value="-1"></option>
                                                <option value="0" {{ ($process->security_need_auth ? $process->security_need_auth : old('security_need_auth')) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                                <option value="1" {{ ($process->security_need_auth ? $process->security_need_auth : old('security_need_auth')) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                                <option value="2" {{ ($process->security_need_auth ? $process->security_need_auth : old('security_need_auth')) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                                <option value="3" {{ ($process->security_need_auth ? $process->security_need_auth : old('security_need_auth')) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                                <option value="4" {{ ($process->security_need_auth ? $process->security_need_auth : old('security_need_auth')) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                            </select>
                                        </td>
                                    @endif
                                </tr>
                            </table>
                            @if($errors->has('security_need'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('security_need') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.security_need_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <label class="label-maturity-2"
                                   for="macroprocessus">{{ trans('cruds.process.fields.macroprocessus') }}</label>
                            <select class="form-control select2 {{ $errors->has('macroProcessues') ? 'is-invalid' : '' }}"
                                    name="macroprocess_id" id="macroprocess_id">
                                <option></option>
                                @foreach($macroProcessuses as $id => $macroprocess)
                                    <option value="{{ $id }}" {{ ($process->macroprocess_id ? $process->macroprocess_id : old('macroprocess_id')) == $id ? 'selected' : '' }}>{{ $macroprocess }}</option>
                                @endforeach
                                {{ $process->macroprocess_id }}
                            </select>
                            @if($errors->has('macroProcessues'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('macroProcessues') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.macroprocessus_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="owner">{{ trans('cruds.process.fields.owner') }}</label>
                            <select class="form-control select2-free {{ $errors->has('owner') ? 'is-invalid' : '' }}"
                                    name="owner" id="owner">
                                @if (!$owner_list->contains(old('owner')))
                                    <option> {{ old('owner') }}</option>
                                @endif
                                @foreach($owner_list as $t)
                                    <option {{ (old('owner') ? old('owner') : $process->owner) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('owner'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('owner') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.owner_helper') }}</span>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label for="activities">{{ trans('cruds.process.fields.activities') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('activities') ? 'is-invalid' : '' }}"
                                    name="activities[]" id="activities" multiple>
                                @foreach($activities as $id => $activities)
                                    <option value="{{ $id }}" {{ (in_array($id, old('activities', [])) || $process->activities->contains($id)) ? 'selected' : '' }}>{{ $activities }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('activities'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('activities') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.activities_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="entities">{{ trans('cruds.process.fields.entities') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('entities') ? 'is-invalid' : '' }}"
                                    name="entities[]" id="entities" multiple>
                                @foreach($entities as $id => $entities)
                                    <option value="{{ $id }}" {{ (in_array($id, old('entities', [])) || $process->entities->contains($id)) ? 'selected' : '' }}>{{ $entities }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('entities'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('entities') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.entities_helper') }}</span>
                        </div>

                    </div>
                    <div class="col-4">

                        <div class="form-group">
                            <label for="informations">{{ trans('cruds.process.fields.informations') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('informations') ? 'is-invalid' : '' }}"
                                    name="informations[]" id="informations" multiple>
                                @foreach($informations as $id => $info)
                                    <option value="{{ $id }}" {{ (in_array($id, old('informations', [])) || $process->information->contains($id)) ? 'selected' : '' }}>{{ $info }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('informations'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('informations') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.informations_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="applications">{{ trans('cruds.process.fields.applications') }}</label>
                        <div style="padding-bottom: 4px">
                            <span class="btn btn-info btn-xs select-all"
                                  style="border-radius: 0">{{ trans('global.select_all') }}</span>
                            <span class="btn btn-info btn-xs deselect-all"
                                  style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                        </div>
                        <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                name="applications[]" id="applications" multiple>
                            @foreach($applications as $id => $application)
                                <option value="{{ $id }}" {{ (in_array($id, old('applications', [])) || $process->applications->contains($id)) ? 'selected' : '' }}>{{ $application }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('applications'))
                            <div class="invalid-feedback">
                                {{ $errors->first('applications') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.process.fields.applications_helper') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.processes.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
