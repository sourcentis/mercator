@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $activity->name }}
@endsection

@section('content')

    <form method="POST" action="{{ route("admin.activities.update", [$activity->id]) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.activity.title_singular') }}
            </div>

            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($activity))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $activity->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.activity.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $activity->name) }}" maxlength=64
                                   required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.activity.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $activity->name) }}" maxlength=64
                                   required autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.activity.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $activity->type ?? '')))
                                    <option>{{ old('type', $activity->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $activity->type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">{{ $errors->first('type') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.activity.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $activity->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label-maturity-2"
                           for="description">{{ trans('cruds.activity.fields.description') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description"
                              id="description">{{ old('description', $activity->description) }}</textarea>
                    @if($errors->has('description'))
                        <div class="invalid-feedback">
                            {{ $errors->first('description') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.activity.fields.description_helper') }}</span>
                </div>

                <div class='row'>
                    <div class='col-6'>
                        <div class="form-group">
                            <label for="operations">{{ trans('cruds.activity.fields.processes') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('processes') ? 'is-invalid' : '' }}"
                                    name="processes[]" id="processes" multiple>
                                @foreach($processes as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('processes', [])) || $activity->processes->contains($id)) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('processes'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('processes') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.processes_helper') }}</span>
                        </div>

                    </div>
                    <div class='col-6'>

                        <div class="form-group">
                            <label for="operations">{{ trans('cruds.activity.fields.operations') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('operations') ? 'is-invalid' : '' }}"
                                    name="operations[]" id="operations" multiple>
                                @foreach($operations as $id => $operation)
                                    <option value="{{ $id }}" {{ (in_array($id, old('operations', [])) || $activity->operations->contains($id)) ? 'selected' : '' }}>{{ $operation }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('operations'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('operations') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.operations_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class="col">
                        <div class="form-group">
                            <label for="applications">{{ trans('cruds.process.fields.applications') }}</label>
                            <div style="padding-bottom: 4px">
                                <span class="btn btn-info btn-xs select-all"
                                      style="border-radius: 0">{{ trans('global.select_all') }}</span>
                                <span class="btn btn-info btn-xs deselect-all"
                                      style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                            </div>
                            <select class="form-control select2 {{ $errors->has('applications') ? 'is-invalid' : '' }}"
                                    name="applications[]" id="applications[]" multiple>
                                @foreach($applications as $id => $application)
                                    <option value="{{ $id }}" {{ (in_array($id, old('applications', [])) || $activity->applications->contains($id)) ? 'selected' : '' }}>{{ $application }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('applications'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('applications') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.process.fields.applications_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.activity.bia') }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">

                <div class='row'>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="maximum_tolerable_downtime">{{ trans('cruds.activity.fields.maximum_tolerable_downtime') }}</label>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <span class="help-block">{{ trans('global.days') }}</span>
                                        <input type="number" class="form-control" id="maximum_tolerable_downtime_days"
                                               name="maximum_tolerable_downtime_days"
                                               min="0" max="365"
                                               value="{{ old('maximum_tolerable_downtime_days', intdiv($activity->maximum_tolerable_downtime, 60 * 24)) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.hours') }}</span>
                                        <input type="number" class="form-control" id="maximum_tolerable_downtime_hours"
                                               name="maximum_tolerable_downtime_hours"
                                               min="0" max="24"
                                               value="{{ old('maximum_tolerable_downtime_hours', intdiv($activity->maximum_tolerable_downtime, 60) % 24) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.minutes') }}</span>
                                        <input type="number" class="form-control" id="maximum_tolerable_downtime_minute"
                                               name="maximum_tolerable_downtime_minutes"
                                               min="0" max="60"
                                               value="{{ old('maximum_tolerable_downtime_minutes', $activity->maximum_tolerable_downtime % 60) }}">
                                    </td>
                                </tr>
                            </table>
                            @if($errors->has('maximum_tolerable_downtime'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('maximum_tolerable_downtime') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.maximum_tolerable_downtime_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="name">{{ trans('cruds.activity.fields.maximum_tolerable_data_loss') }}</label>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <span class="help-block">{{ trans('global.days') }}</span>
                                        <input type="number" class="form-control" id="maximum_tolerable_data_loss_days"
                                               name="maximum_tolerable_data_loss_days"
                                               min="0" max="365"
                                               value="{{ old('maximum_tolerable_data_loss_days', intdiv($activity->maximum_tolerable_data_loss, 60 * 24)) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.hours') }}</span>
                                        <input type="number" class="form-control" id="maximum_tolerable_data_loss_hours"
                                               name="maximum_tolerable_data_loss_hours"
                                               min="0" max="24"
                                               value="{{ old('maximum_tolerable_data_loss_hours', intdiv($activity->maximum_tolerable_data_loss, 60) % 24) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.minutes') }}</span>
                                        <input type="number" class="form-control"
                                               id="maximum_tolerable_data_loss_minutes"
                                               name="maximum_tolerable_data_loss_minutes"
                                               min="0" max="60"
                                               value="{{ old('maximum_tolerable_data_loss_minutes', $activity->maximum_tolerable_data_loss % 60) }}">
                                    </td>
                                </tr>
                            </table>
                            @if($errors->has('maximum_tolerable_data_loss'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('maximum_tolerable_data_loss') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.maximum_tolerable_data_loss_helper') }}</span>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="recovery_time_objective">{{ trans('cruds.activity.fields.recovery_time_objective') }}</label>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <span class="help-block">{{ trans('global.days') }}</span>
                                        <input type="number" class="form-control" id="recovery_time_objective_days"
                                               name="recovery_time_objective_days"
                                               min="0" max="365"
                                               value="{{ old('recovery_time_objective_days', intdiv($activity->recovery_time_objective, 60 * 24)) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.hours') }}</span>
                                        <input type="number" class="form-control" id="recovery_time_objective_hours"
                                               name="recovery_time_objective_hours"
                                               min="0" max="24"
                                               value="{{ old('recovery_time_objective_hours',  intdiv($activity->recovery_time_objective, 60) % 24) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.minutes') }}</span>
                                        <input type="number" class="form-control" id="recovery_time_objective_minutes"
                                               name="recovery_time_objective_minutes"
                                               min="0" max="60"
                                               value="{{ old('recovery_time_objective_minutes', $activity->recovery_time_objective % 60) }}">
                                    </td>
                                </tr>
                            </table>
                            @if($errors->has('recovery_time_objective'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('recovery_time_objective') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.recovery_time_objective_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="recovery_point_objective">{{ trans('cruds.activity.fields.recovery_point_objective') }}</label>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <span class="help-block">{{ trans('global.days') }}</span>
                                        <input type="number" class="form-control" id="recovery_point_objective_days"
                                               name="recovery_point_objective_days"
                                               min="0" max="365"
                                               value="{{ old('recovery_point_objective_days', intdiv($activity->recovery_point_objective, 60 * 24)) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.hours') }}</span>
                                        <input type="number" class="form-control" id="recovery_point_objective_hours"
                                               name="recovery_point_objective_hours"
                                               min="0" max="24"
                                               value="{{ old('recovery_point_objective_hours', intdiv($activity->recovery_point_objective, 60) % 24) }}">
                                    </td>
                                    <td>
                                        <span class="help-block">{{ trans('global.minutes') }}</span>
                                        <input type="number" class="form-control" id="recovery_point_objective_minutes"
                                               name="recovery_point_objective_minutes"
                                               min="0" max="60"
                                               value="{{ old('recovery_point_objective_minutes', $activity->recovery_point_objective % 60) }}">
                                    </td>
                                </tr>
                            </table>
                            @if($errors->has('recovery_point_objective'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('recovery_point_objective') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.recovery_point_objective_helper') }}</span>
                        </div>
                    </div>
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="col-8">
                    <table class="table-narrow" id="dynamicAddRemove">
                        <tr>
                            <th>{{ trans('cruds.activity.fields.impact_type') }}</th>
                            <th>{{ trans('cruds.activity.fields.gravity') }}</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td width="200px">
                                <div class="col">
                                    <select class="form-control select2-free {{ $errors->has('impact_type') ? 'is-invalid' : '' }}"
                                            name="impact_type" id="inputTypeField">
                                        <option></option>
                                        @foreach($types as $type)
                                            <option>{{$type}}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block">{{ trans('cruds.activity.fields.impact_type_helper') }}</span>
                                </div>
                            </td>
                            <td>
                                <select class="form-control select2 risk" id="inputValueField">
                                    <option value="-1"></option>
                                    <option value="0">{{ trans('global.none') }}</option>
                                    <option value="1">{{ trans('global.low') }}</option>
                                    <option value="2">{{ trans('global.medium') }}</option>
                                    <option value="3">{{ trans('global.strong') }}</option>
                                    <option value="4">{{ trans('global.very_strong') }}</option>
                                </select>
                                <span class="help-block">{{ trans('cruds.activity.fields.gravity_helper') }}</span>
                            </td>
                            <td>
                                <button type="button" id="dynamic-ar" class="btn btn-outline-primary">Add</button>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                Liste des impactes
                            </th>
                        </tr>
                        @foreach($activity->impacts as $impact)
                            <tr>
                                <td>
                                    <div class="col">
                                        <select class="form-control select2-free {{ $errors->has('patching_group') ? 'is-invalid' : '' }}"
                                                name="impact_types[]" id='type{{ $loop->iteration }}'>
                                            @foreach($types as $type)
                                                <option {{ $impact->impact_type === $type ? 'selected' : '' }}>{{$type}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-control select2 risk" name="severities[]">'
                                        <option value="-1"></option>
                                        '
                                        <option value="0" {{ $impact->severity==0 ? 'selected' : '' }}>{{ trans('global.none') }}</option>
                                        <option value="1" {{ $impact->severity==1 ? 'selected' : '' }}>{{ trans('global.low') }}</option>
                                        <option value="2" {{ $impact->severity==2 ? 'selected' : '' }}>{{ trans('global.medium') }}</option>
                                        <option value="3" {{ $impact->severity==3 ? 'selected' : '' }}>{{ trans('global.strong') }}</option>
                                        <option value="4" {{ $impact->severity==4 ? 'selected' : '' }}>{{ trans('global.very_strong') }}</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger remove-input-field">Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans('cruds.activity.drp') }}
            </div>
            <!------------------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class='row'>
                    <div class="col">
                        <div class="form-group">
                            <label for="drp">{{ trans('cruds.activity.fields.drp') }}</label>
                            <textarea class="form-control ckeditor {{ $errors->has('drp') ? 'is-invalid' : '' }}"
                                      name="drp" id="drp">{{ old('drp', $activity->drp) }}</textarea>
                            @if($errors->has('drp'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('drp') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.drp_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class="col">
                        <div class="form-group">
                            <label for="drp_link">{{ trans('cruds.activity.fields.drp_link') }}</label>
                            <input class="form-control {{ $errors->has('drp_link') ? 'is-invalid' : '' }}" type="text"
                                   name="drp_link" id="drp_link" value="{{ old('drp_link', $activity->drp_link) }}"
                                   maxlength=256/>
                            @if($errors->has('drp_link'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('drp_link') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.activity.fields.drp_link_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.activities.index') }}">
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
            $("#dynamic-ar").click(function () {
                console.log($("#inputValueField").val());
                if (($("#inputTypeField").val() != '') && ($("#inputValueField").val() != -1)) {
                    input = $("#dynamicAddRemove")
                        .append(
                            '<tr>' +
                            '<td><div class="col"><input type="impact_type" name="impact_types[]" value="' + $("#inputTypeField").val() + '" class="form-control" /></div></td>' +
                            '<td>' +
                            '<select class="form-control select2 risk" name="severities[]">' +
                            '    <option value="-1"></option>' +
                            '    <option value="0" ' + ($("#inputValueField").val() == '0' ? 'selected' : '') + '>{{ trans('global.none') }}</option>' +
                            '    <option value="1" ' + ($("#inputValueField").val() == '1' ? 'selected' : '') + '>{{ trans('global.low') }}</option>' +
                            '    <option value="2" ' + ($("#inputValueField").val() == '2' ? 'selected' : '') + '>{{ trans('global.medium') }}</option>' +
                            '    <option value="3" ' + ($("#inputValueField").val() == '3' ? 'selected' : '') + '>{{ trans('global.strong') }}</option>' +
                            '    <option value="4" ' + ($("#inputValueField").val() == '4' ? 'selected' : '') + '>{{ trans('global.very_strong') }}</option>' +
                            '</select>' +
                            '</td>' +
                            '<td><button type="button" class="btn btn-outline-danger remove-input-field">Delete</button></td>' +
                            '</tr>');


                    $('#inputTypeField').val(null).trigger('change');
                    $('#inputValueField').val(null).trigger('change');
                }
            });

            $(document).on('click', '.remove-input-field', function () {
                $(this).parents('tr').remove();
            });

        });
    </script>
@endsection
