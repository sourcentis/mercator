<nav class="navbar navbar-expand fixed-top">
    <div class="container-fluid">
        <button id="toggle-sidebar-btn" class="btn toggle-sidebar-btn" type="button"><i class="bi bi-list"></i></button>
        <a class="navbar-brand d-flex justify-content-center"
           href="/admin">{{ (env('APP_NAME') === null) || (env('APP_NAME') === "Laravel") ? "Mercator" : env('APP_NAME') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-3">
                @php
                    use App\Models\Cartographer;
                    $hasExploreAccess = Gate::allows('explore_access')
                        || !empty(session('cartographer_permissions', []));
                @endphp
                @if($hasExploreAccess)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="menu1" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">{{ trans('panel.menu.views') }}</a>
                    <ul class="dropdown-menu" aria-labelledby="menu1">
                        @if(Cartographer::canAccessAny([\App\Models\DataProcessing::class]))
                            <li><a class="dropdown-item" href="/admin/report/gdpr">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.gdpr') }}</a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\Entity::class, \App\Models\Relation::class]))
                            <li><a class="dropdown-item" href="/admin/report/ecosystem">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.ecosystem') }}</a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\MacroProcessus::class, \App\Models\Process::class, \App\Models\Activity::class, \App\Models\Operation::class, \App\Models\Task::class, \App\Models\Actor::class, \App\Models\Information::class]))
                            <li><a class="dropdown-item" href="/admin/report/information_system">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.information_system') }}</a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\ApplicationBlock::class, \App\Models\Application::class, \App\Models\ApplicationService::class, \App\Models\ApplicationModule::class, \App\Models\Database::class]))
                            <li><a class="dropdown-item" href="/admin/report/applications">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.applications') }}</a>
                            </li>
                            @if(Cartographer::canAccessAny([\App\Models\ApplicationFlow::class]))
                                <li><a class="dropdown-item" href="/admin/report/application_flows">
                                        <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.application_flows') }}
                                    </a>
                                </li>
                            @endif
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\ZoneAdmin::class, \App\Models\Annuaire::class, \App\Models\ForestAd::class, \App\Models\Domain::class]))
                            <li><a class="dropdown-item" href="/admin/report/administration">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.administration') }}</a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\Network::class, \App\Models\Subnetwork::class, \App\Models\Gateway::class, \App\Models\Router::class, \App\Models\NetworkSwitch::class, \App\Models\Cluster::class, \App\Models\LogicalServer::class, \App\Models\Certificate::class, \App\Models\Container::class]))
                            <li><a class="dropdown-item" href="/admin/report/logical_infrastructure">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.logical_infrastructure') }}
                                </a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\Site::class, \App\Models\Building::class, \App\Models\Bay::class, \App\Models\PhysicalServer::class, \App\Models\PhysicalSwitch::class, \App\Models\PhysicalRouter::class, \App\Models\Workstation::class, \App\Models\StorageDevice::class, \App\Models\Peripheral::class, \App\Models\Phone::class, \App\Models\WifiTerminal::class, \App\Models\PhysicalSecurityDevice::class]))
                            <li><a class="dropdown-item" href="/admin/report/physical_infrastructure">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.physical_infrastructure') }}
                                </a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\Zone::class]))
                            <li><a class="dropdown-item" href="/admin/report/security_zones">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.security_zones') }}
                                </a>
                            </li>
                        @endif
                        @if(Cartographer::canAccessAny([\App\Models\PhysicalLink::class, \App\Models\Wan::class, \App\Models\Man::class, \App\Models\Lan::class]))
                            <li><a class="dropdown-item" href="/admin/report/network_infrastructure">
                                    <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.network_infrastructure') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="menu2" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">{{ trans('panel.menu.preferences') }}</a>
                    <ul class="dropdown-menu" aria-labelledby="menu2">
                        <li><a class="dropdown-item" href="/profile/preferences">
                                <i class="bi bi-gear-fill"></i>{{ trans('panel.menu.options') }}</a>
                        </li>
                        @can('profile_password_edit')
                            <li><a class="dropdown-item" href="/profile/password">
                                    <i class="bi bi-person-fill-lock"></i>{{ trans('panel.menu.password') }}</a>
                            </li>
                        @endcan
                        @if(session('is_cartographer'))
                            <li><a class="dropdown-item" href="{{ route('admin.cartographer.list') }}">
                                    <i class="bi bi-pin-map-fill"></i>{{ trans('panel.menu.cartographer') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
                @can('tools_access')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menu3" role="button" data-bs-toggle="dropdown"
                           aria-expanded="false">{{ trans('panel.menu.tools') }}</a>
                        <ul class="dropdown-menu" aria-labelledby="menu3">

                            @can('graph_access')
                                <li><a class="dropdown-item" href="/admin/bpmn">
                                    <i class="bi bi-briefcase-fill"></i>BPMN</a>
                                </li>

                                <li><a class="dropdown-item" href="/admin/graphs">
                                        <i class="bi bi-map-fill"></i>{{ trans('cruds.graph.title') }}</a>
                                </li>
                            @endcan
                            {{--
                            @can('cairn_access')
                                <li><a class="dropdown-item" href="/admin/cairn/create">
                                        <i class="bi bi-diagram-3-fill"></i>{{ trans('panel.menu.cairn') }}</a>
                                </li>
                            @endcan
                            --}}
                            @can('explore_access')
                                <li><a class="dropdown-item" href="/admin/report/explore">
                                        <i class="bi bi-globe2"></i>{{ trans('panel.menu.explore') }}</a>
                                </li>
                                <li><a class="dropdown-item" href="/admin/report/dependency">
                                    <i class="bi bi-diagram-2"></i>{{ trans('panel.menu.dependency') }}</a>
                                </li>
                            @endcan
                            @can('query_access')
                                <li><a class="dropdown-item" href="/admin/queries">
                                    <i class="bi bi-binoculars-fill"></i>{{ trans('cruds.tools.query.title_short') }}</a>
                                </li>
                            @endcan
                            @can('reports_access')
                                <li><a class="dropdown-item" href="/admin/doc/report">
                                        <i class="bi bi-file-earmark-richtext-fill"></i>{{ trans('panel.menu.reports') }}</a>
                                </li>
                                <li><a class="dropdown-item" href="/admin/doc/lists">
                                        <i class="bi bi-file-earmark-spreadsheet-fill"></i>{{ trans('panel.menu.lists') }}</a>
                                </li>
                                @if (\App\Support\MonarcSettings::enabled())
                                    <li><a class="dropdown-item" href="/admin/monarc">
                                            <img src="/images/monarc.png" alt="" width="18" height="18" class="me-3 align-middle">{{ trans('cruds.configuration.monarc.title_short') }}</a>
                                    </li>
                                @endif
                            @endcan
                        </ul>
                    </li>
                @endcan
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="menu4" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">{{ trans('panel.menu.help') }}</a>
                    <ul class="dropdown-menu" aria-labelledby="menu4">

                        <li><a class="dropdown-item" href="/admin/doc/schema">
                                <i class="bi bi-database-fill"></i>{{ trans('panel.menu.schema') }}</a>
                        </li>
                        <li><a class="dropdown-item" href="/admin/doc/guide">
                                <i class="bi bi-book-fill"></i>{{ trans('panel.menu.guide') }}</a>
                        </li>
                        @if (Auth::user()->language==='fr')
                        <li><a class="dropdown-item" target="_blank" href="https://sourcentis.github.io/mercator/fr/">
                                <i class="bi bi-book-fill"></i>{{ trans('panel.menu.doc') }}</a>
                        </li>
                        @else
                        <li><a class="dropdown-item" target="_blank" href="https://sourcentis.github.io/mercator/">
                                <i class="bi bi-book-fill"></i>{{ trans('panel.menu.doc') }}</a>
                        </li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('admin.doc.info') }}">
                                <i class="bi bi-credit-card-2-front-fill"></i>{{ trans('panel.menu.info') }}</a>
                        </li>
                        <li><a class="dropdown-item" href="/admin/doc/about">
                                <i class="bi bi-info-circle-fill"></i>{{ trans('panel.menu.about') }}</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
