@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Új felvételi kérelem')

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

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
  'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
    @vite('resources/assets/js/form-wizard-numbered.js')
    @vite('resources/assets/js/form-basic-inputs.js')
@endsection

@section('content')
    <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Új folyamat /</span> Felvételi kérelem
    </h4>

    <div class="row">
        <!-- Vertical Wizard -->
        <div class="col-12 mb-4">
            <div class="bs-stepper wizard-vertical vertical mt-2">
            <div class="bs-stepper-header">
                <div class="step" data-target="#data-section-1">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label mt-1">
                        <span class="bs-stepper-title">Alapadatok</span>
                        <span class="bs-stepper-subtitle">Alapadatok megadása</span>
                        </span>
                    </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#data-section-2">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label mt-1">
                        <span class="bs-stepper-title">Bérelemek</span>
                        <span class="bs-stepper-subtitle">Bérelemek megadása</span>
                        </span>
                    </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#data-section-3">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label mt-1">
                        <span class="bs-stepper-title">Munkaidő</span>
                        <span class="bs-stepper-subtitle">Munkaidő megadása</span>
                        </span>
                    </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#data-section-4">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">4</span>
                        <span class="bs-stepper-label mt-1">
                        <span class="bs-stepper-title">Eszközök</span>
                        <span class="bs-stepper-subtitle">Eszközök, hozzáférések megadása</span>
                        </span>
                    </button>
                </div>
                <div class="line"></div>
                <div class="step" data-target="#data-section-5">
                    <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">5</span>
                        <span class="bs-stepper-label mt-1">
                        <span class="bs-stepper-title">Dokumentumok</span>
                        <span class="bs-stepper-subtitle">Dokumentumok feltöltése</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="bs-stepper-content">
                <form onSubmit="return false">
                    <!-- Data section 1 -->
                    <div id="data-section-1" class="content">
                        <div class="content-header mb-3">
                        <h6 class="mb-0">Alapadatok</h6>
                        <small>Add meg az alapadatokat</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="last-name">Vezetéknév</label>
                                <input type="text" id="last-name" class="form-control" placeholder="Teszt" />
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="first-name">Keresztnév</label>
                                <input type="email" id="first-name" class="form-control" placeholder="János" />
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="middle-name">Utónév</label>
                                <input type="email" id="middle-name" class="form-control" />
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="job_ad_exists">
                                    <label class="form-check-label" for="job_ad_exists">Felvétel álláshirdetéssel történt</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="has_prior_employment">
                                    <label class="form-check-label" for="has_prior_employment">Volt munkajogviszonya a Kutatóközponttal</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="has_current_volunteer_contract">
                                    <label class="form-check-label" for="has_current_volunteer_contract">Van önkéntes szerződéses jogviszonya a Kutatóközponttal</label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="applicants_female_count" class="form-label">Álláshirdetésre jelentkezett nők száma</label>
                                <input class="form-control" type="number" value="18" id="applicants_female_count" />
                            </div>
                            <div class="col-sm-4">
                                <label for="applicants_male_count" class="form-label">Álláshirdetésre jelentkezett férfiak száma</label>
                                <input class="form-control" type="number" value="18" id="applicants_male_count" />
                            </div>
                            <div class="col-sm-4">
                                <label for="citizenship_id" class="form-label">Állampolgárság</label>
                                <select class="form-select" id="citizenship_id">
                                    <option value="1" selected>Magyar</option>
                                    <option value="2">EGT tagállambeli</option>
                                    <option value="3">Harmadik országbeli</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="workgroup_id_1" class="form-label">Csoport 1</label>
                                <select class="form-select" id="workgroup_id_1">
                                    <option value="1" selected>Első</option>
                                    <option value="2">Második</option>
                                    <option value="3">Harmadik</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="workgroup_id_2" class="form-label">Csoport 2</label>
                                <select class="form-select" id="workgroup_id_2">
                                    <option value="1" selected>Első</option>
                                    <option value="2">Második</option>
                                    <option value="3">Harmadik</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-1">Tovább</span>
                                    <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Data section 2 -->
                    <div id="data-section-2" class="content">
                        <div class="content-header mb-3">
                        <h6 class="mb-0">Bérelemek</h6>
                        <small>Add meg a bérelemeket</small>
                        </div>
                        <div class="row g-3">
                            <h7 class="mb-0">Alapbér</h7>
                            <div class="col-sm-6">
                                <label for="base_salary_cost_center_1" class="form-label">Költséghely 1</label>
                                <select class="form-select" id="base_salary_cost_center_1">
                                    <option value="1" selected>Első költséghely</option>
                                    <option value="2">Második költséghely</option>
                                    <option value="3">Harmadik költséghely</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_monthly_gross_1" class="form-label">Havi bruttó bér 1</label>
                                <input class="form-control" type="number" value="" id="base_salary_monthly_gross_1" />
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_cost_center_2" class="form-label">Költséghely 2</label>
                                <select class="form-select" id="base_salary_cost_center_2">
                                    <option selected>Válassz költséghelyet</option>
                                    <option value="1">Első költséghely</option>
                                    <option value="2">Második költséghely</option>
                                    <option value="3">Harmadik költséghely</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_monthly_gross_2" class="form-label">Havi bruttó bér 2</label>
                                <input class="form-control" type="number" value="" id="base_salary_monthly_gross_2" />
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_cost_center_3" class="form-label">Költséghely 3</label>
                                <select class="form-select" id="base_salary_cost_center_3">
                                    <option selected>Válassz költséghelyet</option>
                                    <option value="1">Első költséghely</option>
                                    <option value="2">Második költséghely</option>
                                    <option value="3">Harmadik költséghely</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_monthly_gross_3" class="form-label">Havi bruttó bér 3</label>
                                <input class="form-control" type="number" value="" id="base_salary_monthly_gross_3" />
                            </div>

                            <hr class="my-4" />

                            <h7 class="mb-0 mt-0">Egészségügyi pótlék</h7>
                            <div class="col-sm-6">
                                <label for="health_allowance_cost_center_4" class="form-label">Költséghely 4</label>
                                <select class="form-select" id="health_allowance_cost_center_4">
                                    <option selected>Válassz költséghelyet</option>
                                    <option value="1">Első költséghely</option>
                                    <option value="2">Második költséghely</option>
                                    <option value="3">Harmadik költséghely</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="health_allowance_monthly_gross_4" class="form-label">Havi bruttó bér 4</label>
                                <input class="form-control" type="number" value="" id="health_allowance_monthly_gross_4" />
                            </div>

                            <hr class="my-4" />

                            <h7 class="mb-0 mt-0">Vezetői pótlék</h7>
                            <div class="col-sm-4">
                                <label for="management_allowance_cost_center_5" class="form-label">Költséghely 5</label>
                                <select class="form-select" id="management_allowance_cost_center_5">
                                    <option selected>Válassz költséghelyet</option>
                                    <option value="1">Első költséghely</option>
                                    <option value="2">Második költséghely</option>
                                    <option value="3">Harmadik költséghely</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="management_allowance_monthly_gross_5" class="form-label">Havi bruttó bér 5</label>
                                <input class="form-control" type="number" value="" id="management_allowance_monthly_gross_5" />
                            </div>
                            <div class="col-sm-4">
                                <label for="management_allowance_end_date" class="form-label">Időtartam vége</label>
                                <input class="form-control" type="date" value="" id="management_allowance_end_date" />
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-primary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-1">Tovább</span>
                                    <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Data section 3 -->
                    <div id="data-section-3" class="content">
                        <div class="content-header mb-3">
                        <h6 class="mb-0">Munkaidő</h6>
                        <small>Add meg a munkaidő adatokat</small>
                        </div>
                        <div class="row g-3">
                            
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-primary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-1">Tovább</span>
                                    <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Data section 4 -->
                    <div id="data-section-4" class="content">
                        <div class="content-header mb-3">
                        <h6 class="mb-0">Eszközök</h6>
                        <small>Add meg a használandó eszközökat, anyagokat, hozzáféréseket</small>
                        </div>
                        <div class="row g-3">
                            
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-primary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                </button>
                                <button class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-1">Tovább</span>
                                    <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Data section 5 -->
                    <div id="data-section-5" class="content">
                        <div class="content-header mb-3">
                        <h6 class="mb-0">Dokumentumok</h6>
                        <small>Töltsd fel a szükséges dokumentumokat</small>
                        </div>
                        <div class="row g-3">
                        
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-primary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                </button>
                                <button class="btn btn-success btn-submit">Folyamat indítása</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
        <!-- /Vertical Wizard -->
        
        <!-- Because of a bug, this hidden wizard-numbered is required to appear vertical wizard correctly -->
        <input type="hidden" class="wizard-numbered" />
    </div>
@endsection
