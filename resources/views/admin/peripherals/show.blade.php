@extends('layouts.admin')

@section('title')
    {{ $peripheral->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.peripherals.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$peripheral->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($peripheral)
        <a class="btn btn-info" href="{{ route('admin.peripherals.edit', $peripheral->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('peripheral_create')
        <a class="btn btn-warning" href="{{ route('admin.peripherals.clone', $peripheral->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('peripheral_delete')
        <form action="{{ route('admin.peripherals.destroy', $peripheral->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('cruds.peripheral.title_singular') }}
    </div>
    <div class="card-body">
        @include('admin.peripherals._details', [
            'peripheral' => $peripheral,
            'withLink' => false,
        ])
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.ecosystem.title") }}
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.provider') }}
                    </th>
                    <td>
                        @if($peripheral->provider!=null)
                            @canShow($peripheral->provider)
                                <a href="{{ route('admin.entities.show', $peripheral->provider->id) }}">
                                    {{ $peripheral->provider->name }}
                                </a>
                            @elsecanShow
                                {{ $peripheral->provider->name }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.responsible') }}
                    </th>
                    <td>
                        {{ $peripheral->responsible ?? '' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.application.title") }}
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.applications') }}
                    </th>
                    <td>
                        @foreach($peripheral->applications as $application)
                            @canShow($application)
                                <a href="{{ route('admin.applications.show', $application->id) }}">
                                    {{ $application->name }}
                                </a>
                            @elsecanShow
                                {{ $application->name }}
                            @endcanShow
                            @if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        Common Platform Enumeration (CPE)
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.vendor') }}
                    </th>
                    <td width="23%">
                        {{ $peripheral->vendor }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.product') }}
                    </th>
                    <td width="23%">
                        {{ $peripheral->product }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.version') }}
                    </th>
                    <td width="23%">
                        {{ $peripheral->pversion }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.administration.title") }}
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.domain') }}
                    </th>
                    <td>
                        @if($peripheral->domain!=null)
                            @canShow($peripheral->domain)
                                <a href="{{ route('admin.domains.show', $peripheral->domain->id) }}">
                                    {{ $peripheral->domain->name }}
                                </a>
                            @elsecanShow
                                {{ $peripheral->domain->name }}
                            @endcanShow
                        @endif
                    </td>

                </tr>
            </tbody>
        </table>
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.logical_infrastructure.title_short") }}
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.address_ip') }}
                    </th>
                    <td>
                        {{ $peripheral->address_ip }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.physical_infrastructure.title_short") }}
    </div>
    <!------------------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.site') }}
                    </th>
                    <td width="23%">
                        @if($peripheral->site!=null)
                            @canShow($peripheral->site)
                                <a href="{{ route('admin.sites.show', $peripheral->site->id) }}">
                                    {{ $peripheral->site->name }}
                                </a>
                            @elsecanShow
                                {{ $peripheral->site->name }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.building') }}
                    </th>
                    <td  width="23%">
                        @if($peripheral->building!=null)
                            @canShow($peripheral->building)
                                <a href="{{ route('admin.buildings.show', $peripheral->building->id) }}">
                                    {{ $peripheral->building->name }}
                                </a>
                            @elsecanShow
                                {{ $peripheral->building->name }}
                            @endcanShow
                        @endif
                    </td>
                    <th width="10%">
                        {{ trans('cruds.peripheral.fields.bay') }}
                    </th>
                    <td width="23%">
                        @if($peripheral->bay!=null)
                            @canShow($peripheral->bay)
                                <a href="{{ route('admin.bays.show', $peripheral->bay->id) }}">
                                    {{ $peripheral->bay->name }}
                                </a>
                            @elsecanShow
                                {{ $peripheral->bay->name }}
                            @endcanShow
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @include('admin._footer', ['model' => $peripheral])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.peripherals.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
<br><br>
@endsection
