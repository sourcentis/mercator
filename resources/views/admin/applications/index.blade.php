@extends('layouts.admin')

@section('title')
    {{ trans('cruds.application.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('application_create')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a id="btn-new" class="btn btn-success" href="{{ route('admin.applications.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.application.title_singular') }}
        </a>
    </div>
</div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.application.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.application.fields.name') }}
                        </th>
			<th data-column="type">
			    {{ trans('cruds.application.fields.type') }}
			</th>
                        <th>
                            {{ trans('cruds.application.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.application.fields.responsible') }}
                        </th>
                        <th>
                            {{ trans('cruds.application.fields.entity_resp') }}
                        </th>
                        <th>
                            {{ trans('cruds.application.fields.application_block') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.application.fields.attributes') }}
                        </th>
                        <th data-column="vendor">
                            {{ trans('cruds.application.fields.vendor') }}
                        </th>
                        <th data-column="editor">
                            {{ trans('cruds.application.fields.editor') }}
                        </th>
                        <th data-column="functional_referent">
                            {{ trans('cruds.application.fields.functional_referent') }}
                        </th>
			<th data-column="status"> 
			    {{ trans('cruds.application.fields.status') }}
			</th>
                        <th data-column="comments">
                            {{ trans('cruds.application.fields.comments') }}
                        </th>
                        <th>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $key => $application)
                        <tr data-entry-id="{{ $application->id }}"

                        @if (
                                ($application->description==null)||
                                ($application->responsible==null)||
                                ($application->technology==null)||
                                ($application->type==null)||
                                ($application->processes->count()==0)||
                                ((auth()->user()->granularity>=2)&&
                                    (
                                    ($application->entities->count()==0)||
                                    ($application->entity_resp_id==null)||
                                    ($application->users==null)||
                                    ($application->security_need_c==null)||
                                    ($application->security_need_i==null)||
                                    ($application->security_need_a==null)||
                                    ($application->security_need_t==null)||
                                    ($application->applicationBlock==null)
                                    )
                                )
                            )
                            class="table-warning"
                        @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $application->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$application" />
                            </td>
		            <td>
			        {{ $application->type }}
			    </td>
                            <td>
                                {!! $application->description ?? '' !!}
                            </td>
                            <td>
                                {{ $application->responsible ?? '' }}
                            </td>
                            <td>
                                @if ($application->entityResp!=null)
                                <x-show-link :model="$application->entityResp" />
                                @endif
                            </td>
                            <td>
                                @if ($application->applicationBlock!=null)
                                <x-show-link :model="$application->applicationBlock" />
                                @endif
                            </td>
                            <td>
                                @php
                                foreach(explode(" ",$application->attributes) as $a)
                                    echo "<div class='badge badge-info'>$a</div> ";
                                @endphp
                            </td>
                            <td>
                                {{ $application->vendor }}
                            </td>
                            <td>
                                {{ $application->editor }}
                            </td>
                            <td>
                                {{ $application->functional_referent }}
                            </td>
			    <td> 
   				{{ $application->status }}
			    </td>
                            <td>
                                {!! nl2br(e($application->comments)) !!}
                            </td>
                            <td nowrap>
                                @can('application_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.applications.show', $application->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($application)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.applications.edit', $application->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @if(auth()->user()->can('application_delete'))
                                    <form action="{{ route('admin.applications.destroy', $application->id) }}" method="POST" data-confirm="{{ trans('global.areYouSure') }}" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
    @include('partials.pagination-footer', ['paginator' => $applications])
</div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.application.title_singular"),
    'URL' => route('admin.applications.massDestroy'),
    'canDelete' => (bool) auth()->user()->can('application_delete'),
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'vendor', 'editor', 'functional_referent', 'type', 'status', 'comments'],
    ));
</script>
@endsection
