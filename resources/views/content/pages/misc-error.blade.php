@php
  $customizerHidden = 'customizer-hide';
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')

@section('page-style')
<!-- Page -->
@vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection


@section('content')
<!-- Error -->
<div class="container-xxl container-p-y">
  <div class="misc-wrapper">
    <h2 class="mb-2 mx-2">Az oldal nem található :(</h2>
    <p class="mb-4 mx-2">Ajaj! 😖 A kért URL nem elérhető a szerveren.</p>
    <a href="{{url('/folyamatok')}}" class="btn btn-primary">Vissza a folyamatok oldalra</a>
    <div class="mt-3">
      <img src="{{asset('assets/img/illustrations/page-misc-error-'.$configData['style'].'.png')}}" alt="page-misc-error-light" width="500" class="img-fluid" data-app-dark-img="illustrations/page-misc-error-dark.png" data-app-light-img="illustrations/page-misc-error-light.png">
    </div>
  </div>
</div>
<!-- /Error -->
@endsection
