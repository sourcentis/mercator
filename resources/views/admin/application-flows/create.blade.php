@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.applicationFlow.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.application-flows.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.create') }} {{ trans('cruds.applicationFlow.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->hasMultiplePerimeters())
                <div class="col-sm-2">
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
                <div class="col-sm-1">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.applicationFlow.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', '') }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationFlow.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.applicationFlow.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', '') }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationFlow.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-3">

                        <div class="form-group">
                            <label for="name">{{ trans('cruds.applicationFlow.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type') == $type ? 'selected' : '' }}>{{$type}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationFlow.fields.type_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.applicationFlow.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', []), true) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationFlow.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.applicationFlow.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description" id="description">{{ old('description') }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <!-- <span class="help-block">{{ trans('cruds.applicationFlow.fields.description_helper') }}</span> -->
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.applicationFlow.fields.source') }}</label>
                            <select class="form-control select2 {{ $errors->has('dest_id') ? 'is-invalid' : '' }}"
                                    name="src_id" id="src_id">
                                <option></option>
                                @foreach($items as $id => $name)
                                    <option value="{{ $id }}" {{ old('src_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('src_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('src_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationFlow.fields.source_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.applicationFlow.fields.destination') }}</label>
                            <select class="form-control select2 {{ $errors->has('dest_id') ? 'is-invalid' : '' }}"
                                    name="dest_id" id="dest_id">
                                <option></option>
                                @foreach($items as $id => $name)
                                    <option value="{{ $id }}" {{ old('dest_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('dest_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.applicationFlow.fields.destination_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="informations">{{ trans('cruds.database.fields.informations') }}</label>
                            <select class="form-control select2 {{ $errors->has('informations') ? 'is-invalid' : '' }}"
                                    name="informations[]" id="informations" multiple>
                                @foreach($informations as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('informations', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('informations'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('informations') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.database.fields.informations_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-check">
                            <label for="crypted">{{ trans('cruds.applicationFlow.fields.crypted') }}</label>
                            <div class="form-switch">
                                <input class="form-check-input" type="checkbox" id="crypted" name="crypted"
                                       value="1" {{ old('crypted') ? 'checked' : '' }}>
                                <label class="form-check-label"
                                       for="crypted">{{ trans('cruds.applicationFlow.fields.crypted_helper') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-check">
                            <label for="bidirectional">{{ trans('cruds.applicationFlow.fields.bidirectional') }}</label>
                            <div class="form-switch">
                                <input class="form-check-input" type="checkbox" id="bidirectional" name="bidirectional"
                                       value="1" {{ old('bidirectional') ? 'checked' : '' }}>
                                <label class="form-check-label"
                                       for="bidirectional">{{ trans('cruds.applicationFlow.fields.bidirectional_helper') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.application-flows.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection

@section('scripts')
    <script @nonce>
        document.addEventListener("DOMContentLoaded", function () {

            var allEditors = document.querySelectorAll('.ckeditor');
            for (var i = 0; i < allEditors.length; ++i) {
                ClassicEditor.create(
                    allEditors[i], {
                        extraPlugins: []
                    }
                );
            }

            $(".select2-free").select2({
                placeholder: "{{ trans('global.pleaseSelect') }}",
                allowClear: true,
                tags: true
            });

        });

    </script>
@endsection
