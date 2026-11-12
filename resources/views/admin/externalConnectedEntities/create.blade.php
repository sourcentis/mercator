@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.externalConnectedEntity.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.external-connected-entities.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.create') }} {{ trans('cruds.externalConnectedEntity.title_singular') }}
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
                <div class="col-sm-3">
                        <div class="form-group">
                            <label class="label-required"
                                   for="name">{{ trans('cruds.externalConnectedEntity.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name','') }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required"
                                   for="name">{{ trans('cruds.externalConnectedEntity.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name','') }}" required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="recommended"
                                   for="type">{{ trans('cruds.externalConnectedEntity.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type','') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ old('type') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.type_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.externalConnectedEntity.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', [])) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label class="recommended"
                           for="description">{{ trans('cruds.externalConnectedEntity.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description" id="description">{{ old('description') }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.description_helper') }}</span>
                </div>

            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-6">
                        <b>{{ trans('cruds.externalConnectedEntity.title_source') }}</b>
                    </div>
                    <div class="col-sm-6">
                        <b>{{ trans('cruds.externalConnectedEntity.title_dest') }}</b>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="recommended"
                                   for="entity_resp_id">{{ trans('cruds.externalConnectedEntity.fields.entity') }}</label>
                            <select class="form-control select2 {{ $errors->has('entity') ? 'is-invalid' : '' }}"
                                    name="entity_id" id="entity_id">
                                <option></option>
                                @foreach($entities as $id => $entity)
                                    <option value="{{ $id }}" {{  old('entity_id','') == $id ? 'selected' : '' }}>{{ $entity }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('entity'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('entity') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.entity_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="recommended"
                                   for="network_id">{{ trans('cruds.externalConnectedEntity.fields.network') }}</label>
                            <select class="form-control select2 {{ $errors->has('network') ? 'is-invalid' : '' }}"
                                    name="network_id"
                                    id="network_id">
                                <option></option>
                                @foreach($networks as $id => $network)
                                    <option value="{{ $id }}" {{ old('network_id') == $id ? 'selected' : '' }}>{{ $network }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('network'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('network') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.network_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="contacts">{{ trans('cruds.externalConnectedEntity.fields.contacts') }}</label>
                            <input class="form-control {{ $errors->has('contacts') ? 'is-invalid' : '' }}" type="text"
                                   name="contacts" id="contacts" value="{{ old('contacts', '') }}">
                            @if($errors->has('contacts'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('contacts') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.contacts_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="recommended"
                                   for="subnetworks">{{ trans('cruds.externalConnectedEntity.fields.subnetworks') }}</label>
                            <select class="form-control select2-free {{ $errors->has('subnetworks') ? 'is-invalid' : '' }}"
                                    name="subnetworks[]" id="subnetworks" multiple>
                                @foreach($subnetworks as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('subnetworks', []))  ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('subnetworks'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('subnetworks') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.subnetworks_helper') }}</span>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="src">{{ trans('cruds.externalConnectedEntity.fields.src_desc') }}</label>
                            <input class="form-control {{ $errors->has('src_desc') ? 'is-invalid' : '' }}" type="text"
                                   name="src_desc" id="src_desc" value="{{ old('src_desc', '') }}">
                            @if($errors->has('src_desc'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('src_desc') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.src_desc_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm">
                        <div class="form-group">
                            <label for="dest">{{ trans('cruds.externalConnectedEntity.fields.dest_desc') }}</label>
                            <input class="form-control {{ $errors->has('dest_desc') ? 'is-invalid' : '' }}" type="text"
                                   name="dest_desc" id="dest_desc" value="{{ old('dest_desc', '') }}">
                            @if($errors->has('dest'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.dest_desc_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="dest">{{ trans('cruds.externalConnectedEntity.fields.src') }}</label>
                            <input class="form-control {{ $errors->has('src') ? 'is-invalid' : '' }}" type="text"
                                   name="src" id="src" value="{{ old('src', '') }}">
                            @if($errors->has('src'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('src') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.src_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm">
                        <div class="form-group">
                            <label for="dest">{{ trans('cruds.externalConnectedEntity.fields.dest') }}</label>
                            <input class="form-control {{ $errors->has('dest') ? 'is-invalid' : '' }}" type="text"
                                   name="dest" id="dest" value="{{ old('dest', '') }}">
                            @if($errors->has('dest'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.dest_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.externalConnectedEntity.title_security') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="form-group">
                        <label class="recommended"
                               for="security">{{ trans('cruds.externalConnectedEntity.fields.security') }}</label>
                        <textarea class="form-control ckeditor {{ $errors->has('security') ? 'is-invalid' : '' }}"
                                  name="security" id="security">{{ old('security') }}</textarea>
                        @if($errors->has('security'))
                            <div class="invalid-feedback">
                                {{ $errors->first('security') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.security_helper') }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label class="recommended"
                               for="controls">{{ trans('cruds.externalConnectedEntity.fields.documents') }}</label>
                        <div class="dropzone dropzone-previews" id="dropzoneFileUpload"></div>
                        @if($errors->has('documents'))
                            <div class="invalid-feedback">
                                {{ $errors->first('documents') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.externalConnectedEntity.fields.documents_helper') }}</span>
                    </div>
                </div>

            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.external-connected-entities.index') }}">
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

            var image_uploader = new window.Dropzone("#dropzoneFileUpload", {
                url: '/admin/documents/store',
                headers: {'x-csrf-token': '{{csrf_token()}}'},
                paramName: 'file',
                addRemoveLinks: true,
                timeout: 50000,
                removedfile: function (file) {
                    console.log("remove file " + file.name + " " + file.id);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{csrf_token()}}'
                        },
                        type: 'GET',
                        url: '{{ url( "/admin/documents/delete" ) }}' + "/" + file.id,
                        success: function (data) {
                            console.log("File has been successfully removed");
                        },
                        error: function (e) {
                            console.log("File not removed");
                            console.log(e);
                        }
                    });
                    // console.log('{{ url( "/documents/delete" ) }}'+"/"+file.id+']');
                    var fileRef;
                    return (fileRef = file.previewElement) != null ?
                        fileRef.parentNode.removeChild(file.previewElement) : void 0;
                },
                success: function (file, response) {
                    file.id = response.id;
                    console.log("success response");
                    console.log(response);
                },
                error: function (file, response) {
                    console.log("error response");
                    console.log(response);
                    return false;
                },
                init: function () {
                    //Add existing files into dropzone
                    var existingFiles = [
                            @foreach(session()->get("documents") as $document)
                        {
                            name: "{{ $document->filename }}", size: {{ $document->size }}, id: {{ $document->id }}
                        },
                        @endforeach
                    ];
                    for (i = 0; i < existingFiles.length; i++) {
                        this.emit("addedfile", existingFiles[i]);
                        this.emit("complete", existingFiles[i]);
                    }
                }
            });
        });
    </script>
@endsection
