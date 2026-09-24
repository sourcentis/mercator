@extends('layouts.admin')

@section('title')
    {{ trans('cruds.applicationService.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('application_service_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route("admin.application-services.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.applicationService.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.applicationService.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.applicationService.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.applicationService.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.applicationService.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationService.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationService.fields.exposition') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationService.fields.applications') }}
                        </th>
                        <th>
                            {{ trans('cruds.applicationService.fields.modules') }}
                        </th>
			<th data-column="comments">
			    {{ trans('cruds.applicationService.fields.comments') }}
			</th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicationServices as $key => $applicationService)
                        <tr data-entry-id="{{ $applicationService->id }}"

                        @if (
                                ($applicationService->description==null)||
                                ($applicationService->servicesApplications->count()==0)
                            )
                                class="table-warning"
                        @endif

                            >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $applicationService->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$applicationService" />
                            </td>
                            <td>
                                {{ $applicationService->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $applicationService->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $applicationService->description !!}
                            </td>
                            <td>
                                {{ $applicationService->exposition ?? '' }}
                            </td>
                            <td>
                                @foreach($applicationService->servicesApplications as $application)
                                    <x-show-link :model="$application" />
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($applicationService->modules as $module)
                                    <x-show-link :model="$module" />
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
			    <td>
			        {!! nl2br(e($applicationService->comments)) !!}
			    </td> 
                            <td nowrap>
                                @can('application_service_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.application-services.show', $applicationService->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($applicationService)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.application-services.edit', $applicationService->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('application_service_delete')
                                    <form action="{{ route('admin.application-services.destroy', $applicationService->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    
    @include('partials.pagination-footer', ['paginator' => $applicationServices])
</div>
</div>



@endsection
@section('scripts')
@parent
<script>
@include('partials.datatable', array(
    'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
    'title' => trans("cruds.applicationService.title_singular"),
    'URL' => route('admin.application-services.massDestroy'),
    'canDelete' => auth()->user()->can('application_service_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'comments'],
));
</script>
@endsection
