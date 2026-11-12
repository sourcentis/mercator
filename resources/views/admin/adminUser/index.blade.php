@extends('layouts.admin')

@section('title')
    {{ trans('cruds.adminUser.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('admin_user_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.admin-users.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.adminUser.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.adminUser.title') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.adminUser.fields.user_id') }}
                        </th>
                        <th>
                            {{ trans('cruds.adminUser.fields.lastname') }}
                        </th>
                        <th>
                            {{ trans('cruds.adminUser.fields.firstname') }}
                        </th>
                        <th>
                            {{ trans('cruds.adminUser.fields.domain') }}
                        </th>
                        <th>
                            {{ trans('cruds.adminUser.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.adminUser.fields.attributes') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $key => $user)
                        <tr data-entry-id="{{ $user->id }}"
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $user->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$user" :label="$user->user_id ?? ''" />
                            </td>
                            <td>
                                {{ $user->lastname ?? '' }}
                            </td>
                            <td>
                                {{ $user->firstname ?? '' }}
                            </td>
                            <td>
                                {{ $user->domain->name ?? '' }}
                            </td>
                            <td>
                                {{ $user->type ?? '' }}
                            </td>
                            <td>
                                @php
                                    foreach(explode(" ",$user->attributes) as $a)
                                        echo "<div class='badge badge-info'>$a</div> ";
                                @endphp
                            </td>
                            <td nowrap>
                                @can('admin_user_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.admin-users.show', $user->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($user)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.admin-users.edit', $user->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('admin_user_delete')
                                    <form action="{{ route('admin.admin-users.destroy', $user->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $users])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.adminUser.title_singular"),
            'URL' => route('admin.admin-users.massDestroy'),
            'canDelete' => auth()->user()->can('admin_user_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter'],
));
    </script>
@endsection
