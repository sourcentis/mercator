@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.backup.title_singular') }}
@endsection

@section('content')
<form method="POST" action="{{ route('admin.backups.store') }}">
    @csrf
    <div class="card">
        <div class="card-header">{{ trans('global.create') }} {{ trans('cruds.backup.title_singular') }}</div>
        <div class="card-body">

            {{-- Nom + Type --}}
            <div class="row">
                @if (auth()->user()->hasMultiplePerimeters())
                <div class="col-md-2">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="label-required" for="name">{{ trans('cruds.backup.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               type="text" name="name" id="name"
                               value="{{ old('name', '') }}" required maxlength="255" autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.backup.fields.name_helper') }}</span>
                    </div>
                </div>
            @else
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="label-required" for="name">{{ trans('cruds.backup.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               type="text" name="name" id="name"
                               value="{{ old('name', '') }}" required maxlength="255" autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.backup.fields.name_helper') }}</span>
                    </div>
                </div>
            @endif
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="type">{{ trans('cruds.backup.fields.type') }}</label>
                        <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                name="type" id="type">
                            <option></option>
                            @foreach($type_list as $type)
                                <option {{ old('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        <span class="help-block">{{ trans('cruds.backup.fields.type_helper') }}</span>
                    </div>
                </div>

                {{-- Attributs --}}
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="attributes">{{ trans('cruds.backup.fields.attributes') }}</label>
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
                        <span class="help-block">{{ trans('cruds.backup.fields.attributes_helper') }}</span>
                    </div>
            </div>

            {{-- Description --}}
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="label-maturity-1" for="description">{{ trans('cruds.backup.fields.description') }}</label>
                        <textarea
                                class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                name="description" id="description">{{ old('description') }}</textarea>
                        @if($errors->has('description'))
                            <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.backup.fields.description_helper') }}</span>
                    </div>
                </div>
            </div>

            {{-- Fréquence + Cycle + Rétention --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="backup_frequency">{{ trans('cruds.backup.frequency') }}</label>
                        <select class="form-control select2" name="backup_frequency" id="backup_frequency">
                            <option value="">{{ trans('global.pleaseSelect') }}</option>
                            @foreach(trans('cruds.backup.frequencies') as $val => $label)
                                <option value="{{ $val }}" {{ old('backup_frequency') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="help-block">{{ trans('cruds.backup.fields.backup_frequency_helper') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="backup_cycle">{{ trans('cruds.backup.cycle') }}</label>
                        <select class="form-control select2" name="backup_cycle" id="backup_cycle">
                            <option value="">{{ trans('global.pleaseSelect') }}</option>
                            @foreach(trans('cruds.backup.cycles') as $val => $label)
                                <option value="{{ $val }}" {{ old('backup_cycle') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="help-block">{{ trans('cruds.backup.fields.backup_cycle_helper') }}</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="backup_retention">{{ trans('cruds.backup.retention') }}</label>
                        <input class="form-control {{ $errors->has('backup_retention') ? 'is-invalid' : '' }}"
                               type="number" name="backup_retention" id="backup_retention"
                               min="1" max="36500" value="{{ old('backup_retention') }}"/>
                        @if($errors->has('backup_retention'))
                            <div class="invalid-feedback">{{ $errors->first('backup_retention') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.backup.fields.backup_retention_helper') }}</span>
                    </div>
                </div>
            </div>

            {{-- Serveurs logiques --}}
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="logical_server_ids">{{ trans('cruds.backup.fields.logical_servers') }}</label>
                        <select class="form-control select2 {{ $errors->has('logical_server_ids') ? 'is-invalid' : '' }}"
                                name="logical_server_ids[]" id="logical_server_ids" multiple>
                            @foreach($logicalServers as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, old('logical_server_ids', [])) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('logical_server_ids'))
                            <div class="invalid-feedback">{{ $errors->first('logical_server_ids') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.backup.fields.logical_server_helper') }}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- Devices de stockage --}}
                <div class="col">
                    <div class="form-group">
                        <label for="storage_device_ids">{{ trans('cruds.backup.fields.storage_devices') }}</label>
                        <select class="form-control select2 {{ $errors->has('storage_device_ids') ? 'is-invalid' : '' }}"
                                name="storage_device_ids[]" id="storage_device_ids" multiple>
                            @foreach($storageDevices as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, old('storage_device_ids', [])) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('storage_device_ids'))
                            <div class="invalid-feedback">{{ $errors->first('storage_device_ids') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.backup.fields.storage_device_helper') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.backups.index') }}">{{ trans('global.back_to_list') }}</a>
    <button id="btn-save" class="btn btn-success" type="submit">{{ trans('global.save') }}</button>
</div>
</form>
@endsection
