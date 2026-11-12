@extends('layouts.admin')

@section('title')
    CVE List for {{ $cpe }}
@endsection

@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-default" onclick="history.back()">
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">

        CVE List for {{ $cpe }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>
                        </th>
                        <th>
                            CVE
                        </th>
                        <th>
                            Affected Version
                        </th>
                        <th>
                            Description
                        </th>
                        <th>
                            Date Published
                        </th>
                        <th>
                            Date Updated
                        </th>
                        <th>
                            Score
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cves as $cve)
                        <tr>
                            <td>
                            </td>
                            <td nowrap>
                                <a href="https://nvd.nist.gov/vuln/detail/{{ $cve->cveId }}">
                                {{ $cve->cveId }}
                                </a>
                            </td>
                            <td>
                                {{ $cve->version }}
                            </td>
                            <td>
                                {{ $cve->description }}
                            </td>
                            <td>
                                {{ $cve->datePublished }}
                            </td>
                            <td>
                                {{ $cve->dateUpdated }}
                            </td>
                            <td nowrap>
                                @if($cve->baseSeverity=="HIGH")
                                <span class="badge-high">
                                @elseif($cve->baseSeverity=="CRITICAL")
                                <span class="badge-critical">
                                @elseif($cve->baseSeverity=="MEDIUM")
                                <span class="badge-medium">
                                @elseif($cve->baseSeverity=="LOW")
                                <span class="badge-low">
                                @else
                                <span class="label label-default">
                                @endif
                                {{ $cve->baseScore }}
                                {{ $cve->baseSeverity }}
                                </span>
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
    'title' => "CVE",
    'URL' => null,
    'canDelete' => false
));
</script>
@endsection
