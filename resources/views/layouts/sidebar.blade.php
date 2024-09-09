<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu ">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/main/Logo-.png') }}" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/main/Logo-.png') }}" alt="" height="50">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/main/Logo-.png') }}" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/main/Logo-.png') }}" alt="" height="50">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>

            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('/') ? 'active' : '' }}"
                        href="{{ url('/') }}">
                        <i class='bx bx-home-alt-2'></i> <span>Tableau de bord</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#vol" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="vol">
                        <i class='bx bxs-plane-land'></i> <span>Les vols</span>
                    </a>
                    <div class="collapse menu-dropdown" id="vol">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link " href="{{ route('Vols.arrivee') }}">
                                    <span>Vol arrivee</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link " href="{{ route('vols.depart') }}">
                                    <span>Vol depart </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#airport" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="airport">
                        <i class='bx bxs-wrench'></i> <span>Programme</span>
                    </a>
                    <div class="collapse menu-dropdown" id="airport">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link " href="{{ route('programme.saisonnier') }}">
                                    <span>Saisonnier</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link " href="{{ route('programme.somaine') }}">
                                    <span>Semaine charger </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#database" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="database">
                        <i class='bx bx-data'></i> <span>Données</span>
                    </a>
                    <div class="collapse menu-dropdown" id="database">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link  {{ request()->routeIs('import.index') ? 'active' : '' }} "
                                    href="{{ route('import.index') }}">
                                    <span>Import</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="{{ route('export.index') }}">
                                    <span>Export</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="{{ route('frere.index') }}">
                                    <span>Vols frére</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="">
                        <i class="bx bx-wrench"></i> <span>Settings</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
