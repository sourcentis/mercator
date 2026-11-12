@extends('layouts.admin')

@section('title')
    {{ $information->name }}
@endsection

@section('content')
<div class="form-group">
    <div class="form-group">
        <a class="btn btn-default" href="{{ route('admin.information.index') }}">
            {{ trans('global.back_to_list') }}
        </a>


        @can('explore_access')

        <a class="btn btn-success" href="{{ route('admin.report.explore') }}?node={{$information->getUID()}}">
            {{ trans('global.explore') }}
        </a>


        @endcan

        @canEdit($information)
            <a class="btn btn-info" href="{{ route('admin.information.edit', $information->id) }}">
                {{ trans('global.edit') }}
            </a>
        @endcanEdit

        @can('information_delete')
            <form action="{{ route('admin.information.destroy', $information->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.delete') }}">
            </form>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.information.title') }}
    </div>

    <div class="card-body">

        @include('admin.information._details', [
            'information' => $information,
            'withLink' => false,
        ])
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans("cruds.menu.metier.title_short") }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">

        <table class="table table-bordered table-striped table-report" id="{{ $information->getUID() }}">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.information.fields.owner') }}
                    </th>
                    <td width="25%">
                        {{ $information->owner }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.information.fields.administrator') }}
                    </th>
                    <td width="25%">
                        {{ $information->administrator }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.information.fields.storage') }}
                    </th>
                    <td width="25%">
                        {{ $information->storage }}
                    </td>
                </tr>

                <tr>
                    <th>
                        {{ trans('cruds.information.fields.sensitivity') }}
                    </th>
                    <td>
                        {{ $information->sensitivity }}
                    </td>
                    <th>
                        {{ trans('cruds.information.fields.security_need') }}
                        @if (config('mercator-config.parameters.security_need_auth'))
                        + {{ trans("global.authenticity_short") }}
                        @endif
                    </th>
                    <td colspan="3">
                    {{ trans('global.confidentiality') }} :
                        @if ($information->security_need_c==0){{ trans('global.none') }}@endif
                        @if ($information->security_need_c==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                        @if ($information->security_need_c==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                        @if ($information->security_need_c==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                        @if ($information->security_need_c==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                    &nbsp;
                    {{ trans('global.integrity') }} :
                        @if ($information->security_need_i==0){{ trans('global.none') }}@endif
                        @if ($information->security_need_i==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                        @if ($information->security_need_i==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                        @if ($information->security_need_i==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                        @if ($information->security_need_i==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                    &nbsp;
                    {{ trans('global.availability') }} :
                        @if ($information->security_need_a==0){{ trans('global.none') }}@endif
                        @if ($information->security_need_a==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                        @if ($information->security_need_a==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                        @if ($information->security_need_a==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                        @if ($information->security_need_a==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                    &nbsp;
                    {{ trans('global.tracability') }} :
                        @if ($information->security_need_t==0){{ trans('global.none') }}@endif
                        @if ($information->security_need_t==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                        @if ($information->security_need_t==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                        @if ($information->security_need_t==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                        @if ($information->security_need_t==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                    @if (config('mercator-config.parameters.security_need_auth'))
                    &nbsp;
                    {{ trans('global.authenticity') }} :
                        @if ($information->security_need_auth==0){{ trans('global.none') }}@endif
                        @if ($information->security_need_auth==1)<span class="veryLowRisk">{{ trans('global.low') }}</span>@endif
                        @if ($information->security_need_auth==2)<span class="lowRisk">{{ trans('global.medium') }}</span>@endif
                        @if ($information->security_need_auth==3)<span class="mediumRisk">{{ trans('global.strong') }}</span>@endif
                        @if ($information->security_need_auth==4)<span class="highRisk">{{ trans('global.very_strong') }}</span>@endif
                    @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        Relations
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report" id="{{ $information->getUID() }}">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.information.fields.parents') }}
                    </th>
                    <td width="40%">
                        @foreach($information->parents as $parent)
                            @canShow($parent)<a href="{{ route('admin.information.show', $parent->id) }}">{{$parent->name}}</a>@elsecanShow{{$parent->name}}@endcanShow
                            @if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <th width="10%">
                        {{ trans('cruds.information.fields.children') }}
                    </th>
                    <td width="40%">
                        @foreach($information->children as $child)
                            @canShow($child)<a href="{{ route('admin.information.show', $child->id) }}">{{$child->name}}</a>@elsecanShow{{$child->name}}@endcanShow
                            @if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.information.fields.processes') }}
                    </th>
                    <td colspan="3">
                        @foreach($information->processes as $process)
                            @canShow($process)
                                <a href="{{ route('admin.processes.show', $process->id) }}">
                                    {{ $process->name }}
                                </a>
                            @elsecanShow
                                {{ $process->name }}
                            @endcanShow
                            @if (!$loop->last)
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
        {{ trans('cruds.menu.gdpr.title_short') }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report" id="{{ $information->getUID() }}">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.information.fields.constraints') }}
                    </th>
                    <td>
                        {!! clean($information->constraints ?? '') !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-header">
        {{ trans('cruds.information.fields.retention') }}
    </div>
    <!---------------------------------------------------------------------------------------------------->
    <div class="card-body">
        <table class="table table-bordered table-striped table-report">
            <tbody>
                <tr>
                    <th width="10%">
                        {{ trans('cruds.information.fields.retention_period') }}
                    </th>
                    <td width="40%">
                        {{ $information->retention_period }}
                    </td>
                    <th width="10%">
                        {{ trans('cruds.information.fields.final_disposition') }}
                    </th>
                    <td width="40%">
                        {{ $information->final_disposition }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.information.fields.retention_notes') }}
                    </th>
                    <td colspan="3">
                        {!! clean($information->retention_notes ?? '') !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

        @include('admin._footer', ['model' => $information])
    </div>
    <div class="form-group">
        <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.information.index') }}">
            {{ trans('global.back_to_list') }}
        </a>
    </div>

@endsection
