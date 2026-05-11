<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('pageTitle')</title>
    <link rel="stylesheet" href="{{ asset('src/css/style.css') }}">
    <!-- Using Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <!-- Main Container -->
    <div class="app-container" id="appMainContainer">

        <!-- Sidebar Navigation -->
        <aside class="sidebar ">
            <div class="logo">
                <i class="fas fa-handshake"></i>
                <span>SimpleCRM</span>
            </div>
            <nav class="nav-menu">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}" data-section="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.customers') }}"
                    class="nav-item {{ Route::is('admin.customers') ? 'active' : '' }}" data-section="customers">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="{{ route('admin.leads') }}" class="nav-item {{ Route::is('admin.leads') ? 'active' : '' }}"
                    data-section="leads">
                    <i class="fas fa-user-tie"></i>
                    <span>Leads</span>
                </a>
                <a href="{{ route('admin.tasks') }}" class="nav-item {{ Route::is('admin.tasks') ? 'active' : '' }}"
                    data-section="tasks">
                    <i class="fas fa-tasks"></i>
                    <span>Tasks & Follow-Ups</span>
                </a>
                <a href="{{ route('admin.profile') }}" class="nav-item {{ Route::is('admin.profile') ? 'active' : '' }}"
                    id="sidebarProfileLink" data-section="profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile Settings</span>
                </a>
                @if(auth()->user()->role != 'agent')
                <!-- User Management Dropdown Sub-menu Section -->
                <div class="nav-dropdown-wrapper">
                    <a href="#" class="nav-item" id="sidebarUsersLink"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 14px;">
                            <i class="fas fa-users-cog"></i>
                            <span class="nav-label-text">User Management</span>
                        </span>
                        <i class="fas fa-chevron-down submenu-caret nav-label-text"
                            style="font-size: 11px; transition: transform 0.2s ease;"></i>
                    </a>
                    <div class="sidebar-submenu {{ Route::is('admin.addUserForm') || Route::is('admin.allUsers') ? 'open' : '' }}"" id="usersSubmenu">
                        <a href="{{ route('admin.allUsers') }}" class="nav-item sub-nav-item {{ Route::is('admin.allUsers') ? 'active' : '' }}"" data-section="users" data-view="all"
                            style="padding: 10px 14px; font-size: 13px; border-radius: 8px;">
                            <i class="fas fa-list" style="font-size: 12px; width: 18px;"></i>
                            <span class="nav-label-text">All Users</span>
                        </a>
                        <a href="{{ route('admin.addUserForm') }}" class="nav-item sub-nav-item {{ Route::is('admin.addUserForm') ? 'active' : '' }}"" data-section="users" data-view="add"
                            style="padding: 10px 14px; font-size: 13px; border-radius: 8px;">
                            <i class="fas fa-user-plus" style="font-size: 12px; width: 18px;"></i>
                            <span class="nav-label-text">Add Users</span>
                        </a>
                    </div>
                </div>
                @endif
                <a href="{{ route('admin.logout') }}" class="nav-item" id="sidebarLogoutLink"
                    onclick="event.preventDefault();document.getElementById('logout-form').submit();"
                    style="color: var(--danger); margin-top: 16px; border-top: 1px solid var(--border-color); border-radius: 0; padding-top: 16px;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                </a>
                <form action="{{ route('admin.logout') }}" id="logout-form" method="post">
                    @csrf
                </form </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-nav">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <!-- Sidebar Collapse Toggle Button -->
                    <button class="theme-toggle" id="sidebarToggleBtn" title="Collapse / Expand Sidebar"
                        style="margin-right: 4px;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Search customers, leads...">
                    </div>
                </div>
                <div class="nav-right">
                    <!-- Theme Toggle Button -->
                    <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                        <i class="fas fa-moon"></i>
                    </button>
                    @livewire('admin.top-user-info')
                </div>
            </header>

            <!-- Dynamic Content Section -->
            <section class="content-section">
                @yield('content')
            </section>

        </main>
    </div>
    <!-- Toast Container for Notifications -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Load JavaScript -->
    <script src="{{ asset('src/js/main.js') }}"></script>

    {{-- Show notification if session has 'notification' data --}}
    @if(session('notification'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                showNotification(
                    @json(session('notification.type')),
                    @json(session('notification.message'))
                );

            });
        </script>
    @endif
    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('notify', (event) => {

                showNotification(
                    event.type,
                    event.message
                );

            });

        });
    </script>
    @stack('scripts')

</body>

</html>