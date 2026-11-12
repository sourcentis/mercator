@extends('layouts.admin')

@section('title')
    {{ trans('cruds.network.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('network_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.networks.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.network.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.network.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.network.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.network.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.network.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.network.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.network.fields.protocol_type') }}
                        </th>
                        <th>
                            {{ trans('cruds.network.fields.security_need') }}
                        </th>
                        <th data-column="responsible">
                            {{ trans('cruds.network.fields.responsible') }}
                        </th>
                        <th data-column="responsible_sec">
                            {{ trans('cruds.network.fields.responsible_sec') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($networks as $key => $network)
                        <tr data-entry-id="{{ $network->id }}"
                            @if (
                                ($network->description===null)||
                                ($network->responsible===null)||
                                ($network->responsible_sec===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $network->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$network" />
                            </td>
                            <td>
                                {{ $network->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", $network->attributes) as $attribute) {
                                    echo "<span class='badge badge-info'>";
                                    echo $attribute;
                                    echo "</span> ";
                                }
                                ?>
                            </td>
                            <td>
                                {!! clean($network->description ?? '') !!}
                            </td>
                            <td>
                                {{ $network->protocol_type ?? '' }}
                            </td>
                            <td>
                                @if ($network->security_need_c==1)
                                    <span class="veryLowRisk"> 1 </span>
                                @elseif ($network->security_need_c==2)
                                    <span class="lowRisk"> 2 </span>
                                @elseif ($network->security_need_c==3)
                                    <span class="mediumRisk"> 3 </span>
                                @elseif ($network->security_need_c==4)
                                    <span class="highRisk"> 4 </span>
                                @else
                                    <span> * </span>
                                @endif
                                -
                                @if ($network->security_need_i==1)
                                    <span class="veryLowRisk"> 1 </span>
                                @elseif ($network->security_need_i==2)
                                    <span class="lowRisk"> 2 </span>
                                @elseif ($network->security_need_i==3)
                                    <span class="mediumRisk"> 3 </span>
                                @elseif ($network->security_need_i==4)
                                    <span class="highRisk"> 4 </span>
                                @else
                                    <span> * </span>
                                @endif
                                -
                                @if ($network->security_need_a==1)
                                    <span class="veryLowRisk"> 1 </span>
                                @elseif ($network->security_need_a==2)
                                    <span class="lowRisk"> 2 </span>
                                @elseif ($network->security_need_a==3)
                                    <span class="mediumRisk"> 3 </span>
                                @elseif ($network->security_need_a==4)
                                    <span class="highRisk"> 4 </span>
                                @else
                                    <span> * </span>
                                @endif
                                -
                                @if ($network->security_need_t==1)
                                    <span class="veryLowRisk"> 1 </span>
                                @elseif ($network->security_need_t==2)
                                    <span class="lowRisk"> 2 </span>
                                @elseif ($network->security_need_t==3)
                                    <span class="mediumRisk"> 3 </span>
                                @elseif ($network->security_need_t==4)
                                    <span class="highRisk"> 4 </span>
                                @else
                                    <span> * </span>
                                @endif
                                @if (config('mercator-config.parameters.security_need_auth'))
                                    -
                                    @if ($network->security_need_auth==1)
                                        <span class="veryLowRisk"> 1 </span>
                                    @elseif ($network->security_need_auth==2)
                                        <span class="lowRisk"> 2 </span>
                                    @elseif ($network->security_need_auth==3)
                                        <span class="mediumRisk"> 3 </span>
                                    @elseif ($network->security_need_auth==4)
                                        <span class="highRisk"> 4 </span>
                                    @else
                                        <span> * </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $network->responsible }}
                            </td>
                            <td>
                                {{ $network->responsible_sec }}
                            </td>
                            <td nowrap>
                                @can('network_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.networks.show', $network->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($network)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.networks.edit', $network->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('network_delete')
                                    <form action="{{ route('admin.networks.destroy', $network->id) }}" method="POST"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                          style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger"
                                               value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        
        @include('partials.pagination-footer', ['paginator' => $networks])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.network.title_singular"),
            'URL' => route('admin.networks.massDestroy'),
            'canDelete' => auth()->user()->can('network_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'responsible', 'responsible_sec'],
));
    </script>
@endsection
