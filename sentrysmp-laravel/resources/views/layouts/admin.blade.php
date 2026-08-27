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
    <style>
        .admin-layout input:not([type=checkbox]):not([type=radio]):not([type=range]),
        .admin-layout select,
        .admin-layout textarea {
            padding: 7px 10px;
            margin-bottom: 0;
            border-radius: 8px;
            background: #1a1a1a;
            border: 1px solid #383838;
            font-size: 13px;
            line-height: 1.4;
        }
        .admin-layout input:not([type=checkbox]):not([type=radio]):not([type=range]):focus,
        .admin-layout select:focus,
        .admin-layout textarea:focus {
            border-color: #555;
            outline: none;
        }
        .admin-layout textarea {
            resize: vertical;
            min-height: 80px;
        }
    </style>
</head>
<body>

<!-- Mobile hamburger -->
<button class="admin-burger" id="admin-burger" aria-label="Toggle navigation">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar overlay backdrop (mobile) -->
<div class="admin-overlay" id="admin-overlay"></div>

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
            <div style="display:flex;align-items:center;gap:12px;">
                @hasSection('back_url')
                    <a href="@yield('back_url')" class="btn-admin btn-admin-secondary" style="padding:6px 10px;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                @endif
                <h1>@yield('title', 'Dashboard')</h1>
            </div>
            <span style="color:#666;font-size:13px;">Logged in as <strong style="color:#ccc;">{{ session('admin_username', 'Admin') }}</strong></span>
        </div>

        @if(session('success'))
            <div class="alert alert-success admin-alert" role="alert">
                <i class="bi bi-check-circle-fill" style="margin-right:6px;"></i>
                {{ session('success') }}
                <button type="button" class="admin-alert-close" onclick="this.closest('.admin-alert').remove()" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error admin-alert" role="alert">
                <i class="bi bi-exclamation-circle-fill" style="margin-right:6px;"></i>
                {{ $errors->first() }}
                <button type="button" class="admin-alert-close" onclick="this.closest('.admin-alert').remove()" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error admin-alert" role="alert">
                <i class="bi bi-exclamation-circle-fill" style="margin-right:6px;"></i>
                {{ session('error') }}
                <button type="button" class="admin-alert-close" onclick="this.closest('.admin-alert').remove()" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script>
(function () {
    var burger  = document.getElementById('admin-burger');
    var sidebar = document.getElementById('admin-sidebar');
    var overlay = document.getElementById('admin-overlay');

    function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('visible'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('visible'); }

    if (burger)  burger.addEventListener('click', function () { sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Auto-dismiss success alerts after 5 s
    document.querySelectorAll('.admin-alert.alert-success').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 420);
        }, 5000);
    });

    // Clickable table rows
    document.querySelectorAll('tr.admin-table-row-link[data-href]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('.no-row-click, a, button, form, input, select, textarea')) return;
            window.location.href = row.dataset.href;
        });
    });
})();
</script>

@stack('scripts')
</body>
</html>
