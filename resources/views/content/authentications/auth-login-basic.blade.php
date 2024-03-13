@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Login Basic - Pages')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/pages-auth.js'
])
@endsection

@section('content')
<div class="container-xxl">
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
    <!-- Register -->
    <div class="card">
        <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center">
            <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
            <span class="app-brand-text demo text-body fw-bold">{{config('variables.templateName')}}</span>
            </a>
        </div>
        <!-- /Logo -->

        <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email-username" placeholder="Add meg az email címed" autofocus value="{{ old('email-username') }}">
                @if($errors->has('email-username'))
                    @error('email-username')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                @endif
            </div>
            <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Jelszó</label>
                    <!--<a href="javascript:void(0);">
                        <small>Elfelejtetted a jelszavad?</small>
                    </a>-->
                </div>
                <div class="input-group input-group-merge">
                    <input type="password" id="password" class="form-control" name="password" placeholder="Add meg a jelszavad" aria-describedby="password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
            </div>
            <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">Bejelentkezés</button>
            </div>
        </form>
        </div>
    </div>
    <!-- /Register -->
    </div>
</div>
</div>
@endsection
