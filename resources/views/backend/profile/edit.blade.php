<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Heading & Breadcrumb -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-person-circle" aria-hidden="true"></i></span>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Admin Profile</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">My Account Profile</h1>
                    <p class="text-muted mb-0">Manage your administrator profile, contact information, avatar image, and security credentials.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Return to Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Please check the form for errors:</strong>
                <ul class="mb-0 mt-2 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Profile Hero Card -->
        <div class="panel p-4 my-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <div class="position-relative">
                    <img id="headerAvatarPreview" 
                         src="{{ $user->avatar_url }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-circle border border-3 border-primary-subtle shadow-sm object-fit-cover" 
                         style="width: 96px; height: 96px;">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2" title="Active Administrator"></span>
                </div>

                <div class="text-center text-md-start flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                        <h4 class="mb-0 fw-bold text-dark">{{ $profile->full_name ?: $user->name }}</h4>
                        @foreach($roles as $role)
                            <span class="badge {{ $role->name === 'superadmin' ? 'bg-danger' : ($role->name === 'admin' ? 'bg-primary' : 'bg-secondary') }}">
                                <i class="bi bi-shield-check me-1"></i> {{ ucfirst($role->name) }}
                            </span>
                        @endforeach

                        @if($user->is_approved)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-patch-check-fill me-1"></i> Approved Access
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="bi bi-hourglass-split me-1"></i> Pending Approval
                            </span>
                        @endif
                    </div>

                    <p class="text-muted small mb-2">
                        <i class="bi bi-envelope me-1"></i> {{ $user->email }}
                        @if($profile->designation)
                            <span class="mx-2">&bull;</span>
                            <i class="bi bi-briefcase me-1"></i> {{ $profile->designation }}
                        @endif
                        @if($profile->phone)
                            <span class="mx-2">&bull;</span>
                            <i class="bi bi-telephone me-1"></i> {{ $profile->phone }}
                        @endif
                    </p>

                    @if($profile->bio)
                        <p class="text-secondary small mb-0 fst-italic">"{{ $profile->bio }}"</p>
                    @endif
                </div>

                <div class="text-center text-md-end text-muted small">
                    <div>Member since: <strong>{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</strong></div>
                    <code class="d-block mt-1 font-monospace text-secondary">Guard: backend</code>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Personal Information & Avatar Upload -->
            <div class="col-12 col-lg-7 space-y-4">
                <div class="panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i> Personal Details &amp; Avatar
                        </h5>
                        <span class="badge bg-light-subtle text-muted border small">backend_profile</span>
                    </div>

                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Avatar File Upload with Live Preview -->
                        <div class="mb-4 p-3 border rounded bg-light-subtle">
                            <label class="form-label fw-bold small text-dark d-block">
                                <i class="bi bi-image me-1"></i> Profile Photo (Avatar)
                            </label>

                            <div class="d-flex align-items-center gap-3">
                                <img id="formAvatarPreview" 
                                     src="{{ $user->avatar_url }}" 
                                     alt="Preview" 
                                     class="rounded-circle border object-fit-cover shadow-sm" 
                                     style="width: 70px; height: 70px;">

                                <div class="flex-grow-1">
                                    <input type="file" 
                                           name="avatar" 
                                           id="avatarInput" 
                                           class="form-control form-control-sm @error('avatar') is-invalid @enderror" 
                                           accept="image/png,image/jpeg,image/jpg,image/webp">
                                    <div class="form-text small">
                                        Upload JPG, PNG, or WEBP (Max: 2MB). Stored securely in public storage.
                                    </div>
                                    @error('avatar')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label small fw-bold">Account Username <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="full_name" class="form-label small fw-bold">Full Display Name</label>
                                <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $profile->full_name) }}" placeholder="e.g. Captain Jack Sparrow">
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $profile->phone) }}" placeholder="+1 555-0199">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="designation" class="form-label small fw-bold">Job Title / Designation</label>
                                <input type="text" name="designation" id="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $profile->designation) }}" placeholder="e.g. Hardware Fleet Manager">
                                @error('designation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="address" class="form-label small fw-bold">Office / Physical Address</label>
                                <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $profile->address) }}" placeholder="e.g. Tech Bay 4, Silicon Pier">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="bio" class="form-label small fw-bold">Bio / About You</label>
                                <textarea name="bio" id="bio" rows="3" class="form-control @error('bio') is-invalid @enderror" placeholder="Write a short summary about your responsibilities...">{{ old('bio', $profile->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Save Profile Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Password Change & Security Roles -->
            <div class="col-12 col-lg-5 space-y-4">
                <!-- Update Password Card -->
                <div class="panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-shield-lock text-primary me-2"></i> Change Password
                        </h5>
                        <i class="bi bi-key text-muted fs-5"></i>
                    </div>

                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-bold">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label small fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>

                        <div class="pt-2 d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-primary fw-bold">
                                <i class="bi bi-lock me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Roles & Direct Permissions Readout -->
                <div class="panel p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-shield-check text-success me-2"></i> Access Privileges
                        </h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">Active</span>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">Assigned Roles</label>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($roles as $role)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-shield-shaded me-1"></i> {{ ucfirst($role->name) }}
                                </span>
                            @empty
                                <span class="text-muted small">No specific role assigned. Access relies on direct permissions.</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="small fw-bold text-muted text-uppercase d-block mb-1">
                            Permissions Available ({{ $permissions->count() }})
                        </label>
                        <div class="d-flex flex-wrap gap-1" style="max-height: 200px; overflow-y: auto;">
                            @forelse($permissions as $perm)
                                <span class="badge bg-light-subtle text-secondary border font-monospace" style="font-size: 11px;">
                                    {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-muted small">No active permissions found.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Avatar Preview Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const avatarInput = document.getElementById('avatarInput');
            const formAvatarPreview = document.getElementById('formAvatarPreview');
            const headerAvatarPreview = document.getElementById('headerAvatarPreview');

            if (avatarInput) {
                avatarInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            if (formAvatarPreview) formAvatarPreview.src = e.target.result;
                            if (headerAvatarPreview) headerAvatarPreview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</x-app-layout>

