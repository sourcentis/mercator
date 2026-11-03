@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.patching') }}
@endsection

@section('content')

<form method="POST" action='{{ route("admin.patching.application") }}' enctype="multipart/form-data">
    @method('PUT')
    @csrf
    <input type='hidden' name="id" value='{{$application->id}}'/>

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
                            {{ trans('cruds.application.fields.name') }}
                        </th>
                        <td>
                            {{ $application->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.application.fields.description') }}
                        </th>
                        <td>
                            {!! $application->description !!}
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
                                <option {{ in_array($a, old('attributes', explode(' ', $application->attributes ?? '')), true) ? 'selected' : '' }}>{{$a}}</option>
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
                            <option value="1" {{ ($application->patching_frequency===1) ? "selected" : ""}}>1 month</option>
                            <option value="2" {{ ($application->patching_frequency===2) ? "selected" : ""}}>2 months</option>
                            <option value="3" {{ ($application->patching_frequency===3) ? "selected" : ""}}>3 months</option>
                            <option value="4" {{ ($application->patching_frequency===4) ? "selected" : ""}}>4 months</option>
                            <option value="6" {{ ($application->patching_frequency===6) ? "selected" : ""}}>6 months</option>
                            <option value="12" {{ ($application->patching_frequency===12) ? "selected" : ""}}>12 months</option>
                        </select>
                        <span class="help-block">Fréquence de mise à jour</span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="next_update">{{ trans('cruds.logicalServer.fields.next_update') }}</label>
                        <input class="form-control date" type="date" name="next_update"  id="next_update" value="{{ old('next_update', $application->next_update) }}">
                        <span class="help-block">{{ trans('cruds.logicalServer.fields.next_update_helper') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!------------------------------------------------------------------------------------------------------------->
        {{-- Common Platform Enumeration --}}
        <!------------------------------------------------------------------------------------------------------------->
        @include('partials.cpe-selector', [
            'part'    => 'a',
            'vendor'  => $application->vendor,
            'product' => $application->product,
            'version' => $application->version,
        ])

</div>
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.patching.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
    <button class="btn btn-danger" type="submit">
        {{ trans('global.save') }}
    </button>
</div>
</form>

{{-- Modal Événements --}}
<div class="modal fade" id="eventsModal" tabindex="-1" aria-labelledby="eventsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventsModalLabel">Évènements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="eventsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="appToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="appToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ----------------------------------------------------------------
    // Toast Bootstrap
    // ----------------------------------------------------------------
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('appToast');
        toastEl.classList.remove('bg-success', 'bg-danger');
        toastEl.classList.add('bg-' + type);
        document.getElementById('appToastBody').textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 }).show();
    }

    // ----------------------------------------------------------------
    // Événements — construction HTML
    // ----------------------------------------------------------------
    let currentEvents = @json($application->events);

    function generateEventsList(events) {
        if (!events.length) {
            return '<p class="text-muted">Aucun évènement.</p>';
        }
        let ret = '<ul class="list-unstyled">';
        events.forEach(function (event) {
            ret += `
                <li data-id="${event.id}" class="mb-3 border-bottom pb-2 position-relative">
                    <button class="delete_event btn btn-sm btn-outline-danger position-absolute end-0 top-0" type="button">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    <div class="pe-5">${event.message}</div>
                    <small class="text-muted">
                        Date : ${moment(event.created_at).format('DD-MM-YYYY')}
                        | Utilisateur : ${event.user.name}
                    </small>
                </li>`;
        });
        ret += '</ul>';
        return ret;
    }

    function bindDeleteEvents() {
        document.querySelectorAll('#eventsModalBody .delete_event').forEach(btn => {
            btn.addEventListener('click', function () {
                const li = this.closest('li');
                const eventId = li.getAttribute('data-id');
                if (!eventId) return;

                $.ajax({
                    url: '/admin/application-events/' + eventId,
                    type: 'DELETE',
                    data: {
                        application_id: {{ $application->id }},
                        _token: "{{ csrf_token() }}"
                    },
                    success: (data) => {
                        currentEvents = data.events;
                        li.remove();
                        showToast('Évènement supprimé !', 'success');
                    },
                    error: () => {
                        showToast('Une erreur est survenue', 'danger');
                    }
                });
            });
        });
    }

    $('.events_list_button').on('click', function (e) {
        e.preventDefault();
        const modalBody = document.getElementById('eventsModalBody');
        modalBody.innerHTML = generateEventsList(currentEvents);
        bindDeleteEvents();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('eventsModal')).show();
    });

    // ----------------------------------------------------------------
    // Événements — ajout
    // ----------------------------------------------------------------
    $('#addEventBtn').on('click', function (e) {
        e.preventDefault();
        const app_id  = {{ $application->id }};
        const user_id = {{ auth()->id() }};
        const message = $('#eventMessage').val();

        if (message !== '' && user_id && app_id) {
            $.post("{{ route('admin.application-events.store') }}", {
                application_id: app_id,
                user_id: user_id,
                message: message,
                _token: "{{ csrf_token() }}"
            }, 'json')
            .done((data) => {
                currentEvents = data.events;
                showToast('Évènement ajouté !', 'success');
                $('#eventMessage').val('');
            })
            .fail(() => {
                showToast('Une erreur est survenue', 'danger');
            });
        }
    });

    // ----------------------------------------------------------------
    // Calcul next_update
    // ----------------------------------------------------------------
    const update_date        = document.getElementById('update_date');
    const patching_frequency = document.getElementById('patching_frequency');
    const next_update        = document.getElementById('next_update');

    update_date.addEventListener('change', function () {
        if (!patching_frequency.value) {
            next_update.value = '';
        } else {
            next_update.value = moment(update_date.value, 'YYYY-MM-DD')
                .add(patching_frequency.value, 'months')
                .format('YYYY-MM-DD');
        }
    });

    $('#clock').on('click', function (e) {
        e.preventDefault();
        if (patching_frequency.value) {
            const today = moment();
            update_date.value = today.format('YYYY-MM-DD');
            next_update.value = today.add(patching_frequency.value, 'months').format('YYYY-MM-DD');
        }
    });

    $('#patching_frequency').on('select2:select', function () {
        if (!$('#patching_frequency').val()) {
            $('#next_update').val('');
        } else {
            next_update.value = moment(update_date.value, 'YYYY-MM-DD')
                .add(patching_frequency.value, 'months')
                .format('YYYY-MM-DD');
        }
    });
});

// ----------------------------------------------------------------
// Recherche CVE
// ----------------------------------------------------------------
function searchCVE() {
    const vendor  = document.getElementById('vendor-selector').value;
    const product = document.getElementById('product-selector').value;
    const version = document.getElementById('version-selector').value;
    const cpe     = 'cpe:2.3:a:' + vendor + ':' + product + ':' + version;

    const form  = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('admin.cve.search', '__CPE__') }}".replace('__CPE__', encodeURIComponent(cpe));

    const token   = document.createElement('input');
    token.type    = 'hidden';
    token.name    = '_token';
    token.value   = "{{ csrf_token() }}";
    form.appendChild(token);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
