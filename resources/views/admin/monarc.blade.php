@extends('layouts.admin')

@section('title')
    {{ trans('cruds.monarc.title_short') }}
@endsection

@section('content')

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($apiError)
    <div class="alert alert-danger" role="alert">
        {{ $apiError }}
    </div>
@endif

@if ($viewSections === [])
    <div class="alert alert-info">
        {{ trans('cruds.monarc.no_rows_mosp') }}
    </div>
@else
    <form method="POST" action="{{ route('admin.monarc.export') }}" id="monarc-export-form">
        @csrf

        <div class="card mb-3">
            <div class="card-header">{{ trans('cruds.monarc.analysis_model') }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label-required" for="monarc_anr_select">{{ trans('cruds.monarc.name') }}</label>
                            <input type="hidden" id="monarc_name" name="name" value="{{ $initialAnrName }}">
                            {{-- Selected option driven solely by $initialAnrName (MonarcSelectionState),
                                 matched against the live ANR list by label — MonarcSyncState's
                                 anr_id is never consulted for the form's own pre-filled values. --}}
                            <select class="form-control" id="monarc_anr_select" style="width: 100%;">
                                <option value="">{{ trans('cruds.monarc.sync.anr_select_placeholder') }}</option>
                                @foreach ($monarcAnrs as $anr)
                                    <option value="{{ $anr['id'] }}" {{ $initialAnrName !== '' && $initialAnrName === $anr['label'] ? 'selected' : '' }}>
                                        {{ $anr['label'] }}
                                    </option>
                                @endforeach
                                @if ($initialAnrName !== '' && ! collect($monarcAnrs)->contains(fn ($anr) => $anr['label'] === $initialAnrName))
                                    <option value="{{ $initialAnrName }}" selected>{{ $initialAnrName }}</option>
                                @endif
                            </select>
                            <span class="help-block">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="monarc-anr-select-spinner" role="status" aria-hidden="true"></span>
                                {{ trans('cruds.monarc.sync.anr_select_help') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="monarc_description">{{ trans('cruds.monarc.description') }}</label>
                            <input class="form-control" type="text" name="description" id="monarc_description"
                                   value="{{ old('description', $savedState['description'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="monarc_language">{{ trans('cruds.monarc.language') }}</label>
                            <select class="form-control select2" name="language" id="monarc_language">
                                <option value="fr" {{ old('language', $savedState['language'] ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                                <option value="en" {{ old('language', $savedState['language'] ?? 'fr') === 'en' ? 'selected' : '' }}>English</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6" id="monarc-sync-model-row">
                        <div class="form-group">
                            <label for="monarc_model_id">{{ trans('cruds.monarc.sync.model_label') }}</label>
                            <select class="form-control select2" id="monarc_model_id">
                                <option value="">{{ trans('cruds.monarc.sync.model_placeholder') }}</option>
                                @foreach ($monarcModels as $model)
                                    <option value="{{ $model['id'] }}" {{ (string) ($syncState['model_id'] ?? '') === (string) $model['id'] ? 'selected' : '' }}>
                                        {{ $model['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-block">{{ trans('cruds.monarc.sync.model_help') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mosp_referentials">{{ trans('cruds.monarc.mosp_referentials') }}</label>
                            <select class="form-control select2" name="mosp_referentials[]" id="mosp_referentials" multiple style="min-height: 100px;">
                                @foreach ($mospReferentials as $referential)
                                    <option value="{{ $referential['id'] }}" {{ in_array($referential['id'], $selectedReferentialIds, true) ? 'selected' : '' }}
                                        data-organization="{{ $referential['organization'] }}">
                                        {{ $referential['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-block">{{ trans('cruds.monarc.mosp_referentials_helper') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">{{ trans('cruds.monarc.selection') }}</div>
            <div class="card-body">
                @foreach ($viewSections as $section)
                    <h6 class="fw-bold {{ $loop->first ? '' : 'mt-4' }} mb-2">{{ $section['label'] }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 16%;">{{ trans('cruds.monarc.row_families') }}</th>
                                    <th style="width: 7%;">{{ trans('cruds.monarc.row_asset') }}</th>
                                    <th style="width: 17%;">{{ trans('cruds.monarc.row_object') }}</th>
                                    <th style="width: 5%;">{{ trans('cruds.monarc.row_nb_amv') }}</th>
                                    <th style="width: 8%;" class="text-center">{{ trans('cruds.monarc.row_generic') }}</th>
                                    <th style="width: 47%;">{{ trans('cruds.monarc.objects') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($section['rows'] as $row)
                                    <tr>
                                        <td>{{ collect($row['families'])->sortBy(fn ($model) => $familyOrder[$model] ?? PHP_INT_MAX)->map(fn ($model) => $familyLabelByModel[$model] ?? $model)->implode(', ') }}</td>
                                        <td>{{ $row['asset_code'] }}</td>
                                        <td>{{ $row['label'] }}</td>
                                        <td>
                                            @php $riskCount = $amvCountByAssetUuid[$row['asset_uuid']] ?? 0; @endphp
                                            @if ($riskCount > 0)
                                                <a href="#" class="row-risk-count-link"
                                                   data-asset-uuid="{{ $row['asset_uuid'] }}" data-asset-label="{{ $row['label'] }}">{{ $riskCount }}</a>
                                            @else
                                                {{ $riskCount }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input row-generic-toggle"
                                                   data-row-uuid="{{ $row['row_uuid'] }}"
                                                   name="rows[{{ $row['row_uuid'] }}][generic]" value="1"
                                                   title="{{ trans('cruds.monarc.row_generic_help') }}">
                                        </td>
                                        <td>
                                            <input type="hidden" class="row-asset-uuid"
                                                   name="rows[{{ $row['row_uuid'] }}][asset_uuid]"
                                                   data-row-uuid="{{ $row['row_uuid'] }}" value="{{ $row['asset_uuid'] }}">
                                            <select class="form-control row-objects" multiple
                                                    data-row-uuid="{{ $row['row_uuid'] }}" data-asset-code="{{ $row['asset_code'] }}"
                                                    name="rows[{{ $row['row_uuid'] }}][objects][]"></select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 18%;"></th>
                                <th style="width: 8%;"></th>
                                <th style="width: 19%;">{{ trans('cruds.monarc.risk_count') }}</th>
                                <th style="width: 5%;"><strong><span id="monarc-risk-count">0</span></strong></th>
                                <th style="width: 25%;"></th>
                                <th style="width: 25%;"></th>
                            </tr>
                        </thead>
                    </table>
                 </div>
            </div>
        </div>

        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.home') }}">
                {{ trans('global.cancel') }}
            </a>
            <button class="btn btn-success" type="submit" formaction="{{ route('admin.monarc.save') }}" formnovalidate>
                {{ trans('global.save') }}
            </button>
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-file-export me-1"></i>{{ trans('cruds.monarc.export_button') }}
            </button>
            <button class="btn btn-warning" type="button" id="monarc-clear-btn">
                {{ trans('cruds.monarc.clear_button') }}
            </button>
        </div>
    </form>

    <div class="card mb-3" id="monarc-sync-card">
        <div class="card-header">{{ trans('cruds.monarc.sync.title') }}</div>
        <div class="card-body">
            <p class="text-muted small">{{ trans('cruds.monarc.sync.help') }}</p>

            <div class="row mb-3">
                <div class="col-md-4">
                    <span id="monarc-sync-anr-label">
                        @if (isset($syncState['anr_id']))
                            {{ trans('cruds.monarc.sync.anr_linked', ['id' => $syncState['anr_id'], 'label' => $syncState['anr_label'] ?? '']) }}
                        @else
                            {{ trans('cruds.monarc.sync.anr_none') }}
                        @endif
                    </span>
                </div>
                <div class="col-md-4">
                    <span id="monarc-sync-last-date">
                        @if (! empty($syncState['last_synced_at']))
                            {{ trans('cruds.monarc.sync.last_sync', ['date' => \Illuminate\Support\Carbon::parse($syncState['last_synced_at'])->format('d/m/Y H:i')]) }}
                        @else
                            {{ trans('cruds.monarc.sync.last_sync_none') }}
                        @endif
                    </span>
                </div>
                <div class="col-md-4">
                    <span id="monarc-sync-count" data-count="{{ $syncedItemsCount }}">{{ trans('cruds.monarc.sync.synced_count', ['count' => $syncedItemsCount]) }}</span>
                </div>
            </div>

            @if ($linkedAnrMissing)
                <div class="alert alert-warning py-2">{{ trans('cruds.monarc.sync.errors.anr_missing') }}</div>
            @endif

            @if ($monarcAnrsError)
                <div class="alert alert-danger py-2">{{ $monarcAnrsError }}</div>
            @endif
            @if ($monarcModelsError)
                <div class="alert alert-danger py-2">{{ $monarcModelsError }}</div>
            @elseif ($monarcModels === [])
                <div class="alert alert-warning py-2">{{ trans('cruds.monarc.sync.errors.no_models') }}</div>
            @endif

            <button type="button" class="btn btn-primary" id="monarc-sync-btn">
                <span class="spinner-border spinner-border-sm d-none me-1" id="monarc-sync-spinner" role="status" aria-hidden="true"></span>
                <i class="fas fa-sync me-1"></i>{{ trans('cruds.monarc.sync.create_button') }}
            </button>
        </div>
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="monarcSyncToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="monarcSyncToastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ trans('cruds.monarc.risks_modal_close') }}"></button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="monarcRisksModal" tabindex="-1" aria-labelledby="monarcRisksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monarcRisksModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('cruds.monarc.risks_modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ trans('cruds.monarc.risks_modal_threat') }}</th>
                                <th>{{ trans('cruds.monarc.risks_modal_vulnerability') }}</th>
                            </tr>
                        </thead>
                        <tbody id="monarcRisksModalBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('cruds.monarc.risks_modal_close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script @nonce>
(function () {
    'use strict';

    var families = @json($familiesForJs);
    var familiesByAssetCode = @json($familiesByAssetCode);
    var relations = @json($relations);
    var amvCountByAssetUuid = @json($amvCountByAssetUuid);
    var risksByAssetUuid = @json($risksByAssetUuid);
    var primaryFamilies = @json($primaryFamilies);
    var savedRows = @json($savedRows);
    var risksModalEmptyMessage = @json(trans('cruds.monarc.risks_modal_empty'));
    var syncUrl = @json(route('admin.monarc.sync'));
    var syncAnrLinkedTemplate = @json(trans('cruds.monarc.sync.anr_linked', ['id' => '__ID__', 'label' => '__LABEL__']));
    var syncLastSyncNoneMessage = @json(trans('cruds.monarc.sync.last_sync_none'));
    var syncCountTemplate = @json(trans('cruds.monarc.sync.synced_count', ['count' => '__COUNT__']));
    var syncNetworkErrorMessage = @json(trans('cruds.monarc.sync.network_error'));
    var syncAnrSelectPlaceholder = @json(trans('cruds.monarc.sync.anr_select_placeholder'));
    var syncAnrNewOptionTemplate = @json(trans('cruds.monarc.sync.anr_select_new_option', ['label' => '__LABEL__']));
    var anrRowsUrlTemplate = @json(route('admin.monarc.anr-rows', ['anrId' => '__ANR_ID__']));

    document.addEventListener('DOMContentLoaded', function () {
        initRowSelects();
        initGenericToggles();
        initRiskCounter();
        initRisksModal();
        initClearButton();
        initSync();
    });

    // ── Zone 2 : Select2 multiple par ligne ─────────────────────────────────
    // Strict filter (not just a sort hint): a row only ever offers objects
    // from the Mercator families mapped to its asset code (server-side
    // mirror: MonarcController::familiesByAssetCode() / rowsFromAssets()).
    function select2DataForRow(assetCode) {
        var allowedModels = familiesByAssetCode[assetCode] || [];

        return families
            .filter(function (family) { return allowedModels.indexOf(family.model) !== -1; })
            .map(function (family) { return { text: family.label, children: family.items }; });
    }

    function initRowSelects() {
        if (typeof $ === 'undefined' || !$.fn.select2) {
            return;
        }

        $('.row-objects').each(function () {
            var $sel = $(this);
            $sel.select2({
                width: '100%',
                data: select2DataForRow($sel.data('asset-code')),
                placeholder: '',
            });

            // Restore a previously saved/exported selection, if any.
            var rowUuid = $sel.data('row-uuid');
            var saved = (savedRows[rowUuid] || {}).objects || [];
            if (saved.length > 0) {
                $sel.val(saved).trigger('change.select2');
            }
        });

        $('.row-objects').on('change', function () {
            computeRiskCount();
        });
    }

    // Un objet "générique" remplace la sélection individuelle de cette ligne :
    // le select d'objets Mercator est désactivé tant que la case est cochée.
    function applyGenericState(rowUuid, checked) {
        // Disables only — the previous selection stays visible (and comes
        // back if the checkbox is unchecked later); a disabled <select>'s
        // options are simply never submitted, so nothing extra is sent
        // server-side (see MonarcController::flattenRowSelection(), which
        // ignores "objects" for a generic row regardless).
        $('.row-objects[data-row-uuid="' + rowUuid + '"]').prop('disabled', checked);
    }

    function initGenericToggles() {
        document.querySelectorAll('.row-generic-toggle').forEach(function (checkbox) {
            var rowUuid = checkbox.getAttribute('data-row-uuid');

            // Restore a previously saved/exported selection, if any.
            if ((savedRows[rowUuid] || {}).generic) {
                checkbox.checked = true;
            }
            applyGenericState(rowUuid, checkbox.checked);

            checkbox.addEventListener('change', function () {
                applyGenericState(rowUuid, checkbox.checked);
                computeRiskCount();
            });
        });
    }

    // ── Zone 3 : compteur de risques (miroir exact de countRisks()) ────────
    // A checked generic-type row keeps its previous select value visible
    // (see applyGenericState()) but that selection is never actually
    // submitted (disabled fields aren't) — mirror that here so the
    // client-side estimate doesn't count it too.
    function isGenericRow(rowUuid) {
        var checkbox = document.querySelector('.row-generic-toggle[data-row-uuid="' + rowUuid + '"]');
        return !!(checkbox && checkbox.checked);
    }

    function selectedSet() {
        var set = {};
        document.querySelectorAll('.row-objects').forEach(function (sel) {
            if (isGenericRow(sel.getAttribute('data-row-uuid'))) {
                return;
            }
            ($(sel).val() || []).forEach(function (key) { set[key] = true; });
        });
        return set;
    }

    function occurrenceCount(key, selected, memo, visiting) {
        if (Object.prototype.hasOwnProperty.call(memo, key)) {
            return memo[key];
        }
        var model = key.split(':')[0];
        if (primaryFamilies.indexOf(model) !== -1) {
            return memo[key] = 1;
        }
        if (visiting[key]) {
            return 1;
        }
        visiting[key] = true;

        var groups = relations[key] || [];
        var parents = [];
        for (var i = 0; i < groups.length; i++) {
            var matched = groups[i].filter(function (p) { return selected[p]; });
            if (matched.length > 0) {
                parents = matched;
                break;
            }
        }

        var result;
        if (parents.length === 0) {
            result = 1;
        } else {
            result = 0;
            parents.forEach(function (p) {
                result += occurrenceCount(p, selected, memo, visiting);
            });
        }
        delete visiting[key];

        return memo[key] = result;
    }

    function computeRiskCount() {
        var selected = selectedSet();
        var memo = {};
        var visiting = {};
        var total = 0;

        document.querySelectorAll('.row-objects').forEach(function (sel) {
            var rowUuid = sel.getAttribute('data-row-uuid');
            if (isGenericRow(rowUuid)) {
                return;
            }
            var assetUuidInput = document.querySelector('.row-asset-uuid[data-row-uuid="' + rowUuid + '"]');
            var assetUuid = assetUuidInput ? assetUuidInput.value : '';
            var n = amvCountByAssetUuid[assetUuid] || 0;
            var keys = $(sel).val() || [];

            keys.forEach(function (key) {
                total += n * occurrenceCount(key, selected, memo, visiting);
            });
        });

        // A generic-type row contributes its own asset's full risk count
        // exactly once — it has no cartography placements to multiply by.
        document.querySelectorAll('.row-generic-toggle:checked').forEach(function (checkbox) {
            var rowUuid = checkbox.getAttribute('data-row-uuid');
            var assetUuidInput = document.querySelector('.row-asset-uuid[data-row-uuid="' + rowUuid + '"]');
            var assetUuid = assetUuidInput ? assetUuidInput.value : '';
            total += amvCountByAssetUuid[assetUuid] || 0;
        });

        var el = document.getElementById('monarc-risk-count');
        if (el) {
            el.textContent = total;
        }
    }

    function initRiskCounter() {
        computeRiskCount();
    }

    // ── Zone 4 : détail des risques d'un actif (modale) ─────────────────────
    function initRisksModal() {
        document.querySelectorAll('.row-risk-count-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                showRisksModal(this.getAttribute('data-asset-uuid'), this.getAttribute('data-asset-label'));
            });
        });
    }

    function showRisksModal(assetUuid, assetLabel) {
        var modalEl = document.getElementById('monarcRisksModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        document.getElementById('monarcRisksModalLabel').textContent = assetLabel;

        var tbody = document.getElementById('monarcRisksModalBody');
        tbody.innerHTML = '';
        var risks = risksByAssetUuid[assetUuid] || [];

        if (risks.length === 0) {
            var emptyRow = document.createElement('tr');
            var emptyCell = document.createElement('td');
            emptyCell.colSpan = 2;
            emptyCell.className = 'text-muted';
            emptyCell.textContent = risksModalEmptyMessage;
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
        } else {
            risks.forEach(function (risk) {
                var row = document.createElement('tr');
                var threatCell = document.createElement('td');
                threatCell.textContent = risk.threat;
                var vulnCell = document.createElement('td');
                vulnCell.textContent = risk.vulnerability;
                row.appendChild(threatCell);
                row.appendChild(vulnCell);
                tbody.appendChild(row);
            });
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // ── Bouton "Effacer" : réinitialise le formulaire affiché sans rien
    // sauvegarder — un rechargement de page restaure le dernier état enregistré.
    function initClearButton() {
        var btn = document.getElementById('monarc-clear-btn');
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function () {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#monarc_anr_select').val(null).trigger('change');
            } else {
                document.getElementById('monarc_name').value = '';
            }
            document.getElementById('monarc_description').value = '';

            var referentials = document.getElementById('mosp_referentials');
            if (referentials) {
                Array.from(referentials.options).forEach(function (opt) { opt.selected = false; });
            }

            document.querySelectorAll('.row-generic-toggle').forEach(function (checkbox) {
                checkbox.checked = false;
                applyGenericState(checkbox.getAttribute('data-row-uuid'), false);
            });

            $('.row-objects').val(null).trigger('change.select2');
            computeRiskCount();
        });
    }

    // ── Zone 5 : création/synchronisation de l'ANR Monarc ──────────────────
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showSyncToast(message, type) {
        var toastEl = document.getElementById('monarcSyncToast');
        if (!toastEl || typeof bootstrap === 'undefined') {
            return;
        }
        toastEl.classList.remove('bg-success', 'bg-danger');
        toastEl.classList.add(type === 'error' ? 'bg-danger' : 'bg-success');
        document.getElementById('monarcSyncToastBody').textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 5000 }).show();
    }

    // Lookup of known existing-ANR ids ("18" -> "My analysis"), built once the
    // combobox is initialized — tells buildSyncFormData() whether the
    // select2 value is an existing ANR id or free text the admin just typed.
    var monarcAnrLabelById = {};

    function buildSyncFormData() {
        var form = document.getElementById('monarc-export-form');
        var formData = new FormData(form);

        var modelSelect = document.getElementById('monarc_model_id');
        if (modelSelect && modelSelect.value) {
            formData.append('model_id', modelSelect.value);
        }

        var anrSelect = document.getElementById('monarc_anr_select');
        var chosen = anrSelect ? (anrSelect.value || '').trim() : '';
        if (chosen !== '') {
            if (Object.prototype.hasOwnProperty.call(monarcAnrLabelById, chosen)) {
                formData.append('anr_id', chosen);
                formData.append('anr_label', monarcAnrLabelById[chosen]);
            } else {
                formData.append('anr_label', chosen);
            }
        }

        return formData;
    }

    function setSyncBusy(busy) {
        var btn = document.getElementById('monarc-sync-btn');
        var spinner = document.getElementById('monarc-sync-spinner');
        if (!btn) {
            return;
        }
        btn.disabled = busy;
        if (spinner) {
            spinner.classList.toggle('d-none', !busy);
        }
    }

    function applySyncResult(result) {
        var anrLabelEl = document.getElementById('monarc-sync-anr-label');
        if (anrLabelEl && result.anr_id) {
            anrLabelEl.textContent = syncAnrLinkedTemplate.replace('__ID__', result.anr_id).replace('__LABEL__', result.anr_label || '');
        }

        var lastDateEl = document.getElementById('monarc-sync-last-date');
        if (lastDateEl && (result.status === 'synced' || result.status === 'up_to_date')) {
            lastDateEl.textContent = new Date().toLocaleString();
        }

        var countEl = document.getElementById('monarc-sync-count');
        if (countEl && typeof result.sent_count === 'number' && result.sent_count > 0) {
            var updated = parseInt(countEl.getAttribute('data-count') || '0', 10) + result.sent_count;
            countEl.setAttribute('data-count', String(updated));
            countEl.textContent = syncCountTemplate.replace('__COUNT__', updated);
        }

        if (result.anr_id) {
            // Reflect the (possibly just-created, or just-switched-to) ANR
            // in the combobox itself, so a follow-up click resends its real
            // anr_id rather than re-typing/recreating the same label.
            var anrSelect = document.getElementById('monarc_anr_select');
            var idStr = String(result.anr_id);
            if (anrSelect && typeof $ !== 'undefined' && $.fn.select2) {
                monarcAnrLabelById[idStr] = result.anr_label || '';
                if ($(anrSelect).find('option[value="' + idStr + '"]').length === 0) {
                    $(anrSelect).append(new Option(result.anr_label || idStr, idStr, false, false));
                }
                $(anrSelect).val(idStr).trigger('change');
            }
        }
    }

    // Select2 "tags" combobox: lists the ANRs already present on the
    // configured Monarc instance, and also accepts free text — typing a
    // name not in the list creates a new tag whose value equals the typed
    // text itself (Select2's default tagging behavior), which is exactly
    // how buildSyncFormData() tells "pick existing" apart from "create new".
    // The merged name/ANR field: resolves the select2 value to its real
    // label (an existing ANR's own label, or the raw typed text for a new
    // one) and mirrors it into the hidden "name" input actually submitted
    // by the export/save/sync form — see the blade's initialAnrName comment.
    function currentAnrChoiceLabel(chosen) {
        return Object.prototype.hasOwnProperty.call(monarcAnrLabelById, chosen) ? monarcAnrLabelById[chosen] : chosen;
    }

    function syncNameFromAnrSelect() {
        var anrSelect = document.getElementById('monarc_anr_select');
        var chosen = anrSelect ? (anrSelect.value || '').trim() : '';
        document.getElementById('monarc_name').value = chosen === '' ? '' : currentAnrChoiceLabel(chosen);
    }

    // Applies a {row_uuid: {asset_uuid, objects?, generic?}} map (as
    // returned by GET admin/monarc/anr/{id}/rows) onto the page's row
    // controls — the same restore MonarcController::rowsFromSyncItems()
    // reconstructed from monarc_sync_items for that ANR.
    // Replaces EVERY row's state (not just the ones present in "rows") —
    // a row absent from the map is cleared, so switching from one ANR to
    // another (or to one with no tracked items at all) never leaves a
    // previous selection looking incorrectly still-tracked.
    function applyRestoredRows(rows) {
        document.querySelectorAll('.row-generic-toggle').forEach(function (checkbox) {
            var rowUuid = checkbox.getAttribute('data-row-uuid');
            var row = rows[rowUuid] || null;

            checkbox.checked = !! (row && row.generic);
            applyGenericState(rowUuid, checkbox.checked);

            var $objectsSel = $('.row-objects[data-row-uuid="' + rowUuid + '"]');
            if (! checkbox.checked && $objectsSel.length > 0) {
                $objectsSel.val((row && row.objects) || []).trigger('change.select2');
            }
        });

        computeRiskCount();
    }

    // Refreshes the "ANR liée / dernière synchronisation / objets suivis"
    // summary the moment an existing ANR is picked, mirroring what
    // applySyncResult() does after an actual sync — this is only a
    // client-side PREVIEW of that ANR's state though: it does not touch
    // monarc_sync_items or MonarcSyncState, so nothing is actually "linked"
    // (and the reset button's enabled state is left alone) until the admin
    // clicks "Create/Synchronize".
    function applyAnrSelectionMetadata(anrId, label, syncedCount, lastSyncedAt) {
        var anrLabelEl = document.getElementById('monarc-sync-anr-label');
        if (anrLabelEl) {
            anrLabelEl.textContent = syncAnrLinkedTemplate.replace('__ID__', anrId).replace('__LABEL__', label || '');
        }

        var lastDateEl = document.getElementById('monarc-sync-last-date');
        if (lastDateEl) {
            lastDateEl.textContent = lastSyncedAt ? new Date(lastSyncedAt).toLocaleString() : syncLastSyncNoneMessage;
        }

        var countEl = document.getElementById('monarc-sync-count');
        if (countEl) {
            countEl.setAttribute('data-count', String(syncedCount));
            countEl.textContent = syncCountTemplate.replace('__COUNT__', syncedCount);
        }
    }

    // Applies the server's authoritative name/language for the picked ANR
    // (MonarcController::anrRows() just merged these same values into
    // MonarcSelectionState) onto the two form fields — the hidden "name"
    // input needs no select2 dance, but the language <select> does.
    function applyAnrFields(name, language) {
        var nameInput = document.getElementById('monarc_name');
        if (nameInput && typeof name === 'string') {
            nameInput.value = name;
        }

        var languageSelect = document.getElementById('monarc_language');
        if (languageSelect && (language === 'fr' || language === 'en')) {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $(languageSelect).val(language).trigger('change.select2');
            } else {
                languageSelect.value = language;
            }
        }
    }

    // Spinner + disabling of every field the restore is about to overwrite
    // (name/ANR combobox, language, each row's object select and generic
    // checkbox) plus the sync button, for the duration of the fetch — all
    // re-enabled once it settles, success or failure alike.
    function setAnrRestoreBusy(busy) {
        var spinner = document.getElementById('monarc-anr-select-spinner');
        if (spinner) {
            spinner.classList.toggle('d-none', !busy);
        }

        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#monarc_anr_select').prop('disabled', busy);
            $('#monarc_language').prop('disabled', busy);
            $('.row-objects').prop('disabled', busy);
        }

        document.querySelectorAll('.row-generic-toggle').forEach(function (checkbox) {
            checkbox.disabled = busy;
        });

        var syncBtn = document.getElementById('monarc-sync-btn');
        if (syncBtn) {
            syncBtn.disabled = busy;
        }
    }

    // Fired the moment the admin picks an already-existing Monarc ANR from
    // the combobox: restores the row selection from what monarc_sync_items
    // actually recorded for it (best-effort — see the controller docblock),
    // rather than leaving the page's current, unrelated selection in place,
    // and refreshes the name/language fields and the summary display to match.
    function restoreRowsForAnr(anrId, label) {
        setAnrRestoreBusy(true);

        fetch(anrRowsUrlTemplate.replace('__ANR_ID__', anrId), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (data) {
                setAnrRestoreBusy(false);

                if (!data) {
                    showSyncToast(syncNetworkErrorMessage, 'error');

                    return;
                }

                applyAnrFields(data.name, data.language);
                if (data.rows) {
                    // Re-applies each row's own disabled state (generic vs.
                    // not) on top of setAnrRestoreBusy(false)'s blanket
                    // re-enable above — must run after it, not before.
                    applyRestoredRows(data.rows);
                }
                applyAnrSelectionMetadata(anrId, label, data.synced_count || 0, data.last_synced_at || null);
            })
            .catch(function () {
                // Best-effort restore only — leave the current selection untouched on failure.
                setAnrRestoreBusy(false);
                showSyncToast(syncNetworkErrorMessage, 'error');
            });
    }

    function initAnrSelect() {
        var $sel = $('#monarc_anr_select');
        if (typeof $ === 'undefined' || !$.fn.select2 || $sel.length === 0) {
            return;
        }

        // Options are already server-rendered (including the currently
        // linked ANR, pre-selected) — just index them for buildSyncFormData().
        // Only numeric values are real ANR ids: the blade also renders one
        // "fallback" option for a saved-but-not-yet-existing name (its value
        // is the raw typed text itself), which must NOT be indexed here —
        // otherwise the on-load restore below would treat it as an existing
        // ANR and fetch a bogus, non-numeric "id".
        $sel.find('option[value!=""]').each(function () {
            if (/^\d+$/.test(this.value)) {
                monarcAnrLabelById[this.value] = this.textContent.trim();
            }
        });

        $sel.select2({
            width: '100%',
            tags: true,
            placeholder: syncAnrSelectPlaceholder,
            allowClear: true,
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '' || Object.prototype.hasOwnProperty.call(monarcAnrLabelById, term)) {
                    return null;
                }
                return { id: term, text: syncAnrNewOptionTemplate.replace('__LABEL__', term), newTag: true };
            },
        });

        function restoreIfExistingAnrChosen() {
            var chosen = ($sel.val() || '').trim();
            if (chosen !== '' && Object.prototype.hasOwnProperty.call(monarcAnrLabelById, chosen)) {
                restoreRowsForAnr(chosen, monarcAnrLabelById[chosen]);
            }
        }

        $sel.on('change', function () {
            syncNameFromAnrSelect();
            restoreIfExistingAnrChosen();
        });

        // A browser/select2 "change" event only fires on an actual value
        // transition — not when the field is already sitting on an existing
        // ANR at page load (pre-selected from MonarcSelectionState) and the
        // admin re-picks the very same option, or after "Reset link" (which
        // clears monarc_sync_items/MonarcSyncState but deliberately leaves
        // MonarcSelectionState's name/rows alone). Both cases would
        // otherwise silently show a stale (or, post-reset, simply wrong)
        // row selection with no way to refresh it from the UI. Running the
        // same restore once at init time — whenever the field already
        // resolves to a real ANR — keeps what's on screen honest in both.
        restoreIfExistingAnrChosen();
    }

    function initSync() {
        initAnrSelect();

        var btn = document.getElementById('monarc-sync-btn');
        if (btn) {
            btn.addEventListener('click', function () {
                setSyncBusy(true);

                fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: buildSyncFormData()
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        setSyncBusy(false);
                        if (!result.ok || result.data.status === 'error' || result.data.status === 'anr_missing') {
                            showSyncToast((result.data && result.data.message) || syncNetworkErrorMessage, 'error');
                            return;
                        }
                        applySyncResult(result.data);
                        showSyncToast(result.data.message, 'success');
                    })
                    .catch(function () {
                        setSyncBusy(false);
                        showSyncToast(syncNetworkErrorMessage, 'error');
                    });
            });
        }

    }
})();
</script>
@endsection
