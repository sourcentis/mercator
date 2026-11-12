@extends('layouts.admin')

@section('title')
    {{ trans('cruds.user.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('user_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.users.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.user.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.user.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.user.fields.login') }}
                        </th>
                        <th>
                            {{ trans('cruds.user.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.user.fields.email') }}
                        </th>
                        <th>
                            {{ trans('cruds.user.fields.roles') }}
                        </th>
                        <th>
                            {{ trans('cruds.cartographer.title') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $key => $user)
                        <tr data-entry-id="{{ $user->id }}">
                            <td>

                            </td>
                            <td>
                                <x-show-link :model="$user" :label="$user->login ?? ''" />
                            </td>
                            <td>
                                {{ $user->name ?? '' }}
                            </td>
                            <td>
                                {{ $user->email ?? '' }}
                            </td>
                            <td>@php
                              $cells = [];
                              foreach($user->roles as $item) {
                                  $component = new \App\View\Components\ShowLink($item, $item->title ?? '');
                                  if ($component->url) {
                                      $cells[] = '<a href="'.e($component->url).'">'.e($component->label).'</a>';
                                  } else {
                                      $cells[] = e($component->label);
                                  }
                              }
                              echo implode(', ', $cells);
                            @endphp</td>
                            <td>@php
                                  $cells = [];
                                  foreach($user->cartographerEntries as $entry) {
                                      if (!$entry->cartographiable) continue;
                                      $showRoute = $routes[$entry->cartographiable_type] ?? null;
                                      $label = $entry->cartographiable->name ?? '(id:'.$entry->cartographiable_id.')';
                                      if ($showRoute) {
                                          $href = route($showRoute, $entry->cartographiable_id);
                                          $title = e($models[$entry->cartographiable_type] ?? '');
                                          $cells[] = '<a href="'.e($href).'" class="badge bg-primary text-decoration-none me-1 mb-1"
                              title="'.$title.'">'.e($label).'</a>';
                                      } else {
                                          $cells[] = '<span class="badge bg-secondary me-1 mb-1">'.e($label).'</span>';
                                      }
                                  }
                                  echo implode(' ', $cells);
                              @endphp</td>
                            <td nowrap>
                                @can('user_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.users.show', $user->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($user)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.users.edit', $user->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('user_delete')
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
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
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'title' => trans("cruds.user.title_singular"),
            'URL' => route('admin.users.massDestroy'),
            'canDelete' => auth()->user()->can('user_delete') ? true : false
        ));
    </script>
@endsection
