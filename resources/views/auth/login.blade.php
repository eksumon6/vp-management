@extends('layouts.app')

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card p-4 shadow-sm">
        <h1 class="h5 text-success mb-3"><i class="bi bi-box-arrow-in-right"></i> লগইন করুন</h1>
        <form method="POST" action="{{ route('login.attempt') }}" class="d-grid gap-3">
          @csrf
          <div>
            <label class="form-label">ইমেইল</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
          </div>
          <div>
            <label class="form-label">পাসওয়ার্ড</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">আমাকে মনে রাখুন</label>
          </div>
          <button class="btn btn-primary" type="submit"><i class="bi bi-shield-lock"></i> প্রবেশ করুন</button>
        </form>
      </div>
    </div>
  </div>
@endsection
