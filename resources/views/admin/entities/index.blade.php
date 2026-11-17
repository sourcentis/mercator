@extends('layouts.admin')

@section('title')
    {{ trans('cruds.entity.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('entity_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.entities.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.entity.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.entity.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.entity.fields.name') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.entity.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.entity.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.entity.fields.contact_point') }}
                        </th>
                        <th>
                            {{ trans('cruds.entity.fields.relations') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.entity.fields.description') }}
                        </th>
                        <th data-column="security_level">
                            {{ trans('cruds.entity.fields.security_level') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entities as $key => $entity)
                        <tr data-entry-id="{{ $entity->id }}"
                            @if(($entity->description==null)||
                                ($entity->contact_point==null)||
                                ($entity->security_level==null)||
                                ($entity->contact_point==null)||
                                ($entity->processes->count()==0)
                                )
                                class="table-warning"
                            @endif
                          >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $entity->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$entity" />
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $entity->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $entity->type }}
                            </td>
                            <td>
                                {!! $entity->contact_point  ?? '' !!}
                            </td>
                            <td>
                                @foreach ($entity->destinationRelations as $relation)
                                    <x-show-link :model="$relation" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                {!! $entity->description !!}
                            </td>
                            <td>
                                {!! $entity->security_level !!}
                            </td>
                            <td nowrap>
                                @can('entity_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.entities.show', $entity->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($entity)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.entities.edit', $entity->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('entity_delete')
                                    <form action="{{ route('admin.entities.destroy', $entity->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $entities])
</div>
</div>
@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.entity.title_singular"),
    'URL' => route('admin.entities.massDestroy'),
    'canDelete' => auth()->user()->can('entity_delete'),
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes', 'description', 'security_level'],
    )
);
</script>
@endsection
