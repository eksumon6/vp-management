@extends('layouts.app')
@section('content')
  <div class="d-flex align-items-center gap-2 mb-3">
    <h1 class="h4 text-success mb-0"><i class="bi bi-pencil-square"></i> লিজগ্রহীতা সম্পাদনা</h1>
    <a href="{{ route('lessees.index') }}" class="btn btn-outline-success">
      <i class="bi bi-arrow-left"></i> তালিকায় ফিরুন
    </a>
  </div>
  <div class="card p-3">
    <form method="POST" action="{{ route('lessees.update', $lessee->id) }}">
      @method('PUT')
      @include('lessees._form', ['lessee' => $lessee])
    </form>
  </div>
@endsection
