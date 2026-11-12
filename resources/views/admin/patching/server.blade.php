@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.patching') }}
@endsection

@section('content')

<form method="POST" action='{{ route("admin.patching.server") }}' enctype="multipart/form-data">
    @method('PUT')
    @csrf
    <input type='hidden' name="id" value='{{$server->id}}'/>

    <!---------------------------------------------------------------------------------------------------->
    <div class="row">
        <div class="col-sm">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.patching.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </div>
    </div>

    <div class="card">
    <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
        {{ trans('panel.menu.patching') }}
        </div>
    <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th width="10%">
                            {{ trans('cruds.logicalServer.fields.name') }}
                        </th>
                        <td>
                            {{ $server->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.logicalServer.fields.description') }}
                        </th>
                        <td>
                            {!! $server->description !!}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-header">
            Patching
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="recommended" for="attributes">{{ trans('cruds.logicalServer.fields.attributes') }}</label>
                        <select class="form-control select2-free {{ $errors->has('patching_group') ? 'is-invalid' : '' }}" name="attributes[]" id="attributes[]" multiple>
                            @foreach($attributes_list as $a)
                                <option {{ in_array($a, old('attributes', explode(' ', $server->attributes ?? '')), true) ? 'selected' : '' }}>{{$a}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('attributes'))
                            <div class="invalid-feedback">
                                {{ $errors->first('attributes') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.logicalServer.fields.attributes_helper') }}</span>
                    </div>
                </div>
               <div class="col-3">
                    <div class="form-group">
                      <label for="update_date">Mise à jour</label>
                      <div class="d-flex align-items-center">
                        <input class="form-control date me-2" type="date" id="update_date" name="update_date" value="2023-10-01">
                        <a href="#" class="nav-link" id="clock">
                          <i class="bi bi-alarm-fill"></i>
                        </a>
                      </div>
                      <span class="help-block">Date de mise à jour.</span>
                    </div>
                  </div>
                <div class="col-2">
                    <div class="form-group">
                        <label for="update_date">
                            <div class="row">
                                <div class="col">
                                    Periodicité
                                </div>
                                <div class="col-1 d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="global_periodicity" id="globalCheckbox">
                                    <label class="form-check-label" for="globalCheckbox">
                                      Global
                                    </label>
                                </div>
                            </div>
                        </label>
                        <select class="form-control select2" name="patching_frequency" id="patching_frequency">
                            <option vlaue="0"></option>
                            <option value="1" {{ ($server->patching_frequency===1) ? "selected" : ""}}>1 month</option>
                            <option value="2" {{ ($server->patching_frequency===2) ? "selected" : ""}}>2 months</option>
                            <option value="3" {{ ($server->patching_frequency===3) ? "selected" : ""}}>3 months</option>
                            <option value="4" {{ ($server->patching_frequency===4) ? "selected" : ""}}>4 months</option>
                            <option value="6" {{ ($server->patching_frequency===6) ? "selected" : ""}}>6 months</option>
                            <option value="12" {{ ($server->patching_frequency===12) ? "selected" : ""}}>12 months</option>
                        </select>
                        <span class="help-block">Fréquence de mise à jour</span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="next_update">{{ trans('cruds.logicalServer.fields.next_update') }}</label>
                        <input class="form-control date" type="date" name="next_update"  id="next_update" value="{{ old('next_update', $server->next_update) }}">
                        <span class="help-block">{{ trans('cruds.logicalServer.fields.next_update_helper') }}</span>
                    </div>
                </div>
            </div>
        </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.logical_infrastructure.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->

    <div class="card-body">
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label class="recommended" for="operating_system">{{ trans('cruds.logicalServer.fields.operating_system') }}</label>
                    <select class="form-control select2-free {{ $errors->has('operating_system') ? 'is-invalid' : '' }}" name="operating_system" id="operating_system">
                        @if (!$operating_system_list->contains(old('operating_system')))
                            <option> {{ old('operating_system') }}</option>
                        @endif
                        @foreach($operating_system_list as $t)
                            <option {{ (old('operating_system') ? old('operating_system') : $server->operating_system) == $t ? 'selected' : '' }}>{{$t}}</option>
                        @endforeach
                    </select>
                    @if($errors->has('operating_system'))
                        <div class="invalid-feedback">
                            {{ $errors->first('operating_system') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.operating_system_helper') }}</span>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="install_date">{{ trans('cruds.logicalServer.fields.install_date') }}</label>
                    <input class="form-control date" type="date" name="install_date" id="install_date" value="{{ old('install_date', $server->install_date) }}">
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.install_date_helper') }}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="recommended" for="environment">{{ trans('cruds.logicalServer.fields.environment') }}</label>
                    <select class="form-control select2-free {{ $errors->has('environment') ? 'is-invalid' : '' }}" name="environment" id="environment">
                        @if (!$environment_list->contains(old('environment')))
                            <option> {{ old('environment') }}</option>
                        @endif
                        @foreach($environment_list as $t)
                            <option {{ (old('environment') ? old('environment') : $server->environment) == $t ? 'selected' : '' }}>{{$t}}</option>
                        @endforeach
                    </select>
                    @if($errors->has('environment'))
                        <div class="invalid-feedback">
                            {{ $errors->first('environment') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.environment_helper') }}</span>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label class="recommended" for="address_ip">{{ trans('cruds.logicalServer.fields.address_ip') }}</label>
                    <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text" name="address_ip" id="address_ip" value="{{ old('address_ip', $server->address_ip) }}">
                    @if($errors->has('address_ip'))
                        <div class="invalid-feedback">
                            {{ $errors->first('address_ip') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.address_ip_helper') }}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label for="net_services">{{ trans('cruds.logicalServer.fields.net_services') }}</label>
                    <input class="form-control {{ $errors->has('net_services') ? 'is-invalid' : '' }}" type="text" name="net_services" id="net_services" value="{{ old('net_services', $server->net_services) }}">
                    @if($errors->has('net_services'))
                        <div class="invalid-feedback">
                            {{ $errors->first('net_services') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.net_services_helper') }}</span>
                </div>

            </div>
        </div>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.logicalServer.fields.configuration") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label for="cpu">{{ trans('cruds.logicalServer.fields.cpu') }}</label>
                    <input class="form-control {{ $errors->has('cpu') ? 'is-invalid' : '' }}" type="text" name="cpu" id="cpu" value="{{ old('cpu', $server->cpu) }}">
                    @if($errors->has('cpu'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cpu') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.cpu_helper') }}</span>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="memory">{{ trans('cruds.logicalServer.fields.memory') }}</label>
                    <input class="form-control {{ $errors->has('memory') ? 'is-invalid' : '' }}" type="text" name="memory" id="memory" value="{{ old('memory', $server->memory) }}">
                    @if($errors->has('memory'))
                        <div class="invalid-feedback">
                            {{ $errors->first('memory') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.memory_helper') }}</span>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="disk">{{ trans('cruds.logicalServer.fields.disk') }}</label>
                    <input class="form-control {{ $errors->has('disk') ? 'is-invalid' : '' }}" type="text" name="disk" id="disk" value="{{ old('disk', $server->disk) }}">
                    @if($errors->has('disk'))
                        <div class="invalid-feedback">
                            {{ $errors->first('disk') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.disk_helper') }}</span>
                </div>
            </div>
            <div class="col-sm">
                <div class="form-group">
                    <label for="disk_used">{{ trans('cruds.logicalServer.fields.disk_used') }}</label>
                    <input class="form-control {{ $errors->has('disk_used') ? 'is-invalid' : '' }}" type="text" name="disk_used" id="disk_used" value="{{ old('disk_used', $server->disk_used) }}">
                    @if($errors->has('disk_used'))
                        <div class="invalid-feedback">
                            {{ $errors->first('disk_used') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.disk_used_helper') }}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <textarea class="form-control ckeditor {{ $errors->has('configuration') ? 'is-invalid' : '' }}" name="configuration" id="configuration">{{ old('configuration', $server->configuration) }}</textarea>
                    @if($errors->has('configuration'))
                        <div class="invalid-feedback">
                            {{ $errors->first('configuration') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.logicalServer.fields.configuration_helper') }}</span>
                </div>
            </div>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        <div class="row">
            <div class="col-sm">
                <div class="form-group">
                    <label class="recommended" for="controls">{{ trans('cruds.dataProcessing.fields.documents') }}</label>
                    <div class="dropzone dropzone-previews" id="dropzoneFileUpload"></div>
                    @if($errors->has('documents'))
                        <div class="invalid-feedback">
                            {{ $errors->first('documents') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.dataProcessing.fields.documents_helper') }}</span>
                </div>
            </div>
        </div>
        <!---------------------------------------------------------------------------------------------------->
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.patching.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script @nonce>
document.addEventListener("DOMContentLoaded", function () {

    const update_date = document.getElementById('update_date');
    const patching_frequency = document.getElementById('patching_frequency');
    const next_update = document.getElementById('next_update');

    update_date.addEventListener('change', function() {
       if (patching_frequency.value=="") {
           next_update.value="";
       }
       else {
        var startDate = moment(update_date.value, 'YYYY-MM-DD');
        var newDate = startDate.add(patching_frequency.value, 'months');
        next_update.value = newDate.format('YYYY-MM-DD');
        }
    });

    $('#clock').click(function (e) {
        if (patching_frequency.value!="") {
            var today = moment();
            update_date.value = today.format('YYYY-MM-DD')
            var newDate = today.add(patching_frequency.value, 'months');
            next_update.value = newDate.format('YYYY-MM-DD');
            }
        return false;
        });

    $('#patching_frequency').on('select2:select', function (e) {
        console.log("patching_frequency changed");
        if ($('#patching_frequency').val()=="") {
            $('#next_update').val("");
        }
       else {
        var startDate = moment(update_date.value, 'YYYY-MM-DD');
        var newDate = startDate.add(patching_frequency.value, 'months');
        next_update.value = newDate.format('YYYY-MM-DD');
        }
    });

});
</script>
@endsection
