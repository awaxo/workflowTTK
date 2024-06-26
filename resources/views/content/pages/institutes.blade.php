@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Intézmények')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/css/app.css'
])
@endsection

@section('content')
    <h4 class="py-3 mb-4">Intézetek</h4>

    <div class="row g-5">
        @foreach($institutes as $institute)
            <div class="col-lg-3 col-sm-4 col-6 mb-4">
                <a href="#">
                <div class="card card-border-shadow-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-buildings"></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $institute->group_level }}</h4>
                        </div>
                        <p class="mb-1">{{ $institute->name }}</p>
                        <p class="mb-0">
                            <span class="fw-medium me-1">{{ $institute->workgroup_count }}</span>
                            <small class="text-muted">csoport</small>
                        </p>
                    </div>
                </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
