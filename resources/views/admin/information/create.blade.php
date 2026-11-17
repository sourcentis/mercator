@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.information.title_singular') }}
@endsection

@section('content')
<form method="POST" action="{{ route("admin.information.store") }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header">
            {{ trans('global.create') }} {{ trans('cruds.information.title_singular') }}
        </div>
        <div class="card-body">
            <div class="row">
                @if (auth()->user()->hasMultiplePerimeters())
                <div class="col-2">
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
                <div class="col-3">
                    <div class="form-group">
                        <label class="label-required" for="name">{{ trans('cruds.information.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                               id="name" value="{{ old('name', '') }}" required maxlength="64" autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.name_helper') }}</span>
                    </div>
                </div>
            @else
                <div class="col-5">
                    <div class="form-group">
                        <label class="label-required" for="name">{{ trans('cruds.information.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                               id="name" value="{{ old('name', '') }}" required maxlength="64" autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.name_helper') }}</span>
                    </div>
                </div>
            @endif
                <div class="col-2">
                    <div class="form-group">
                        <label for="type">{{ trans('cruds.information.fields.type') }}</label>
                        <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                name="type" id="type">
                            <option></option>
                            @if (!$type_list->contains(old('type')))
                                <option> {{ old('type') }}</option>
                            @endif
                            @foreach($type_list as $type)
                                <option {{ old('type')==$type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('type'))
                            <div class="invalid-feedback">
                                {{ $errors->first('type') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.type_helper') }}</span>
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label for="attributes">{{ trans('cruds.information.fields.attributes') }}</label>
                        <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                name="attributes[]" id="attributes" multiple>
                            @foreach($attributes_list as $a)
                                <option {{ in_array($a, old('attributes', []))  ? 'selected' : '' }}>{{$a}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('attributes'))
                            <div class="invalid-feedback">
                                {{ $errors->first('attributes') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.attributes_helper') }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-9">
                    <div class="form-group">
                        <label class="label-maturity-1"
                               for="description">{{ trans('cruds.information.fields.description') }}</label>
                        <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                  name="description" id="description">{{ old('description') }}</textarea>
                        @if($errors->has('description'))
                            <div class="invalid-feedback">
                                {{ $errors->first('description') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.description_helper') }}</span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                        <select id="iconSelect"
                                name="iconSelect"
                                class="form-control js-icon-picker"
                                data-icons='@json($icons)'
                                data-selected="-1"
                                data-default-img="{{ asset('images/information.png') }}"
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
            {{ trans("cruds.menu.metier.title_short") }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="label-maturity-1"
                               for="owner">{{ trans('cruds.information.fields.owner') }}</label>
                        <select class="form-control select2-free {{ $errors->has('owner') ? 'is-invalid' : '' }}"
                                name="owner" id="owner">
                            @if (!$owner_list->contains(old('owner')))
                                <option> {{ old('owner') }}</option>
                            @endif
                            @foreach($owner_list as $t)
                                <option {{ old('owner') == $t ? 'selected' : '' }}>{{$t}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('owner'))
                            <div class="invalid-feedback">
                                {{ $errors->first('owner') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.owner_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="label-maturity-1"
                               for="administrator">{{ trans('cruds.information.fields.administrator') }}</label>
                        <select class="form-control select2-free {{ $errors->has('administrator') ? 'is-invalid' : '' }}"
                                name="administrator" id="administrator">
                            @if (!$owner_list->contains(old('administrator')))
                                <option> {{ old('administrator') }}</option>
                            @endif
                            @foreach($administrator_list as $t)
                                <option {{ old('administrator') == $t ? 'selected' : '' }}>{{$t}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('administrator'))
                            <div class="invalid-feedback">
                                {{ $errors->first('administrator') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.administrator_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label class="label-maturity-1"
                               for="storage">{{ trans('cruds.information.fields.storage') }}</label>
                        <select class="form-control select2-free {{ $errors->has('storage') ? 'is-invalid' : '' }}"
                                name="storage" id="storage">
                            @if (!$owner_list->contains(old('storage')))
                                <option> {{ old('storage') }}</option>
                            @endif
                            @foreach($storage_list as $t)
                                <option {{ old('storage') == $t ? 'selected' : '' }}>{{$t}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('storage'))
                            <div class="invalid-feedback">
                                {{ $errors->first('storage') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.storage_helper') }}</span>
                    </div>
                </div>

            </div>

            <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="sensitivity">{{ trans('cruds.information.fields.sensitivity') }}</label>
                            <select class="form-control select2-free {{ $errors->has('sensitivity') ? 'is-invalid' : '' }}"
                                    name="sensitivity" id="sensitivity">
                                @if (!$owner_list->contains(old('sensitivity')))
                                    <option> {{ old('sensitivity') }}</option>
                                @endif
                                @foreach($sensitivity_list as $t)
                                    <option {{ old('sensitivity') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('sensitivity'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('sensitivity') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.information.fields.sensitivity_helper') }}</span>
                        </div>
                    </div>

                <div class="col-4">
                    <div class="form-group">
                        <label
                                class="label-maturity-2"
                                for="security_need">{{ trans('cruds.information.fields.security_need') }}
                        </label>
                        <table cellspacing="5" cellpadding="5" border="0" width='100%'>
                            <tr>
                                <td align="right" width="10">
                                    <label for="security_need">{{ trans('global.confidentiality_short') }}</label>
                                </td>
                                <td width="120">
                                    <select class="form-control select2 risk {{ $errors->has('security_need_c') ? 'is-invalid' : '' }}"
                                            name="security_need_c" id="security_need_c">
                                        <option value="-1"></option>
                                        <option value="0" {{ old('security_need_c') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                        <option value="1" {{ old('security_need_c') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                        <option value="2" {{ old('security_need_c') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                        <option value="3" {{ old('security_need_c') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                        <option value="4" {{ old('security_need_c') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                    </select>
                                </td>
                                <td align="right">
                                    <label for="security_need">{{ trans('global.integrity_short') }}</label>
                                </td>
                                <td width="120">
                                    <select class="form-control select2 risk {{ $errors->has('security_need_i') ? 'is-invalid' : '' }}"
                                            name="security_need_i" id="security_need_i">
                                        <option value="-1"></option>
                                        <option value="0" {{ old('security_need_i') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                        <option value="1" {{ old('security_need_i') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                        <option value="2" {{ old('security_need_i') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                        <option value="3" {{ old('security_need_i') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                        <option value="4" {{ old('security_need_i') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                    </select>
                                </td>
                                <td align="right">
                                    <label for="security_need">{{ trans('global.availability_short') }}</label>
                                </td>
                                <td width="120">
                                    <select class="form-control select2 risk {{ $errors->has('security_need_a') ? 'is-invalid' : '' }}"
                                            name="security_need_a" id="security_need_a">
                                        <option value="-1"></option>
                                        <option value="0" {{ old('security_need_a') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                        <option value="1" {{ old('security_need_a') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                        <option value="2" {{ old('security_need_a') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                        <option value="3" {{ old('security_need_a') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                        <option value="4" {{ old('security_need_a') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                    </select>
                                </td>
                                <td align="right">
                                    <label for="security_need">{{ trans('global.tracability_short') }}</label>
                                </td>
                                <td width="120">
                                    <select class="form-control select2 risk {{ $errors->has('security_need_t') ? 'is-invalid' : '' }}"
                                            name="security_need_t" id="security_need_t">
                                        <option value="-1"></option>
                                        <option value="0" {{ old('security_need_t') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                        <option value="1" {{ old('security_need_t') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                        <option value="2" {{ old('security_need_t') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                        <option value="3" {{ old('security_need_t') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                        <option value="4" {{ old('security_need_t') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                    </select>
                                </td>
                                @if (config('mercator-config.parameters.security_need_auth'))
                                    <td align="right">
                                        <label for="security_need">{{ trans('global.authenticity_short') }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_auth') ? 'is-invalid' : '' }}"
                                                name="security_need_auth" id="security_need_auth">
                                            <option value="-1"></option>
                                            <option value="0" {{ old('security_need_auth') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_auth') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_auth') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_auth') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_auth') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
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
                        <span class="help-block">{{ trans('cruds.information.fields.security_need_helper') }}</span>
                    </div>
                </div>

                </div>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            Relations
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="processes">{{ trans('cruds.information.fields.parents') }}</label>
                        <select class="form-control select2 {{ $errors->has('parents') ? 'is-invalid' : '' }}"
                                name="parents[]" id="parents" multiple>
                            @foreach($information_list as $id => $name)
                                <option value="{{ $id }}" {{ (in_array($id, old('parents', []))) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('parents'))
                            <div class="invalid-feedback">
                                {{ $errors->first('parents') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.parents_helper') }}</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="children">{{ trans('cruds.information.fields.children') }}</label>
                        <select class="form-control select2 {{ $errors->has('children') ? 'is-invalid' : '' }}"
                                name="children[]" id="children" multiple>
                            @foreach($information_list as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, old('children', [])) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('children'))
                            <div class="invalid-feedback">
                                {{ $errors->first('children') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.children_helper') }}</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="processes">{{ trans('cruds.information.fields.processes') }}</label>
                        <select class="form-control select2 {{ $errors->has('processes') ? 'is-invalid' : '' }}"
                                name="processes[]" id="processes" multiple>
                            @foreach($processes as $id => $process)
                                <option value="{{ $id }}" {{ in_array($id, old('processes', [])) ? 'selected' : '' }}>{{ $process }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('processes'))
                            <div class="invalid-feedback">
                                {{ $errors->first('processes') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.processes_helper') }}</span>
                    </div>
                </div>
            </div>

        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.menu.gdpr.title_short') }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">

            <div class="form-group">
                <label for="constraints">{{ trans('cruds.information.fields.constraints') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('constraints') ? 'is-invalid' : '' }}"
                          name="constraints" id="constraints">{{ old('constraints') }}</textarea>
                @if($errors->has('constraints'))
                    <div class="invalid-feedback">
                        {{ $errors->first('constraints') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.information.fields.constraints_helper') }}</span>
            </div>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.information.fields.retention') }}
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="retention_period">{{ trans('cruds.information.fields.retention_period') }}</label>
                        <select class="form-control select2-free {{ $errors->has('retention_period') ? 'is-invalid' : '' }}"
                                name="retention_period" id="retention_period">
                            @if (!$retention_period_list->contains(old('retention_period', '')))
                                <option>{{ old('retention_period', '') }}</option>
                            @endif
                            @foreach($retention_period_list as $t)
                                <option {{ old('retention_period', '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('retention_period'))
                            <div class="invalid-feedback">
                                {{ $errors->first('retention_period') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.retention_period_helper') }}</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="final_disposition">{{ trans('cruds.information.fields.final_disposition') }}</label>
                        <select class="form-control select2-free {{ $errors->has('final_disposition') ? 'is-invalid' : '' }}"
                                name="final_disposition" id="final_disposition">
                            @if (!$final_disposition_list->contains(old('final_disposition', '')))
                                <option>{{ old('final_disposition', '') }}</option>
                            @endif
                            @foreach($final_disposition_list as $t)
                                <option {{ old('final_disposition', '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('final_disposition'))
                            <div class="invalid-feedback">
                                {{ $errors->first('final_disposition') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.information.fields.final_disposition_helper') }}</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="retention_notes">{{ trans('cruds.information.fields.retention_notes') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('retention_notes') ? 'is-invalid' : '' }}"
                          name="retention_notes" id="retention_notes">{{ old('retention_notes', '') }}</textarea>
                @if($errors->has('retention_notes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('retention_notes') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.information.fields.retention_notes_helper') }}</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.information.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <button id="btn-save" class="btn btn-success" type="submit">
            {{ trans('global.save') }}
        </button>
    </div>
</form>
@endsection
