@extends('layouts.admin')

@section('title')
    {{ trans('cruds.gateway.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('gateway_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route('admin.gateways.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.gateway.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.gateway.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.gateway.fields.name') }}
                        </th>
                        <th data-column="type">
                            {{ trans('cruds.gateway.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.gateway.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.gateway.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.gateway.fields.authentification') }}
                        </th>
                        <th>
                            {{ trans('cruds.gateway.fields.ip') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($gateways as $gateway)
                        <tr data-entry-id="{{ $gateway->id }}"
                            @if (
                              ($gateway->description===null)||
                              ($gateway->authentification===null)||
                              ($gateway->ip===null)
                              )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $gateway->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$gateway" />
                            </td>
                            <td>
                                {{ $gateway->type }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $gateway->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {!! $gateway->description ?? '' !!}
                            </td>
                            <td>
                                {{ $gateway->authentification ?? '' }}
                            </td>
                            <td>
                                {{ $gateway->ip ?? '' }}
                            </td>
                            <td nowrap>
                                @can('gateway_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.gateways.show', $gateway->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($gateway)
                                    <a class="btn btn-xs btn-info"
                                       href="{{ route('admin.gateways.edit', $gateway->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('gateway_delete')
                                    <form action="{{ route('admin.gateways.destroy', $gateway->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $gateways])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.gateway.title_singular"),
            'URL' => route('admin.gateways.massDestroy'),
            'canDelete' => auth()->user()->can('gateway_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'type', 'attributes'],
));
    </script>
@endsection
