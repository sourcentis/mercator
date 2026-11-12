@extends('layouts.admin')

@section('title')
    {{ trans('cruds.annuaire.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('annuaire_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.annuaires.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.annuaire.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.annuaire.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.annuaire.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.annuaire.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.annuaire.fields.attributes') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.annuaire.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.annuaire.fields.solution') }}
                        </th>
                        <th>
                            {{ trans('cruds.annuaire.fields.zone_admin') }}
                        </th>
                        <th>
                            {{ trans('cruds.annuaire.fields.application') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($annuaires as $key => $annuaire)
                        <tr data-entry-id="{{ $annuaire->id }}"
                            @if (
                                ($annuaire->description===null)||
                                ($annuaire->solution===null)||
                                ($annuaire->zone_admin_id===null)||
                                ((auth()->user()->granularity>=2)&&
                                    ($annuaire->application_id===null)
                                )
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $annuaire->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$annuaire" />
                            </td>
                            <td>
                                {{ $annuaire->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $annuaire->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $annuaire->description !!}
                            </td>
                            <td>
                                {{ $annuaire->solution ?? '' }}
                            </td>
                            <td>
                                @if ($annuaire->zoneAdmin!=null)
                                    <x-show-link :model="$annuaire->zoneAdmin" />
                                @endif
                            </td>
                            <td>
                                @if ($annuaire->application!=null)
                                    <x-show-link :model="$annuaire->application" />
                                @endif
                            </td>

                            <td nowrap>
                                @can('annuaire_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.annuaires.show', $annuaire->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($annuaire)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.annuaires.edit', $annuaire->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('annuaire_delete')
                                    <form action="{{ route('admin.annuaires.destroy', $annuaire->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $annuaires])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.annuaire.title_singular"),
            'URL' => route('admin.annuaires.massDestroy'),
            'canDelete' => auth()->user()->can('annuaire_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'description'],
));
    </script>
@endsection
