<x-guest-layout>
    <section class="auth-card">
        <a class="auth-brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-icon"><i class="bi bi-cpu-fill" aria-hidden="true"></i></span>
            <span><strong>JackSparow TECH</strong><small>Admin Portal &bull; Staff Workspace</small></span>
        </a>
        <div class="auth-visual">
            <img src="{{ asset('assets/images/png/dasher-ui-bootstrap-5.jpg') }}" alt="adminHMD dashboard interface">
        </div>

        <form method="POST" action="{{ route('admin.login') }}" class="needs-validation">
            @csrf
            <div class="mb-4">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace mb-2">guard: backend &bull; table: users</span>
                <h1 class="h3 mb-1">Admin Portal Sign In</h1>
                <p class="text-muted mb-0">Authorized personnel only. Enter your administrator credentials.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label small fw-bold" for="email">Admin Email Address</label>
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email', 'admin@ecommerce.test') }}" required autofocus autocomplete="username" placeholder="admin@ecommerce.test">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold" for="password">Security Password</label>
                <input class="form-control" id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                <label class="form-check-label small" for="rememberMe">Keep me signed in</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-shield-lock-fill me-1" aria-hidden="true"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="auth-footer text-center mt-3">
            <a href="{{ route('home') }}" class="text-muted small">&larr; Return to Customer Storefront</a>
        </div>
    </section>
</x-guest-layout>

