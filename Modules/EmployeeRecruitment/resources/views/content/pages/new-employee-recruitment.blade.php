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
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
    'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss',
    'resources/assets/vendor/libs/dropzone/dropzone.scss',
    'resources/css/app.css'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/select2/i18n/hu.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
    'resources/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js',
    'resources/assets/vendor/libs/cleavejs/cleave.js',
    'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
    'resources/assets/vendor/libs/dropzone/dropzone.min.js',
    'resources/assets/vendor/libs/dropzone/dropzone.js',
    'resources/js/dropzone-manager.js'
  ])
@endsection

@section('page-script')
    @vite([
        'resources/assets/js/form-wizard-numbered.js',
        'resources/assets/js/form-basic-inputs.js',
        'Modules/EmployeeRecruitment/resources/assets/js/employee-recruitment.js'
    ])
@endsection

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Új folyamat /</span> Felvételi kérelem
    </h4>

    <div class="alert alert-danger alert-dismissible d-none" role="alert" id="errorAlert">
        <span id="errorAlertMessage"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

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
                            <span class="bs-stepper-title">Jogviszony</span>
                            <span class="bs-stepper-subtitle">Jogviszony megadása</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#data-section-3">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Bérelemek</span>
                            <span class="bs-stepper-subtitle">Bérelemek megadása</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#data-section-4">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">4</span>
                            <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Munkaidő</span>
                            <span class="bs-stepper-subtitle">Munkaidő megadása</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#data-section-5">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">5</span>
                            <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Egyéb adatok</span>
                            <span class="bs-stepper-subtitle">Egyéb adatok megadása</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#data-section-6">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">6</span>
                            <span class="bs-stepper-label mt-1">
                            <span class="bs-stepper-title">Dokumentumok</span>
                            <span class="bs-stepper-subtitle">Dokumentumok feltöltése</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content" id="new-recruitment">

                    <!-- Data section 1 -->
                    <div id="data-section-1" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Alapadatok</h5>
                            <small>Add meg az alapadatokat</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="name">Név</label>
                                <input type="text" id="name" class="form-control" name="name" />
                            </div>
                            <div class="col-sm-8">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="job_ad_exists">
                                    <label class="form-check-label" for="job_ad_exists">Felvétel álláshirdetéssel történt?</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="has_prior_employment">
                                    <label class="form-check-label" for="has_prior_employment">Volt már munkajogviszonya a Kutatóközponttal?</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="has_current_volunteer_contract">
                                    <label class="form-check-label" for="has_current_volunteer_contract">Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal?</label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="applicants_female_count" class="form-label">Álláshirdetésre jelentkezett nők száma</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" id="applicants_female_count" name="applicants_female_count" />
                                    <span class="ms-2">Fő</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="applicants_male_count" class="form-label">Álláshirdetésre jelentkezett férfiak száma</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" id="applicants_male_count" name="applicants_male_count" />
                                    <span class="ms-2">Fő</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="citizenship" class="form-label">Állampolgárság</label>
                                <select class="form-select" id="citizenship" name="citizenship">
                                    <option value="Magyar" selected>Magyar</option>
                                    <option value="EGT tagállambeli">EGT tagállambeli</option>
                                    <option value="Harmadik országbeli">Harmadik országbeli</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="workgroup_id_1" class="form-label">Csoport 1</label>
                                <select class="form-select select2" id="workgroup_id_1" name="workgroup_id_1">
                                    <option selected>Válassz csoportot</option>
                                    @foreach($workgroups1 as $workgroup)
                                        <option value="{{ $workgroup->id }}" data-workgroup="{{ $workgroup->workgroup_number }}" title="{{ $workgroup->leader_name }}">{{ $workgroup->workgroup_number . ' - ' .  $workgroup->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="workgroup_id_2" class="form-label">Csoport 2</label>
                                <select class="form-select select2" id="workgroup_id_2" name="workgroup_id_2">
                                    <option value="-1" selected>Nincs csoport</option>
                                    @foreach($workgroups2 as $workgroup)
                                        <option value="{{ $workgroup->id }}" data-workgroup="{{ $workgroup->workgroup_number }}" title="{{ $workgroup->leader_name }}">{{ $workgroup->workgroup_number . ' - ' .  $workgroup->name }}</option>
                                    @endforeach
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
                            <h5 class="mb-0">Jogviszony</h5>
                            <small>Add meg az jogviszony adatokat</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label for="position_type" class="form-label">Munkakör típusa</label>
                                <select class="form-select" id="position_type" name="position_type">
                                    <option value="kutatói" selected>Kutatói</option>
                                    <option value="nem-kutatói">Nem kutatói</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="position_id" class="form-label">Munkakör</label>
                                <select class="form-select" id="position_id" name="position_id">
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}" data-type="{{ $position->type }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="job_description" class="form-label">Munkaköri leírás</label>
                                <form action="/file/upload" class="dropzone needsclick" id="job_description" name="job_description">
                                    @csrf
                                    <div class="dz-message needsclick">
                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                    </div>
                                </form>
                                <input type="hidden" id="job_description_file" data-original-name="" name="job_description_file" />
                            </div>
                            <div class="col-sm-4">
                                <label for="employment_type" class="form-label">Jogviszony típusa</label>
                                <select class="form-select" id="employment_type" name="employment_type">
                                    <option value="Határozott" selected>Határozott</option>
                                    <option value="Határozatlan">Határozatlan</option>
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <label for="task" class="form-label">Feladat</label>
                                <textarea class="form-control" id="task" rows="3" name="task"></textarea>                              
                            </div>
                            <div class="col-sm-4">
                                <label for="employment_start_date" class="form-label">Jogviszony kezdete</label>
                                <input type="text" id="employment_start_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="employment_start_date" />
                            </div>
                            <div class="col-sm-4">
                                <label for="employment_end_date" class="form-label">Jogviszony vége</label>
                                <input type="text" id="employment_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="employment_end_date" />
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
                            <h5 class="mb-0">Bérelemek</h5>
                            <small>Add meg a bérelemeket</small>
                        </div>
                        <div class="row g-3">
                            <p class="mb-0"><strong>Alapbér</strong></p>
                            <div class="col-sm-6">
                                <label for="base_salary_cost_center_1" class="form-label">Költséghely 1</label>
                                <select class="form-select select2" id="base_salary_cost_center_1" name="base_salary_cost_center_1">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_monthly_gross_1" class="form-label">Havi bruttó bér 1</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" id="base_salary_monthly_gross_1" name="base_salary_monthly_gross_1" />
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_cost_center_2" class="form-label">Költséghely 2</label>
                                <select class="form-select select2" id="base_salary_cost_center_2" name="base_salary_cost_center_2">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_monthly_gross_2" class="form-label">Havi bruttó bér 2</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" id="base_salary_monthly_gross_2" name="base_salary_monthly_gross_2" />
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_cost_center_3" class="form-label">Költséghely 3</label>
                                <select class="form-select select2" id="base_salary_cost_center_3" name="base_salary_cost_center_3">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="base_salary_monthly_gross_3" class="form-label">Havi bruttó bér 3</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" value="" id="base_salary_monthly_gross_3" name="base_salary_monthly_gross_3" />
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>

                            <hr class="my-4" />

                            <p class="mb-0 mt-0"><strong>Egészségügyi pótlék</strong></p>
                            <div class="col-sm-6">
                                <label for="health_allowance_cost_center_4" class="form-label">Költséghely 4</label>
                                <select class="form-select select2" id="health_allowance_cost_center_4" name="health_allowance_cost_center_4">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="health_allowance_monthly_gross_4" class="form-label">Havi bruttó bér 4</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" value="" id="health_allowance_monthly_gross_4" name="health_allowance_monthly_gross_4" />
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>

                            <hr class="my-4" />

                            <p class="mb-0 mt-0"><strong>Vezetői pótlék</strong></p>
                            <div class="col-sm-4">
                                <label for="management_allowance_cost_center_5" class="form-label">Költséghely 5</label>
                                <select class="form-select select2" id="management_allowance_cost_center_5" name="management_allowance_cost_center_5">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="management_allowance_monthly_gross_5" class="form-label">Havi bruttó bér 5</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" value="" id="management_allowance_monthly_gross_5" name="management_allowance_monthly_gross_5"/>
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="management_allowance_end_date" class="form-label">Időtartam vége</label>
                                <input type="text" id="management_allowance_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="management_allowance_end_date" />
                            </div>

                            <hr class="my-4" />

                            <p class="mb-0 mt-0"><strong>Bérpótlék 1</strong></p>
                            <div class="col-sm-4">
                                <label for="extra_pay_1_cost_center_6" class="form-label">Költséghely 6</label>
                                <select class="form-select select2" id="extra_pay_1_cost_center_6" name="extra_pay_1_cost_center_6">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="extra_pay_1_monthly_gross_6" class="form-label">Havi bruttó bér 6</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" value="" id="extra_pay_1_monthly_gross_6" name="extra_pay_1_monthly_gross_6" />
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="extra_pay_1_end_date" class="form-label">Időtartam vége</label>
                                <input type="text" id="extra_pay_1_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="extra_pay_1_end_date" />
                            </div>

                            <hr class="my-4" />

                            <p class="mb-0 mt-0"><strong>Bérpótlék 2</strong></p>
                            <div class="col-sm-4">
                                <label for="extra_pay_2_cost_center_7" class="form-label">Költséghely 7</label>
                                <select class="form-select select2" id="extra_pay_2_cost_center_7" name="extra_pay_2_cost_center_7">
                                    <option selected>Válassz költséghelyet</option>
                                    @foreach($costcenters as $costcenter)
                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="extra_pay_2_monthly_gross_7" class="form-label">Havi bruttó bér 7</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control numeral-mask" type="text" value="" id="extra_pay_2_monthly_gross_7" name="extra_pay_2_monthly_gross_7" />
                                    <span class="ms-2">Ft</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label for="extra_pay_2_end_date" class="form-label">Időtartam vége</label>
                                <input type="text" id="extra_pay_2_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="extra_pay_2_end_date" />
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

                    <!-- Data section 4 -->
                    <div id="data-section-4" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Munkaidő</h5>
                            <small>Add meg a munkaidő adatokat</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label for="weekly_working_hours" class="form-label">Heti munkaóraszám</label>
                                <select class="form-select" id="weekly_working_hours" name="weekly_working_hours">
                                    <option value="40" selected>40 óra</option>
                                    <option value="30">30 óra</option>
                                    <option value="25">25 óra</option>
                                    <option value="20">20 óra</option>
                                    <option value="16">16 óra</option>
                                    <option value="15">15 óra</option>
                                    <option value="10">10 óra</option>
                                    <option value="8">8 óra</option>
                                    <option value="5">5 óra</option>
                                </select>
                            </div>

                            <p class="mb-0 mt-4"><strong>Munkaidő</strong></p>
                            <div class="col-sm-4">
                                <label for="work_start_monday" class="form-label">Hétfő - munkaidő kezdete</label>
                                <input type="text" id="work_start_monday" placeholder="ÓÓ:PP" class="form-control" name="work_start_monday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="work_end_monday" class="form-label">Hétfő - munkaidő vége</label>
                                <input type="text" id="work_end_monday" placeholder="ÓÓ:PP" class="form-control" name="work_end_monday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="monday_duration" class="form-label">Hétfő - munkaidő hossza</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" type="text" id="monday_duration" disabled />
                                    <span class="ms-2">óra</span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="work_start_tuesday" class="form-label">Kedd - munkaidő kezdete</label>
                                <input type="text" id="work_start_tuesday" placeholder="ÓÓ:PP" class="form-control" name="work_start_tuesday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="work_end_tuesday" class="form-label">Kedd - munkaidő vége</label>
                                <input type="text" id="work_end_tuesday" placeholder="ÓÓ:PP" class="form-control" name="work_end_tuesday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="tuesday_duration" class="form-label">Kedd - munkaidő hossza</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" type="text" id="tuesday_duration" disabled />
                                    <span class="ms-2">óra</span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="work_start_wednesday" class="form-label">Szerda - munkaidő kezdete</label>
                                <input type="text" id="work_start_wednesday" placeholder="ÓÓ:PP" class="form-control" name="work_start_wednesday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="work_end_wednesday" class="form-label">Szerda - munkaidő vége</label>
                                <input type="text" id="work_end_wednesday" placeholder="ÓÓ:PP" class="form-control" name="work_end_wednesday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="wednesday_duration" class="form-label">Szerda - munkaidő hossza</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" type="text" id="wednesday_duration" disabled />
                                    <span class="ms-2">óra</span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="work_start_thursday" class="form-label">Csütörtök - munkaidő kezdete</label>
                                <input type="text" id="work_start_thursday" placeholder="ÓÓ:PP" class="form-control" name="work_start_thursday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="work_end_thursday" class="form-label">Csütörtök - munkaidő vége</label>
                                <input type="text" id="work_end_thursday" placeholder="ÓÓ:PP" class="form-control" name="work_end_thursday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="thursday_duration" class="form-label">Csütörtök - munkaidő hossza</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" type="text" id="thursday_duration" disabled />
                                    <span class="ms-2">óra</span>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <label for="work_start_friday" class="form-label">Péntek - munkaidő kezdete</label>
                                <input type="text" id="work_start_friday" placeholder="ÓÓ:PP" class="form-control" name="work_start_friday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="work_end_friday" class="form-label">Péntek - munkaidő vége</label>
                                <input type="text" id="work_end_friday" placeholder="ÓÓ:PP" class="form-control" name="work_end_friday" />
                            </div>
                            <div class="col-sm-4">
                                <label for="friday_duration" class="form-label">Péntek - munkaidő hossza</label>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" type="text" id="friday_duration" disabled />
                                    <span class="ms-2">óra</span>
                                </div>
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

                    <!-- Data section 5 -->
                    <div id="data-section-5" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Egyéb adatok</h5>
                            <small>Add meg a jogosultságokat, használandó eszközöket</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="email">Javasolt email cím</label>
                                <input type="text" id="email" class="form-control" placeholder="Email" name="email" />
                            </div>
                            <div class="col-sm-4">
                                <label for="entry_permissions" class="form-label">Belépési jogosultságok</label>
                                <select class="form-select select2" id="entry_permissions" placeholder="&nbsp;" name="entry_permissions" data-style="btn-default" multiple data-icon-base="bx" data-tick-icon="bx-check text-primary" multiple>
                                    <optgroup label="Általános belépési engedélyek">
                                        <option value="auto">Autó behajtás</option>
                                        <option value="kerekpar">Kerékpár behajtás</option>
                                    </optgroup>
                                    <optgroup label="Intézményi belépési engedélyek">
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->room_number }}" data-workgroup="{{ $room->workgroup_number }}">{{ $room->room_number }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="license_plate">Rendszám</label>
                                <input type="email" id="license_plate" class="form-control" name="license_plate" />
                            </div>
                            <div class="col-sm-4">
                                <label for="employee_room" class="form-label">Dolgozószoba</label>
                                <select class="form-select select2" id="employee_room" name="employee_room" multiple>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->room_number }}" data-workgroup="{{ $room->workgroup_number }}">{{ $room->room_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="phone_extension">Telefon mellék</label>
                                <input type="email" id="phone_extension" class="form-control" name="phone_extension" />
                            </div>
                            <div class="col-sm-4">
                                <label for="external_access_rights" class="form-label">Hozzáférési jogosultságok</label>
                                <select class="form-select select2" id="external_access_rights" placeholder="&nbsp;" name="external_access_rights" data-style="btn-default" multiple data-icon-base="bx" data-tick-icon="bx-check text-primary" multiple>
                                    @foreach($externalAccessRights as $externalAccessRight)
                                        <option value="{{ $externalAccessRight->id }}">{{ $externalAccessRight->external_system }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="required_tools" class="form-label">Munkavégzéshez szükséges eszközök</label>
                                <select class="form-select select2" id="required_tools" placeholder="&nbsp;" name="required_tools" data-style="btn-default" multiple data-icon-base="bx" data-tick-icon="bx-check text-primary" multiple>
                                    <option value="asztal">asztal</option>
                                    <option value="szek">szék</option>
                                    <option value="asztali_szamitogep">asztali számítógép</option>
                                    <option value="laptop">laptop</option>
                                    <option value="laptop_taska">laptop táska</option>
                                    <option value="monitor">monitor</option>
                                    <option value="billentyuzet">billentyűzet</option>
                                    <option value="eger">egér</option>
                                    <option value="dokkolo">dokkoló</option>
                                    <option value="mobiltelefon">mobiltelefon</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="available_tools" class="form-label">Munkavégzéshez rendelkezésre álló eszközök</label>
                                <select class="form-select select2" id="available_tools" placeholder="&nbsp;" name="available_tools" data-style="btn-default" multiple data-icon-base="bx" data-tick-icon="bx-check text-primary" multiple>
                                    <!-- dynamically updated -->
                                </select>
                            </div>
                            <div class="col-sm-4 dynamic-tools-container">
                                <!-- Dynamic tools will be added here -->
                            </div>
                            <div class="col-sm-4">
                                <label for="work_with_radioactive_isotopes" class="form-label">Sugárzó izotóppal fog dolgozni</label>
                                <select class="selectpicker w-100" id="work_with_radioactive_isotopes" placeholder="&nbsp;" name="work_with_radioactive_isotopes" data-style="btn-default" data-icon-base="bx" data-tick-icon="bx-check text-primary">
                                    <option value="" selected disabled></option>
                                    <option value="1">Igen</option>
                                    <option value="0">Nem</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="work_with_carcinogenic_materials" class="form-label">Rákkeltő anyaggal fog dolgozni</label>
                                <select class="selectpicker w-100" id="work_with_carcinogenic_materials" placeholder="&nbsp;" name="work_with_carcinogenic_materials" data-style="btn-default" data-icon-base="bx" data-tick-icon="bx-check text-primary">
                                    <option value="" selected disabled></option>
                                    <option value="1">Igen</option>
                                    <option value="0">Nem</option>
                                </select>
                            </div>
                            <div class="col-sm-8 planned-carcinogenic-materials">
                                <label for="planned_carcinogenic_materials_use" class="form-label">Használni tervezett rákkeltő anyagok felsorolása</label>
                                <textarea class="form-control" id="planned_carcinogenic_materials_use" rows="3" name="planned_carcinogenic_materials_use"></textarea>                              
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

                    <!-- Data section 6 -->
                    <div id="data-section-6" class="content">
                        <div class="content-header mb-3">
                            <h5 class="mb-0">Dokumentumok</h5>
                            <small>Töltsd fel a szükséges dokumentumokat</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="personal_data_sheet" class="form-label">Személyi adatlap</label>
                                <form action="/file/upload" class="dropzone needsclick" id="personal_data_sheet" name="personal_data_sheet">
                                    @csrf
                                    <div class="dz-message needsclick">
                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                    </div>
                                </form>
                                <input type="hidden" id="personal_data_sheet_file" data-original-name="" />
                            </div>
                            <div class="col-sm-6">
                                <label for="student_status_verification" class="form-label">Hallgatói jogviszony igazolás</label>
                                <form action="/file/upload" class="dropzone needsclick" id="student_status_verification" name="student_status_verification">
                                    @csrf
                                    <div class="dz-message needsclick">
                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                    </div>
                                </form>
                                <input type="hidden" id="student_status_verification_file" data-original-name="" />
                            </div>
                            <div class="col-sm-12">
                                <label for="certificates" class="form-label">Bizonyítványok</label>
                                <form action="/file/upload" class="dropzone needsclick" id="certificates" name="certificates">
                                    @csrf
                                    <div class="dz-message needsclick">
                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                    </div>
                                </form>
                                <input type="hidden" id="certificates_file" data-original-name="" />
                            </div>
                            <div class="col-sm-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="requires_commute_support" name="requires_commute_support">
                                    <label class="form-check-label" for="requires_commute_support">Munkába járási támogatást igényel</label>
                                </div>
                            </div>
                            <div class="col-sm-6 commute-support-form">
                                <label for="commute_support_form" class="form-label">Munkába járási adatlap</label>
                                <form action="/file/upload" class="dropzone needsclick" id="commute_support_form" name="commute_support_form">
                                    @csrf
                                    <div class="dz-message needsclick">
                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                    </div>
                                </form>
                                <input type="hidden" id="commute_support_form_file" data-original-name="" />
                            </div>
                        
                            <div class="col-12 d-flex justify-content-between">
                                <button class="btn btn-primary btn-prev">
                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                </button>
                                <button class="btn btn-success btn-submit">Folyamat indítása</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
