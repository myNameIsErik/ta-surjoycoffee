<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; {{ config('app.name') }}</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #6f4e37, #4b3624); min-height: 100vh; display: flex; align-items: center; }
        .card { border: 0; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
        .btn-brand { background-color: #6f4e37; border-color: #6f4e37; color: #fff; }
        .btn-brand:hover { background-color: #4b3624; color: #fff; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4">
                <div class="text-center mb-3">
                    <h3><i class="bi bi-cup-hot-fill text-brand"></i></h3>
                    <h5 class="mb-0">{{ config('app.name') }}</h5>
                    <small class="text-muted">Aplikasi Laporan Keuangan Cafe</small>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger small">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', 'admin@cafe.test') }}" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" value="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <button class="btn btn-brand w-100"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</button>
                </form>
                <div class="mt-3 text-center text-muted small">
                    Default: <code>admin@cafe.test</code> / <code>password</code>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
