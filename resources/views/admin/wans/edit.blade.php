@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $wan->name }}
@endsection

@section('content')
<form method="POST" action="{{ route("admin.wans.update", [$wan->id]) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf
    <div class="card">
        <div class="card-header">
            {{ trans('global.edit') }} {{ trans('cruds.wan.title_singular') }}
        </div>
        <div class="card-body">
            <div class="row">
                @if (auth()->user()->canChoosePerimeterOf($wan))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $wan->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                        <label for="name">{{ trans('cruds.wan.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $wan->name) }}" required autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.wan.fields.name_helper') }}</span>
                    </div>
                </div>
            @else
                <div class="col-sm-5">
                    <div class="form-group">
                        <label for="name">{{ trans('cruds.wan.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $wan->name) }}" required autofocus/>
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.wan.fields.name_helper') }}</span>
                    </div>
                </div>
            @endif
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="type">{{ trans('cruds.wan.fields.type') }}</label>
                        <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                name="type" id="type">
                            @if (!$type_list->contains(old('type', $wan->type ?? '')))
                                <option>{{ old('type', $wan->type ?? '') }}</option>
                            @endif
                            @foreach($type_list as $type)
                                <option {{ old('type', $wan->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('type'))
                            <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.wan.fields.type_helper') }}</span>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-group">
                        <label for="attributes">{{ trans('cruds.wan.fields.attributes') }}</label>
                        <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                name="attributes[]" id="attributes" multiple>
                            @foreach($attributes_list as $a)
                                <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $wan->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('attributes'))
                            <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.wan.fields.attributes_helper') }}</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="mans">{{ trans('cruds.wan.fields.mans') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('mans') ? 'is-invalid' : '' }}" name="mans[]" id="mans" multiple>
                    @foreach($mans as $id => $mans)
                        <option value="{{ $id }}" {{ (in_array($id, old('mans', [])) || $wan->mans->contains($id)) ? 'selected' : '' }}>{{ $mans }}</option>
                    @endforeach
                </select>
                @if($errors->has('mans'))
                    <div class="invalid-feedback">
                        {{ $errors->first('mans') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.wan.fields.mans_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="lans">{{ trans('cruds.wan.fields.lans') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('lans') ? 'is-invalid' : '' }}" name="lans[]" id="lans" multiple>
                    @foreach($lans as $id => $lans)
                        <option value="{{ $id }}" {{ (in_array($id, old('lans', [])) || $wan->lans->contains($id)) ? 'selected' : '' }}>{{ $lans }}</option>
                    @endforeach
                </select>
                @if($errors->has('lans'))
                    <div class="invalid-feedback">
                        {{ $errors->first('lans') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.wan.fields.lans_helper') }}</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.wans.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <button id="btn-save" class="btn btn-success" type="submit">
            {{ trans('global.save') }}
        </button>
    </div>
</form>
@endsection
