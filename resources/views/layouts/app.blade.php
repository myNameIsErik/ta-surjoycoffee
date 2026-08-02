<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laporan Keuangan') &mdash; {{ config('app.name') }}</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --brand: #6f4e37; --brand-dark: #4b3624; }
        body { background: #f5f3f0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .navbar-brand { font-weight: 700; }
        .bg-brand { background-color: var(--brand) !important; }
        .text-brand { color: var(--brand) !important; }
        .btn-brand { background-color: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-brand:hover { background-color: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .stat-card { border-left: 4px solid var(--brand); }
        .table thead th { background: #faf7f4; font-size: .85rem; text-transform: uppercase; letter-spacing: .03em; }
        .nav-link.active { font-weight: 600; }
        .report-table tfoot { font-weight: 600; background: #faf7f4; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .card { box-shadow: none; }
        }
    </style>
    @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-brand no-print">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-shop me-1"></i> {{ config('app.name') }}
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('products.*') || request()->routeIs('stock.*') || request()->routeIs('categories.*') ? 'active' : '' }}" data-bs-toggle="dropdown" href="#"><i class="bi bi-box-seam me-1"></i>Persediaan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('products.index') }}"><i class="bi bi-box me-1"></i>Master Barang</a></li>
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('categories.index') }}"><i class="bi bi-tags me-1"></i>Kategori Barang</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('stock.index') }}"><i class="bi bi-arrow-left-right me-1"></i>Transaksi Stok</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('stock.create', ['type' => 'purchase']) }}"><i class="bi bi-box-arrow-in-down me-1 text-success"></i>Stok Masuk (Beli)</a></li>
                            <li><a class="dropdown-item" href="{{ route('stock.create', ['type' => 'sale']) }}"><i class="bi bi-cart-check me-1 text-primary"></i>Penjualan</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('consignees.*') || request()->routeIs('consignments.*') || request()->routeIs('consignment-products.*') ? 'active' : '' }}" data-bs-toggle="dropdown" href="#"><i class="bi bi-truck me-1"></i>Konsinyasi</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('consignment-products.index') }}"><i class="bi bi-box me-1"></i>Master Barang Konsinyasi</a></li>
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('consignees.index') }}"><i class="bi bi-shop-window me-1"></i>Penerima Konsinyasi</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('consignments.index') }}"><i class="bi bi-list-ul me-1"></i>Transaksi Konsinyasi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('consignments.create', ['type' => 'stock_in']) }}"><i class="bi bi-box-arrow-in-down me-1 text-success"></i>Stok Masuk (Konsinyasi)</a></li>
                            <li><a class="dropdown-item" href="{{ route('consignments.create', ['type' => 'send']) }}"><i class="bi bi-box-arrow-right me-1 text-warning"></i>Kirim Titipan</a></li>
                            <li><a class="dropdown-item" href="{{ route('consignments.create', ['type' => 'sold']) }}"><i class="bi bi-cash-coin me-1 text-primary"></i>Lapor Terjual</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('stock.report') || request()->routeIs('stock.sales-report') || request()->routeIs('stock.purchase-report') || request()->routeIs('consignments.position') ? 'active' : '' }}" data-bs-toggle="dropdown" href="#"><i class="bi bi-bar-chart me-1"></i>Laporan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('stock.report') }}"><i class="bi bi-clipboard-data me-1"></i>Posisi Stok</a></li>
                            <li><a class="dropdown-item" href="{{ route('stock.sales-report') }}"><i class="bi bi-cart-check me-1 text-primary"></i>Laporan Penjualan</a></li>
                            <li><a class="dropdown-item" href="{{ route('stock.purchase-report') }}"><i class="bi bi-box-arrow-in-down me-1 text-success"></i>Laporan Pembelian</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('consignments.position') }}"><i class="bi bi-truck me-1"></i>Posisi Konsinyasi</a></li>
                        </ul>
                    </li>
                @endauth
            </ul>
            <ul class="navbar-nav">
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                        <span class="badge bg-light text-dark ms-1">{{ auth()->user()->roleLabel() }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if (auth()->user()->isAdmin())
                            <li><a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people me-1"></i>Manajemen User</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Periksa input Anda:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="text-center text-muted py-3 small no-print">
    &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Aplikasi stockist distribusi
</footer>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
