<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', trans('panel.page.untitled')) | {{ trans('panel.site_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="d-flex flex-column justify-content-center align-items-center">
<div class="container">
    @if(session('message'))
        <div class="row mb-2">
            <div class="col-lg-12">
                <div class="alert alert-success" role="alert">{{ session('message') }}</div>
            </div>
        </div>
    @endif
    @if(session('info'))
        <div class="row mb-2">
            <div class="col-lg-12">
                <div class="alert alert-info" role="alert">{{ session('info') }}</div>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="row mb-2">
            <div class="col-lg-12">
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            </div>
        </div>
    @endif
    @yield("content")
</div>
@yield('scripts')
</body>
</html>
