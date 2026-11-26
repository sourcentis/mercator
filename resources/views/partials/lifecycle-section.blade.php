{{--
    "Lifecycle" section of an asset show page (separate card section, after the
    object's own sections).

    @param \Illuminate\Database\Eloquent\Model $object
    @param list<string> $fields  fields to display, in order
--}}
<!---------------------------------------------------------------------------------------------------->
<div class="card-header">
    {{ trans('cruds.lifecycle.title') }}
</div>
<!---------------------------------------------------------------------------------------------------->
<div class="card-body">
    <table class="table table-bordered table-striped table-report">
        <tbody>
        @include('partials.lifecycle-details', ['object' => $object, 'fields' => $fields])
        </tbody>
    </table>
</div>
