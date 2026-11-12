@php
    // Fiche d'un objet supprimé : objet transmis par le contrôleur (BPMN), sinon résolu par la
    // liaison de route (voir TrashModeMiddleware).
    $trashObject = ($trashObject ?? null) ?: collect(request()->route()?->parameters() ?? [])
        ->first(fn ($p) => $p instanceof \Illuminate\Database\Eloquent\Model
            && method_exists($p, 'trashed') && $p->trashed());
@endphp
<div class="alert alert-danger d-flex align-items-center flex-wrap gap-2 mb-3" role="status" id="trash-banner">
    <i class="bi bi-trash3-fill fs-5"></i>
    <span class="flex-grow-1">
        <strong>{{ trans('cruds.trash.mode') }}</strong> —
        {{ trans('cruds.trash.banner') }}
    </span>
    <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.trash.index') }}">
        {{ trans('cruds.trash.overview') }}
    </a>
</div>
@if ($trashObject)
    @php
        $trashClass = get_class($trashObject);
        $trashKey = \App\Support\TrashMode::key($trashClass);
        $trashCanRestore = \App\Support\TrashMode::modelForKey($trashKey) !== null
            && Gate::allows(\App\Support\TrashMode::permissionPrefix($trashClass).'_delete');
        $trashDeleter = method_exists($trashObject, 'deleter') ? $trashObject->deleter?->name : null;
    @endphp
    <div class="alert alert-warning d-flex align-items-center flex-wrap gap-2 mb-3" id="trash-object">
        <span class="flex-grow-1">
            {{ trans('cruds.trash.object_deleted', ['date' => $trashObject->deleted_at?->format('d/m/Y H:i')]) }}
            @if ($trashDeleter)
                {{ trans('cruds.trash.deleted_by', ['user' => $trashDeleter]) }}
            @endif
        </span>
        @if ($trashCanRestore)
            <form method="POST" action="{{ route('admin.trash.restore', [$trashKey, $trashObject->getKey()]) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ trans('cruds.trash.restore') }}
                </button>
            </form>
        @endif
    </div>
@endif
