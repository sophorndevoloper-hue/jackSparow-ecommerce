<x-guest-layout>
    <section class="auth-card">
        <a class="auth-brand" href="{{ route('admin.dashboard') }}">
            <span class="brand-icon"><i class="bi bi-cpu-fill" aria-hidden="true"></i></span>
            <span><strong>JackSparow TECH</strong><small>Admin Portal &bull; Staff Registration</small></span>
        </a>

        <form method="POST" action="{{ route('admin.register') }}" class="needs-validation">
            @csrf
            <div class="mb-4">
                <h1 class="h3 mb-1">Create Account</h1>
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

