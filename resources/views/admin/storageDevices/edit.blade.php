@extends('layouts.admin')

@section('title')
    {{ trans('global.edit') }} {{ $storageDevice->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route("admin.storage-devices.update", [$storageDevice->id]) }}"
          enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card">
            <div class="card-header">
                {{ trans('global.edit') }} {{ trans('cruds.storageDevice.title_singular') }}
            </div>
            <div class="card-body">
                <div class="row">
                    @if (auth()->user()->canChoosePerimeterOf($storageDevice))
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="perimeter_id">{{ trans('cruds.perimeter.title_short') }}</label>
                        <select class="form-control select2 {{ $errors->has('perimeter_id') ? 'is-invalid' : '' }}"
                                name="perimeter_id" id="perimeter_id">
                            @foreach (\App\Models\Perimeter::whereIn('id', auth()->user()->perimeterIds())->orderBy('name')->get() as $perimeterOption)
                                <option value="{{ $perimeterOption->id }}"
                                        {{ (int) old('perimeter_id', $storageDevice->perimeter_id) === $perimeterOption->id ? 'selected' : '' }}>
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
                            <label class="label-required" for="name">{{ trans('cruds.storageDevice.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $storageDevice->name) }}" required
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.name_helper') }}</span>
                        </div>
                    </div>
            @else
                <div class="col-sm-5">
                        <div class="form-group">
                            <label class="label-required" for="name">{{ trans('cruds.storageDevice.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text"
                                   name="name" id="name" value="{{ old('name', $storageDevice->name) }}" required
                                   autofocus/>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.name_helper') }}</span>
                        </div>
                    </div>
            @endif
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="type">{{ trans('cruds.storageDevice.fields.type') }}</label>
                            <select class="form-control select2-free {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                    name="type" id="type">
                                @if (!$type_list->contains(old('type', $storageDevice->type ?? '')))
                                    <option>{{ old('type', $storageDevice->type ?? '') }}</option>
                                @endif
                                @foreach($type_list as $type)
                                    <option {{ old('type', $storageDevice->type) == $type ? 'selected' : '' }}>{{$type}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('type') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.type_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="attributes">{{ trans('cruds.storageDevice.fields.attributes') }}</label>
                            <select class="form-control select2-free-tags {{ $errors->has('attributes') ? 'is-invalid' : '' }}"
                                    name="attributes[]" id="attributes" multiple>
                                @foreach($attributes_list as $a)
                                    <option {{ in_array($a, old('attributes', array_filter(explode(' ', (string) $storageDevice->attributes)))) ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('attributes'))
                                <div class="invalid-feedback">{{ $errors->first('attributes') }}</div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.attributes_helper') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-9">
                        <div class="form-group">
                            <label class="label-description"
                                   for="description">{{ trans('cruds.storageDevice.fields.description') }}</label>
                            <textarea
                                    class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                    name="description"
                                    id="description">{{ old('description', $storageDevice->description) }}</textarea>
                            @if($errors->has('description'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.description_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="iconSelect">{{ trans('global.icon_select') }}</label>
                            <select id="iconSelect"
                                    name="iconSelect"
                                    class="form-control js-icon-picker"
                                    data-icons='@json($icons)'
                                    data-selected="{{ $storageDevice->icon_id ?? '-1' }}"
                                    data-default-img="{{ asset('images/storagedev.png') }}"
                                    data-url-template="{{ route('admin.documents.show', ':id') }}"
                                    data-upload="#iconFile">
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="file" id="iconFile" name="iconFile" accept="image/png"/>
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
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="address_ip">{{ trans('cruds.storageDevice.fields.address_ip') }}</label>
                        <input class="form-control {{ $errors->has('address_ip') ? 'is-invalid' : '' }}" type="text"
                               name="address_ip" id="address_ip"
                               value="{{ old('address_ip', $storageDevice->address_ip) }}">
                        @if($errors->has('address_ip'))
                            <div class="invalid-feedback">
                                {{ $errors->first('address_ip') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.storageDevice.fields.address_ip_helper') }}</span>
                    </div>
                </div>
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-header">
                {{ trans("cruds.menu.physical_infrastructure.title_short") }}
            </div>
            <!---------------------------------------------------------------------------------------------------->
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="site_id">{{ trans('cruds.storageDevice.fields.site') }}</label>
                            <select class="form-control select2 {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                    name="site_id" id="site_id">
                                @foreach($sites as $id => $site)
                                    <option value="{{ $id }}" {{ ($storageDevice->site ? $storageDevice->site->id : old('site_id')) == $id ? 'selected' : '' }}>{{ $site }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.site_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="label-maturity-1"
                                   for="building_id">{{ trans('cruds.storageDevice.fields.building') }}</label>
                            <select class="form-control select2 {{ $errors->has('building') ? 'is-invalid' : '' }}"
                                    name="building_id" id="building_id">
                                @foreach($buildings as $id => $building)
                                    <option value="{{ $id }}" {{ ($storageDevice->building ? $storageDevice->building->id : old('building_id')) == $id ? 'selected' : '' }}>{{ $building }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('building'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('building') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.building_helper') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="bay_id">{{ trans('cruds.storageDevice.fields.bay') }}</label>
                            <select class="form-control select2 {{ $errors->has('bay') ? 'is-invalid' : '' }}"
                                    name="bay_id" id="bay_id">
                                @foreach($bays as $id => $bay)
                                    <option value="{{ $id }}" {{ ($storageDevice->bay ? $storageDevice->bay->id : old('bay_id')) == $id ? 'selected' : '' }}>{{ $bay }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('bay'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('bay') }}
                                </div>
                            @endif
                            <span class="help-block">{{ trans('cruds.storageDevice.fields.bay_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>

                @can('backup_edit')
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-header">
                    {{ trans("cruds.backup.title") }}
                </div>
                <!---------------------------------------------------------------------------------------------------->
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-10">
                            <table class="table" id="dynamicAddRemove">
                                <tr>
                                    <th width="30%">{{ trans('cruds.logicalServer.title') }}</th>
                                    <th width="20%">{{ trans('cruds.backup.frequency') }}</th>
                                    <th width="30%">{{ trans('cruds.backup.cycle') }}</th>
                                    <th width="20%">{{ trans('cruds.backup.retention') }}</th>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="col">
                                            <select class="form-control select2" name="logical_server_id" id="logical_server_id">
                                                <option></option>
                                                @foreach($logicalServers as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-control select2" name="backup_frequency" id="backup_frequency">
                                            <option></option>
                                            <option value="1">{{ trans("cruds.backup.frequencies.1") }}</option>
                                            <option value="2">{{ trans("cruds.backup.frequencies.2") }}</option>
                                            <option value="3">{{ trans("cruds.backup.frequencies.3") }}</option>
                                            <option value="4">{{ trans("cruds.backup.frequencies.4") }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2" name="backup_cycle" id="backup_cycle">
                                            <option></option>
                                            <option value="1">{{ trans("cruds.backup.cycles.1") }}</option>
                                            <option value="2">{{ trans("cruds.backup.cycles.2") }}</option>
                                            <option value="3">{{ trans("cruds.backup.cycles.3") }}</option>
                                            <option value="4">{{ trans("cruds.backup.cycles.4") }}</option>
                                            <option value="5">{{ trans("cruds.backup.cycles.5") }}</option>
                                            <option value="6">{{ trans("cruds.backup.cycles.6") }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" type="number" name="backup_retention" id="backup_retention" min="1" max="36500"/>
                                    </td>
                                    <td>
                                        <button type="button" id="dynamic-ar" class="btn btn-outline-primary">{{ trans("global.add") }}</button>
                                    </td>
                                </tr>

                                {{-- Lignes des backups existants --}}
                                @foreach($storageDevice->backups as $backup)
                                @php($firstServer = $backup->logicalServers->first())
                                <tr>
                                    <td>
                                        <select class="form-control select2" name="logical_server_id[]">
                                            <option value=""></option>
                                            @foreach($logicalServers as $id => $name)
                                                <option value="{{ $id }}" {{ $firstServer?->id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2" name="backup_frequency[]">
                                            <option value=""></option>
                                            <option value="1" {{ $backup->backup_frequency == 1 ? 'selected' : '' }}>{{ trans("cruds.backup.frequencies.1") }}</option>
                                            <option value="2" {{ $backup->backup_frequency == 2 ? 'selected' : '' }}>{{ trans("cruds.backup.frequencies.2") }}</option>
                                            <option value="3" {{ $backup->backup_frequency == 3 ? 'selected' : '' }}>{{ trans("cruds.backup.frequencies.3") }}</option>
                                            <option value="4" {{ $backup->backup_frequency == 4 ? 'selected' : '' }}>{{ trans("cruds.backup.frequencies.4") }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2" name="backup_cycle[]">
                                            <option value=""></option>
                                            <option value="1" {{ $backup->backup_cycle == 1 ? 'selected' : '' }}>{{ trans("cruds.backup.cycles.1") }}</option>
                                            <option value="2" {{ $backup->backup_cycle == 2 ? 'selected' : '' }}>{{ trans("cruds.backup.cycles.2") }}</option>
                                            <option value="3" {{ $backup->backup_cycle == 3 ? 'selected' : '' }}>{{ trans("cruds.backup.cycles.3") }}</option>
                                            <option value="4" {{ $backup->backup_cycle == 4 ? 'selected' : '' }}>{{ trans("cruds.backup.cycles.4") }}</option>
                                            <option value="5" {{ $backup->backup_cycle == 5 ? 'selected' : '' }}>{{ trans("cruds.backup.cycles.5") }}</option>
                                            <option value="6" {{ $backup->backup_cycle == 6 ? 'selected' : '' }}>{{ trans("cruds.backup.cycles.6") }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control"
                                               name="backup_retention[]"
                                               min="1" max="36500"
                                               value="{{ $backup->backup_retention }}" />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger remove-input-field">
                                            {{ trans('global.delete') }}
                                        </button>
                                    </td>
                                </tr>
                                @endforeach



                            </table>
                        </div>
                    </div>
                </div>
                @endcan

        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.storage-devices.index') }}">
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

        // Options générées côté serveur pour réutilisation dans les lignes dynamiques
        const serverOptions = `
            <option value=""></option>
            @foreach($logicalServers as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        `;

        const frequencyOptions = `
            <option value=""></option>
            <option value="1">{{ trans("cruds.backup.frequencies.1") }}</option>
            <option value="2">{{ trans("cruds.backup.frequencies.2") }}</option>
            <option value="3">{{ trans("cruds.backup.frequencies.3") }}</option>
            <option value="4">{{ trans("cruds.backup.frequencies.4") }}</option>
        `;

        const cycleOptions = `
            <option value=""></option>
            <option value="1">{{ trans("cruds.backup.cycles.1") }}</option>
            <option value="2">{{ trans("cruds.backup.cycles.2") }}</option>
            <option value="3">{{ trans("cruds.backup.cycles.3") }}</option>
            <option value="4">{{ trans("cruds.backup.cycles.4") }}</option>
            <option value="5">{{ trans("cruds.backup.cycles.5") }}</option>
            <option value="6">{{ trans("cruds.backup.cycles.6") }}</option>
        `;

        $("#dynamic-ar").click(function () {
            const serverId   = $("#logical_server_id").val();
            const freqId     = $("#backup_frequency").val();
            const cycleId    = $("#backup_cycle").val();
            const retention  = $("#backup_retention").val();

            if (!serverId) return;

            const $row = $(`
                <tr>
                    <td>
                        <select class="form-control select2" name="logical_server_id[]">
                            ${serverOptions}
                        </select>
                    </td>
                    <td>
                        <select class="form-control select2" name="backup_frequency[]">
                            ${frequencyOptions}
                        </select>
                    </td>
                    <td>
                        <select class="form-control select2" name="backup_cycle[]">
                            ${cycleOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="backup_retention[]"
                               min="1" max="36500"
                               value="${retention}" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger remove-input-field">
                            {{ trans('global.delete') }}
                        </button>
                    </td>
                </tr>`
            );

            // Pré-sélectionner les valeurs choisies dans la ligne de saisie
            $row.find('[name="logical_server_id[]"]').val(serverId);
            $row.find('[name="backup_frequency[]"]').val(freqId);
            $row.find('[name="backup_cycle[]"]').val(cycleId);

            // Initialiser Select2 sur les selects de la nouvelle ligne
            $row.find('select').select2({ width: '100%' });

            $("#dynamicAddRemove tbody").append($row);

            // Reset des champs de saisie
            $("#logical_server_id").val('').trigger('change');
            $("#backup_frequency").val('').trigger('change');
            $("#backup_cycle").val('').trigger('change');
            $("#backup_retention").val('');
        });

        $(document).on('click', '.remove-input-field', function () {
            $(this).closest('tr').remove();
        });
    });
</script>
@include('partials.location-cascade', [
    'buildingSiteMap' => $buildingSiteMap,
    'bayBuildingMap' => $bayBuildingMap ?? null,
])
@endsection
