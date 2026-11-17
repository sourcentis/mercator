@php
    $blameable = in_array(\App\Traits\Blameable::class, class_uses_recursive($model), true);
    $showAuthor = $blameable && \Illuminate\Support\Facades\Gate::allows('audit_log_access');
    if ($showAuthor) {
        $model->loadMissing(['creator:id,name,deleted_at', 'updater:id,name,deleted_at']);
    }
@endphp
<div class="card-footer">
    {{ trans('global.created_at') }} {{ $model->created_at ? $model->created_at->format(trans('global.timestamp')) : '' }}
    @if ($showAuthor && $model->creator)
        {{ trans('global.by') }} {{ $model->creator->name }}@if ($model->creator->trashed()) ({{ trans('global.deleted_user') }})@endif
    @endif
    |
    {{ trans('global.updated_at') }} {{ $model->updated_at ? $model->updated_at->format(trans('global.timestamp')) : '' }}
    @if ($showAuthor && $model->updater)
        {{ trans('global.by') }} {{ $model->updater->name }}@if ($model->updater->trashed()) ({{ trans('global.deleted_user') }})@endif
    @endif
</div>
