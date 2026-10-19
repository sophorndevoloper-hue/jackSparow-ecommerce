<x-guest-layout>
    <section class="auth-card">
        <a class="auth-brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-icon p-1" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.75), rgba(15, 23, 42, 0.95)) !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; border-radius: 10px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);">
                <img src="{{ asset('assets/images/brand/logo/jacksparrow-icon.png') }}" alt="JackSparrow Logo" style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 0 4px rgba(56, 189, 248, 0.35));">
            </span>
            <span><strong>JACK<span style="color: #38bdf8;">SPARROW</span></strong><small>B2B Computer Parts &bull; Staff Workspace</small></span>
        </a>
        <!-- <div class="auth-visual">
            <img src="{{ asset('assets/images/png/dasher-ui-bootstrap-5.jpg') }}" alt="adminHMD dashboard interface">
        </div> -->

        <form method="POST" action="{{ route('admin.login') }}" class="needs-validation">
            @csrf
            <div class="mb-4">
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
                <input id="email" class="form-control" type="email" name="email"
                    value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="email@example.com">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold" for="password">Security Password</label>
                <input class="form-control" id="password" type="password" name="password" required
                    autocomplete="current-password" placeholder="••••••••">
            </div>

            <div class="form-check mb-4 d-flex justify-content-between align-items-center line-height-1">
                <div>
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label small" for="rememberMe">Keep me signed in</label>
                </div>
                <div>
                    <a href="{{ route('admin.register') }}" class="text-muted small">
                        Don't have an account? Sign Up
                    </a>
                </div>
            </div>
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