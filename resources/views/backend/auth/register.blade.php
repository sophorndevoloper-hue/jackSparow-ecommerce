<x-guest-layout>
    <section class="auth-card">
        <a class="auth-brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-icon p-1" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.75), rgba(15, 23, 42, 0.95)) !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; border-radius: 10px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);">
                <img src="{{ asset('assets/images/brand/logo/jacksparrow-icon.png') }}" alt="JackSparrow Logo" style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 0 4px rgba(56, 189, 248, 0.35));">
            </span>
            <span><strong>JACK<span style="color: #38bdf8;">SPARROW</span></strong><small>B2B Computer Parts &bull; Staff Registration</small></span>
        </a>

        <form method="POST" action="{{ route('admin.register') }}" class="needs-validation">
            @csrf
            <div class="mb-4">
                <h1 class="h3 mb-1">Create Admin Account</h1>
                <p class="text-muted mb-0">Sign in to your admin workspace. New accounts require superadmin approval before access.</p>
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
                <label class="form-label small fw-bold" for="name">Full Name</label>
                <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="John Doe">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold" for="email">Admin Email Address</label>
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="email@example.com">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold" for="password">Password</label>
                <input class="form-control" id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold" for="password_confirmation">Confirm Password</label>
                <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i> Register Account
            </button>
        </form>

        <div class="auth-footer text-center mt-3">
            <a href="{{ route('admin.login') }}" class="text-muted small">Already have an account? Sign In</a>
        </div>
    </section>
</x-guest-layout>

