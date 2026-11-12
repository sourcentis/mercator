@extends('layouts.admin')

@section('title')
    Assigner des mesures de sécurité
@endsection

@section('content')
<form method="POST" action="{{ route('admin.security-controls-associate') }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="card">
        <div class="card-header">
            Assigner des mesures de sécurité
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="label-required">Application / Processus</label>
                        <select class="form-control select2 {{ $errors->has('src_id') ? 'is-invalid' : '' }}" name="source" id="source">
                            <option></option>
                            <optgroup label="Processes">
                            @foreach($procs as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                            </optgroup>
                            <optgroup label="Applications">
                            @foreach($apps as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                            </optgroup>
                        </select>
                        @if($errors->has('src_id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('src_id') }}
                            </div>
                        @endif
                        <span class="help-block">Objet de la mesure de sécurité</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="recommended">Mesures de sécurité</label>
                        <div id="checkboxes">
                        @foreach($controls as $control)
                        <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" id="{{ $control->id }}" name="CTRL_{{ $control->id }}">
                          <label class="form-check-label">{{ $control->name }}</label>
                        </div>
                        @endforeach
                        </div>

                        @if($errors->has('src_id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('dest_id') }}
                            </div>
                        @endif
                        <span class="help-block">Liste des mesures de sécurité appliquée à l'objet</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-secondary" href="{{ route('admin.security-controls.index') }}">
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
    $(document.body).on("change","#source",function(){
        $(':checkbox').prop('checked', false);
        $.getJSON('/admin/security-controls-list?id='+$(this).prop('value'), function(data) {
            data['controls'].forEach((x, i) => $("[name='"+x+"']").prop("checked", true));
        });
    });
});
</script>
@endsection
