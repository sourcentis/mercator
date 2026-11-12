@extends('layouts.admin')

@section('title')
    {{ trans('cruds.phone.title_singular') }} {{ trans('global.list') }}
@endsection

@section('content')
    @can('phone_create')
        <div style="margin-bottom: 10px;" class="row">
            <div class="col-lg-12">
                <a id="btn-new" class="btn btn-success" href="{{ route("admin.phones.create") }}">
                    {{ trans('global.add') }} {{ trans('cruds.phone.title_singular') }}
                </a>
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.phone.title_singular') }} {{ trans('global.list') }}
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
                            {{ trans('cruds.phone.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.phone.fields.type') }}
                        </th>
                        <th data-column="attributes">
                            {{ trans('cruds.phone.fields.attributes') }}
                        </th>
                        <th>
                            {{ trans('cruds.phone.fields.address_ip') }}
                        </th>
                        <th>
                            {{ trans('cruds.phone.fields.site') }}
                        </th>
                        <th>
                            {{ trans('cruds.phone.fields.building') }}
                        </th>
                        <th data-column="description">
                            {{ trans('cruds.phone.fields.description') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($phones as $phone)
                        <tr data-entry-id="{{ $phone->id }}"
                            @if (
                                ($phone->description===null)||
                                ($phone->site_id===null)||
                                ($phone->building_id===null)
                                 )
                                class="table-warning"
                                @endif
                        >
                            <td>

                            </td>
                            @if (auth()->user()->hasMultiplePerimeters())
                                <td>{{ $phone->perimeter->name }}</td>
                            @endif
                            <td>
                                <x-show-link :model="$phone" />
                            </td>
                            <td>
                                {{ $phone->type ?? '' }}
                            </td>
                            <td>
                                <?php
                                foreach (explode(" ", (string) $phone->attributes) as $attribute) {
                                    if (strlen(trim($attribute)) > 0) {
                                        echo "<span class='badge badge-info'>" . e($attribute) . "</span> ";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                {{ $phone->address_ip ?? '' }}
                            </td>
                            <td>
                                @if ($phone->site!==null)
                                    <x-show-link :model="$phone->site" />
                                @endif
                            </td>
                            <td>
                                @if ($phone->building!==null)
                                    <x-show-link :model="$phone->building" />
                                @endif
                            </td>
                            <td>
                                {!! $phone->description !!}
                            </td>
                            <td nowrap>
                                @can('phone_show')
                                    <a class="btn btn-xs btn-primary"
                                       href="{{ route('admin.phones.show', $phone->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @canEdit($phone)
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.phones.edit', $phone->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcanEdit

                                @can('phone_delete')
                                    <form action="{{ route('admin.phones.destroy', $phone->id) }}" method="POST"
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
        
        @include('partials.pagination-footer', ['paginator' => $phones])
</div>
    </div>
@endsection

@section('scripts')
    @parent
    <script @nonce>
        @include('partials.datatable', array(
            'id' => '#dataTable',
            'order' => auth()->user()->hasMultiplePerimeters() ? '[[2, "asc"]]' : '[[1, "asc"]]',
            'title' => trans("cruds.phone.title_singular"),
            'URL' => route('admin.phones.massDestroy'),
            'canDelete' => auth()->user()->can('phone_delete') ? true : false,
    'serverSidePagination' => true,
    'hiddenColumns' => ['perimeter', 'attributes', 'description'],
));
    </script>
@endsection
