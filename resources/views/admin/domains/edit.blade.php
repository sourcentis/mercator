@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $domain->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.domains.update", [$domain->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.domain.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($domain))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $domain->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.domain.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', $domain->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.domain.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.domain.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                                   id="name" value="{{ old('name', $domain->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.domain.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.domain.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $domain->type ?? '')))
                                    <option>{{ old('type', $domain->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $domain->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.domain.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.domain.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $domain->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.domain.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="description">{{ trans('cruds.domain.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description"
                              id="description">{{ old('description', $domain->description) }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.domain.fields.description_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="domain_ctrl_cnt">{{ trans('cruds.domain.fields.domain_ctrl_cnt') }}</label>
                    <input class="form-control {{ $errors->has('domain_ctrl_cnt') ? 'is-invalid' : '' }}" type="number"
                           name="domain_ctrl_cnt" id="domain_ctrl_cnt"
                           value="{{ old('domain_ctrl_cnt', $domain->domain_ctrl_cnt) }}" step="1">
                    @if($errors->has('domain_ctrl_cnt'))
                        <div class="invalid-feedback">
                            {{ $errors->first('domain_ctrl_cnt') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.domain.fields.domain_ctrl_cnt_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="user_count">{{ trans('cruds.domain.fields.user_count') }}</label>
                    <input class="form-control {{ $errors->has('user_count') ? 'is-invalid' : '' }}" type="number"
                           name="user_count" id="user_count" value="{{ old('user_count', $domain->user_count) }}"
                           step="1">
                    @if($errors->has('user_count'))
                        <div class="invalid-feedback">
                            {{ $errors->first('user_count') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.domain.fields.user_count_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="machine_count">{{ trans('cruds.domain.fields.machine_count') }}</label>
                    <input class="form-control {{ $errors->has('machine_count') ? 'is-invalid' : '' }}" type="number"
                           name="machine_count" id="machine_count"
                           value="{{ old('machine_count', $domain->machine_count) }}" step="1">
                    @if($errors->has('machine_count'))
                        <div class="invalid-feedback">
                            {{ $errors->first('machine_count') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.domain.fields.machine_count_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="label-maturity-1"
                           for="relation_inter_domaine">{{ trans('cruds.domain.fields.relation_inter_domaine') }}</label>
                    <input class="form-control {{ $errors->has('relation_inter_domaine') ? 'is-invalid' : '' }}"
                           type="text" name="relation_inter_domaine" id="relation_inter_domaine"
                           value="{{ old('relation_inter_domaine', $domain->relation_inter_domaine) }}">
                    @if($errors->has('relation_inter_domaine'))
                        <div class="invalid-feedback">
                            {{ $errors->first('relation_inter_domaine') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.domain.fields.relation_inter_domaine_helper') }}</span>
                </div>

                <div class="form-group">
                    <label for="link_to_as">{{ trans('cruds.forestAd.title') }}</label>
                    <div style="padding-bottom: 4px">
                        <span class="btn btn-info btn-xs select-all"
                              style="border-radius: 0">{{ trans('global.select_all') }}</span>
                        <span class="btn btn-info btn-xs deselect-all"
                              style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('forestAds') ? 'is-invalid' : '' }}"
                            name="forestAds[]" id="forestAds" multiple>
                        @foreach($forestAds as $id => $forestAd)
                            <option value="{{ $id }}" {{ (in_array($id, old('forestAds', [])) || $domain->forestAds->contains($id)) ? 'selected' : '' }}>{{ $forestAd }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('forestAds'))
                        <span class="text-danger">{{ $errors->first('forestAds') }}</span>
                    @endif
                </div>

            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.logical_infrastructure.title_short") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="logicalServers">{{ trans('cruds.domain.fields.logical_servers') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('responsible') ? 'is-invalid' : '' }}"
                                    name="logicalServers[]" id="logicalServers" multiple>
                                @foreach($logicalServers as $id => $name)
                                    <option value="{{$id}}" {{ (in_array($id, old('logicalServers', [])) || $domain->logicalServers->contains($id)) ? 'selected' : '' }}>{{$name}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('logicalServers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('logicalServers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.logical_servers_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.domains.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
