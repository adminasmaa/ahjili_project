<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{url('/')}}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/logo-dark.png') }}" alt="" height="30">
            </span>
        </a>
        <!-- Light Logo-->
        <a  href="{{url('/')}}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/logo-light.png') }}" alt="" height="40">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <!-- <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span >@lang('translation.menu')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span >@lang('translation.dashboards')</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarDashboards">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="dashboard-analytics" class="nav-link" >@lang('translation.analytics')</a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-crm" class="nav-link" >@lang('translation.crm')</a>
                            </li>
                            <li class="nav-item">
                                <a href="index" class="nav-link" >@lang('translation.ecommerce')</a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-crypto" class="nav-link" >@lang('translation.crypto')</a>
                            </li>
                            <li class="nav-item">
                                <a href="dashboard-projects" class="nav-link" >@lang('translation.projects')</a>
                            </li>
                        </ul>
                    </div>
                </li>


            </ul> -->

            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span >@lang('translation.user_app')</span></li>

                <li class="nav-item">
                    <a class="nav-link" href="{{url('/admin/dashboard')}}"   >
                        <i class="mdi mdi-home-account"></i> <span >Dashboard</span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="mdi mdi-account-group"></i> <span >@lang('translation.user')</span>
                    </a>
                    @if (auth()->user()->hasPermission('read_users'))

                    <div class="collapse menu-dropdown {{showdropdownMenu(['users','users?status=active','users?status=ban','users?status=block'])}}" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                                <a href="{{route('admin.users.index')}}" class="nav-link {{activeRoute(route('admin.users.index'))}}" >@lang('translation.users.all')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.users.index',['status' => 'active'])}}" class="nav-link {{activeRoutechk(route('admin.users.index',['status' => 'active']))}}" >@lang('translation.users.active')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.users.index',['status' => 'ban'])}}" class="nav-link {{activeRoutechk(route('admin.users.index',['status' => 'ban']))}}" >@lang('translation.users.ban')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.users.index',['status' => 'block'])}}" class="nav-link {{activeRoutechk(route('admin.users.index',['status' => 'block']))}}" >@lang('translation.users.block')</a>
                            </li>
                        </ul>
                    </div>

                    @endif
                </li> <!-- end Dashboard Menu -->


                <li class="nav-item">
                    <a class="nav-link menu-link" href="#mangements" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="mdi mdi-account-group"></i> <span >@lang('site.mangements')</span>
                    </a>

                    @if (auth()->user()->hasPermission('read_roles'))

                    <div class="collapse menu-dropdown" id="mangements">
                        <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                                <a href="{{route('admin.usermangements.index')}}" class="nav-link {{activeRoute(route('admin.usermangements.index'))}}" >@lang('translation.usermangements')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.roles.index')}}" class="nav-link {{activeRoutechk(route('admin.roles.index'))}}" >@lang('translation.roles')</a>
                            </li>

                        </ul>
                    </div>
                    @endif
                </li> <!-- end Dashboard Menu -->

                @if (auth()->user()->hasPermission('read_helps'))

                <li class="nav-item">
                    <a class="nav-link" href="{{route('admin.helps.index')}}"   >
                        <i class="mdi mdi-help-circle-outline"></i> <span >@lang('translation.helps')</span>
                    </a>
                </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link" href="{{route('admin.notifications.index')}}"   >
                        <i class="mdi mdi-notification-clear-all"></i> <span >@lang('translation.notifications')</span>
                    </a>
                </li>


{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{route('admin.usermangements.index')}}"   >--}}
{{--                        <i class="mdi mdi-account-group"></i> <span >@lang('translation.usermangements')</span>--}}
{{--                    </a>--}}
{{--                </li>--}}
{{--                <li class="nav-item">--}}
{{--                    <a class="nav-link" href="{{route('admin.roles.index')}}"   >--}}
{{--                        <i class="mdi mdi-account-settings"></i> <span >@lang('translation.roles')</span>--}}
{{--                    </a>--}}
{{--                </li>--}}

            </ul>

            @if (auth()->user()->hasPermission('read_posts'))

            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span >@lang('translation.user_app_post')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarPosts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="mdi mdi-post-outline"></i> <span >@lang('translation.post')</span>
                    </a>
                    <div class="collapse menu-dropdown {{showdropdownMenu(['posts','post-status/active','post-status/block','post-status/ban'])}}" id="sidebarPosts">
                        <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                                <a href="{{route('admin.posts.index')}}" class="nav-link {{activeRoute(route('admin.posts.index'))}}" >@lang('translation.user_posts.all')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.posts.status','active')}}" class="nav-link {{activeRoutechk(route('admin.posts.status','active'))}}" >@lang('translation.users.active')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.posts.status','block')}}" class="nav-link {{activeRoutechk(route('admin.posts.status','block'))}}" >@lang('translation.users.block')</a>
                            </li>
                            <li class="nav-item active">
                                <a href="{{route('admin.posts.status','ban')}}" class="nav-link {{activeRoutechk(route('admin.posts.status','ban'))}}"  >@lang('translation.users.ban')</a>
                            </li>
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->


            </ul>

            @endif
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span >@lang('translation.Report Abuse')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarReportAbuse" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="mdi mdi-post-outline"></i> <span >@lang('translation.Report Abuse')</span>
                    </a>
                    <div class="collapse menu-dropdown {{showdropdownMenu(['report-messages','report-abuse-posts'])}}" id="sidebarReportAbuse">
                        <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                                 <a href="{{route('admin.report-messages.index')}}" class="nav-link {{activeRoute(route('admin.report-messages.index'))}}" >@lang('translation.Report Messages')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.report-abuse-posts.index')}}" class="nav-link {{activeRoute(route('admin.report-abuse-posts.index'))}}" >@lang('translation.Report Abuse Posts')</a>
                            </li>
                        </ul>
                    </div>
                </li> <!-- end Dashboard Menu -->


            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
