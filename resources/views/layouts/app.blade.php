<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logo/jacksparrow-icon.png') }}">
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
      body.sidebar-open {
        overflow: auto !important;
      }
      .sidebar-backdrop,
      body.sidebar-open .sidebar-backdrop {
        display: none !important;
        pointer-events: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
      }
    }
    body:not(.modal-open) .modal-backdrop {
      display: none !important;
      pointer-events: none !important;
      visibility: hidden !important;
      opacity: 0 !important;
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

    document.addEventListener('DOMContentLoaded', function () {
      if (window.matchMedia('(min-width: 992px)').matches) {
        document.body.classList.remove('sidebar-open');
      }
      var backdrop = document.querySelector('.sidebar-backdrop');
      if (backdrop) {
        backdrop.addEventListener('click', function () {
          document.body.classList.remove('sidebar-open');
        });
      }
      if (!document.body.classList.contains('modal-open')) {
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
      }
    });
    window.addEventListener('resize', function () {
      if (window.matchMedia('(min-width: 992px)').matches) {
        document.body.classList.remove('sidebar-open');
      }
    });
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

  <!-- Global Admin Toast Notification Container -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;" id="adminToastContainer"></div>

  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script src="{{ asset('assets/js/confirm-modal.js') }}"></script>
  <script>
    window.adminNotify = function (message, type = 'info', title = '') {
      var container = document.getElementById('adminToastContainer');
      if (!container) return;

      var bgClass = {
        'success': 'bg-success text-white',
        'error': 'bg-danger text-white',
        'danger': 'bg-danger text-white',
        'warning': 'bg-warning text-dark',
        'info': 'bg-primary text-white'
      }[type] || 'bg-dark text-white';

      var iconClass = {
        'success': 'bi-check-circle-fill',
        'error': 'bi-exclamation-triangle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
      }[type] || 'bi-bell-fill';

      var defaultTitle = {
        'success': 'Success',
        'error': 'Attention Required',
        'danger': 'Attention Required',
        'warning': 'Notice',
        'info': 'Information'
      }[type] || 'Notification';

      var toastEl = document.createElement('div');
      toastEl.className = 'toast align-items-center border-0 shadow-lg mb-2 show';
      toastEl.setAttribute('role', 'alert');
      toastEl.setAttribute('aria-live', 'assertive');
      toastEl.setAttribute('aria-atomic', 'true');
      toastEl.innerHTML = `
        <div class="d-flex ${bgClass} rounded-3 p-2">
          <div class="toast-body d-flex align-items-center gap-2 py-1 px-2">
            <i class="bi ${iconClass} fs-5 flex-shrink-0"></i>
            <div>
              <strong class="d-block small" style="letter-spacing: 0.02em;">${title || defaultTitle}</strong>
              <span class="small opacity-95">${message}</span>
            </div>
          </div>
          <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      `;

      container.appendChild(toastEl);
      if (window.bootstrap && window.bootstrap.Toast) {
        var bsToast = new window.bootstrap.Toast(toastEl, { delay: 4500 });
        bsToast.show();
      }
      toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });
      setTimeout(function () {
        if (toastEl.parentNode) {
          toastEl.remove();
        }
      }, 5000);
    };
  </script>
  @stack('scripts')
</body>
</html>
