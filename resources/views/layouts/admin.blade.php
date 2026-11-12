<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', trans('panel.page.untitled')) | {{ trans('panel.site_title') }}</title>
    <script>
        window._lang = {
            colvis:  "{{ trans('global.datatables.colvis') }}",
            copy:    "{{ trans('global.datatables.copy') }}",
            print:   "{{ trans('global.datatables.print') }}",
            delete:  "{{ trans('global.datatables.delete') }}",
            graphTooLarge: @json(trans('global.graph_too_large'))
        };
        window.mercatorGraph = {
            maxNodes: @json((int) config('mercator.parameters.max_nodes', 500))
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
    @if (\App\Support\TrashMode::active())
    <style>
        /* Mode corbeille : fond hachuré pour signaler que seuls les objets supprimés sont affichés */
        body.trash-mode #content-home {
            min-height: calc(100vh - 56px);
            background-color: #f3dede;
            background-image: repeating-linear-gradient(-45deg, transparent 0 18px, rgba(176, 42, 55, .06) 18px 36px);
        }
    </style>
    @endif
    <script>
    try {
        if (localStorage.getItem('sidebar_hidden') === 'true') {
            document.documentElement.classList.add('sidebar-preload-hidden');
        }
    } catch (_) {
        // Storage unavailable; no-op
    }
    </script>
</head>
<body class="{{ \App\Support\TrashMode::active() ? 'trash-mode' : '' }}">
    @include('partials.navbar')
    <div class="d-flex">
    @include('partials.sidebar')
        <div id="content-home" class="content flex-grow-1 p-3">
            @if(session('message'))
                <div class="row mb-2">
                    <div class="col-lg-12">
                        <div class="alert alert-success" role="alert">{{ session('message') }}</div>
                    </div>
                </div>
            @endif
            @if($errors->count() > 0)
                <div class="alert alert-danger">
                    <ul class="list-group-flush">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (\App\Support\TrashMode::active())
                @include('admin.trash._banner')
            @endif

            @yield('content')
        </div>
    </div>
    <script>
        (function() {
            const isHidden = localStorage.getItem('sidebar_hidden') === 'true';
            if (isHidden) {
                document.getElementById('sidebar').classList.add('sidebar-hidden');
                document.getElementById('content-home').classList.add('content-expanded');
            }
        })();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content-home');

            sidebar.classList.toggle('sidebar-hidden');
            content.classList.toggle('content-expanded');

            // Sauvegarder l'état
            const isHidden = sidebar.classList.contains('sidebar-hidden');
            localStorage.setItem('sidebar_hidden', isHidden);
        }
    </script>
    @yield('scripts')
    @if (\App\Support\TrashMode::active())
        @include('admin.trash._script')
    @endif
    <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
</body>
</html>
