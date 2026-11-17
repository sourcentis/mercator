@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $cluster->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.clusters.update", [$cluster->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.cluster.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($cluster))
                <div class="col-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $cluster->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-2">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.cluster.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name"
                                   id="name" value="{{ old('name', $cluster->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-4">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.cluster.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name"
                                   id="name" value="{{ old('name', $cluster->name) }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-3">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="type">{{ trans('cruds.cluster.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type"
                                    id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('users') ? old('users') : $cluster->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('types'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('types') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.cluster.fields.attributes') }}</label>
                            <select class="form-control select2-free {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes[]" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, explode(' ',$cluster->attributes)) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-9">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.cluster.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $cluster->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $cluster->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/cluster.png') }}"
                                    data-url-template="{{ route('admin.documents.show', ':id') }}"
                                    data-upload="#iconFile">
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="file" id="iconFile" name="iconFile" accept="image/png"/>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address_ip">{{ trans('cruds.cluster.fields.address_ip') }}</label>
                    <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text"
                           name="address_ip" id="address_ip" value="{{ old('address_ip', $cluster->address_ip) }}">
                    @if($errors->has('address_ip'))
                        <div class="invalid-feedback">
                            {{ $errors->first('address_ip') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.cluster.fields.address_ip_helper') }}</span>
                </div>

            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.logical_infrastructure.title_short") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="logical_servers">{{ trans('cruds.cluster.fields.logical_servers') }}</label>
                            <select class="form-control select2 {{ $errors->has('logical_servers') ? 'is-invalid' : '' }}"
                                    name="logical_servers[]" id="logical_servers" multiple>
                                @foreach($logical_servers as $id => $logical_server)
                                    <option value="{{ $id }}" {{ (in_array($id, old('logical_servers', [])) || $cluster->logicalServers->contains($id)) ? 'selected' : '' }}>{{ $logical_server }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('logical_servers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('logical_servers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.logical_servers_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="routers">{{ trans('cruds.cluster.fields.routers') }}</label>
                            <select class="form-control select2 {{ $errors->has('routers') ? 'is-invalid' : '' }}"
                                    name="routers[]" id="routers" multiple>
                                @foreach($routers as $id => $router)
                                    <option value="{{ $id }}" {{ (in_array($id, old('routers', [])) || $cluster->routers->contains($id)) ? 'selected' : '' }}>{{ $router }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('routers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('routers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.routers_helper') }}</span>
                        </div>
                    </div>
                </div>

            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.physical_infrastructure.title_short") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="physical_servers">{{ trans('cruds.cluster.fields.physical_servers') }}</label>
                            <select class="form-control select2 {{ $errors->has('logical_servers') ? 'is-invalid' : '' }}"
                                    name="physical_servers[]" id="physical_servers" multiple>
                                @foreach($physical_servers as $id => $physical_server)
                                    <option value="{{ $id }}" {{ (in_array($id, old('physical_servers', [])) || $cluster->physicalServers->contains($id)) ? 'selected' : '' }}>{{ $physical_server }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('physical_servers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('physical_servers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.cluster.fields.physical_servers_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.clusters.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
