<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VP Management</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- DataTables (Bootstrap 5 skin) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

  <!-- Select2 + Bootstrap 5 theme -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

  <style>
    :root{ --app-green:#16a34a; --app-green-dark:#15803d; --app-green-50:#dcfce7; }
    body{
      background: radial-gradient(1200px 600px at 10% 0%, rgba(22,163,74,.08), transparent),
                  linear-gradient(180deg, rgba(220,252,231,.4), transparent 150px);
    }
    .navbar { backdrop-filter: blur(6px); }
    .navbar-brand, .nav-link { font-weight: 600; }
    .nav-link, .dropdown-item { display:inline-flex; align-items:center; gap:.5rem; }
    .btn-primary{ background-color: var(--app-green)!important; border-color: var(--app-green-dark)!important; }
    .btn-primary:hover{ background-color: var(--app-green-dark)!important; border-color: var(--app-green-dark)!important; }
    .btn-outline-success{ color: var(--app-green-dark)!important; border-color: var(--app-green)!important; }
    .btn-outline-success:hover{ background-color: var(--app-green)!important; color:#fff!important; }
    .card{ border-color:#e9f7ef!important; box-shadow:0 4px 16px rgba(22,163,74,.06); }
    .table>thead>tr>th{ background: var(--app-green-50); }
    .select2-container--bootstrap-5 .select2-selection{ min-height:calc(2.4rem + 2px); padding:.375rem .75rem; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm">
    <div class="container-xxl">
      <a class="navbar-brand text-success" href="{{ route('dashboard') }}">
        <i class="bi bi-buildings"></i> ভিপি লীজ ব্যবস্থাপনা
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold text-success' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('properties.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('properties.index') }}"><i class="bi bi-geo-alt"></i> সম্পত্তি</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('lessees.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('lessees.index') }}"><i class="bi bi-person-badge"></i> লিজগ্রহীতা</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('leases.index') ? 'active fw-bold text-success' : '' }}" href="{{ route('leases.index') }}"><i class="bi bi-journal-text"></i> লীজ ব্যবস্থাপনা</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.dues') ? 'active fw-bold text-success' : '' }}" href="{{ route('reports.dues') }}"><i class="bi bi-cash-coin"></i> বকেয়া তালিকা</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.collections') ? 'active fw-bold text-success' : '' }}" href="{{ route('reports.collections') }}"><i class="bi bi-journal-text"></i> আদায় রিপোর্ট</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('applications.*') ? 'active fw-bold text-success' : '' }}" href="{{ route('applications.index') }}"><i class="bi bi-journal-text"></i> আবেদন</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('ordersheet.*') ? 'active fw-bold text-success' : '' }}" href="{{ route('ordersheet.index') }}"><i class="bi bi-journal-text"></i> অর্ডার শীট</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container-xxl my-4">
    @if(session('ok'))  <div class="alert alert-success">{{ session('ok') }}</div>@endif
    @if(session('err')) <div class="alert alert-danger">{{ session('err') }}</div>@endif
    @if ($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $.fn.dataTable.ext.errMode = 'alert';
    $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }});
  </script>
</body>
</html>
