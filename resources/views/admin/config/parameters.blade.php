@extends('layouts.admin')

@section('title')
    {{ trans('cruds.configuration.parameters.title') }}
@endsection

@section('content')

{{-- ─── Alert messages ──────────────────────────────────────────────────── --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php $tab = $active_tab ?? 'general'; @endphp

{{-- ─── Tab navigation ──────────────────────────────────────────────────── --}}
<ul class="nav nav-tabs" id="configTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'general' ? 'active' : '' }}"
                id="tab-general-btn" data-bs-toggle="tab" data-bs-target="#tab-general"
                type="button" role="tab"
                aria-controls="tab-general" aria-selected="{{ $tab === 'general' ? 'true' : 'false' }}">
            <i class="fas fa-cog me-1"></i>
            {{ trans('cruds.configuration.parameters.title_short') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'perimeters' ? 'active' : '' }}"
                id="tab-perimeters-btn" data-bs-toggle="tab" data-bs-target="#tab-perimeters"
                type="button" role="tab"
                aria-controls="tab-perimeters" aria-selected="{{ $tab === 'perimeters' ? 'true' : 'false' }}">
            <i class="fas fa-object-group me-1"></i>
            {{ trans('cruds.configuration.perimeters.title_short') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'cert' ? 'active' : '' }}"
                id="tab-cert-btn" data-bs-toggle="tab" data-bs-target="#tab-cert"
                type="button" role="tab"
                aria-controls="tab-cert" aria-selected="{{ $tab === 'cert' ? 'true' : 'false' }}">
            <i class="fas fa-shield-alt me-1"></i>
            {{ trans('cruds.configuration.certificate.title_short') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'cleanup' ? 'active' : '' }}"
                id="tab-cleanup-btn" data-bs-toggle="tab" data-bs-target="#tab-cleanup"
                type="button" role="tab"
                aria-controls="tab-cleanup" aria-selected="{{ $tab === 'cleanup' ? 'true' : 'false' }}">
            <i class="fas fa-broom me-1"></i>
            {{ trans('cruds.configuration.cleanup.title_short') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'cve' ? 'active' : '' }}"
                id="tab-cve-btn" data-bs-toggle="tab" data-bs-target="#tab-cve"
                type="button" role="tab"
                aria-controls="tab-cve" aria-selected="{{ $tab === 'cve' ? 'true' : 'false' }}">
            <i class="fas fa-bug me-1"></i>
            {{ trans('cruds.configuration.cve.title_short') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'reminders' ? 'active' : '' }}"
                id="tab-reminders-btn" data-bs-toggle="tab" data-bs-target="#tab-reminders"
                type="button" role="tab"
                aria-controls="tab-reminders" aria-selected="{{ $tab === 'reminders' ? 'true' : 'false' }}">
            <i class="fas fa-clock me-1"></i>
            {{ trans('cruds.notifications.tab_title_reminders') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'notifications' ? 'active' : '' }}"
                id="tab-notifications-btn" data-bs-toggle="tab" data-bs-target="#tab-notifications"
                type="button" role="tab"
                aria-controls="tab-notifications" aria-selected="{{ $tab === 'notifications' ? 'true' : 'false' }}">
            <i class="fas fa-bell me-1"></i>
            {{ trans('cruds.notifications.tab_title_notifications') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'documents' ? 'active' : '' }}"
                id="tab-documents-btn" data-bs-toggle="tab" data-bs-target="#tab-documents"
                type="button" role="tab"
                aria-controls="tab-documents" aria-selected="{{ $tab === 'documents' ? 'true' : 'false' }}">
            <i class="fas fa-file-alt me-1"></i>
            {{ trans('cruds.configuration.documents.title') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold {{ $tab === 'monarc' ? 'active' : '' }}"
                id="tab-monarc-btn" data-bs-toggle="tab" data-bs-target="#tab-monarc"
                type="button" role="tab"
                aria-controls="tab-monarc" aria-selected="{{ $tab === 'monarc' ? 'true' : 'false' }}">
            {{ trans('cruds.configuration.monarc.title_short') }}
        </button>
    </li>
</ul>

<div class="tab-content" id="configTabsContent">

    {{-- ================================================================== --}}
    {{-- TAB 1 : Paramètres généraux                                         --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'general' ? 'show active' : '' }}"
         id="tab-general" role="tabpanel" aria-labelledby="tab-general-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="general">

            <div class="card">
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label>{{ trans('cruds.configuration.parameters.help') }}</label>
                    </div>
                </div>
                <div class="card-body border-top">
                    <h6 class="fw-bold mb-3">{{ trans('cruds.menu.application.title_short') }}</h6>
                    <div class="form-group mb-3">
                        <label class="d-block mb-1">
                            {{ trans('cruds.configuration.parameters.application_documents_helper') }}
                        </label>
                        <div class="form-check form-switch">
                            <input name="application_documents" id="application_documents"
                                   type="checkbox" class="form-check-input"
                                   {{ $application_documents ? 'checked' : '' }}>
                            <label class="form-check-label" for="application_documents">
                                {{ trans('cruds.configuration.parameters.application_documents') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body border-top">
                    <h6 class="fw-bold mb-3">{{ trans('cruds.menu.logical_infrastructure.title_short') }}</h6>
                    <div class="form-group mb-3">
                        <label class="d-block mb-1">
                            {{ trans('cruds.configuration.parameters.security_need_auth_helper') }}
                        </label>
                        <div class="form-check form-switch">
                            <input name="security_need_auth" id="security_need_auth"
                                   type="checkbox" class="form-check-input"
                                   {{ $security_need_auth ? 'checked' : '' }}>
                            <label class="form-check-label" for="security_need_auth">
                                {{ trans('cruds.configuration.parameters.security_need_auth') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body border-top">
                    <h6 class="fw-bold mb-3">{{ trans('cruds.configuration.parameters.cartography') }}</h6>
                    <div class="form-group mb-3">
                        <label class="d-block mb-1">
                            {{ trans('cruds.configuration.parameters.max_nodes_helper') }}
                        </label>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <label class="form-label" for="max_nodes">
                                {{ trans('cruds.configuration.parameters.max_nodes') }}
                            </label>
                            <input type="number" name="max_nodes" id="max_nodes"
                                   class="form-control @error('max_nodes') is-invalid @enderror"
                                   min="0" max="100000" step="1" value="{{ old('max_nodes', $max_nodes) }}">
                            @error('max_nodes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
                <a class="btn btn-default" href="{{ route('admin.home') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </form>

    </div>{{-- /tab-general --}}

    {{-- ================================================================== --}}
    {{-- TAB 2 : Certificats                                                 --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'cert' ? 'show active' : '' }}"
         id="tab-cert" role="tabpanel" aria-labelledby="tab-cert-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="cert">

            <div class="card">
                <div class="card-body">

                    <div class="form-group mb-3">
                        <label>{{ trans('cruds.configuration.certificate.help') }}</label>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cert_mail_subject">
                            {{ trans('cruds.configuration.certificate.message_subject') }}
                        </label>
                        <input class="form-control" type="text"
                               name="mail_subject" id="cert_mail_subject"
                               value="{{ $cert_mail_subject }}" required/>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cert_mail_from">
                            {{ trans('cruds.configuration.certificate.sent_from') }}
                        </label>
                        <input class="form-control" type="text"
                               name="mail_from" id="cert_mail_from"
                               value="{{ $cert_mail_from }}" required/>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cert_mail_to">
                            {{ trans('cruds.configuration.certificate.to') }}
                        </label>
                        <input class="form-control" type="text"
                               name="mail_to" id="cert_mail_to"
                               value="{{ $cert_mail_to }}" required/>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cert_expire_delay">
                            {{ trans('cruds.configuration.certificate.delay') }}
                        </label>
                        <select class="form-control select2" name="expire_delay" id="cert_expire_delay">
                            @foreach ([
                                '1'  => '1 '  . trans('global.day'),
                                '7'  => '7 '  . trans('global.days'),
                                '15' => '15 ' . trans('global.days'),
                                '30' => '1 '  . trans('global.month'),
                                '60' => '2 '  . trans('global.months'),
                                '90' => '3 '  . trans('global.months'),
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ $cert_expire_delay == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cert_check_frequency">
                            {{ trans('cruds.configuration.certificate.recurrence') }}
                        </label>
                        <select class="form-control select2" name="check_frequency" id="cert_check_frequency">
                            <option value="0"  {{ $cert_check_frequency == '0'  ? 'selected' : '' }}>{{ trans('global.never') }}</option>
                            <option value="1"  {{ $cert_check_frequency == '1'  ? 'selected' : '' }}>{{ trans('global.day') }}</option>
                            <option value="7"  {{ $cert_check_frequency == '7'  ? 'selected' : '' }}>{{ trans('global.week') }}</option>
                            <option value="30" {{ $cert_check_frequency == '30' ? 'selected' : '' }}>{{ trans('global.month') }}</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="d-block mb-1">
                            {{ trans('cruds.configuration.certificate.one_mail') }} /
                            {{ trans('cruds.configuration.certificate.multiple_mails') }}
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="group"
                                   id="certRadios1" value="0"
                                   {{ $cert_group === '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="certRadios1">
                                {{ trans('cruds.configuration.certificate.one_mail') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="group"
                                   id="certRadios2" value="1"
                                   {{ $cert_group === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="certRadios2">
                                {{ trans('cruds.configuration.certificate.multiple_mails') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="d-block mb-1">
                            {{ trans('cruds.configuration.certificate.one_notification') }} /
                            {{ trans('cruds.configuration.certificate.multiple_notifications') }}
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="repeat-notification"
                                   id="certRadios3" value="0"
                                   {{ $cert_repeat_notification === '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="certRadios3">
                                {{ trans('cruds.configuration.certificate.one_notification') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="repeat-notification"
                                   id="certRadios4" value="1"
                                   {{ $cert_repeat_notification === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="certRadios4">
                                {{ trans('cruds.configuration.certificate.multiple_notifications') }}
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
                <button class="btn btn-secondary" type="submit" name="action" value="test">
                    <i class="fas fa-paper-plane me-1"></i>{{ trans('global.test') }}
                </button>
            </div>

        </form>

    </div>{{-- /tab-cert --}}

    {{-- ================================================================== --}}
    {{-- TAB : Nettoyage de la base de données                              --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'cleanup' ? 'show active' : '' }}"
         id="tab-cleanup" role="tabpanel" aria-labelledby="tab-cleanup-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="cleanup">

            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ trans('cruds.configuration.cleanup.help') }}
                    </p>
                </div>

                @foreach ([
                    'audit_logs' => ['enabled' => $cleanup_audit_logs_enabled, 'retention' => $cleanup_audit_logs_retention],
                    'deleted_objects' => ['enabled' => $cleanup_deleted_objects_enabled, 'retention' => $cleanup_deleted_objects_retention],
                ] as $key => $item)
                <div class="card-body border-top">
                    <h6 class="fw-bold mb-3">{{ trans('cruds.configuration.cleanup.'.$key) }}</h6>
                    <div class="form-group mb-3">
                        <label class="d-block mb-1">
                            {{ trans('cruds.configuration.cleanup.'.$key.'_helper') }}
                        </label>
                        <div class="form-check form-switch">
                            <input name="{{ $key }}_enabled" id="{{ $key }}_enabled"
                                   type="checkbox" class="form-check-input"
                                   {{ $item['enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $key }}_enabled">
                                {{ trans('cruds.configuration.cleanup.'.$key.'_enabled') }}
                            </label>
                        </div>
                    </div>
                    <fieldset id="fieldset-{{ $key }}"
                              {{ $item['enabled'] ? '' : 'disabled' }}
                              class="{{ $item['enabled'] ? '' : 'opacity-50' }}">
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <label class="form-label" for="{{ $key }}_retention">
                                {{ trans('cruds.configuration.cleanup.retention') }}
                            </label>
                            <select name="{{ $key }}_retention" id="{{ $key }}_retention"
                                    class="form-control select2 @error($key.'_retention') is-invalid @enderror"
                                    data-allow-clear="false"
                                    {{ $item['enabled'] ? '' : 'disabled' }}>
                                @foreach ($cleanup_retention_options as $months)
                                    <option value="{{ $months }}" {{ (int) old($key.'_retention', $item['retention']) === $months ? 'selected' : '' }}>
                                        {{ trans_choice('cruds.configuration.cleanup.months', $months, ['count' => $months]) }}
                                    </option>
                                @endforeach
                            </select>
                            @error($key.'_retention')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </fieldset>
                </div>
                @endforeach

                <div class="card-footer text-muted small">
                    {{ trans('cruds.configuration.cleanup.schedule') }}
                    —
                    {{ trans('cruds.configuration.cleanup.last_run') }} :
                    @if ($cleanup_last_run)
                        {{ $cleanup_last_run['date'] }}
                        @isset($cleanup_last_run['audit_logs'])
                            · {{ trans('cruds.configuration.cleanup.last_run_logs', ['count' => $cleanup_last_run['audit_logs']]) }}
                        @endisset
                        @isset($cleanup_last_run['deleted_objects'])
                            · {{ trans('cruds.configuration.cleanup.last_run_objects', ['count' => $cleanup_last_run['deleted_objects']]) }}
                        @endisset
                        @if (($cleanup_last_run['skipped_objects'] ?? 0) > 0)
                            · {{ trans('cruds.configuration.cleanup.last_run_skipped', ['count' => $cleanup_last_run['skipped_objects']]) }}
                        @endif
                    @else
                        {{ trans('global.never') }}
                    @endif
                </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
                <button class="btn btn-outline-danger" type="submit" form="trash-enter-form" id="btn-trash-enter">
                    <i class="bi bi-trash3 me-1"></i>{{ trans('cruds.trash.enter') }}
                </button>
            </div>
        </form>
        {{-- Formulaire séparé (pas de formulaires imbriqués) --}}
        <form id="trash-enter-form" method="POST" action="{{ route('admin.trash.enter') }}">
            @csrf
        </form>

    </div>{{-- /tab-cleanup --}}

    {{-- ================================================================== --}}
    {{-- TAB 3 : CVE                                                         --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'cve' ? 'show active' : '' }}"
         id="tab-cve" role="tabpanel" aria-labelledby="tab-cve-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="cve">

            <div class="card">
                <div class="card-body">

                    <div class="form-group mb-3">
                        <label>{{ trans('cruds.configuration.cve.help') }}</label>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cve_mail_subject">
                            {{ trans('cruds.configuration.cve.message_subject') }}
                        </label>
                        <input class="form-control" type="text"
                               name="mail_subject" id="cve_mail_subject"
                               value="{{ $cve_mail_subject }}" required/>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cve_mail_from">
                            {{ trans('cruds.configuration.cve.sent_from') }}
                        </label>
                        <input class="form-control" type="text"
                               name="mail_from" id="cve_mail_from"
                               value="{{ $cve_mail_from }}" required/>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cve_mail_to">
                            {{ trans('cruds.configuration.cve.to') }}
                        </label>
                        <input class="form-control" type="text"
                               name="mail_to" id="cve_mail_to"
                               value="{{ $cve_mail_to }}" required/>
                    </div>

                    <div class="form-group mb-3">
                        <label class="label-required" for="cve_check_frequency">
                            {{ trans('cruds.configuration.cve.recurrence') }}
                        </label>
                        <select class="form-control select2" name="check_frequency" id="cve_check_frequency">
                            <option value="0"  {{ $cve_check_frequency == '0'  ? 'selected' : '' }}>{{ trans('global.never') }}</option>
                            <option value="1"  {{ $cve_check_frequency == '1'  ? 'selected' : '' }}>{{ trans('global.day') }}</option>
                            <option value="7"  {{ $cve_check_frequency == '7'  ? 'selected' : '' }}>{{ trans('global.week') }}</option>
                            <option value="30" {{ $cve_check_frequency == '30' ? 'selected' : '' }}>{{ trans('global.month') }}</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="cpe_guesser">
                            {{ trans('cruds.configuration.cpe.guesser') }}
                        </label>
                        <input class="form-control" type="text"
                               name="cpe_guesser" id="cpe_guesser"
                               value="{{ $cpe_guesser }}"/>
                        <span class="help-block text-muted small">
                            {{ trans('cruds.configuration.cpe.guesser_helper') }}
                        </span>
                    </div>


                    <div class="form-group mb-3">
                        <label for="cve_provider">
                            {{ trans('cruds.configuration.cve.provider') }}
                        </label>
                        <input class="form-control" type="text"
                               name="provider" id="cve_provider"
                               value="{{ $cve_provider }}"/>
                        <span class="help-block text-muted small">
                            {{ trans('cruds.configuration.cve.provider_helper') }}
                        </span>
                    </div>
                </div>
                    <div class="card-footer">
                        Last CPE-Sync : {{ $last_cpe_sync ?? "Never" }}
                    </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
                <button class="btn btn-secondary" type="submit" name="action" value="test">
                    <i class="fas fa-paper-plane me-1"></i>{{ trans('global.test') }} Mail
                </button>
                <button class="btn btn-secondary" type="submit" name="action" value="test_provider">
                    <i class="fas fa-plug me-1"></i>{{ trans('global.test') }} Provider
                </button>
                <button class="btn btn-secondary" type="submit" name="action" value="test_guesser">
                    <i class="fas fa-plug me-1"></i>{{ trans('global.test') }} Guesser
                </button>
            </div>

        </form>

    </div>{{-- /tab-cve --}}

    {{-- ================================================================== --}}
    {{-- TAB 4 : Rappels                                                     --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'reminders' ? 'show active' : '' }}"
         id="tab-reminders" role="tabpanel" aria-labelledby="tab-reminders-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="reminders">

            <div class="card">
                <div class="card-body">

                    <p class="text-muted mb-3">
                        {{ trans('cruds.notifications.tab_legend_reminders') }}
                    </p>

                    <div class="form-group mb-3">
                        <div class="form-check form-switch">
                            <input name="reminders_enabled" id="reminders_enabled"
                                   type="checkbox" class="form-check-input"
                                   {{ $notif_reminders_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="reminders_enabled">
                                {{ trans('cruds.notifications.reminders_enabled') }}
                            </label>
                        </div>
                    </div>

                    <fieldset id="fieldset-reminders"
                              {{ $notif_reminders_enabled ? '' : 'disabled' }}
                              class="{{ $notif_reminders_enabled ? '' : 'opacity-50' }}">

                        <div class="row">
                        <div class="col-3">
                        <div class="form-group mb-3">
                            <label for="reminder_from">{{ trans('cruds.notifications.reminder_from') }}</label>
                            <input class="form-control" type="email"
                                   name="reminder_from" id="reminder_from"
                                   value="{{ $notif_reminder_from }}"/>
                        </div>
                        </div>

                        <div class="col-3">
                        <div class="form-group mb-3">
                            <label for="reminder_to">{{ trans('cruds.notifications.reminder_to') }}</label>
                            <input class="form-control" type="email"
                                   name="reminder_to" id="reminder_to"
                                   value="{{ $notif_reminder_to }}"/>
                        </div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-6">
                        <div class="form-group mb-3">
                            <label for="reminder_subject">{{ trans('cruds.notifications.reminder_subject') }}</label>
                            <input class="form-control" type="text"
                                   name="reminder_subject" id="reminder_subject"
                                   value="{{ $notif_reminder_subject }}"/>
                        </div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-9">
                            <div class="form-group mb-3">
                                <label for="reminder_body">{{ trans('cruds.notifications.reminder_body') }}</label>
                                <textarea class="form-control" name="reminder_body" id="reminder_body"
                                          rows="13">{{ $notif_reminder_body }}</textarea>
                            </div>
                        </div>
                        <div class="col-3">
                            <br><br>
                            <table class="table table-sm table-bordered table-hover" style="font-size: 14px; font-family: var(--font-mono);">
                            <thead class="table-dark">
                              <tr>
                                <th style="width: 42%;">Variable</th>
                                <th>Description</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr><td><code>:user</code></td><td>Nom de l'utilisateur</td></tr>
                              <tr><td><code>:count</code></td><td>Nombre d'objets</td></tr>
                              <tr><td><code>:list</code></td><td>Liste des objets</td></tr>
                              <tr><td><code>:month</code></td><td>Récurence de mise à jour</td></tr>
                              <tr><td><code>:mercator</code></td><td>URL de Mercator</td></tr>
                            </tbody>
                            </table>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-3">
                        <div class="form-group mb-3">
                            <label for="reminder_months">{{ trans('cruds.notifications.reminder_months') }}</label>
                            <input class="form-control" type="number" min="1"
                                   name="reminder_months" id="reminder_months"
                                   value="{{ $notif_reminder_months }}"/>
                        </div>
                        </div>
                        <div class="col-3">
                        <div class="form-group mb-3">
                            <label for="reminder_every_days">{{ trans('cruds.notifications.reminder_every_days') }}</label>
                            <input class="form-control" type="number" min="1"
                                   name="reminder_every_days" id="reminder_every_days"
                                   value="{{ $notif_reminder_every_days }}"/>
                        </div>
                        </div>
                        </div>

                    </fieldset>

                </div>
                <div class="card-footer text-muted small">
                    {{ trans('cruds.notifications.last_reminder_sent') }} :
                    {{ $notif_reminder_last_sent ?? trans('global.never') }}
                </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
                <button class="btn btn-secondary" type="submit" name="action" value="test_reminder"
                        {{ $notif_reminders_enabled ? '' : 'disabled' }}>
                    <i class="fas fa-paper-plane me-1"></i>{{ trans('cruds.notifications.btn_test_reminder') }}
                </button>
            </div>

        </form>

    </div>{{-- /tab-reminders --}}

    {{-- ================================================================== --}}
    {{-- TAB 5 : Notifications de modification                               --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'notifications' ? 'show active' : '' }}"
         id="tab-notifications" role="tabpanel" aria-labelledby="tab-notifications-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="notifications">

            <div class="card">
                <div class="card-body">

                    <p class="text-muted mb-3">
                        {{ trans('cruds.notifications.tab_legend_notifications') }}
                    </p>

                    <div class="form-group mb-3">
                        <div class="form-check form-switch">
                            <input name="modification_enabled" id="modification_enabled"
                                   type="checkbox" class="form-check-input"
                                   {{ $notif_modification_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="modification_enabled">
                                {{ trans('cruds.notifications.modification_enabled') }}
                            </label>
                        </div>
                    </div>

                    <fieldset id="fieldset-modification"
                              {{ $notif_modification_enabled ? '' : 'disabled' }}
                              class="{{ $notif_modification_enabled ? '' : 'opacity-50' }}">

                        <div class="row">
                        <div class="col-3">
                        <div class="form-group mb-3">
                            <label for="modification_from">{{ trans('cruds.notifications.modification_from') }}</label>
                            <input class="form-control" type="email"
                                   name="modification_from" id="modification_from"
                                   value="{{ $notif_modification_from }}"/>
                        </div>
                        </div>

                        <div class="col-3">
                        <div class="form-group mb-3">
                            <label for="modification_copy_to">{{ trans('cruds.notifications.modification_copy_to') }}</label>
                            <input class="form-control" type="email"
                                   name="modification_copy_to" id="modification_copy_to"
                                   value="{{ $notif_modification_copy_to }}"/>
                        </div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-6">
                        <div class="form-group mb-3">
                            <label for="modification_subject">{{ trans('cruds.notifications.modification_subject') }}</label>
                            <input class="form-control" type="text"
                                   name="modification_subject" id="modification_subject"
                                   value="{{ $notif_modification_subject }}"/>
                        </div>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-9">
                            <div class="form-group mb-3">
                                <label for="modification_body">{{ trans('cruds.notifications.modification_body') }}</label>
                                <textarea class="form-control" name="modification_body" id="modification_body"
                                          rows="13">{{ $notif_modification_body }}</textarea>
                            </div>
                        </div>
                        <div class="col-3">
                            <br><br>
                            <table class="table table-sm table-bordered table-hover" style="font-size: 14px; font-family: var(--font-mono);">
                            <thead class="table-dark">
                              <tr>
                                <th style="width: 42%;">Variable</th>
                                <th>Description</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr><td><code>:user</code></td><td>Nom de l'utilisateur</td></tr>
                              <tr><td><code>:email</code></td><td>Email de l'utilisateur</td></tr>
                              <tr><td><code>:name</code></td><td>Nom de l'objet</td></tr>
                              <tr><td><code>:object</code></td><td>Type d'objet</td></tr>
                              <tr><td><code>:id</code></td><td>Identifiant de l'objet</td></tr>
                              <tr><td><code>:object_url</code></td><td>URL de l'objet</td></tr>
                              <tr><td><code>:object_history_url</code></td><td>URL de l'historique</td></tr>
                              <tr><td><code>:fields</code></td><td>Champs modifiés</td></tr>
                              <tr><td><code>:timestamp</code></td><td>Date et heure de la modification</td></tr>
                            </tbody>
                            </table>
                        </div>
                        </div>

                    </fieldset>

                </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
                <button class="btn btn-secondary" type="submit" name="action" value="test_modification"
                        {{ $notif_modification_enabled ? '' : 'disabled' }}>
                    <i class="fas fa-paper-plane me-1"></i>{{ trans('cruds.notifications.btn_test_modification') }}
                </button>
            </div>

        </form>

    </div>{{-- /tab-notifications --}}

    {{-- ================================================================== --}}
    {{-- TAB 6 : Documents                                                   --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'documents' ? 'show active' : '' }}"
         id="tab-documents" role="tabpanel" aria-labelledby="tab-documents-btn">

        <div class="card">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>{{ trans('cruds.configuration.documents.count') }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>{{ trans('cruds.configuration.documents.total_size') }}</span>
                                <span class="badge bg-secondary rounded-pill">
                                    @php
                                        $bytes = $sum;
                                        $units = ['B','KB','MB','GB','TB'];
                                        $i = 0;
                                        while ($bytes >= 1024 && $i < 4) { $bytes /= 1024; $i++; }
                                        echo round($bytes, 2) . ' ' . $units[$i];
                                    @endphp
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <form action="{{ route('admin.config.documents.check') }}" method="GET">
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-check-circle me-1"></i>{{ trans('global.check') }}
                    </button>
                </form>

                @if (isset($documents) && $documents->isNotEmpty())
                <div class="mt-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-list me-1"></i>{{ trans('cruds.document.list') }}
                    </h6>
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ trans('cruds.configuration.documents.name') }}</th>
                                <th>{{ trans('cruds.configuration.documents.mimetype') }}</th>
                                <th>{{ trans('cruds.configuration.documents.size') }}</th>
                                <th>{{ trans('cruds.configuration.documents.hash') }}</th>
                                <th>{{ trans('cruds.configuration.documents.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $doc)
                            <tr>
                                <td>{{ $doc->id }}</td>
                                <td>
                                    <a href="{{ route('admin.documents.show', $doc->id) }}">
                                        {{ substr($doc->filename, 0, 64) }}
                                    </a>
                                </td>
                                <td>{{ $doc->mimetype }}</td>
                                <td>{{ $doc->humanSize() }}</td>
                                <td><code class="small">{{ $doc->hash }}</code></td>
                                <td>
                                    @php $path = storage_path('docs/') . $doc->id @endphp
                                    @if (!file_exists($path))
                                        <span class="badge bg-warning text-dark">MISSING</span>
                                    @elseif ($doc->hash === hash_file('sha256', $path))
                                        <span class="badge bg-success">OK</span>
                                    @else
                                        <span class="badge bg-danger">HASH FAILS</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>{{-- /tab-documents --}}

    {{-- ================================================================== --}}
    {{-- TAB 7 : Monarc                                                      --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'monarc' ? 'show active' : '' }}"
         id="tab-monarc" role="tabpanel" aria-labelledby="tab-monarc-btn">

        <form method="POST" action="{{ route('admin.config.parameters') }}">
            @method('PUT')
            @csrf
            <input type="hidden" name="active_tab" value="monarc">

            <div class="card">
                <div class="card-body">

                    <div class="form-group mb-3">
                        <label>{{ trans('cruds.configuration.monarc.help') }}</label>
                    </div>

                    <div class="form-group mb-3">
                        <div class="form-check form-switch">
                            <input name="enabled" id="monarc_enabled"
                                   type="checkbox" class="form-check-input"
                                   {{ $monarc_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="monarc_enabled">
                                {{ trans('cruds.configuration.monarc.enabled') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="monarc_url">{{ trans('cruds.configuration.monarc.url') }}</label>
                        <input class="form-control" type="text"
                               name="url" id="monarc_url"
                               value="{{ $monarc_url }}"/>
                    </div>

                    <div class="form-group mb-3">
                        <label for="monarc_uid">{{ trans('cruds.configuration.monarc.uid') }}</label>
                        <input class="form-control" type="text"
                               name="uid" id="monarc_uid"
                               value="{{ $monarc_uid }}"/>
                    </div>

                    <div class="form-group mb-3">
                        <label for="monarc_password">{{ trans('cruds.configuration.monarc.password') }}</label>
                        <input class="form-control" type="password"
                               name="password" id="monarc_password"
                               placeholder="{{ $monarc_has_password ? trans('cruds.configuration.monarc.password_placeholder') : '' }}"
                               autocomplete="new-password"/>
                    </div>

                </div>
            </div>

            <div class="form-group mt-3">
                <button class="btn btn-success" type="submit" name="action" value="save">
                    <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                </button>
            </div>

        </form>

        <form method="POST" action="{{ route('admin.monarc.test-connection') }}" class="mt-2">
            @csrf
            <button class="btn btn-secondary" type="submit">
                <i class="fas fa-plug me-1"></i>{{ trans('cruds.configuration.monarc.test_connection') }}
            </button>
        </form>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">{{ trans('cruds.monarc.sync.reset_button') }}</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        @if (isset($monarc_sync_state['anr_id']))
                            {{ trans('cruds.monarc.sync.anr_linked', ['id' => $monarc_sync_state['anr_id'], 'label' => $monarc_sync_state['anr_label'] ?? '']) }}
                        @else
                            {{ trans('cruds.monarc.sync.anr_none') }}
                        @endif
                    </div>
                    <div class="col-md-4">
                        @if (! empty($monarc_sync_state['last_synced_at']))
                            {{ trans('cruds.monarc.sync.last_sync', ['date' => \Illuminate\Support\Carbon::parse($monarc_sync_state['last_synced_at'])->format('d/m/Y H:i')]) }}
                        @else
                            {{ trans('cruds.monarc.sync.last_sync_none') }}
                        @endif
                    </div>
                    <div class="col-md-4">
                        {{ trans('cruds.monarc.sync.synced_count', ['count' => $monarc_synced_items_count]) }}
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>{{ trans('cruds.configuration.monarc.link_reset_help') }}</label>
                </div>

                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#monarcResetModal"
                        @if (! isset($monarc_sync_state['anr_id'])) disabled @endif>
                    {{ trans('cruds.monarc.sync.reset_button') }}
                </button>
            </div>
        </div>

        <div class="modal fade" id="monarcResetModal" tabindex="-1" aria-labelledby="monarcResetModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="monarcResetModalLabel">{{ trans('cruds.monarc.sync.reset_confirm_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('global.cancel') }}"></button>
                    </div>
                    <div class="modal-body">
                        {{ trans('cruds.monarc.sync.reset_confirm_body') }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('global.cancel') }}</button>
                        <form method="POST" action="{{ route('admin.monarc.sync.reset') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('cruds.monarc.sync.reset_confirm_ok') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /tab-monarc --}}

    {{-- ================================================================== --}}
    {{-- TAB 8 : Périmètres                                                  --}}
    {{-- ================================================================== --}}
    <div class="tab-pane fade {{ $tab === 'perimeters' ? 'show active' : '' }}"
         id="tab-perimeters" role="tabpanel" aria-labelledby="tab-perimeters-btn">

        <div class="card">
            <div class="card-body">
                <div class="form-group mb-3">
                    <label>{{ trans('cruds.configuration.perimeters.help') }}</label>
                </div>

                <form method="POST" action="{{ route('admin.perimeters.activation') }}">
                    @method('PUT')
                    @csrf
                    <div class="form-group mb-3">
                        <div class="form-check form-switch">
                            <input name="perimeters_enabled" id="perimeters_enabled"
                                   type="checkbox" class="form-check-input"
                                   {{ $perimeters_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="perimeters_enabled">
                                {{ trans('cruds.configuration.perimeters.enabled') }}
                            </label>
                        </div>
                    </div>
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-save me-1"></i>{{ trans('global.save') }}
                    </button>
                </form>
            </div>

            <div class="card-body border-top">
                <div class="col-6">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">{{ trans('cruds.perimeter.title') }}</h6>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPerimeterModal">
                            <i class="fas fa-plus me-1"></i>{{ trans('global.add') }}
                        </button>
                    </div>

                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>{{ trans('cruds.perimeter.fields.id') }}</th>
                                <th>{{ trans('cruds.perimeter.fields.name') }}</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($perimeters as $perimeter)
                                <tr>
                                    <td>{{ $perimeter->id }}</td>
                                    <td>{{ $perimeter->name }}</td>
                                    <td nowrap>
                                        <button type="button" class="btn btn-xs btn-info"
                                                data-bs-toggle="modal" data-bs-target="#editPerimeterModal{{ $perimeter->id }}">
                                            {{ trans('global.edit') }}
                                        </button>

                                        @if ($perimeter->id !== \App\Models\Perimeter::DEFAULT_ID)
                                            @if ($perimeter->roles_count > 0)
                                                <button type="button" class="btn btn-xs btn-danger" disabled
                                                        title="{{ trans('cruds.perimeter.errors.in_use') }}">
                                                    {{ trans('global.delete') }}
                                                </button>
                                            @else
                                                <form action="{{ route('admin.perimeters.destroy', $perimeter->id) }}" method="POST"
                                                      data-confirm="{{ trans('global.areYouSure') }}" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger">
                                                        {{ trans('global.delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>

                                {{-- Modale d'édition (nom uniquement — id auto, non modifiable) --}}
                                <div class="modal fade" id="editPerimeterModal{{ $perimeter->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.perimeters.update', $perimeter->id) }}">
                                                @method('PUT')
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ trans('global.edit') }} {{ trans('cruds.perimeter.title_singular') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('global.cancel') }}"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group mb-3">
                                                        <label class="label-required" for="name{{ $perimeter->id }}">
                                                            {{ trans('cruds.perimeter.fields.name') }}
                                                        </label>
                                                        <input class="form-control" type="text" minlength="2" maxlength="32"
                                                               name="name" id="name{{ $perimeter->id }}" value="{{ $perimeter->name }}" required/>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('global.cancel') }}</button>
                                                    <button type="submit" class="btn btn-success">{{ trans('global.save') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modale de création --}}
        <div class="modal fade" id="addPerimeterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.perimeters.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ trans('global.add') }} {{ trans('cruds.perimeter.title_singular') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('global.cancel') }}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="label-required" for="new_perimeter_name">
                                    {{ trans('cruds.perimeter.fields.name') }}
                                </label>
                                <input class="form-control" type="text" minlength="2" maxlength="32"
                                       name="name" id="new_perimeter_name" required/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('global.cancel') }}</button>
                            <button type="submit" class="btn btn-success">{{ trans('global.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /tab-perimeters --}}
</div>{{-- /tab-content --}}

{{-- ─── Persistance de l'onglet lors de la navigation manuelle ─────────── --}}
@section('scripts')
@parent
<script @nonce>
(function () {
    'use strict';
    document.addEventListener('shown.bs.tab', function (e) {
        var id = e.target.getAttribute('data-bs-target').slice(1);
        sessionStorage.setItem('mercator_config_tab', id);
        history.replaceState(null, '', '#' + id);
    });
})();

(function () {
    'use strict';
    function bindNotifToggle(cbId, fsId, tabId, btnVal) {
        var cb  = document.getElementById(cbId);
        var fs  = document.getElementById(fsId);
        var btn = document.querySelector('#' + tabId + ' button[value="' + btnVal + '"]');
        if (!cb || !fs) return;
        cb.addEventListener('change', function () {
            fs.disabled = !cb.checked;
            fs.classList.toggle('opacity-50', !cb.checked);
            // Select2 ne suit pas l'état hérité du fieldset : on désactive le select lui-même.
            fs.querySelectorAll('select').forEach(function (s) { s.disabled = !cb.checked; });
            if (btn) btn.disabled = !cb.checked;
        });
    }
    bindNotifToggle('reminders_enabled',    'fieldset-reminders',    'tab-reminders',    'test_reminder');
    bindNotifToggle('modification_enabled', 'fieldset-modification', 'tab-notifications', 'test_modification');
    bindNotifToggle('audit_logs_enabled',      'fieldset-audit_logs',      'tab-cleanup', null);
    bindNotifToggle('deleted_objects_enabled', 'fieldset-deleted_objects', 'tab-cleanup', null);
})();
</script>
@endsection

@endsection
