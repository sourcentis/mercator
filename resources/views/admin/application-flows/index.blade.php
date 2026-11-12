@extends('layouts.admin')

@section('title')
    {{ trans('cruds.applicationFlow.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('application_flow_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.application-flows.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.applicationFlow.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.applicationFlow.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class=" table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10">

                        </th>
                        @if (auth()->user()->hasMultiplePerimeters())
                            <th data-column="perimeter">{{ trans('cruds.perimeter.title_short') }}</th>
                        @endif
                        <th>
                            {{ trans('cruds.applicationFlow.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationFlow.fields.type_short') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationFlow.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationFlow.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationFlow.fields.source') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationFlow.fields.destination') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationFlow.fields.crypted') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($flows as $key => $flow)
                        <tr data-entry-id="{{ $flow->id }}"

                            @if(
                                // no description
                                ($flow->description==null)||
                                // no source
                                (
                                  ($flow->applicationSource==null)&&
                                  ($flow->serviceSource==null)&&
                                  ($flow->moduleSource==null)&&
                                  ($flow->databaseSource==null)
                                )||
                                // no destination
                                (
                                  ($flow->applicationDest==null)&&
                                  ($flow->serviceDest==null)&&
                                  ($flow->moduleDest==null)&&
                                  ($flow->databaseDest==null)
                                )
                              )
                                class="table-warning"
                                @endif


                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $flow->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$flow" />
                            </td>
                            <td>
                                {{ $flow->type }}
                            </td>
                            <td>
                                @php
                                    foreach(explode(" ",$flow->attributes) as $attribute)
                                        echo "<span class='badge badge-info'>$attribute</span> ";
                                @endphp
                            </td>
                            <td>
                                {!! $flow->description ?? '' !!}
                            </td>
                            <td>
                                @if ($flow->applicationSource!=null)
                                    <x-show-link :model="$flow->applicationSource" />
                                @endif
                                @if ($flow->serviceSource!=null)
                                    <x-show-link :model="$flow->serviceSource" />
                                @endif
                                @if ($flow->moduleSource!=null)
                                    <x-show-link :model="$flow->moduleSource" />
                                @endif
                                @if ($flow->databaseSource!=null)
                                    <x-show-link :model="$flow->databaseSource" />
                                @endif
                            </td>
                            <td>
                                @if ($flow->applicationDest!=null)
                                    <x-show-link :model="$flow->applicationDest" />
                                @endif
                                @if ($flow->serviceDest!=null)
                                    <x-show-link :model="$flow->serviceDest" />
                                @endif
                                @if ($flow->moduleDest!=null)
                                    <x-show-link :model="$flow->moduleDest" />
                                @endif
                                @if ($flow->databaseDest!=null)
                                    <x-show-link :model="$flow->databaseDest" />
                                @endif
                            </td>
                            <td>
                                @if ($flow->crypted==0)
                                    Non
                                @elseif ($flow->crypted==1)
                                    Oui
                                @endif
                            </td>
                            <td nowrap>
                                @can('application_flow_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.application-flows.show', $flow->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($flow)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.application-flows.edit', $flow->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('application_flow_delete')
                                    <form action="{{ route('admin.application-flows.destroy', $flow->id) }}" method="POST"
                                          data-confirm="{{ trans('global.areYouSure') }}"
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
        
        @include('partials.pagination-footer', ['paginator' => $flows])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'hiddenColumns' => ['perimeter'],
            'title' => trans("cruds.applicationFlow.title_singular"),
            'URL' => route('admin.application-flows.massDestroy'),
            'canDelete' => auth()->user()->can('application_flow_delete') ? true : false,
    'serverSidePagination' => true
));
    </script>
@endsection
