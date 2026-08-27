<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · @yield('title', 'Dashboard') · SentrySMP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar-header">
            <img src="{{ asset('images/logo.png') }}" alt="SentrySMP">
            <div class="admin-sidebar-title">Admin Panel</div>
        </div>

        <div class="admin-nav-section">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-heading">Shop</div>
            <a href="{{ route('admin.categories.index') }}" class="admin-nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> Categories
            </a>
            <a href="{{ route('admin.products.index') }}" class="admin-nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i> Products
            </a>
            <a href="{{ route('admin.commands.index') }}" class="admin-nav-item {{ request()->routeIs('admin.commands.*') ? 'active' : '' }}">
                <i class="bi bi-terminal-fill"></i> Commands
            </a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-heading">Delivery</div>
            <a href="{{ route('admin.command-queue.index') }}" class="admin-nav-item {{ request()->routeIs('admin.command-queue.*') ? 'active' : '' }}">
                <i class="bi bi-list-task"></i> Command Queue
            </a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-heading">Commerce</div>
            <a href="{{ route('admin.vouchers.index') }}" class="admin-nav-item {{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-fill"></i> Vouchers
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="admin-nav-item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-fill"></i> Transactions
            </a>
            <a href="{{ route('admin.settings.payment') }}" class="admin-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i> Payment Settings
            </a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-heading">Content</div>
            <a href="{{ route('admin.team.index') }}" class="admin-nav-item {{ request()->routeIs('admin.team.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Team
            </a>
        </div>

        <div class="admin-sidebar-footer">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn-admin btn-admin-danger" style="width:100%;justify-content:center;">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main content -->
    <div class="admin-content">
        <div class="admin-topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <span style="color:#666;font-size:13px;">Logged in as <strong style="color:#ccc;">{{ session('admin_username', 'Admin') }}</strong></span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
