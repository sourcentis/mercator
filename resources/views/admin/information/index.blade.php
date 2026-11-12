@extends('layouts.admin')

@section('title')
    {{ trans('cruds.information.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('information_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route('admin.information.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.information.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.information.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.information.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.information.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.information.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.information.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.information.fields.owner') }}
                        </th>
                        <th>
                            {{ trans('cruds.information.fields.security_need') }}
                            @if (config('mercator-config.parameters.security_need_auth'))
                            + {{ trans("global.authenticity_short") }}
                            @endif
                        </th>
                        <th>
                            {{ trans('cruds.information.fields.sensitivity') }}
                        </th>
                        <th>
                            {{ trans('cruds.information.fields.parents') }}
                        </th>
                        <th>
                            {{ trans('cruds.information.fields.children') }}
                        </th>
                        <th data-column="constraints">
                            {{ trans('cruds.information.fields.constraints') }}
                        </th>
                        <th data-column="retention_period">
                            {{ trans('cruds.information.fields.retention_period') }}
                        </th>
                        <th data-column="final_disposition">
                            {{ trans('cruds.information.fields.final_disposition') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($information as $key => $info)
                        <tr data-entry-id="{{ $info->id }}"
                            @if(($info->description==null)||
                                ($info->owner==null)||
                                ($info->administrator==null)||
                                ($info->storage==null)||
                                ((auth()->user()->granularity>=2)&&
                                    (
                                    ($info->security_need_c==null)||
                                    ($info->security_need_i==null)||
                                    ($info->security_need_a==null)||
                                    ($info->security_need_t==null)
                                    )
                                )||
                                ($info->sensitivity==null)
                                )
                                                      class="table-warning"
                            @endif
                            >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $info->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$info" />
                            </td>
                            <td>
                                {{ $info->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", $info->attributes) as $attribute) {
                                    echo "<span class='badge badge-info'>";
                                    echo $attribute;
                                    echo "</span> ";
                                }
                                ?>
                            </td>
                            <td>
                                {!! clean($info->description ?? '') !!}
                            </td>
                            <td>
                                {{ $info->owner ?? '' }}
                            </td>
                            <td nowrap>
                                @php
                                if ($info->security_need_c==0)
                                    echo "<span class='noRisk'>0</span>";
                                elseif ($info->security_need_c==1)
                                    echo "<span class='veryLowRisk'>1</span>";
                                elseif ($info->security_need_c==2)
                                    echo "<span class='lowRisk'>2</span>";
                                elseif ($info->security_need_c==3)
                                    echo "<span class='mediumRisk'>3</span>";
                                elseif ($info->security_need_c==4)
                                    echo "<span class='highRisk'>4</span>";
                                else
                                    echo "<span> * </span>";
                                echo " - ";
                                if ($info->security_need_i==0)
                                    echo "<span class='noRisk'>0</span>";
                                elseif ($info->security_need_i==1)
                                    echo "<span class='veryLowRisk'>1</span>";
                                elseif ($info->security_need_i==2)
                                    echo "<span class='lowRisk'>2</span>";
                                elseif ($info->security_need_i==3)
                                    echo "<span class='mediumRisk'>3</span>";
                                elseif ($info->security_need_i==4)
                                    echo "<span class='highRisk'>4</span>";
                                else
                                    echo "<span> * </span>";
                                echo " - ";
                                if ($info->security_need_a==0)
                                    echo "<span class='noRisk'>0</span>";
                                elseif ($info->security_need_a==1)
                                    echo "<span class='veryLowRisk'>1</span>";
                                elseif ($info->security_need_a==2)
                                    echo "<span class='lowRisk'>2</span>";
                                elseif ($info->security_need_a==3)
                                    echo "<span class='mediumRisk'>3</span>";
                                elseif ($info->security_need_a==4)
                                    echo "<span class='highRisk'>4</span>";
                                else
                                    echo "<span> * </span>";
                                echo " - ";
                                if ($info->security_need_t==0)
                                    echo "<span class='noRisk'>0</span>";
                                elseif ($info->security_need_t==1)
                                    echo "<span class='veryLowRisk'>1</span>";
                                elseif ($info->security_need_t==2)
                                    echo "<span class='lowRisk'>2</span>";
                                elseif ($info->security_need_t==3)
                                    echo "<span class='mediumRisk'>3</span>";
                                elseif ($info->security_need_t==4)
                                    echo "<span class='highRisk'>4</span>";
                                else
                                    echo "<span> * </span>";
                                if (config('mercator-config.parameters.security_need_auth')) {
                                    echo "-";
                                    if ($info->security_need_auth==0)
                                        echo "<span class='noRisk'>0</span>";
                                    elseif ($info->security_need_auth==1)
                                        echo "<span class='veryLowRisk'>1</span>";
                                    elseif ($info->security_need_auth==2)
                                        echo "<span class='lowRisk'>2</span>";
                                    elseif ($info->security_need_auth==3)
                                        echo "<span class='mediumRisk'>3</span>";
                                    elseif ($info->security_need_auth==4)
                                        echo "<span class='highRisk'>4</span>";
                                    else
                                        echo "<span> * </span>";
                                    }
                                @endphp
                            </td>
                            <td>
                                {{ $info->sensitivity ?? '' }}
                            </td>
                            <td>
                            @foreach($info->parents as $parent)
                                <x-show-link :model="$parent" />
                                @if(!$loop->last), @endif
                            @endforeach
                            </td>
                            <td>
                            @foreach($info->children as $child)
                                <x-show-link :model="$child" />
                                @if(!$loop->last), @endif
                            @endforeach
                            </td>
                            <td>
                                {{ $info->constraints }}
                            </td>
                            <td>
                                {{ $info->retention_period }}
                            </td>
                            <td>
                                {{ $info->final_disposition }}
                            </td>
                            <td nowrap>
                                @can('information_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.information.show', $info->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($info)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.information.edit', $info->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('information_delete')
                                    <form action="{{ route('admin.information.destroy', $info->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $information])
</div>
</div>
@endsection
@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.information.title_singular"),
    'URL' => route('admin.information.massDestroy'),
    'canDelete' => auth()->user()->can('information_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type','attributes','constraints','retention_period','final_disposition'],
));
</script>
@endsection
