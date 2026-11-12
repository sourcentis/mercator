@extends('layouts.admin')

@section('title')
    {{ trans('cruds.externalConnectedEntity.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('external_connected_entity_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.external-connected-entities.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.externalConnectedEntity.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.externalConnectedEntity.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.externalConnectedEntity.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.externalConnectedEntity.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.entity') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.contacts') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.network') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.subnetworks') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.src_desc') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.src') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.dest_desc') }}
                        </th>
                        <th>
                            {{ trans('cruds.externalConnectedEntity.fields.dest') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.externalConnectedEntity.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($externalConnectedEntities as $externalConnectedEntity)
                        <tr data-entry-id="{{ $externalConnectedEntity->id }}"
                            @if(
                              ($externalConnectedEntity->type==null)||
                              ($externalConnectedEntity->contact==null)
                              )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $externalConnectedEntity->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$externalConnectedEntity" />
                            </td>
                            <td>
                                {{ $externalConnectedEntity->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $externalConnectedEntity->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                @if ($externalConnectedEntity->entity!=null)
                                    <x-show-link :model="$externalConnectedEntity->entity" />
                                @endif
                            </td>
                            <td>
                                {{ $externalConnectedEntity->contacts }}
                            </td>
                            <td>
                                @if($externalConnectedEntity->network!=null)
                                    <x-show-link :model="$externalConnectedEntity->network" />
                                @endif
                            </td>
                            <td>
                                @foreach($externalConnectedEntity->subnetworks as $subnetwork)
                                    <x-show-link :model="$subnetwork" :label="$subnetwork->name . ($subnetwork->address !== null ? ' (' . $subnetwork->address . ')' : '')" />
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                {{ $externalConnectedEntity->src_desc }}
                            </td>
                            <td>
                                {{ $externalConnectedEntity->src }}
                            </td>
                            <td>
                                {{ $externalConnectedEntity->dest_desc }}
                            </td>
                            <td>
                                {{ $externalConnectedEntity->dest }}
                            </td>
                            <td>
                                {!! $externalConnectedEntity->description !!}
                            </td>
                            <td nowrap>
                                @can('external_connected_entity_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.external-connected-entities.show', $externalConnectedEntity->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($externalConnectedEntity)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.external-connected-entities.edit', $externalConnectedEntity->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('external_connected_entity_delete')
                                    <form action="{{ route('admin.external-connected-entities.destroy', $externalConnectedEntity->id) }}"
                                          method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                          style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger"
                                               value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        
        @include('partials.pagination-footer', ['paginator' => $externalConnectedEntities])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.externalConnectedEntity.title_singular"),
            'URL' => route('admin.external-connected-entities.massDestroy'),
            'canDelete' => auth()->user()->can('external_connected_entity_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes', 'description'],
));
    </script>
@endsection
