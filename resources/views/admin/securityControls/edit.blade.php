@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $securityControl->name }}
@endsection

@section('content')
<form method="POST" action="{{ route("admin.security-controls.update", [$securityControl->id]) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf
    <div class="card">
        <div class="card-header">
            {{ trans('global.edit') }} {{ trans('cruds.securityControl.title_singular') }}
        </div>
        <div class="card-body">
            @if (auth()->user()->canChoosePerimeterOf($securityControl))
                <div class="form-group">
                    <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                    <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                            name="perimeter_id" id="perimeter_id">
                        @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                            <option value="{{ $perimeterOption->id }}"
                                    {{ (int) old('perimeter_id', $securityControl->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                {{ $perimeterOption->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->has('perimeter_id'))
                        <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                    @endif
                </div>
            @endif
            <div class="form-group">
                <label class="label-required" for="name">{{ trans('cruds.securityControl.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $securityControl->name) }}" required autofocus/>
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.securityControl.fields.name_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.securityControl.fields.description') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description', $securityControl->description) }}</textarea>
                @if($errors->has('description'))
                    <div class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.securityControl.fields.description_helper') }}</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.security-controls.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
        <button id="btn-save" class="btn btn-success" type="submit">
            {{ trans('global.save') }}
        </button>
    </div>
</form>
@endsection
