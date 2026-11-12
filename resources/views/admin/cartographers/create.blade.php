@extends('layouts.admin')

@section('title')
    {{ trans('cruds.cartographer.title_singular') }}
@endsection

@section('content')
<form method="POST" action="{{ route('admin.cartographers.store') }}">
    @csrf
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.cartographer.title_singular') }}
        </div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Utilisateur / Rôle --}}
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="user_id">{{ trans('cruds.cartographer.fields.user') }}</label>
                        <select name="user_id" id="user_id"
                                class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Aucun --</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}"
                                    {{ (old('user_id', $userId) == $id) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('user_id'))
                            <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.cartographer.fields.user_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="role_id">{{ trans('cruds.cartographer.fields.role') }}</label>
                        <select name="role_id" id="role_id"
                                class="form-control select2 {{ $errors->has('role_id') ? 'is-invalid' : '' }}">
                            <option value="">-- Aucun --</option>
                            @foreach($roles as $id => $title)
                                <option value="{{ $id }}"
                                    {{ (old('role_id', $roleId) == $id) ? 'selected' : '' }}>
                                    {{ $title }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('role_id'))
                            <div class="invalid-feedback">{{ $errors->first('role_id') }}</div>
                        @endif
                        <span class="help-block">{{ trans('cruds.cartographer.fields.role_helper') }}</span>
                    </div>
                </div>
            </div>

            {{-- Type / Objet + bouton Ajouter --}}
            <div class="row align-items-end">
                <div class="col-4">
                    <div class="form-group">
                        <label for="type-select">{{ trans('cruds.cartographer.fields.type') }}</label>
                        <select id="type-select" class="form-control select2">
                            <option value="">-- Choisir un type --</option>
                            @foreach($models as $class => $label)
                                <option value="{{ $class }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="help-block">{{ trans('cruds.cartographer.fields.type_helper') }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label for="object-select">{{ trans('cruds.cartographer.fields.object') }}</label>
                        <select id="object-select" class="form-control select2">
                            <option value="">-- Choisir un objet --</option>
                        </select>
                        <span class="help-block">{{ trans('cruds.cartographer.fields.object_helper') }}</span>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" id="add-object" class="btn btn-secondary">
                                {{ trans('cruds.cartographer.fields.add_object') }}
                            </button>
                        </div>
                        <span class="help-block">&nbsp;</span>
                    </div>
                </div>
            </div>

            {{-- Select multiple : objets associés --}}
            <div class="row">
                <div class="col-8">
                    <div class="form-group">
                        <label for="objects">{{ trans('cruds.cartographer.fields.objects') }}</label>
                        <select name="objects[]" id="objects" class="form-control select2"
                                multiple style="min-height:120px;">
                        </select>
                        @if($errors->has('objects') || $errors->has('objects.*'))
                            <div class="invalid-feedback d-block">
                                {{ $errors->first('objects') ?? $errors->first('objects.*') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.cartographer.fields.objects_helper') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ trans('global.save') }}</button>
        <a href="{{ route('admin.cartographers.index') }}" class="btn btn-default">{{ trans('global.cancel') }}</a>
    </div>
</form>
@endsection

@section('scripts')
@parent
<script @nonce>
document.addEventListener("DOMContentLoaded", function () {
(function () {
    var objectsUrl        = '{{ route('admin.cartographers.objects') }}';
    var assocObjectsUrl   = '{{ route('admin.cartographers.associated-objects') }}';
    var typeLabels        = @json($models);

    // ── Chargement des objets par type ────────────────────────────────────────

    function loadObjects(type) {
        var $sel = $('#object-select');
        $sel.empty().append('<option value="">-- Chargement... --</option>');
        if (!type) {
            $sel.empty().append('<option value="">-- Choisir un objet --</option>');
            return;
        }
        $.getJSON(objectsUrl, { type: type }, function (data) {
            $sel.empty().append('<option value="">-- Choisir un objet --</option>');
            $.each(data, function (i, obj) {
                $sel.append($('<option>', { value: obj.id, text: obj.name || '(id:' + obj.id + ')' }));
            });
            $sel.trigger('change.select2');
        }).fail(function () {
            $sel.empty().append('<option value="">-- Erreur de chargement --</option>');
        });
    }

    // ── Chargement des objets déjà associés ───────────────────────────────────

    function loadAssociatedObjects(userId, roleId) {
        var params = {};
        if (userId)  { params.user_id = userId; }
        else if (roleId) { params.role_id = roleId; }
        else {
            $('#objects').empty().trigger('change');
            return;
        }
        $.getJSON(assocObjectsUrl, params, function (data) {
            var $multi = $('#objects');
            $multi.empty();
            $.each(data, function (i, item) {
                var value = item.type + '|' + item.id;
                $multi.append($('<option>', { value: value, text: item.label, selected: true }));
            });
            $multi.trigger('change');
        });
    }

    // ── Exclusivité mutuelle user / rôle ──────────────────────────────────────

    $('#user_id').on('change', function () {
        if ($(this).val()) {
            $('#role_id').val(null).trigger('change');
        }
        loadAssociatedObjects($(this).val(), '');
    });
    $('#role_id').on('change', function () {
        if ($(this).val()) {
            $('#user_id').val(null).trigger('change');
        }
        loadAssociatedObjects('', $(this).val());
    });

    // ── Changement de type ────────────────────────────────────────────────────

    $('#type-select').on('change', function () {
        loadObjects($(this).val());
    });

    // ── Bouton Ajouter ────────────────────────────────────────────────────────

    $('#add-object').on('click', function () {
        var type     = $('#type-select').val();
        var objId    = $('#object-select').val();
        var objText  = $('#object-select option:selected').text();
        var typeText = $('#type-select option:selected').text();

        if (!type || !objId) { return; }

        var value = type + '|' + objId;
        var $multi = $('#objects');

        if ($multi.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
            return;
        }

        var label = typeText + ' — ' + objText;
        $multi.append($('<option>', { value: value, text: label, selected: true }));
        $multi.trigger('change');
    });

    // ── Validation à la soumission ────────────────────────────────────────────

    $('form').on('submit', function (e) {
        var user = $('#user_id').val();
        var role = $('#role_id').val();

        if (!user && !role) {
            e.preventDefault();
            alert('{{ trans('cruds.cartographer.errors.user_or_role_required') }}');
            return;
        }

        var selectedOptions = $('#objects option:selected').length;
        if (selectedOptions === 0) {
            e.preventDefault();
            alert('{{ trans('cruds.cartographer.fields.objects') }} : {{ trans('validation.required', ['attribute' => trans('cruds.cartographer.fields.objects')]) }}');
        }
    });

    // ── Init Select2 ──────────────────────────────────────────────────────────

    $('.select2').select2({ width: '100%' });

    // ── Pré-sélection au chargement ───────────────────────────────────────────

    var initUser = $('#user_id').val();
    var initRole = $('#role_id').val();
    if (initUser || initRole) {
        loadAssociatedObjects(initUser, initRole);
    }
})();
});
</script>
@endsection
