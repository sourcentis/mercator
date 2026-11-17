@extends('layouts.admin')
@section('content')
    <form method="POST" action='{{ route("admin.bpmn.update", [$id]) }}' enctype="multipart/form-data" id="graph-form">
        @method('PUT')
        @csrf
        <input name='id' type='hidden' value='{{$id}}' id="id""/>
        <input name='class' type='hidden' value='2'/>
        <div class="card">
            <div class="card-header">
                BPMN Raw Edit
            </div>
            <div class="card-body">
                <div class="row">
                    <input name='name' type='text' value="{{ $name }}" id="name"/>
                </div>
                <div class="row">
                    <textarea name='content' id="textarea" rows="80">{{ $content }}</textarea>
                </div>
           </div>
        </div>
        <div class="form-group">
            <a id="btn-cancel" class="btn btn-default" href="{{ route('admin.bpmn.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
            <button id="btn-save" class="btn btn-danger" type="submit">
                {{ trans('global.save') }}
            </button>
        </div>
    </form>
@endsection
