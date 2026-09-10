<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JackSparow TECH admin authentication portal">
    <title>{{ config('app.name', 'JackSparow TECH') }} - Admin Portal</title>

    <!-- Prevent Flash of Light Theme on Reload / Navigation (Instant Theme Initialization) -->
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
        } catch (e) {}
      })();
    </script>

    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendors/bootstrap-icons/bootstrap-icons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
</head>

<body class="auth-body">
    <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>
    <main class="auth-page">
        {{ $slot }}
    </main>

    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>
</body>

</html>