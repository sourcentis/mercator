@extends('layouts.admin')

@section('title')
    {{ trans('global.create') }} {{ trans('cruds.application.title_singular') }}
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.applications.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.create') }} {{ trans('cruds.application.title_singular') }}
            </div>
            <div class="card-body">
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
                            <label class="label-required" for="name">{{ trans('cruds.application.fields.name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', '') }}"
                                   required maxlength="64" autofocus/>
                            <span class="help-block">{{ trans('cruds.application.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-md-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.application.fields.name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', '') }}"
                                   required maxlength="64" autofocus/>
                            <span class="help-block">{{ trans('cruds.application.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="label-maturity-1" for="type">{{ trans('cruds.application.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!in_array(old('type'), $type_list))
                                    <option> {{ old('type') }}</option>
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
                            <span class="help-block">{{ trans('cruds.application.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.application.fields.attributes') }}</label>
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
                            <span class="help-block">{{ trans('cruds.application.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="description">{{ trans('cruds.application.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description" id="description">{{ old('description') }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="-1"
                                    data-default-img="{{ asset('images/application.png') }}"
                                    data-url-template="{{ route('admin.documents.show', ':id') }}"
                                    data-upload="#iconFile">
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="file" id="iconFile" name="iconFile" accept="image/png"/>
                        </div>
                    </div>
                </div>
		<div class="row">
		    <div class="col-md-9">
		        <div class="form-group">
		            <label for="comments">{{ trans('cruds.application.fields.comments') }}</label>
		            <textarea
		                class="form-control {{ $errors->has('comments') ? 'is-invalid' : '' }}"
		                name="comments"
		                id="comments"
		                rows="5">{{ old('comments', $application->comments ?? '') }}</textarea>
		            @if($errors->has('comments'))
		                <div class="invalid-feedback">
		                    {{ $errors->first('comments') }}
		                </div>
		            @endif
		            <span class="help-block">{{ trans('cruds.application.fields.comments_helper') }}</span>
		        </div>
		    </div>
		</div>

		<!------------------------------------------------------------------------------------------------------------->

                @if (config('mercator.parameters.application_documents'))
                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="documents">{{ trans('cruds.application.fields.documents') }}</label>
                            <div class="dropzone dropzone-previews" id="dropzoneFileUpload"></div>
                            @if($errors->has('documents'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('documents') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.documents_helper') }}</span>
                        </div>
                    </div>
                </div>
                 @endif

                <div class="row">
                    <div class="col-md-6">
                         <div class="form-group">
                             <label class="recommended"
                                    for="application_block_id">{{ trans('cruds.application.fields.application_block') }}</label>
                             <select class="form-control select2 {{ $errors->has('application_block') ? 'is-invalid' : '' }}"
                                     name="application_block_id" id="application_block_id">
                                 <option value="">...</option>
                                 @foreach($applicationBlocks as $id => $applicationBlock)
                                     <option value="{{ $id }}" {{ old('application_block_id') == $id ? 'selected' : '' }}>{{ $applicationBlock }}</option>
                                 @endforeach
                             </select>
                             @if($errors->has('application_block'))
                                 <div class="invalid-feedback">
                                     {{ $errors->first('application_block') }}
                                 </div>
                             @endif
                             <span class="help-block">{{ trans('cruds.application.fields.application_block_helper') }}</span>
                         </div>
                     </div>
                </div>

            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.ecosystem.title_short") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-2"
                                   for="responsible">{{ trans('cruds.application.fields.responsible') }}</label>
                            <select class="form-control select2-free {{ $errors->has('responsible') ? 'is-invalid' : '' }}"
                                    name="responsibles[]" id="responsibles" multiple>
                                @foreach($responsible_list as $resp)
                                    <option {{ in_array($resp, old('responsibles', []), true) ? 'selected' : '' }}>{{$resp}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('responsible'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('responsible') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.responsible_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-2" for="entity_resp_id">{{ trans('cruds.application.fields.entity_resp') }}</label>
                            <select class="form-control select2 {{ $errors->has('entity_resp') ? 'is-invalid' : '' }}"
                                    name="entity_resp_id" id="entity_resp_id">
                                <option value="">...</option>
                                @foreach($entities as $id => $name)
                                    <option value="{{ $id }}" {{ old('entity_resp_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('entity_resp'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('entity_resp') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.entity_resp_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="entities">{{ trans('cruds.application.fields.entities') }}</label>
                            <select class="form-control select2 {{ $errors->has('entities') ? 'is-invalid' : '' }}"
                                    name="entities[]" id="entities" multiple>
                                @foreach($entities as $id => $entities)
                                    <option value="{{ $id }}" {{ in_array($id, old('entities', [])) ? 'selected' : '' }}>{{ $entities }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('entities'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('entities') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.entities_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="referent">{{ trans('cruds.application.fields.functional_referent') }}</label>
                            <select class="form-control select2-free {{ $errors->has('functional_referent') ? 'is-invalid' : '' }}"
                                    name="functional_referent" id="referent">
                                @if (!$referent_list->contains(old('functional_referent')))
                                    <option> {{ old('functional_referent') }}</option>
                                @endif
                                @foreach($referent_list as $t)
                                    <option {{ old('functional_referent') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('functional_referent'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('functional_referent') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.functional_referent_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="editor">{{ trans('cruds.application.fields.editor') }}</label>
                            <select class="form-control select2-free {{ $errors->has('editor') ? 'is-invalid' : '' }}"
                                    name="editor" id="editor">
                                @if (!$editor_list->contains(old('editor')))
                                    <option> {{ old('editor') }}</option>
                                @endif
                                @foreach($editor_list as $t)
                                    <option {{ old('editor') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('editor'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('editor') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.editor_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="users">{{ trans('cruds.application.fields.users') }}</label>
                            <select class="form-control select2-free {{ $errors->has('users') ? 'is-invalid' : '' }}"
                                    name="users" id="users">
                                @if (!$users_list->contains(old('users')))
                                    <option> {{ old('users') }}</option>
                                @endif
                                @foreach($users_list as $t)
                                    <option {{ old('users') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('users'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('users') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.users_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="logical_servers">{{ trans('cruds.application.fields.administrators') }}</label>
                            <select class="form-control select2 {{ $errors->has('administrators') ? 'is-invalid' : '' }}"
                                    name="administrators[]" id="administrators" multiple>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('administrators', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('administrators'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('administrators') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.administrators_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.application.title_singular") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="technology">{{ trans('cruds.application.fields.technology') }}</label>
                            <select class="form-control select2-free {{ $errors->has('technology') ? 'is-invalid' : '' }}"
                                    name="technologies[]" id="technologies" multiple>
                                @foreach($technology_list as $t)
                                    <option {{ in_array($t, old('technologies', []), true) ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>

                            @if($errors->has('technology'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('technology') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.technology_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="recommended"
                                   for="external">{{ trans('cruds.application.fields.external') }}</label>
                            <select class="form-control select2-free {{ $errors->has('external') ? 'is-invalid' : '' }}"
                                    name="externals[]" id="externals" multiple>
                                @foreach($external_list as $t)
                                    <option {{ in_array($t, old('externals', []), true) ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            <span class="help-block">{{ trans('cruds.application.fields.external_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="hosting">{{ trans('cruds.application.fields.hosting') }}</label>
                            <select class="form-control select2-free {{ $errors->has('hosting') ? 'is-invalid' : '' }}"
                                    name="hosting" id="hosting">
                                @if (!$hosting_list->contains(old('hosting')))
                                    <option> {{ old('hosting') }}</option>
                                @endif
                                @foreach($hosting_list as $t)
                                    <option {{ old('hosting') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('hosting'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('hosting') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.hosting_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="urls">{{ trans('cruds.application.fields.urls') }}</label>
                            <input class="form-control {{ $errors->has('urls') ? 'is-invalid' : '' }}"
                                   type="text" name="urls" id="urls"
                                   value="{{ old('urls', '') }}">
                            @if($errors->has('urls'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('urls') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.urls_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">{{ trans('cruds.application.fields.status') }}</label>
                            <select class="form-control select2-free {{ $errors->has('status') ? 'is-invalid' : '' }}"
                                    name="status" id="status">
                                @if (!$status_list->contains(old('status')))
                                    <option> {{ old('status') }}</option>
                                @endif
                                @foreach($status_list as $t)
                                    <option {{ old('status') == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('status'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('status') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.status_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="install_date">{{ trans('cruds.application.fields.install_date') }}</label>
                            <input class="form-control date" type="date" name="install_date" id="install_date"
                                   value="{{ old('install_date') }}">
                            <span class="help-block">{{ trans('cruds.application.fields.install_date_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="prod_date">{{ trans('cruds.application.fields.prod_date') }}</label>
                            <input class="form-control date" type="date" name="prod_date" id="prod_date"
                                   value="{{ old('prod_date') }}">
                            <span class="help-block">{{ trans('cruds.application.fields.prod_date_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="update_date">{{ trans('cruds.application.fields.update_date') }}</label>
                            <input class="form-control date" type="date" id="update_date" name="update_date"
                                   value="{{ old('update_date') }}">
                            <span class="help-block">{{ trans('cruds.application.fields.update_date_helper') }}</span>
                        </div>
                    </div>
                    <!-- no events -->
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="documentation">{{ trans('cruds.application.fields.documentation') }}</label>
                            <input class="form-control {{ $errors->has('documentation') ? 'is-invalid' : '' }}"
                                   type="text" name="documentation" id="documentation"
                                   value="{{ old('documentation', '') }}">
                            @if($errors->has('documentation'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('documentation') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.documentation_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="services">{{ trans('cruds.application.fields.services') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('services') ? 'is-invalid' : '' }}"
                                    name="services[]" id="services" multiple>
                                @foreach($services as $id => $services)
                                    <option value="{{ $id }}" {{ in_array($id, old('services', [])) ? 'selected' : '' }}>{{ $services }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('services'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('services') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.services_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label for="databases">{{ trans('cruds.application.fields.databases') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('databases') ? 'is-invalid' : '' }}"
                                    name="databases[]" id="databases" multiple>
                                @foreach($databases as $id => $databases)
                                    <option value="{{ $id }}" {{ in_array($id, old('databases', [])) ? 'selected' : '' }}>{{ $databases }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('databases'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('databases') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.databases_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.application.title_security') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="label-maturity-2"
                                   for="security_need">{{ trans('cruds.application.fields.security_need') }}</label>
                            <table cellspacing="5" cellpadding="5" border="0" width='100%'>
                                <tr>
                                    <td align="right" valign="bottom">
                                        <label for="security_need">{{ trans("global.confidentiality_short") }}</label>
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans("global.confidentiality") }}</span>
                                        <select class="form-control select2 risk {{ $errors->has('security_need_c') ? 'is-invalid' : '' }}"
                                                name="security_need_c" id="security_need_c">
                                            <option value="0" {{ old('security_need_c') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_c') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_c') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_c') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_c') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right" valign="bottom">
                                        <label for="security_need">{{ trans("global.integrity_short") }}</label>
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans("global.integrity") }}</span>
                                        <select class="form-control select2 risk {{ $errors->has('security_need_i') ? 'is-invalid' : '' }}"
                                                name="security_need_i" id="security_need_i">
                                            <option value="0" {{ old('security_need_i') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_i') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_i') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_i') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_i') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right" valign="bottom">
                                        <label for="security_need">{{ trans("global.availability_short") }}</label>
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans("global.availability") }}</span>
                                        <select class="form-control select2 risk {{ $errors->has('security_need_a') ? 'is-invalid' : '' }}"
                                                name="security_need_a" id="security_need_a">
                                            <option value="0" {{ old('security_need_a') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_a') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_a') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_a') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_a') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    <td align="right" valign="bottom">
                                        <label for="security_need">{{ trans("global.tracability_short") }}</label>
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans("global.tracability") }}</span>
                                        <select class="form-control select2 risk {{ $errors->has('security_need_t') ? 'is-invalid' : '' }}"
                                                name="security_need_t" id="security_need_t">
                                            <option value="0" {{ old('security_need_t') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                            <option value="1" {{ old('security_need_t') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                            <option value="2" {{ old('security_need_t') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                            <option value="3" {{ old('security_need_t') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                            <option value="4" {{ old('security_need_t') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                        </select>
                                    </td>
                                    @if (config('mercator-config.parameters.security_need_auth'))
                                        <td align="right" valign="bottom">
                                            <label for="security_need">{{ trans("global.authenticity_short") }}</label>
                                        </td>
                                        <td>
                                            <span class="help-block">{{ trans("global.authenticity") }}</span>
                                            <select class="form-control select2 risk {{ $errors->has('security_need_auth') ? 'is-invalid' : '' }}"
                                                    name="security_need_auth" id="security_need_auth">
                                                <option value="-1"></option>
                                                <option value="0" {{ old('security_need_auth') == 0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                                <option value="1" {{ old('security_need_auth') == 1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                                <option value="2" {{ old('security_need_auth') == 2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                                <option value="3" {{ old('security_need_auth') == 3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                                <option value="4" {{ old('security_need_auth') == 4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
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
                            <span class="help-block">{{ trans('cruds.application.fields.security_need_helper') }}</span>
                        </div>

                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label>{{ trans('cruds.application.fields.RTO') }}</label>
                            <table>
                                <tr>
                                    <td>
                                        <span class="help-block">{{ trans('global.days') }}</span>
                                        <input type="number" class="form-control" id="rto_days" name="rto_days" min="0"
                                               value="{{ old('rto_days') }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.hours') }}</span>
                                        <input type="number" class="form-control" id="rto_hours" name="rto_hours"
                                               min="0" max="24" value="{{ old('rto_hours') }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.minutes') }}</span>
                                        <input type="number" class="form-control" id="rto_minutes" name="rto_minutes"
                                               min="0" max="60" value="{{ old('rto_minutes') }}">
                                    </td>
                                </tr>
                            </table>
                            <span class="help-block">{{ trans('cruds.application.fields.RTO_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="form-group">
                            <label>{{ trans('cruds.application.fields.RPO') }}</label>
                            <table>
                                <tr>
                                    <td>
                                        <span class="help-block">{{ trans('global.days') }}</span>
                                        <input type="number" class="form-control" id="rpo_days" name="rpo_days" min="0"
                                               value="{{ old('rpo_days') }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.hours') }}</span>
                                        <input type="number" class="form-control" id="rpo_hours" name="rpo_hours"
                                               min="0"
                                               max="24" value="{{ old('rpo_hours') }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.minutes') }}</span>
                                        <input type="number" class="form-control" id="rpo_minutes" name="rpo_minutes"
                                               min="0"
                                               max="60" value="{{ old('rpo_minutes') }}">
                                    </td>
                                </tr>
                            </table>
                            <span class="help-block">{{ trans('cruds.application.fields.RPO_helper') }}</span>
                        </div>

                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            {{-- Common Platform Enumeration --}}
            <!------------------------------------------------------------------------------------------------------------->
            @include('partials.cpe-selector', [
                'part'    => 'a',
                'vendor'  => '',
                'product' => '',
                'version' => '',
            ])
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.metier.title_short") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="processes">{{ trans('cruds.application.fields.processes') }}</label>
                            <select class="form-control select2 {{ $errors->has('processes') ? 'is-invalid' : '' }}"
                                    name="processes[]" id="processes" multiple>
                                @foreach($processes as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('processes', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('processes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('processes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.processes_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="activities">{{ trans('cruds.application.fields.activities') }}</label>
                            <select class="form-control select2 {{ $errors->has('activities') ? 'is-invalid' : '' }}"
                                    name="activities[]" id="activities" multiple>
                                @foreach($activities as $id => $activity)
                                    <option value="{{ $id }}" {{ in_array($id, old('activities', [])) ? 'selected' : '' }}>{{ $activity }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('activities'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('activities') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.activities_helper') }}</span>
                        </div>
                    </div>

                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.logical_infrastructure.title_short") }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="logical_servers">{{ trans('cruds.application.fields.logical_servers') }}</label>
                            <select class="form-control select2 {{ $errors->has('logical_servers') ? 'is-invalid' : '' }}"
                                    name="logical_servers[]" id="logical_servers" multiple>
                                @foreach($logical_servers as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('logical_servers', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('logical_servers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('logical_servers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.logical_servers_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="containers">{{ trans('cruds.application.fields.containers') }}</label>
                            <select class="form-control select2 {{ $errors->has('containers') ? 'is-invalid' : '' }}"
                                    name="containers[]" id="containers" multiple>
                                @foreach($containers as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('containers', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('containers'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('containers') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.containers_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="security_devices">{{ trans('cruds.application.fields.security_devices') }}</label>
                            <select class="form-control select2 {{ $errors->has('security_devices') ? 'is-invalid' : '' }}"
                                    name="security_devices[]" id="security_devices" multiple>
                                @foreach($security_devices as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('security_devices', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('security_devices'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('security_devices') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.application.fields.security_devices_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.applications.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-success" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection

@section('scripts')
    @parent
    @if (config('mercator.parameters.application_documents'))
    <script @nonce>
        document.addEventListener("DOMContentLoaded", function () {
            var image_uploader = new Dropzone("#dropzoneFileUpload", {
                url: '/admin/documents/store',
                headers: {'x-csrf-token': '{{csrf_token()}}'},
                paramName: 'file',
                addRemoveLinks: true,
                timeout: 50000,
                removedfile: function (file) {
                    $.ajax({
                        headers: {'X-CSRF-TOKEN': '{{csrf_token()}}'},
                        type: 'GET',
                        url: '{{ url("/admin/documents/delete") }}' + '/' + file.id,
                    });
                    var fileRef;
                    return (fileRef = file.previewElement) != null ?
                        fileRef.parentNode.removeChild(file.previewElement) : void 0;
                },
                success: function (file, response) {
                    file.id = response.id;
                },
                error: function (file, response) {
                    return false;
                },
            });
        });
    </script>
    @endif
    <script @nonce>
        document.addEventListener("DOMContentLoaded", function () {

            // submit the correct button when "enter" key pressed
            $("form input").keypress(function (e) {
                if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
                    $('input[type=submit].default').click();
                    return false;
                } else {
                    return true;
                }
            });
        });
    </script>
@endsection
