@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Not Authorized - Pages')

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection


@section('content')
<!-- Not Authorized -->
<div class="container-xxl container-p-y">
    <div class="misc-wrapper">
        <h2 class="mb-2 mx-2">Nincs jogod az oldal megtekintéséhez</h2>
        <p class="mb-4 mx-2">Az oldal számodra nem elérhető vagy az adott folyamattal, annak aktuális státuszában nincsen teendőd</p>
        <a href="{{url('/dashboard')}}" class="btn btn-primary">Vissza a főoldalra</a>
        <div class="mt-5">
            <img src="{{asset('assets/img/illustrations/girl-with-laptop-'.$configData['style'].'.png')}}" alt="" width="450" class="img-fluid" data-app-light-img="illustrations/girl-with-laptop-light.png" data-app-dark-img="illustrations/girl-with-laptop-dark.png">
        </div>
    </div>
</div>
<!-- /Not Authorized -->
@endsection
