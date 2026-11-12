@extends('layouts.admin')

@section('title')
    {{ trans('cruds.relation.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('relation_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.relations.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.relation.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.relation.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.relation.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.responsible') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.importance') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.source') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.destination') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.start_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.end_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.relation.fields.attributes') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.relation.fields.description') }}
                        </th>
                        <th data-column="order_number">
                            {{ trans('cruds.relation.fields.order_number') }}
                        </th>
                        <th data-column="reference">
                            {{ trans('cruds.relation.fields.reference') }}
                        </th>
                        <th data-column="comments">
                            {{ trans('cruds.relation.fields.comments') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($relations as $key => $relation)
                        <tr data-entry-id="{{ $relation->id }}"
                        @if ($relation->active==false)
                                class="table-secondary"
                        @elseif (($relation->end_date!=null)&&Carbon\Carbon::parse($relation->end_date)->isPast())
                                class="table-danger"
                        @elseif (
                            ($relation->description==null)||
                            ($relation->importance==null)||
                            ($relation->type==null))
                            )
                                class="table-warning"
                        @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $relation->perimeter->name }}</td>
                            @endif
            			    <td>

                				<x-show-link :model="$relation" />
                            </td>
                            <td>
                                {{ $relation->type ?? '' }}
                            </td>
                            <td>
                                {{ $relation->responsible ?? '' }}
                            </td>
                            <td data-order="{{ $relation->importance }}">
                              @if ($relation->importance==1)
                                  <span class="veryLowRisk">
                                  {{ trans('cruds.relation.fields.importance_level.low') }}
                              </span>
                              @elseif ($relation->importance==2)
                                  <span class="lowRisk">
                                  {{ trans('cruds.relation.fields.importance_level.medium') }}
                              </span>
                              @elseif ($relation->importance==3)
                                <span class="mediumRisk">
                                  {{ trans('cruds.relation.fields.importance_level.high') }}
                                </span>
                              @elseif ($relation->importance==4)
                                <span class="highRisk">
                                {{ trans('cruds.relation.fields.importance_level.critical') }}
                                </span>
                              @endif
                            </td>
                            <td>
                                @if ($relation->source!=null)
                                <x-show-link :model="$relation->source" />
                                @endif
                            </td>
                            <td>
                                @if ($relation->destination!=null)
                                <x-show-link :model="$relation->destination" />
                                @endif
                            </td>
                            <td>
                                @if($relation->start_date!=null)
                                    {{ $relation->start_date ?? '' }}
                                @endif
                            </td>
                            <td>
                                @if($relation->end_date!=null)
                                    {{ $relation->end_date ?? '' }}
                                @endif
                            </td>
                            <td>
                                @foreach(explode(" ",$relation->attributes) as $attribute)
                                <span class="badge badge-info">{{ $attribute }}</span>
                                @endforeach
                                @if ($relation->active==false)
                                    <span class="badge badge-info">{{ trans('global.old') }}</span>
                                @else
                                    <span class="badge badge-info">{{ trans('global.active') }}</span>
                                @endif
                            </td>
                            <td>
                                {!! $relation->description !!}
                            </td>
                            <td>
                                {{ $relation->order_number }}
                            </td>
                            <td>
                                {{ $relation->reference }}
                            </td>
                            <td>
                                {{ $relation->comments }}
                            </td>
                            <td nowrap>
                                @can('relation_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.relations.show', $relation->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($relation)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.relations.edit', $relation->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('relation_delete')
                                    <form action="{{ route('admin.relations.destroy', $relation->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $relations])
</div>
</div>
@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.relation.title_singular"),
    'URL' => route('admin.relations.massDestroy'),
    'canDelete' => auth()->user()->can('relation_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'description', 'order_number', 'reference', 'comments'],
));
</script>
@endsection
