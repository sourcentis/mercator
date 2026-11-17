@php
    $actionId     = $action[0];
    $actionLabel  = $action[1];
    $isChecked    = in_array($actionId, old('permissions', [])) || ($role && $role->permissions->contains($actionId));
    $sizeClass    = $disabled ? '' : ' form-switch-lg';
    $normalizedAction = $actionLabel == 'access' ? 'list' : $actionLabel;
    // Permission non détenue par l'utilisateur courant : il ne peut pas l'attribuer
    $locked       = ! $disabled && isset($grantable) && ! in_array((int) $actionId, $grantable, true);
@endphp
<div class="form-switch{{ $sizeClass }}">
    <input class="form-check-input" type="checkbox" name="permissions[]"
        data-check="{{ $permission['name'] }}"
        data-action="{{ $normalizedAction }}"
        id="perm_{{ $actionId }}"
        value="{{ $actionId }}"
        @disabled($disabled || $locked)
        @if($locked) title="{{ trans('cruds.role.permission_not_held') }}" @endif
        @checked($isChecked)>
    <label class="form-check-label" for="for_{{ $actionId }}">{{ $actionLabel=='access' ? 'list' : $actionLabel  }}</label>
</div>
