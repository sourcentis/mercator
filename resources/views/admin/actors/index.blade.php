@extends('layouts.admin')

@section('title')
    {{ trans('cruds.actor.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('actor_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.actors.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.actor.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.actor.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.actor.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.actor.fields.contact') }}
                        </th>
                        <th>
                            {{ trans('cruds.actor.fields.nature') }}
                        </th>
                        <th>
                            {{ trans('cruds.actor.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.actor.fields.attributes') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actors as $key => $actor)
                        <tr data-entry-id="{{ $actor->id }}"
                            @if(($actor->contact==null)||
                                ($actor->nature==null)||
                                ($actor->type==null)
                                )
                          class="table-warning"
                            @endif
                          >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $actor->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$actor" />
                            </td>
                            <td>
                                {{ $actor->contact ?? '' }}
                            </td>
                            <td>
                                {{ $actor->nature ?? '' }}
                            </td>
                            <td>
                                {{ $actor->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $actor->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td nowrap>
                                @can('actor_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.actors.show', $actor->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($actor)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.actors.edit', $actor->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('actor_delete')
                                    <form action="{{ route('admin.actors.destroy', $actor->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $actors])
</div>
</div>
@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.actor.title_singular"),
    'URL' => route('admin.actors.massDestroy'),
    'canDelete' => auth()->user()->can('actor_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes'],
));
</script>
@endsection
