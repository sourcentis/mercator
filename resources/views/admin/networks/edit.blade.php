@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $network->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.networks.update", [$network->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.network.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($network))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $network->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-1">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.network.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', $network->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.network.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.network.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', $network->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.network.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.network.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $network->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.network.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.network.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, explode(' ',$network->attributes)) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.network.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="description">{{ trans('cruds.network.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description"
                              id="description">{{ old('description', $network->description) }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.network.fields.description_helper') }}</span>
                </div>
                <div class="form-group">
                    <label for="protocol_type">{{ trans('cruds.network.fields.protocol_type') }}</label>
                    <input class="form-control {{ $errors->has('protocol_type') ? 'is-invalid' : '' }}" type="text"
                           name="protocol_type" id="protocol_type"
                           value="{{ old('protocol_type', $network->protocol_type) }}">
                    @if($errors->has('protocol_type'))
                        <div class="invalid-feedback">
                            {{ $errors->first('protocol_type') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.network.fields.protocol_type_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="responsible">{{ trans('cruds.network.fields.responsible') }}</label>
                    <input class="form-control {{ $errors->has('responsible') ? 'is-invalid' : '' }}" type="text"
                           name="responsible" id="responsible" value="{{ old('responsible', $network->responsible) }}">
                    @if($errors->has('responsible'))
                        <div class="invalid-feedback">
                            {{ $errors->first('responsible') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.network.fields.responsible_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="responsible_sec">{{ trans('cruds.network.fields.responsible_sec') }}</label>
                    <input class="form-control {{ $errors->has('responsible_sec') ? 'is-invalid' : '' }}" type="text"
                           name="responsible_sec" id="responsible_sec"
                           value="{{ old('responsible_sec', $network->responsible_sec) }}">
                    @if($errors->has('responsible_sec'))
                        <div class="invalid-feedback">
                            {{ $errors->first('responsible_sec') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.network.fields.responsible_sec_helper') }}</span>
                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">

                            <table cellspacing="5" cellpadding="5" border="0">
                                <tr>
                                    <td width="150">
                                        <label class="label-maturity-1"
                                               for="security_need">{{ trans('cruds.information.fields.security_need') }}</label>
                                    </td>
                                    <td align="right" width="10">
                                        <label for="security_need">{{ trans("global.confidentiality_short") }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_c') ? 'is-invalid' : '' }}"
                                                name="security_need_c" id="security_need_c">
                                            <option value="-1"></option>
                                            <option value="0" {{ old('security_need_c', $network->security_need_c) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_c', $network->security_need_c) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_c', $network->security_need_c) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_c', $network->security_need_c) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_c', $network->security_need_c) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right" width="10">
                                        <label for="security_need">{{ trans("global.integrity_short") }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_i') ? 'is-invalid' : '' }}"
                                                name="security_need_i" id="security_need_i">
                                            <option value="-1"></option>
                                            <option value="0" {{ old('security_need_i', $network->security_need_i) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_i', $network->security_need_i) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_i', $network->security_need_i) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_i', $network->security_need_i) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_i', $network->security_need_i) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right" width="10">
                                        <label for="security_need">{{ trans("global.availability_short") }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk {{ $errors->has('security_need_a') ? 'is-invalid' : '' }}"
                                                name="security_need_a" id="security_need_a">
                                            <option value="-1"></option>
                                            <option value="0" {{ old('security_need_a', $network->security_need_a) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_a', $network->security_need_a) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_a', $network->security_need_a) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_a', $network->security_need_a) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_a', $network->security_need_a) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right" width="10">
                                        <label for="security_need">{{ trans("global.tracability_short") }}</label>
                                    </td>
                                    <td width="120">
                                        <select class="form-control select2 risk{{ $errors->has('security_need_t') ? 'is-invalid' : '' }}"
                                                name="security_need_t" id="security_need_t">
                                            <option value="-1"></option>
                                            <option value="0" {{ old('security_need_t', $network->security_need_t) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_t', $network->security_need_t) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_t', $network->security_need_t) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_t', $network->security_need_t) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_t', $network->security_need_t) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    @if (config('mercator-config.parameters.security_need_auth'))
                                        <td align="right" width="10">
                                            <label for="security_need">{{ trans('global.authenticity_short') }}</label>
                                        </td>
                                        <td width="120">
                                            <select class="form-control select2 risk{{ $errors->has('security_need_auth') ? 'is-invalid' : '' }}"
                                                    name="security_need_auth" id="security_need_auth">
                                                <option value="-1"></option>
                                                <option value="0" {{ old('security_need_auth', $network->security_need_auth) == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                                <option value="1" {{ old('security_need_auth', $network->security_need_auth) == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                                <option value="2" {{ old('security_need_auth', $network->security_need_auth) == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                                <option value="3" {{ old('security_need_auth', $network->security_need_auth) == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                                <option value="4" {{ old('security_need_auth', $network->security_need_auth) == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
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
                            <span class="help-block">{{ trans('cruds.network.fields.security_need_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.networks.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
