@extends('layouts.admin')

@section('title')
    {{ $applicationService->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.application-services.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$applicationService->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($applicationService)
        <a class="btn btn-info" href="{{ route('admin.application-services.edit', $applicationService->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('application_service_delete')
        <form action="{{ route('admin.application-services.destroy', $applicationService->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.applicationService.title') }}
    </div>

    <div class="card-body">
        @include('admin.applicationServices._details', [
            'applicationService' => $applicationService,
            'withLink' => false,
        ])
    </div>

    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans('cruds.applicationFlow.title') }}
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
            <tr>
                <th width="20%">
                    {{ trans('cruds.applicationFlow.fields.name') }}
                </th>
                <th width="10%">
                    {{ trans('cruds.applicationFlow.fields.type') }}
                </th>
                <th width="10%">
                    {{ trans('cruds.applicationFlow.fields.attributes') }}
                </th>
                <th width="20%">
                    {{ trans('cruds.applicationFlow.fields.module_source') }}
                </th>
                <th width="20%">
                    {{ trans('cruds.applicationFlow.fields.module_dest') }}
                </th>
                <th width="20%">
                    {{ trans('cruds.applicationFlow.fields.information') }}
                </th>
            </tr>
            @foreach($applicationService->serviceSourceFluxes->union($applicationService->serviceDestFluxes) as $flow)
            <tr>
                <td>
                    @canShow($flow)<a href="{{ route('admin.application-flows.show', $flow->id) }}">{{ $flow->name }}</a>@elsecanShow{{ $flow->name }}@endcanShow
                </td>
                <td>
                   {{ $flow->type }}
                </td>
                <td>
                    @foreach(explode(" ",$flow->attributes) as $attribute)
                        <span class="badge badge-info">{{ $attribute }}</span>
                    @endforeach
                </td>
                <td>
                    @if ($flow->applicationSource!=null)
                        @canShow($flow->applicationSource)<a href="{{ route('admin.applications.show',$flow->applicationSource->id) }}">{{ $flow->applicationSource->name }}</a>@elsecanShow{{ $flow->applicationSource->name }}@endcanShow
                    @endif
                    @if($flow->serviceSource!=null)
                        @canShow($flow->serviceSource)<a href="{{ route('admin.application-services.show', $flow->serviceSource->id) }}">{{ $flow->serviceSource->name }}</a>@elsecanShow{{ $flow->serviceSource->name }}@endcanShow
                    @endif
                    @if ($flow->moduleSource!=null)
                        @canShow($flow->moduleSource)<a href="{{ route('admin.application-modules.show', $flow->moduleSource->id) }}">{{ $flow->moduleSource->name }}</a>@elsecanShow{{ $flow->moduleSource->name }}@endcanShow
                    @endif
                    @if ($flow->databaseSource!=null)
                        @canShow($flow->databaseSource)<a href="{{ route('admin.databases.show',$flow->databaseSource->id) }}">{{ $flow->databaseSource->name }}</a>@elsecanShow{{ $flow->databaseSource->name }}@endcanShow
                    @endif
                </td>
                <td>
                    @if ($flow->applicationDest!=null)
                        @canShow($flow->applicationDest)<a href="{{ route('admin.applications.show',$flow->applicationDest->id) }}">{{ $flow->applicationDest->name }}</a>@elsecanShow{{ $flow->applicationDest->name }}@endcanShow
                    @endif
                    @if ($flow->serviceDest!=null)
                        @canShow($flow->serviceDest)<a href="{{ route('admin.application-services.show', $flow->serviceDest->id) }}">{{ $flow->serviceDest->name }}</a>@elsecanShow{{ $flow->serviceDest->name }}@endcanShow
                    @endif
                    @if ($flow->moduleDest!=null)
                        @canShow($flow->moduleDest)<a href="{{ route('admin.application-modules.show', $flow->moduleDest->id) }}">{{ $flow->moduleDest->name }}</a>@elsecanShow{{ $flow->moduleDest->name }}@endcanShow
                    @endif
                    @if ($flow->databaseDest!=null)
                        @canShow($flow->databaseDest)<a href="{{ route('admin.databases.show',$flow->databaseDest->id) }}">{{ $flow->databaseDest->name }}</a>@elsecanShow{{ $flow->databaseDest->name }}@endcanShow
                    @endif
                </td>
                <td>
                    @foreach($flow->informations as $info)
                        @canShow($info)<a href="{{ route('admin.information.show',$info->id) }}">{{$info->name}}</a>@elsecanShow{{$info->name}}@endcanShow
                        @if (!$loop->last) , @endif
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>


    @include('admin._footer', ['model' => $applicationService])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.application-services.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
