@extends('layouts.admin')

@section('title')
    {{ $externalConnectedEntity->name }}
@endsection

@section('content')
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.external-connected-entities.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$externalConnectedEntity->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($externalConnectedEntity)
            <a class="btn btn-info"
               href="{{ route('admin.external-connected-entities.edit', $externalConnectedEntity->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('entity_delete')
            <form action="{{ route('admin.external-connected-entities.destroy', $externalConnectedEntity->id) }}"
                  method="POST" data-confirm="{{ trans('global.areYouSure') }}"
                  style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan

    </div>
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.externalConnectedEntity.title') }}
        </div>
        <div class="card-body">
            @include('admin.externalConnectedEntities._details', [
                'externalConnectedEntity' => $externalConnectedEntity,
                'withLink' => false,
            ])
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            <div class="row">
                <div class="col-sm-6">
                    {{ trans('cruds.externalConnectedEntity.title_source') }}
                </div>
                <div class="col-sm-6">
                    {{ trans('cruds.externalConnectedEntity.title_dest') }}
                </div>
            </div>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.externalConnectedEntity.fields.entity') }}
                    </th>
                    <td width="40%">
                        @if ($externalConnectedEntity->entity!=null)
                            @canShow($externalConnectedEntity->entity)
                                <a href="{{ route('admin.entities.show', $externalConnectedEntity->entity->id) }}">
                                    {{ $externalConnectedEntity->entity->name }}
                                </a>
                            @elsecanShow
                                {{ $externalConnectedEntity->entity->name }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.externalConnectedEntity.fields.network') }}
                    </th>
                    <td width="40%">
                        @if ($externalConnectedEntity->network!=null)
                            @canShow($externalConnectedEntity->network)
                                <a href="{{ route('admin.networks.show', $externalConnectedEntity->network->id) }}">
                                    {{ $externalConnectedEntity->network->name }}
                                </a>
                            @elsecanShow
                                {{ $externalConnectedEntity->network->name }}
                            @endcanShow
                        @endif
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.externalConnectedEntity.fields.contacts') }}
                    </th>
                    <td width="40%">
                        {{ $externalConnectedEntity->contacts }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.externalConnectedEntity.fields.subnetworks') }}
                    </th>
                    <td width="40%">
                        @foreach($externalConnectedEntity->subnetworks as $subnetwork)
                            @canShow($subnetwork)
                                <a href="{{ route('admin.subnetworks.show', $subnetwork->id) }}">{{ $subnetwork->name }} {{ $subnetwork->address!==null ? ('(' . $subnetwork->address . ')') : "" }}</a>
                            @elsecanShow
                                {{ $subnetwork->name }} {{ $subnetwork->address!==null ? ('(' . $subnetwork->address . ')') : "" }}
                            @endcanShow
                            @if(!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.externalConnectedEntity.fields.src_desc') }}
                    </th>
                    <td>
                        {{ $externalConnectedEntity->src_desc }}
                    </td>
                    <th>
                        {{ trans('cruds.externalConnectedEntity.fields.dest_desc') }}
                    </th>
                    <td>
                        {{ $externalConnectedEntity->dest_desc }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.externalConnectedEntity.fields.src') }}
                    </th>
                    <td>
                        {{ $externalConnectedEntity->src }}
                    </td>
                    <th>
                        {{ trans('cruds.externalConnectedEntity.fields.dest') }}
                    </th>
                    <td>
                        {{ $externalConnectedEntity->dest }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-header">
            {{ trans('cruds.externalConnectedEntity.title_security') }}
        </div>
        <!------------------------------------------------------------------------------------------------------------->
        <div class="card-body">
            <table class="table table-bordered table-striped table-report">
                <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.externalConnectedEntity.fields.security') }}
                    </th>
                    <td>
                        {!! $externalConnectedEntity->security !!}
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.externalConnectedEntity.fields.documents') }}
                    </th>
                    <td>
                        @foreach($externalConnectedEntity->documents as $document)
                            <a href="{{ route('admin.documents.show', $document->id) }}">{{ $document->filename }}</a>
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @include('admin._footer', ['model' => $externalConnectedEntity])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.external-connected-entities.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
@endsection
