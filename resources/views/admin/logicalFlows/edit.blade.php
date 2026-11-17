@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $logicalFlow->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.logical-flows.update", [$logicalFlow->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.logicalFlow.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($logicalFlow))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $logicalFlow->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
                                    {{ $perimeterOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('perimeter_id'))
                            <div class="invalid-feedback">{{ $errors->first('perimeter_id') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-sm-2">
                        <div class="form-group">
                            <label for="name">{{ trans('cruds.logicalFlow.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $logicalFlow->name) }}" autofocus
                                   maxlength='64'/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-4">
                        <div class="form-group">
                            <label for="name">{{ trans('cruds.logicalFlow.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $logicalFlow->name) }}" autofocus
                                   maxlength='64'/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.logicalFlow.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type')))
                                    <option> {{ old('type') }}</option>
                                @endif
                                @foreach($type_list as $t)
                                    <option {{ (old('type') ? old('type') : $logicalFlow->type) == $t ? 'selected' : '' }}>{{$t}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.logicalFlow.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ ( (old('attributes')!=null) && in_array($a,old('attributes'))) || in_array($a, explode(' ',$logicalFlow->attributes)) ? 'selected' : '' }}>{{$a}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('attributes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="description">{{ trans('cruds.logicalFlow.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $logicalFlow->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.description_helper') }}</span>
                        </div>
                    </div>
                </div>
                <!------------------------------------------------------------------------------------------------------>
                <div class="row">
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="chain">{{ trans('cruds.logicalFlow.fields.chain') }}</label>

                            <select class="form-control select2-free {{ $errors->has('chain') ? 'is-invalid' : '' }}"
                                    name="chain" id="chain">
                                <option></option>
                                <option {{ $logicalFlow->chain=="INPUT" ? 'selected' : '' }}>INPUT</option>
                                <option {{ $logicalFlow->chain=="OUTPUT" ? 'selected' : '' }}>OUTPUT</option>
                                <option {{ $logicalFlow->chain=="FORWARD" ? 'selected' : '' }}>FORWARD</option>
                            </select>

                            @if($errors->has('chain'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('chain') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.chain_helper') }}</span>
                        </div>

                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="interface">{{ trans('cruds.logicalFlow.fields.interface') }}</label>
                            <select class="form-control select2-free {{ $errors->has('interface') ? 'is-invalid' : '' }}"
                                    name="interface" id="interface">
                                @if (!$interface_list->contains(old('interface')))
                                    <option> {{ old('interface') }}</option>
                                @endif
                                @foreach($interface_list as $interface)
                                    <option {{ (old('interface') ? old('interface') : $logicalFlow->interface) == $interface ? 'selected' : '' }}>{{$interface}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('interface'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('interface') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.interface_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="router_id">{{ trans('cruds.logicalFlow.fields.router') }}</label>
                            <select class="form-control select2 {{ $errors->has('router') ? 'is-invalid' : '' }}"
                                    name="router_id" id="router">
                                <option></option>
                                @foreach($routers as $id => $name)
                                    <option value="{{$id}}" {{ $logicalFlow->router_id === $id ? 'selected' : '' }}>{{$name}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('router'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('router') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.router_helper') }}</span>
                        </div>
                    </div>
                </div>
                <!------------------------------------------------------------------------------------------------------>
                <div class="row">
                    <div class="col-sm-1">
                        <div class="form-group">
                            <label for="priority">{{ trans('cruds.logicalFlow.fields.priority') }}</label>
                            <input class="form-control {{ $errors->has('priority') ? 'is-invalid' : '' }}" type="text"
                                   name="priority" id="priority" value="{{ old('priority', $logicalFlow->priority) }}"
                                   required>
                            @if($errors->has('priority'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('priority') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.priority_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="form-group">
                            <label for="action">{{ trans('cruds.logicalFlow.fields.action') }}</label>
                            <select class="form-control select2 {{ $errors->has('action') ? 'is-invalid' : '' }}"
                                    name="action" id="action">
                                <option></option>
                                <option {{ $logicalFlow->action=="Permit" ? 'selected' : '' }}>Permit</option>
                                <option {{ $logicalFlow->action=="Deny" ? 'selected' : '' }}>Deny</option>
                                <option {{ $logicalFlow->action=="Block" ? 'selected' : '' }}>Block</option>
                                <option {{ $logicalFlow->action=="Reditect" ? 'selected' : '' }}>Reditect</option>
                                <option {{ $logicalFlow->action=="Log" ? 'selected' : '' }}>Log</option>
                            </select>

                            @if($errors->has('action'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('action') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.action_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="form-group">
                            <label for="protocol">{{ trans('cruds.logicalFlow.fields.protocol') }}</label>
                            <select class="form-control select2-free {{ $errors->has('protocol') ? 'is-invalid' : '' }}"
                                    name="protocol" id="protocol">
                                @if (!$protocol_list->contains(old('protocol')))
                                    <option> {{ old('protocol') }}</option>
                                @endif
                                @foreach($protocol_list as $protocol)
                                    <option {{ (old('protocol') ? old('protocol') : $logicalFlow->protocol) == $protocol ? 'selected' : '' }}>{{$protocol}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('protocol'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('protocol') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.protocol_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="source_ip_range">{{ trans('cruds.logicalFlow.fields.source_ip_range') }}</label>
                            <input class="form-control {{ $errors->has('source_ip_range') ? 'is-invalid' : '' }}"
                                   type="text" name="source_ip_range" id="source_ip_range"
                                   value="{{ old('source_ip_range', $logicalFlow->source_ip_range) }}"/>
                            @if($errors->has('source_ip_range'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('source_ip_range') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.source_ip_range_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-1">
                        <div class="form-group">
                            <label for="source_port">{{ trans('cruds.logicalFlow.fields.source_port') }}</label>
                            <input class="form-control {{ $errors->has('source_port') ? 'is-invalid' : '' }}"
                                   type="text"
                                   name="source_port" id="source_port"
                                   value="{{ old('source_port', $logicalFlow->source_port) }}">
                            @if($errors->has('source_port'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('source_port') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.source_port_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="dest_ip_range">{{ trans('cruds.logicalFlow.fields.dest_ip_range') }}</label>
                            <input class="form-control {{ $errors->has('dest_ip_range') ? 'is-invalid' : '' }}"
                                   type="text" name="dest_ip_range" id="dest_ip_range"
                                   value="{{ old('dest_ip_range', $logicalFlow->dest_ip_range) }}"/>
                            @if($errors->has('dest_ip_range'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest_ip_range') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.dest_ip_range_helper') }}</span>
                        </div>
                    </div>

                    <div class="col-sm-1">
                        <div class="form-group">
                            <label for="dest_port">{{ trans('cruds.logicalFlow.fields.dest_port') }}</label>
                            <input class="form-control {{ $errors->has('dest_port') ? 'is-invalid' : '' }}" type="text"
                                   name="dest_port" id="dest_port"
                                   value="{{ old('dest_port', $logicalFlow->dest_port) }}">
                            @if($errors->has('dest_port'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest_port') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.dest_port_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <select class="form-control select2 {{ $errors->has('src_id') ? 'is-invalid' : '' }}"
                                    name="src_id" id="src_id">
                                <option></option>
                                @foreach($devices as $id => $name)
                                    <option value="{{ $id }}" {{ ($logicalFlow->sourceId() ? $logicalFlow->sourceId() : old('src_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('src_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('src_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.src_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-1">
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <select class="form-control select2 {{ $errors->has('dest_id') ? 'is-invalid' : '' }}"
                                    name="dest_id" id="dest_id">
                                <option></option>
                                @foreach($devices as $id => $name)
                                    <option value="{{ $id }}" {{ ($logicalFlow->destinationId() ? $logicalFlow->destinationId() : old('dest_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('dest_id'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('dest_id') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.physicalLink.fields.dest_helper') }}</span>
                        </div>
                    </div>
                </div>
                <!------------------------------------------------------------------------------------------------------>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="users">{{ trans('cruds.logicalFlow.fields.users') }}</label>
                            <input class="form-control {{ $errors->has('users') ? 'is-invalid' : '' }}" type="text"
                                   name="users" id="users" value="{{ old('users', $logicalFlow->users) }}">
                            @if($errors->has('users'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('users') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.users_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="schedule">{{ trans('cruds.logicalFlow.fields.schedule') }}</label>
                            <input class="form-control {{ $errors->has('schedule') ? 'is-invalid' : '' }}" type="text"
                                   name="schedule" id="schedule" value="{{ old('schedule', $logicalFlow->schedule) }}">
                            @if($errors->has('schedule'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('schedule') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.logicalFlow.fields.schedule_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.logical-flows.index') }}">
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

            //----------------------------------------------------
            // Variable de blocage
            let lock = false;

            // Effaces les champs src en fct de la sélection
            $('#source_ip_range').on('input', function () {
                if (lock) return;
                lock = true;
                $('#src_id').val(null).trigger('change');
                lock = false;
            });

            $('#src_id').on('change', function () {
                if (lock) return;
                lock = true;
                $('#source_ip_range').val('');
                lock = false;
            });

            // Effaces les champs src en fct de la sélection
            $('#dest_ip_range').on('input', function () {
                if (lock) return;
                lock = true;
                $('#dest_id').val(null).trigger('change');
                lock = false;
            });

            $('#dest_id').on('change', function () {
                if (lock) return;
                lock = true;
                $('#dest_ip_range').val('');
                lock = false;
            });
            //----------------------------------------------------

        });
    </script>
@endsection
