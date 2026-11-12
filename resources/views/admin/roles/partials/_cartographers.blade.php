{{--
  Partial : section cartographes d'un rôle
  Variables attendues :
    $role                 – instance Role
    $cartographers        – Collection de Cartographer pour ce rôle
    $cartographiableModels – tableau [class => label]
    $readonly             – bool (true dans show, false dans edit)
--}}
@php($readonly ??= false)

<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-pin-map-fill me-2"></i>Accès cartographe — objets assignés à ce rôle</span>
        <small class="text-muted">{{ $cartographers->count() }} entrée(s)</small>
    </div>

    <div class="card-body p-0">
        @if($cartographers->isEmpty())
            <p class="text-muted p-3 mb-0">Aucun objet assigné à ce rôle.</p>
        @else
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Objet</th>
                        @if(!$readonly) <th></th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartographers as $entry)
                    <tr>
                        <td>{{ $cartographiableModels[$entry->cartographiable_type] ?? $entry->cartographiable_type }}</td>
                        <td>
                            @if($entry->cartographiable)
                                {{ $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')' }}
                            @else
                                <em class="text-muted">(objet supprimé)</em>
                            @endif
                        </td>
                        @if(!$readonly)
                        <td class="text-end" style="width:90px">
                            @can('cartographer_delete')
                            <form action="{{ route('admin.cartographers.destroy', $entry->id) }}" method="POST"
                                  data-confirm="{{ trans('global.areYouSure') }}"
                                  style="display:inline-block">
                                <input type="hidden" name="_method" value="DELETE">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-danger">
                                    {{ trans('global.delete') }}
                                </button>
                            </form>
                            @endcan
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if(!$readonly)
    @can('cartographer_create')
    <div class="card-footer">
        <p class="mb-2 fw-semibold">Ajouter un objet</p>
        <form method="POST" action="{{ route('admin.cartographers.store') }}" id="cart-add-form">
            @csrf
            <input type="hidden" name="role_id" value="{{ $role->id }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Type</label>
                    <select id="cart-type-select" name="cartographiable_type"
                            class="form-select form-select-sm select2-cart" required>
                        <option value="">— Choisir un type —</option>
                        @foreach($cartographiableModels as $class => $label)
                            <option value="{{ $class }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label mb-1">Objet</label>
                    <select id="cart-object-select" name="cartographiable_id"
                            class="form-select form-select-sm select2-cart" required disabled>
                        <option value="">— Choisir d'abord un type —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus-lg"></i> Ajouter
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endcan
    @endif
</div>
