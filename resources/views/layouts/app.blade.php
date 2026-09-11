<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'JackSparow TECH') }} - Admin Dashboard</title>

  <!-- Prevent Flash of Light Theme & Layout Shift on Reload (Instant Theme & Mini Sidebar Initialization) -->
  <style>
    html {
      scroll-behavior: smooth;
    }
    html[data-theme="dark"],
    html[data-bs-theme="dark"] {
      background-color: #0f172a !important;
      color-scheme: dark;
    }
    html[data-theme="dark"] body,
    html[data-bs-theme="dark"] body {
      background-color: #0f172a !important;
      color: #f1f5f9;
    }
    html[data-theme="light"],
    html[data-bs-theme="light"] {
      background-color: #f8fbff;
      color-scheme: light;
    }
    @media (min-width: 992px) {
      html.sidebar-mini .admin-sidebar {
        transform: translateX(-100%) !important;
        visibility: hidden;
      }
      html.sidebar-mini .admin-main {
        margin-left: 0 !important;
      }
    }
    @media print {
      html,
      html[data-theme="dark"],
      html[data-bs-theme="dark"],
      body,
      html[data-theme="dark"] body,
      html[data-bs-theme="dark"] body,
      .admin-shell,
      .admin-main,
      .dashboard-content {
        background-color: #ffffff !important;
        background: #ffffff !important;
        color: #000000 !important;
        color-scheme: light !important;
      }
      .admin-navbar,
      .admin-sidebar,
      .sidebar-backdrop,
      .admin-footer {
        display: none !important;
      }
    }
  </style>
  <script>
    (function () {
      try {
        var theme = localStorage.getItem('adminHMD.colorTheme');
        if (!theme) {
          theme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);

        var isMini = localStorage.getItem('adminHMD.sidebarMini') === 'true';
        if (isMini && window.matchMedia('(min-width: 992px)').matches) {
          document.documentElement.classList.add('sidebar-mini');
        }
      } catch (e) {}
    })();
  </script>

  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  @stack('styles')
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    @include('components.sidebar')

    <div class="admin-main">
      @include('layouts.navigation')

      <main class="dashboard-content">
        {{ $slot }}
      </main>

      @include('components.footer')
    </div>
  </div>

  @include('components.confirm-modal')

  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script src="{{ asset('assets/js/confirm-modal.js') }}"></script>
  @stack('scripts')
</body>
</html>
