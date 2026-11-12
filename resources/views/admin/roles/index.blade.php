@extends('layouts.admin')

@section('title')
    {{ trans('cruds.role.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
@can('role_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a id="btn-new" class="btn btn-success" href="{{ route("admin.roles.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.role.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.role.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.role.fields.title') }}
                        </th>
                        <th>
                            {{ trans('cruds.role.fields.perimeter') }}
                        </th>
                        <th title="{{ trans('cruds.user.title') }}">#</th>
                        <th>{{ trans('cruds.cartographer.title') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $key => $role)
                        <tr data-entry-id="{{ $role->id }}">
                            <td>

                            </td>
                            <td>
                                <x-show-link :model="$role" :label="$role->title ?? ''" />
                            </td>
                            <td>{{ $role->perimeter->name ?? '' }}</td>
                            <td>{{ $role->users_count }}</td>
<td>@php
      $cells = [];
      foreach($role->cartographerEntries->sortBy('cartographiable_type') as $entry) {
          if (!$entry->cartographiable) continue;
          $showRoute = $routes[$entry->cartographiable_type] ?? null;
          $label = $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')';
          $title = e($models[$entry->cartographiable_type] ?? '');
          if ($showRoute) {
              $href = route($showRoute, $entry->cartographiable_id);
              $cells[] = '<a href="'.e($href).'" class="badge bg-primary text-decoration-none"
  title="'.$title.'">'.e($label).'</a>';
          } else {
              $cells[] = '<span class="badge bg-secondary me-1 mb-1" title="'.$title.'">'.e($label).'</span>';
          }
      }
      echo implode(' ', $cells);
  @endphp</td>
                            <td nowrap>
                                @can('role_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.roles.show', $role->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($role)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.roles.edit', $role->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('role_delete')
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
    </div>
</div>
@endsection

@section('scripts')
@parent
<script @nonce>
@include('partials.datatable', array(
    'id' => '#dataTable',
    'title' => trans("cruds.role.title_singular"),
    'URL' => route('admin.roles.massDestroy'),
    'canDelete' => auth()->user()->can('role_delete') ? true : false
));
</script>
@endsection
