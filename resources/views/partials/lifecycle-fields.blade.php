{{--
    Lifecycle block (commissioning, warranty, end of life, hardware identity)
    shared by asset create/edit forms.

    @param \Illuminate\Database\Eloquent\Model|null $object  edited object (null on create / clone)
    @param list<string> $fields                              fields to display, in order
--}}
@php
    $lifecycleObject = $object ?? null;
    $lifecycleDateFields = ['install_date', 'warranty_start_date', 'warranty_end_date', 'end_of_life_date', 'purchase_date'];
@endphp
<!---------------------------------------------------------------------------------------------------->
<div class="card-header">
    {{ trans('cruds.lifecycle.title') }}
</div>
<!---------------------------------------------------------------------------------------------------->
<div class="card-body">
    <div class="row">
        @foreach($fields as $field)
            @php
                $current = $lifecycleObject?->{$field};
                if ($current instanceof \DateTimeInterface) {
                    $current = $current->format('Y-m-d');
                } elseif (in_array($field, $lifecycleDateFields, true) && is_string($current)) {
                    $current = substr($current, 0, 10);
                }
            @endphp
            <div class="col-md-4">
                <div class="form-group">
                    <label for="{{ $field }}">{{ trans('cruds.lifecycle.fields.'.$field) }}</label>
                    @if(in_array($field, $lifecycleDateFields, true))
                        <input class="form-control date {{ $errors->has($field) ? 'is-invalid' : '' }}" type="date"
                               name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $current) }}">
                    @elseif($field === 'fin_value')
                        <input class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}" type="number"
                               step="0.01" min="0" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $current) }}">
                    @else
                        <input class="form-control {{ $errors->has($field) ? 'is-invalid' : '' }}" type="text"
                               maxlength="255" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $current) }}">
                    @endif
                    @if($errors->has($field))
                        <div class="invalid-feedback">{{ $errors->first($field) }}</div>
                    @endif
                    <span class="help-block">{{ trans('cruds.lifecycle.fields.'.$field.'_helper') }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
