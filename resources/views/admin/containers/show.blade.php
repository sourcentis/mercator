@extends('layouts.admin')

@section('title')
    {{ $container->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.containers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$container->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($container)
        <a class="btn btn-info" href="{{ route('admin.containers.edit', $container->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('site_delete')
        <form action="{{ route('admin.containers.destroy', $container->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.container.title') }}
    </div>

    <div class="card-body">
        @include('admin.containers._details', [
            'container' => $container,
            'withLink' => false,
        ])
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.logical_infrastructure.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th width='10%'>
                        {{ trans('cruds.container.fields.logical_servers') }}
                    </th>
                    <td>
                        @foreach($container->logicalServers as $server)
                            @canShow($server)
                                <a href="{{ route('admin.logical-servers.show', $server->id) }}">
                                    {{ $server->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $server->name ?? '' }}
                            @endcanShow
                            @if ($container->logicalServers->last()!=$server)
                            ,
                            @endif
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.application.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.container.fields.applications') }}
                    </th>
                    <td width="40%">
                        @foreach($container->applications as $application)
                            @canShow($application)
                                <a href="{{ route('admin.applications.show', $application->id) }}">
                                    {{ $application->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $application->name ?? '' }}
                            @endcanShow
                            @if ($container->applications->last()!=$application)
                            ,
                            @endif
                        @endforeach
                    </td>
                    <th width="10%">
                        {{ trans('cruds.container.fields.databases') }}
                    </th>
                    <td width="40%">
                        @foreach($container->databases as $database)
                            @canShow($database)
                                <a href="{{ route('admin.databases.show', $database->id) }}">
                                    {{ $database->name ?? '' }}
                                </a>
                            @elsecanShow
                                {{ $database->name ?? '' }}
                            @endcanShow
                            @if ($container->databases->last()!=$database)
                            ,
                            @endif
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @include('admin._footer', ['model' => $container])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.containers.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
