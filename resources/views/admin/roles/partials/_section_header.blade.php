@php
    $hdrActionId = $permission['actions'][0][0];
    $hdrChecked  = in_array($hdrActionId, old('permissions', [])) || ($role && $role->permissions->contains($hdrActionId));
    $hdrSize     = $disabled ? '' : ' form-switch-lg';
    $hdrLocked   = ! $disabled && isset($grantable) && ! in_array((int) $hdrActionId, $grantable, true);
@endphp
<div class="card-header d-flex justify-content-between align-items-center">
    <div class="form-switch{{ $hdrSize }}">
        <input class="form-check-input" type="checkbox" name="permissions[]"
            data-check="{{ $permission['name'] }}"
            id="perm_{{ $hdrActionId }}"
            value="{{ $hdrActionId }}"
            @disabled($disabled || $hdrLocked)
            @if($hdrLocked) title="{{ trans('cruds.role.permission_not_held') }}" @endif
            @checked($hdrChecked)>
        <label class="form-check-label"><b>{{ $label }}</b></label>
    </div>
    @unless($disabled)
        <div class="btn-group btn-group-sm perm-toolbar" role="group">
            <button type="button" class="btn btn-outline-secondary perm-toolbar-btn" data-scope="all">All</button>
            <button type="button" class="btn btn-outline-secondary perm-toolbar-btn" data-scope="list">list</button>
            <button type="button" class="btn btn-outline-secondary perm-toolbar-btn" data-scope="show">show</button>
            <button type="button" class="btn btn-outline-secondary perm-toolbar-btn" data-scope="create">create</button>
            <button type="button" class="btn btn-outline-secondary perm-toolbar-btn" data-scope="edit">edit</button>
            <button type="button" class="btn btn-outline-secondary perm-toolbar-btn" data-scope="delete">delete</button>
        </div>
    @endunless
</div>
