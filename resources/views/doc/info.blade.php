@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.info') }}
@endsection

@section('content')

<div class="row g-3">

    {{-- ── Mercator ──────────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                <i class="bi bi-credit-card-2-front-fill me-3"></i>Mercator
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:45%">{{ trans('panel.info.version') }}</td>
                        <td><strong id="info-version">{{ $mercatorVersion }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.environment') }}</td>
                        <td id="info-env">{{ $appEnv }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.timezone') }}</td>
                        <td id="info-tz">{{ $appTimezone }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.locale') }}</td>
                        <td id="info-locale">{{ $appLocale }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.date') }}</td>
                        <td id="info-date">{{ now()->format(config('panel.date_format', 'd/m/Y') . ' ' . config('panel.time_format', 'H:i')) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.db_driver') }}</td>
                        <td id="info-db">{{ $dbDriver }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.db_version') }}</td>
                        <td id="info-db-version">{{ $dbVersion ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">PHP / Laravel</td>
                        <td id="info-php">{{ $phpVersion }} / {{ $laravelVersion }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.auth_methods') }}</td>
                        <td id="info-auth">{{ implode(', ', $authMethods) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.drivers') }}</td>
                        <td id="info-drivers">
                            <span class="text-muted small">cache</span> {{ $cacheDriver }}
                            · <span class="text-muted small">queue</span> {{ $queueDriver }}
                            · <span class="text-muted small">mail</span> {{ $mailer }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.memory') }}</td>
                        <td id="info-mem">
                            {{ $memUsedMb }}&thinsp;Mo / {{ $memLimit }}
                            @if ($memFreeMb !== null)
                                <span class="text-muted small">({{ $memFreeMb }}&thinsp;Mo {{ trans('panel.info.memory_free') }})</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Utilisateur ──────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                <i class="bi bi-person-circle me-3"></i>{{ trans('panel.info.user') }}
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:45%">{{ trans('panel.info.login') }}</td>
                        <td id="info-login"><code>{{ $userLogin }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.name') }}</td>
                        <td id="info-name">{{ $userName }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.email') }}</td>
                        <td id="info-email">{{ $userEmail }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.user_language') }}</td>
                        <td id="info-user-lang">{{ $userLanguage ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('cruds.user.fields.granularity') }}</td>
                        <td id="info-granularity">{{ $userGranularity ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.roles') }}</td>
                        <td id="info-roles">
                            @forelse ($roles as $role)
                                <span class="badge bg-secondary me-1">{{ $role }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ trans('panel.info.cartographer') }}</td>
                        <td id="info-carto">
                            @if ($isCartographer)
                                <span class="badge bg-success">{{ trans('global.yes') }}</span>
                                @if (! empty($cartographerTypes))
                                    <span class="text-muted small ms-1">({{ implode(', ', $cartographerTypes) }})</span>
                                @endif
                            @else
                                <span class="badge bg-light text-dark border">{{ trans('global.no') }}</span>
                            @endif
                        </td>
                    </tr>
                    @if ($perimetersEnabled)
                        <tr>
                            <td class="text-muted">{{ trans('panel.info.perimeters') }}</td>
                            <td id="info-perimeters">
                                @forelse ($perimeters as $perimeter)
                                    <span class="badge bg-secondary me-1">{{ $perimeter }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ trans('panel.info.active_perimeter') }}</td>
                            <td id="info-active-perimeter"><span class="badge bg-primary">{{ $activePerimeter }}</span></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ── Périmètres ───────────────────────────────────────────────────── --}}
    @if ($perimetersEnabled)
        <div class="col-12">
            <div class="card">
                <div class="card-header fw-bold">
                    <i class="bi bi-diagram-3-fill me-3"></i>{{ trans('panel.info.perimeters') }}
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">{{ trans('panel.info.perimeters_help') }}</p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" id="info-perimeter-summary">
                            <thead>
                                <tr>
                                    <th>{{ trans('panel.info.perimeter') }}</th>
                                    <th>{{ trans('panel.info.roles') }}</th>
                                    <th class="text-end">{{ trans('panel.info.visible_objects') }}</th>
                                    @foreach ($verbs as $verb)
                                        <th class="text-center" title="{{ trans('panel.info.types_with_right') }}">
                                            {{ trans('panel.info.verb_' . $verb) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perimeterSummary as $row)
                                    <tr @class(['table-primary' => $row['active']])>
                                        <td>
                                            <strong>{{ $row['name'] }}</strong>
                                            @if ($row['active'])
                                                <span class="badge bg-primary ms-1">{{ trans('panel.info.active') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @forelse ($row['roles'] as $role)
                                                <span class="badge bg-secondary me-1">{{ $role }}</span>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td class="text-end">{{ $row['objects'] }}</td>
                                        @foreach ($verbs as $verb)
                                            <td class="text-center">
                                                <span @class(['badge', 'bg-success' => $row['types'][$verb] > 0, 'bg-light text-muted border' => $row['types'][$verb] === 0])>
                                                    {{ $row['types'][$verb] }}
                                                </span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ 3 + count($verbs) }}" class="text-muted">{{ trans('panel.info.no_perimeter') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Objets cartographiés ─────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-bold d-flex align-items-center">
                <span><i class="bi bi-map-fill me-3"></i>{{ trans('panel.info.mapped_objects') }}</span>
                <span class="badge bg-primary ms-2" id="info-objects-total">{{ $objectTotal }}</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">{{ trans('panel.info.mapped_objects_help') }}</p>
                <div class="row row-cols-2 row-cols-md-4 row-cols-xl-8 g-2 mb-3" id="info-objects">
                    @foreach ($objectGroups as $group)
                        <div class="col">
                            <a href="#info-group-{{ $group['key'] }}" class="text-decoration-none">
                                <div class="border rounded p-2 text-center h-100">
                                    <div class="fs-4 fw-bold">{{ $group['count'] }}</div>
                                    <div class="small text-muted">{{ $group['title'] }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <button class="btn btn-outline-secondary btn-sm" type="button"
                        data-bs-toggle="collapse" data-bs-target="#info-rights" aria-expanded="false" aria-controls="info-rights">
                    <i class="bi bi-shield-lock me-1"></i>{{ trans('panel.info.rights_detail') }}
                </button>
                @if ($isAdmin)
                    <span class="text-muted small ms-2">{{ trans('panel.info.admin_all_rights') }}</span>
                @endif

                <div class="collapse mt-3" id="info-rights">
                    <p class="text-muted small mb-2">
                        <i class="bi bi-eye"></i> {{ trans('panel.info.verb_access') }}
                        · <i class="bi bi-plus-circle"></i> {{ trans('panel.info.verb_create') }}
                        · <i class="bi bi-pencil"></i> {{ trans('panel.info.verb_edit') }}
                        · <i class="bi bi-trash"></i> {{ trans('panel.info.verb_delete') }}
                        @if (session('is_cartographer'))
                            <br>{{ trans('panel.info.cartographer_help') }}
                        @endif
                    </p>
                    @php($verbIcons = ['access' => 'bi-eye', 'create' => 'bi-plus-circle', 'edit' => 'bi-pencil', 'delete' => 'bi-trash'])
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="sticky-top bg-body">
                                <tr>
                                    <th>{{ trans('panel.info.object_type') }}</th>
                                    <th class="text-end">{{ trans('panel.info.visible_objects') }}</th>
                                    @foreach ($rightColumns as $column)
                                        <th class="text-center">{{ $column['name'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($objectGroups as $group)
                                    <tr class="table-secondary" id="info-group-{{ $group['key'] }}">
                                        <th>{{ $group['title'] }}</th>
                                        <th class="text-end">{{ $group['count'] }}</th>
                                        <th colspan="{{ count($rightColumns) }}"></th>
                                    </tr>
                                    @foreach ($group['models'] as $model)
                                        <tr>
                                            <td class="ps-3">
                                                @if ($model['url'])
                                                    <a href="{{ $model['url'] }}">{{ $model['title'] }}</a>
                                                @else
                                                    {{ $model['title'] }}
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $model['count'] }}</td>
                                            @foreach ($rightColumns as $columnId => $column)
                                                <td class="text-center text-nowrap">
                                                    @foreach ($verbs as $verb)
                                                        @if ($isAdmin || $model['rights'][$columnId][$verb])
                                                            <i class="bi {{ $verbIcons[$verb] }} text-success mx-1" title="{{ trans('panel.info.verb_' . $verb) }}"></i>
                                                        @else
                                                            <i class="bi {{ $verbIcons[$verb] }} text-body-tertiary opacity-25 mx-1" title="{{ trans('panel.info.verb_' . $verb) }}"></i>
                                                        @endif
                                                    @endforeach
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Bouton copier pour helpdesk ──────────────────────────────────────── --}}
<div class="mt-3">
    <button class="btn btn-outline-secondary btn-sm" id="btn-copy-info">
        <i class="bi bi-clipboard me-1"></i>{{ trans('panel.info.copy_for_helpdesk') }}
    </button>
    <span id="copy-feedback" class="text-success ms-2 small d-none">
        <i class="bi bi-check-circle"></i> {{ trans('panel.info.copied') }}
    </span>
</div>

@endsection

@section('scripts')
@parent
<script @nonce>
document.getElementById('btn-copy-info').addEventListener('click', function () {
    function val(id) {
        var el = document.getElementById(id);
        return el ? el.innerText.trim() : '';
    }
    var lines = [
        'Mercator : '      + val('info-version'),
        'Env : '           + val('info-env'),
        'Fuseau : '        + val('info-tz'),
        'Langue : '        + val('info-locale'),
        'Date : '          + val('info-date'),
        'DB : '            + val('info-db'),
        'DB version : '    + val('info-db-version'),
        'PHP / Laravel : ' + val('info-php'),
        'Auth : '          + val('info-auth'),
        'Drivers : '       + val('info-drivers'),
        'Mémoire : '       + val('info-mem'),
        '---',
        'Login : '         + val('info-login'),
        'Nom : '           + val('info-name'),
        'Email : '         + val('info-email'),
        'Langue : '        + val('info-user-lang'),
        'Granularité : '   + val('info-granularity'),
        'Rôles : '         + val('info-roles'),
        'Cartographe : '   + val('info-carto'),
    ];
    if (document.getElementById('info-perimeters')) {
        lines.push('Périmètres : ' + val('info-perimeters'));
        lines.push('Périmètre actif : ' + val('info-active-perimeter'));
    }
    lines.push('Objets visibles : ' + val('info-objects-total'));
    var summary = document.getElementById('info-perimeter-summary');
    if (summary) {
        lines.push('---');
        summary.querySelectorAll('tr').forEach(function (tr) {
            lines.push(Array.from(tr.cells).map(function (td) {
                return td.innerText.trim().replace(/\s+/g, ' ');
            }).join(' | '));
        });
    }
    navigator.clipboard.writeText(lines.join('\n')).then(function () {
        var fb = document.getElementById('copy-feedback');
        fb.classList.remove('d-none');
        setTimeout(function () { fb.classList.add('d-none'); }, 2500);
    });
});
</script>
@endsection
