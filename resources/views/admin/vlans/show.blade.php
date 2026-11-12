@extends('layouts.admin')

@section('title')
    {{ $vlan->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.vlans.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$vlan->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($vlan)
        <a class="btn btn-info" href="{{ route('admin.vlans.edit', $vlan->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('vlan_create')
        <a class="btn btn-warning" href="{{ route('admin.vlans.clone', $vlan->id) }}">
            {{ trans('global.clone') }}
        </a>
    @endcan

    @can('vlan_delete')
        <form action="{{ route('admin.vlans.destroy', $vlan->id) }}" method="POST"
              data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.vlan.title') }}
    </div>

    <div class="card-body">
    @include('admin.vlans._details', [
        'vlan' => $vlan,
        'withLink' => false,
    ])
    </div>
    @include('admin._footer', ['model' => $vlan])
</div>

<div class="card mt-3">
    <div class="card-header">
        {{ trans('cruds.vlan.fields.used_ip_addresses') }}
    </div>
    <div class="card-body">
        @forelse ($subnetworksData as $entry)
            @php $subnetwork = $entry['subnetwork']; @endphp
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <a href="{{ route('admin.subnetworks.show', $subnetwork->id) }}">{{ $subnetwork->name }}</a>
                        @if ($subnetwork->address)
                            &mdash; {{ $subnetwork->address }}
                        @endif
                    </span>
                    <span class="badge bg-secondary">{{ $entry['ips']->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($entry['ips']->isEmpty())
                        <div class="p-3 text-muted">{{ trans('cruds.vlan.fields.no_devices') }}</div>
                    @else
                        <table class="table table-bordered table-striped table-report mb-0">
                            <thead>
                                <tr>
                                    <th>{{ trans('cruds.vlan.fields.ip_address') }}</th>
                                    <th>{{ trans('cruds.vlan.fields.associated_objects') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entry['ips'] as $ip => $objects)
                                    <tr>
                                        <td>{{ $ip }}</td>
                                        <td>
                                            @foreach ($objects as $object)
                                                <a href="{{ $object['route'] }}">{{ $object['name'] }}</a>@if (! $loop->last)<br>@endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">{{ trans('cruds.vlan.fields.no_devices') }}</p>
        @endforelse
    </div>
</div>

<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.vlans.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>
@endsection
