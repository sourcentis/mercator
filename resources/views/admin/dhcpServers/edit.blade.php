@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $dhcpServer->name }}
@endsection

@section('content')
<form method="POST" action="{{ route("admin.dhcp-servers.update", [$dhcpServer->id]) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="card">
        <div class="card-header">
            {{ trans('global.edit') }} {{ trans('cruds.dhcpServer.title_singular') }}
        </div>

        <div class="card-body">
            <div class="row">
                @if (auth()->user()->canChoosePerimeterOf($dhcpServer))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $dhcpServer->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                        <label class="label-required" for="name">{{ trans('cruds.dhcpServer.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $dhcpServer->name) }}" required autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.dhcpServer.fields.name_helper') }}</span>
                    </div>
                </div>
            @else
                <div class="col-sm-5">
                    <div class="form-group">
                        <label class="label-required" for="name">{{ trans('cruds.dhcpServer.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $dhcpServer->name) }}" required autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.dhcpServer.fields.name_helper') }}</span>
                    </div>
                </div>
            @endif
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="type">{{ trans('cruds.dhcpServer.fields.type') }}</label>
                        <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                name="type" id="type">
                            @if (!$type_list->contains(old('type', $dhcpServer->type ?? '')))
                                <option>{{ old('type', $dhcpServer->type ?? '') }}</option>
                            @endif
                            @foreach($type_list as $type)
                                <option {{ old('type', $dhcpServer->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('type'))
                            <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.dhcpServer.fields.type_helper') }}</span>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-group">
                        <label for="attributes">{{ trans('cruds.dhcpServer.fields.attributes') }}</label>
                        <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                name="attributes[]" id="attributes" multiple>
                            @foreach($attributes_list as $a)
                                <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $dhcpServer->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('attributes'))
                            <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.dhcpServer.fields.attributes_helper') }}</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.dhcpServer.fields.description') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description', $dhcpServer->description) }}</textarea>
                @if($errors->has('description'))
                    <div class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dhcpServer.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="label-required" for="address_ip">{{ trans('cruds.dhcpServer.fields.address_ip') }}</label>
                <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text" name="address_ip" id="address_ip" value="{{ old('address_ip', $dhcpServer->address_ip) }}" required>
                @if($errors->has('address_ip'))
                    <div class="invalid-feedback">
                        {{ $errors->first('address_ip') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.dhcpServer.fields.address_ip_helper') }}</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.dhcp-servers.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <button class="btn btn-danger" type="submit">
            {{ trans('global.save') }}
        </button>
    </div>
</form>
@endsection
