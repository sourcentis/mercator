@extends('layouts.admin')

@section('title')
    {{ $workstation->name }}
@endsection

@section('content')

<div class="form-group">
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.workstations.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$workstation->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($workstation)
            <a class="btn btn-info" href="{{ route('admin.workstations.edit', $workstation->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('workstation_create')
            <a class="btn btn-warning" href="{{ route('admin.workstations.clone', $workstation->id) }}">
                {{ trans('global.clone') }}
            </a>
        @endcan

        @can('workstation_delete')
            <form action="{{ route('admin.workstations.destroy', $workstation->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>

<div class="card">
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.workstation.title') }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        @include('admin.workstations._details', [
            'workstation' => $workstation,
            'withLink' => false,
        ])
    </div>

    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        Model / Configuration
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">

        <table class="table table-bordered table-striped table-report">
            <tbody>

               <tr>
                    <th width="10%">{{ trans('cruds.workstation.fields.manufacturer') }}</th>
                    <td width="20%">{{ $workstation->manufacturer }}</td>
                    <th width="10%">{{ trans('cruds.workstation.fields.model') }}</th>
                    <td width="20%">{{ $workstation->model }}</td>
                    <th width="10%">{{ trans('cruds.workstation.fields.serial_number') }}</th>
                    <td width="20%" colspan="2">{{ $workstation->serial_number }}</td>
               </tr>
               <tr>
                    <th>{{ trans('cruds.workstation.fields.cpu') }}</th>
                    <td>{{ $workstation->cpu }}</td>
                    <th>{{ trans('cruds.workstation.fields.memory') }}</th>
                    <td>{{ $workstation->memory }}</td>
                    <th>{{ trans('cruds.workstation.fields.disk') }}</th>
                    <td colspan="2">{{ $workstation->disk }}</td>
               </tr>
               <tr>
                    <th width="10%">{{ trans('cruds.workstation.fields.operating_system') }}</th>
                    <td colspan="6">{{ $workstation->operating_system ?? '' }}</td>
               </tr>

            </tbody>
        </table>
    </div>


    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.administration.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <div class="row">
            <div class="col">
                <table class="table table-bordered table-striped table-report">
                   <tbody>
                       <tr>
                      <th width="10%">{{ trans('cruds.workstation.fields.entity') }}</th>
                      <td width="15%">
                        @if ($workstation->entity!=null)
                        @canShow($workstation->entity)
                        <a href="{{ route('admin.entities.show', $workstation->entity->id) }}">
                            {{ $workstation->entity->name ?? '' }}
                        </a>
                        @elsecanShow
                        {{ $workstation->entity->name ?? '' }}
                        @endcanShow
                        @endif
                      </td>
                      <th width="10%">{{ trans('cruds.workstation.fields.domain') }}</th>
                      <td width="15%">
                        @if ($workstation->domain!=null)
                        @canShow($workstation->domain)
                        <a href="{{ route('admin.domains.show', $workstation->domain_id) }}">
                            {{ $workstation->domain->name ?? '' }}
                        </a>
                        @elsecanShow
                        {{ $workstation->domain->name ?? '' }}
                        @endcanShow
                        @endif
                      </td>
                      <th width="10%">{{ trans('cruds.workstation.fields.user') }}</th>
                      <td width="15%">
                        @if ($workstation->user!=null)
                        <a href="{{ route('admin.users.show', $workstation->user_id) }}">
                            {{ $workstation->user->user_id ?? '' }}
                        </a>
                        @endif
                    </td>
                    <th width="10%">{{ trans('cruds.workstation.fields.other_user') }}</th>
                    <td>{{ $workstation->other_user ?? '' }}</td>
                    </tr>
                    <tr>
                      <th width="10%">{{ trans('cruds.workstation.fields.status') }}</th>
                      <td colspan="3">{{ $workstation->status }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.application.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <div class="row">
            <div class="col">
                <table class="table table-bordered table-striped table-report">
                    <tbody>
                    <tr>
                        <th width="10%">{{ trans('cruds.workstation.fields.applications') }}</th>
                        <td>
                            @foreach($workstation->applications as $application)
                                @canShow($application)
                                    <a href="{{ route('admin.applications.show', $application->id) }}">
                                        {{ $application->name }}
                                    </a>
                                @elsecanShow
                                    {{ $application->name }}
                                @endcanShow
                                @if(!$loop->last)
                                ,
                                @endif
                            @endforeach
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </div>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.logical_infrastructure.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <div class="row">
            <div class="col">
                <table class="table table-bordered table-striped table-report">
                    <tbody>
                    <tr>
                        <th width="10%">{{ trans('cruds.workstation.fields.network') }}</th>
                        <td width="15%">
                            @if ($workstation->network!=null)
                            @canShow($workstation->network)
                            <a href="{{ route('admin.networks.show', $workstation->network_id) }}">
                                {{ $workstation->network->name ?? '' }}
                            </a>
                            @elsecanShow
                            {{ $workstation->network->name ?? '' }}
                            @endcanShow
                            @endif
                        </td>
                        <th width="10%">{{ trans('cruds.workstation.fields.address_ip') }}</th>
                        <td width="15%">{{ $workstation->address_ip }}</td>
                        <th width="10%">{{ trans('cruds.workstation.fields.mac_address') }}</th>
                        <td width="15%">{{ $workstation->mac_address }}</td>
                        <th width="10%">{{ trans('cruds.workstation.fields.network_port_type') }}</th>
                        <td width="15%">{{ $workstation->network_port_type }}</td>
                   </tr>
               </tbody>
            </table>
        </div>
        </div>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.physical_infrastructure.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <div class="row">
            <div class="col">
                <table class="table table-bordered table-striped table-report">
                    <tbody>
                    <tr>
                        <th>{{ trans('cruds.workstation.fields.site') }}</th>
                        <td>
                        @if ($workstation->site != null)
                        @canShow($workstation->site)
                        <a href="{{ route('admin.sites.show', $workstation->site_id) }}">
                            {{ $workstation->site->name ?? '' }}
                        </a>
                        @elsecanShow
                        {{ $workstation->site->name ?? '' }}
                        @endcanShow
                        @endif
                        </td>
                        <th width="10%">{{ trans('cruds.workstation.fields.building') }}</th>
                        <td width="40%">
                        @if ($workstation->building != null)
                        @canShow($workstation->building)
                        <a href="{{ route('admin.buildings.show', $workstation->building_id) }}">
                            {{ $workstation->building->name ?? '' }}
                        </a>
                        @elsecanShow
                        {{ $workstation->building->name ?? '' }}
                        @endcanShow
                        @endif
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('admin._footer', ['model' => $workstation])
</div>
<div id="btn-cancel" class="form-group">
    <a class="btn btn-default" href="{{ route('admin.workstations.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
