@extends('layouts.admin')

@section('title')
    {{ trans('cruds.domain.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('domain_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.domains.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.domain.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.domain.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.domain.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.domain.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.domain.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.domain.title') }}
                        </th>
                        <th>
                            {{ trans('cruds.domain.fields.domain_ctrl_cnt') }}
                        </th>
                        <th>
                            {{ trans('cruds.domain.fields.user_count') }}
                        </th>
                        <th>
                            {{ trans('cruds.domain.fields.machine_count') }}
                        </th>
                        <th>
                            {{ trans('cruds.domain.fields.relation_inter_domaine') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.domain.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($domains as $domain)
                        <tr data-entry-id="{{ $domain->id }}"
                            @if (
                                ($domain->description===null)||
                                ($domain->domain_ctrl_cnt===null)||
                                ($domain->user_count===null)||
                                ($domain->machine_count===null)||
                                ($domain->relation_inter_domaine===null)
                                )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $domain->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$domain" />
                            </td>
                            <td>
                                {{ $domain->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $domain->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                @foreach($domain->forestAds as $forestAd)
                                    <x-show-link :model="$forestAd" />{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </td>
                            <td>
                                {{ $domain->domain_ctrl_cnt ?? '' }}
                            </td>
                            <td>
                                {{ $domain->user_count ?? '' }}
                            </td>
                            <td>
                                {{ $domain->machine_count ?? '' }}
                            </td>
                            <td>
                                {{ $domain->relation_inter_domaine ?? '' }}
                            </td>
                            <td>
                                {!! $domain->description !!}
                            </td>
                            <td nowrap>
                                @can('domain_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.domains.show', $domain->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($domain)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.domains.edit', $domain->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('domain_delete')
                                    <form action="{{ route('admin.domains.destroy', $domain->id) }}"
                                          method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
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
        
        @include('partials.pagination-footer', ['paginator' => $domains])
</div>
    </div>
@endsection
@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.domain.title_singular"),
            'URL' => route('admin.domains.massDestroy'),
            'canDelete' => auth()->user()->can('domain_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes', 'description'],
));
    </script>
@endsection
