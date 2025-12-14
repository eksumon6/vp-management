<!-- One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. -->
@extends('layouts.app')
@section('content')
  <div class="d-flex align-items-center gap-2 mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-plus-circle"></i> নতুন লীজ এসাইন</h1>
    <a href="{{ route('leases.index') }}" class="btn btn-outline-success"><i class="bi bi-arrow-left"></i> তালিকায় ফিরুন</a>
  </div>
  <div class="card p-3">
    <form method="POST" action="{{ route('leases.store') }}">
      @include('leases._form', ['lease'=>null])
    </form>
  </div>
@endsection
