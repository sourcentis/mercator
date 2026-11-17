@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $zone->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.zones.update', [$zone->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.zone.title_singular') }}
            </div>
            <div class="card-body">

                {{-- Ligne 1 : nom, type, attributs --}}
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($zone))
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $zone->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.zone.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   type="text" name="name" id="name"
                                   value="{{ old('name', $zone->name) }}" required autofocus/>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <span class="help-block">{{ trans('cruds.zone.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-md-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.zone.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   type="text" name="name" id="name"
                                   value="{{ old('name', $zone->name) }}" required autofocus/>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <span class="help-block">{{ trans('cruds.zone.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.zone.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @php $currentType = old('type', $zone->type); @endphp
                                @if (!$type_list->contains($currentType))
                                    <option>{{ $currentType }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ $currentType === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <span class="help-block">{{ trans('cruds.zone.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.zone.fields.attributes') }}</label>
                            @php $currentAttrs = old('attributes') !== null ? old('attributes') : array_filter(explode(' ', $zone->attributes ?? '')); @endphp
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, $currentAttrs) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @error('attributes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <span class="help-block">{{ trans('cruds.zone.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Ligne 2 : description --}}
                <div class="form-group">
                    <label for="description">{{ trans('cruds.zone.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description" id="description">{{ old('description', $zone->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Ligne 3 : zones parentes / zones enfants --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="parentZones">{{ trans('cruds.zone.fields.parent_zones') }}</label>
                            <div style="padding-bottom:4px">
                                <span class="btn btn-info btn-xs select-all" style="border-radius:0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all" style="border-radius:0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('parentZones') ? 'is-invalid' : '' }}"
                                    name="parentZones[]" id="parentZones" multiple>
                                @foreach($zones as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ (in_array($id, old('parentZones', [])) || $zone->parentZones->contains($id)) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parentZones') <span class="text-danger">{{ $message }}</span> @enderror
                            <span class="help-block">{{ trans('cruds.zone.fields.parent_zones_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="childZones">{{ trans('cruds.zone.fields.child_zones') }}</label>
                            <div style="padding-bottom:4px">
                                <span class="btn btn-info btn-xs select-all" style="border-radius:0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all" style="border-radius:0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('childZones') ? 'is-invalid' : '' }}"
                                    name="childZones[]" id="childZones" multiple>
                                @foreach($zones as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ (in_array($id, old('childZones', [])) || $zone->childZones->contains($id)) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('childZones') <span class="text-danger">{{ $message }}</span> @enderror
                            <span class="help-block">{{ trans('cruds.zone.fields.child_zones_helper') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Ligne 4 : locaux --}}
                <div class="form-group">
                    <label for="buildings">{{ trans('cruds.zone.fields.buildings') }}</label>
                    <div style="padding-bottom:4px">
                        <span class="btn btn-info btn-xs select-all" style="border-radius:0">{{ trans('global.select_all') }}</span>
                        <span class="btn btn-info btn-xs deselect-all" style="border-radius:0">{{ trans('global.deselect_all') }}</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('buildings') ? 'is-invalid' : '' }}"
                            name="buildings[]" id="buildings" multiple>
                        @foreach($buildings as $id => $name)
                            <option value="{{ $id }}"
                                {{ (in_array($id, old('buildings', [])) || $zone->buildings->contains($id)) ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('buildings') <span class="text-danger">{{ $message }}</span> @enderror
                    <span class="help-block">{{ trans('cruds.zone.fields.buildings_helper') }}</span>
                </div>

                {{-- Ligne 5 : admin users --}}
                <div class="form-group">
                    <label for="adminUsers">{{ trans('cruds.zone.fields.admin_users') }}</label>
                    <div style="padding-bottom:4px">
                        <span class="btn btn-info btn-xs select-all" style="border-radius:0">{{ trans('global.select_all') }}</span>
                        <span class="btn btn-info btn-xs deselect-all" style="border-radius:0">{{ trans('global.deselect_all') }}</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('adminUsers') ? 'is-invalid' : '' }}"
                            name="adminUsers[]" id="adminUsers" multiple>
                        @foreach($adminUsers as $id => $name)
                            <option value="{{ $id }}"
                                {{ (in_array($id, old('adminUsers', [])) || $zone->adminUsers->contains($id)) ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('adminUsers') <span class="text-danger">{{ $message }}</span> @enderror
                    <span class="help-block">{{ trans('cruds.zone.fields.admin_users_helper') }}</span>
                </div>

            </div>
        </div>

        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.zones.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
