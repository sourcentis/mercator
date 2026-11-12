@extends('layouts.admin')

@section('title')
    {{ $activity->name }}
@endsection

@section('content')
<div class="form-group">
    <a class="btn btn-default" href="{{ route('admin.activities.index') }}">
        {{ trans('global.back_to_list') }}
    </a>


    @can('explore_access')

    <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$activity->getUID()}}">
        {{ trans('global.explore') }}
    </a>


    @endcan

    @canEdit($activity)
        <a class="btn btn-info" href="{{ route('admin.activities.edit', $activity->id) }}">
            {{ trans('global.edit') }}
        </a>
    @endcanEdit

    @can('activity_delete')
        <form action="{{ route('admin.activities.destroy', $activity->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.activity.title_singular') }}
    </div>

    <div class="card-body">

        @include('admin.activities._details', [
            'activity' => $activity,
            'withLink' => false
        ])

    </div>

    <div class="card-header">
        {{ trans('cruds.activity.bia') }}
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="20%">
                        {{ trans('cruds.activity.fields.maximum_tolerable_downtime') }}
                    </th>
                    <td>
                        @if (intdiv($activity->maximum_tolerable_downtime,60 * 24) > 0)
                            {{ intdiv($activity->maximum_tolerable_downtime,60 * 24) }}
                            @if (intdiv($activity->maximum_tolerable_downtime,60 * 24) > 1)
                                {{ trans('global.days') }}
                            @else
                                {{ trans('global.day') }}
                            @endif
                        @endif
                        @if ((intdiv($activity->maximum_tolerable_downtime,60) % 24) > 0)
                            {{ intdiv($activity->maximum_tolerable_downtime,60) % 24 }}
                            @if ((intdiv($activity->maximum_tolerable_downtime,60) % 24) > 1)
                                {{ trans('global.hours') }}
                            @else
                                {{ trans('global.hour') }}
                            @endif
                        @endif
                        @if (($activity->maximum_tolerable_downtime % 60) > 0)
                            {{ $activity->maximum_tolerable_downtime % 60 }}
                            @if (($activity->maximum_tolerable_downtime % 60) > 1)
                                {{ trans('global.minutes') }}
                            @else
                                {{ trans('global.minute') }}
                            @endif
                        @endif
                    </td>
                    <th width="20%">
                        {{ trans('cruds.activity.fields.maximum_tolerable_data_loss') }}
                    </th>
                    <td>
                        @if (intdiv($activity->maximum_tolerable_data_loss,60 * 24) > 0)
                            {{ intdiv($activity->maximum_tolerable_data_loss,60 * 24) }}
                            @if (intdiv($activity->maximum_tolerable_data_loss,60 * 24) > 1)
                                {{ trans('global.days') }}
                            @else
                                {{ trans('global.day') }}
                            @endif
                        @endif
                        @if ((intdiv($activity->maximum_tolerable_data_loss,60) % 24) > 0)
                            {{ intdiv($activity->maximum_tolerable_data_loss,60) % 24 }}
                            @if ((intdiv($activity->maximum_tolerable_data_loss,60) % 24) > 1)
                                {{ trans('global.hours') }}
                            @else
                                {{ trans('global.hour') }}
                            @endif
                        @endif
                        @if (($activity->maximum_tolerable_data_loss % 60) > 0)
                            {{ $activity->maximum_tolerable_data_loss % 60 }}
                            @if (($activity->maximum_tolerable_data_loss % 60) > 1)
                                {{ trans('global.minutes') }}
                            @else
                                {{ trans('global.minute') }}
                            @endif
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('cruds.activity.fields.recovery_time_objective') }}
                    </th>
                    <td>
                        @if (intdiv($activity->recovery_time_objective,60 * 24) > 0)
                            {{ intdiv($activity->recovery_time_objective,60 * 24) }}
                            @if (intdiv($activity->recovery_time_objective,60 * 24) > 1)
                                {{ trans('global.days') }}
                            @else
                                {{ trans('global.day') }}
                            @endif
                        @endif
                        @if ((intdiv($activity->recovery_time_objective,60) % 24) > 0)
                            {{ intdiv($activity->recovery_time_objective,60) % 24 }}
                            @if ((intdiv($activity->recovery_time_objective,60) % 24) > 1)
                                {{ trans('global.hours') }}
                            @else
                                {{ trans('global.hour') }}
                            @endif
                        @endif
                        @if (($activity->recovery_time_objective % 60) > 0)
                            {{ $activity->recovery_time_objective % 60 }}
                            @if (($activity->recovery_time_objective % 60) > 1)
                                {{ trans('global.minutes') }}
                            @else
                                {{ trans('global.minute') }}
                            @endif
                        @endif
                    </td>
                    <th>
                        {{ trans('cruds.activity.fields.recovery_point_objective') }}
                    </th>
                    <td>
                        @if (intdiv($activity->recovery_point_objective,60 * 24) > 0)
                            {{ intdiv($activity->recovery_point_objective,60 * 24) }}
                            @if (intdiv($activity->recovery_point_objective,60 * 24) > 1)
                                {{ trans('global.days') }}
                            @else
                                {{ trans('global.day') }}
                            @endif
                        @endif
                        @if ((intdiv($activity->recovery_point_objective,60) % 24) > 0)
                            {{ intdiv($activity->recovery_point_objective,60) % 24 }}
                            @if ((intdiv($activity->recovery_point_objective,60) % 24) > 1)
                                {{ trans('global.hours') }}
                            @else
                                {{ trans('global.hour') }}
                            @endif
                        @endif
                        @if (($activity->recovery_point_objective % 60) > 0)
                            {{ $activity->recovery_point_objective % 60 }}
                            @if (($activity->recovery_point_objective % 60) > 1)
                                {{ trans('global.minutes') }}
                            @else
                                {{ trans('global.minute') }}
                            @endif
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    <b>{{  trans('cruds.activity.impacts') }}</b>
        <table class="table table-bordered table-striped">
        @foreach($activity->impacts as $impact)
        <tr>
            <td width="10%">
                {{ $impact->impact_type }}
            </td>
            <td width="10%">
                @if ($impact->severity==0){{ trans('global.none') }}@endif
                @if ($impact->severity==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                @if ($impact->severity==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                @if ($impact->severity==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                @if ($impact->severity==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
            </td>
            <td>
            </td>
        </tr>
        @endforeach
        </table>
    </div>
    <div class="card-header">
        {{ trans('cruds.activity.drp') }}
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.activity.fields.drp') }}
                    </th>
                    <td>
                        {!! $activity->drp !!}
                    </td>
                </tr>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.activity.fields.drp_link') }}
                    </th>
                    <td>
                        @if (filter_var($activity->drp_link, FILTER_VALIDATE_URL))
                            <a href="{{ $activity->drp_link }}">{{ $activity->drp_link }}</a>
                        @else
                            {{ $activity->drp_link }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @include('admin._footer', ['model' => $activity])
</div>
<div class="form-group">
    <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.activities.index') }}">
        {{ trans('global.back_to_list') }}
    </a>
</div>

@endsection
