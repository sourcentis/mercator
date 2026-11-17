@extends('layouts.admin')

@section('title')
    {{ trans('cruds.backup.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('backup_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.backups.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.backup.title_singular') }}
                </a>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.backup.title_singular') }} {{ trans('global.list') }}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10"></th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>{{ trans('cruds.backup.fields.name') }}</th>
                        <th>{{ trans('cruds.backup.fields.type') }}</th>
                        <th>{{ trans('cruds.backup.fields.attributes') }}</th>
                        <th>{{ trans('cruds.backup.fields.description') }}</th>
                        <th>{{ trans('cruds.backup.frequency') }}</th>
                        <th>{{ trans('cruds.backup.cycle') }}</th>
                        <th>{{ trans('cruds.backup.retention') }}</th>
                        <th>{{ trans('cruds.backup.fields.logical_servers') }}</th>
                        <th>{{ trans('cruds.backup.fields.storage_devices') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($backups as $backup)
                        <tr data-entry-id="{{ $backup->id }}"
                            @if($backup->description === null) class="table-warning" @endif>
                            <td></td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $backup->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$backup" />
                            </td>
                            <td>{{ $backup->type }}</td>
                            <td>
                                <?php
                                foreach (explode(" ", $backup->attributes) as $attribute) {
                                    echo "<span class='badge badge-info'>";
                                    echo $attribute;
                                    echo "</span> ";
                                }
                                ?>
                            </td>
                            <td>{!! $backup->description !!}</td>
                            <td>{{ $backup->backup_frequency ? trans("cruds.backup.frequencies.{$backup->backup_frequency}") : '' }}</td>
                            <td>{{ $backup->backup_cycle ? trans("cruds.backup.cycles.{$backup->backup_cycle}") : '' }}</td>
                            <td>{{ $backup->backup_retention ? $backup->backup_retention . ' ' . trans('cruds.backup.retention_unit') : '' }}</td>
                            <td>
                                @foreach($backup->logicalServers as $server)
                                    <x-show-link :model="$server" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($backup->storageDevices as $device)
                                    <x-show-link :model="$device" />@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td nowrap>
                                @can('backup_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.backups.show', $backup->id) }}">{{ trans('global.view') }}</a>
                                @endcan
                                @canEdit($backup)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.backups.edit', $backup->id) }}">{{ trans('global.edit') }}</a>
                                @endcanEdit
                                @can('backup_delete')
                                    <form action="{{ route('admin.backups.destroy', $backup->id) }}" method="POST"
                                          data-confirm="{{ trans('global.areYouSure') }}"
                                          style="display: inline-block;">
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
        
        @include('partials.pagination-footer', ['paginator' => $backups])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', [
            'id'        => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'hiddenColumns' => ['perimeter'],
            'title'     => trans('cruds.backup.title_singular'),
            'URL'       => route('admin.backups.massDestroy'),
            'canDelete' => auth()->user()->can('backup_delete') ? true : false,
            'serverSidePagination' => true,
        ]);
    </script>
@endsection
