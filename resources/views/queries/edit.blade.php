@extends('layouts.admin')

@section('content')

<form action="{{ $query->exists
                    ? route('admin.queries.update', $query)
                    : route('admin.queries.store') }}"
      method="POST" id="saved-query-form">
    @csrf
    @if ($query->exists)
        @method('PUT')
    @endif

    {{-- Champ caché qui recevra le JSON DSL avant envoi --}}
    <input type="hidden" name="query_json" id="query-json-hidden">

<div class="card">
    <div class="card-body">
        <div class="row">

            {{-- ── Colonne gauche : méta-données ─────────────────── --}}
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">@lang('Informations')</div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">
                                @lang('Nom') <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $query->name) }}"
                                   required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">@lang('Description')</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description"
                                      rows="3">{{ old('description', $query->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="is_public" name="is_public" value="1"
                                   {{ old('is_public', $query->is_public) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_public">
                                @lang('Partagée (visible par tous)')
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Colonne droite : éditeur SQL-like ──────────────── --}}
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>@lang('Requête')</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-format">
                            <i class="fas fa-indent"></i> @lang('Formater')
                        </button>
                    </div>
                    <div class="card-body p-0">
                        {{-- Textarea SQL-like — converti en JSON avant envoi --}}
                        <textarea class="form-control font-monospace border-0 rounded-0
                                         @error('query') is-invalid @enderror"
                                  id="query-editor"
                                  rows="22"
                                  style="resize: vertical; font-size: 0.85rem;"
                                  spellcheck="false"
                                  placeholder="FROM logical-servers
WHERE environment = &quot;production&quot;
WITH applications
OUTPUT list
LIMIT 100"></textarea>
                        @error('query')
                            <div class="invalid-feedback d-block px-3">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer text-muted small">
                        <code>FROM logical-servers WHERE environment = "production" WITH applications OUTPUT list</code>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.queries.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
    <button class="btn btn-success" type="submit">
        {{ trans('global.save') }}
    </button>
</div>

</form>
@endsection

@section('scripts')
@vite(['resources/js/sql-parser.js'])
<script @nonce>
document.addEventListener('DOMContentLoaded', function () {

    const editor = document.getElementById('query-editor');
    const hidden = document.getElementById('query-json-hidden');
    const form   = document.getElementById('saved-query-form');

    // ── Chargement initial — DSL JSON → SQL-like ─────────────────
    @php
        // Priorité : old('query') après une erreur de validation, sinon $query->query
        $queryOld = old('query');
        $dsl = is_array($queryOld) ? $queryOld : ($query->query ?? null);
    @endphp

    @if (!empty($dsl))
    try {
        editor.value = window.MercatorQuery.dslToSql(@json($dsl));
    } catch (e) {
        // Fallback JSON brut si conversion impossible
        editor.value = JSON.stringify(@json($dsl), null, 2);
    }
    @endif

    // ── Formater ─────────────────────────────────────────────────
    document.getElementById('btn-format').addEventListener('click', function () {
        try {
            const dsl  = window.MercatorQuery.parse(editor.value);
            editor.value = window.MercatorQuery.dslToSql(dsl);
            editor.classList.remove('is-invalid');
        } catch (e) {
            editor.classList.add('is-invalid');
            alert('Erreur de syntaxe : ' + e.message);
        }
    });

    // ── Submit : SQL-like → JSON DSL → champ caché ───────────────
    form.addEventListener('submit', function (e) {
        try {
            const dsl = window.MercatorQuery.parse(editor.value);

            if (!dsl.from) {
                throw new Error('Le champ FROM est obligatoire.');
            }

            hidden.value = JSON.stringify(dsl);
            editor.classList.remove('is-invalid');

        } catch (e) {
            e.preventDefault();
            editor.classList.add('is-invalid');
            editor.focus();
            alert('Erreur dans la requête : ' + e.message);
        }
    });

    // ── Retrait de la marque d'erreur à la frappe ─────────────────
    editor.addEventListener('input', function () {
        editor.classList.remove('is-invalid');
    });

});
</script>
@parent
@endsection