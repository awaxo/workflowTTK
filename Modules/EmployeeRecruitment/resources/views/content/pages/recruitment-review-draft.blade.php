@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')


<!-- Vendor Styles -->
@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss',
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
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/select2/i18n/hu.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/@form-validation/transformer.js',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.hu.min.js',
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
        'resources/js/app.js',
        'resources/assets/js/form-wizard-numbered.js',
        'resources/assets/js/form-basic-inputs.js',
        'Modules/EmployeeRecruitment/resources/assets/js/employee-recruitment.js',
        'Modules/EmployeeRecruitment/resources/assets/js/validation-indicators.js'
    ])

    <script>
        const isTitkar9Role = {{ auth()->user()->hasRole('titkar_9_fi') || auth()->user()->hasRole('titkar_9_gi') ? 'true' : 'false' }};
        const isSuspendedReview = {{ isset($isSuspendedReview) && $isSuspendedReview ? 'true' : 'false' }};
    </script>
@endsection

@php
use App\Models\Delegation;
use App\Models\CostCenter;

// Collect all cost centers from recruitment
$allCostCenters = collect([
    $draft->base_salary_cc1,
    $draft->base_salary_cc2, 
    $draft->base_salary_cc3,
    $draft->health_allowance_cc,
    $draft->management_allowance_cc,
    $draft->extra_pay_1_cc,
    $draft->extra_pay_2_cc
])->filter(); // Remove null values

// Pre-calculate cost center codes for all history entries with proof_of_coverage status
$costCenterCodesCache = [];
foreach ($history as $historyItem) {
    if ($historyItem['status'] == 'proof_of_coverage') {
        $userId = $historyItem['user_id'];
        
        // Check if user is an active delegate
        $delegation = Delegation::where('delegate_user_id', $userId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
        
        $userIdsToCheck = [$userId];
        
        // If user is a delegate, also check the original user
        if ($delegation) {
            $userIdsToCheck[] = $delegation->original_user_id;
        }
        
        // Find cost center where project_coordinator_user_id matches any of the user IDs
        $costCenterCode = null;
        foreach ($allCostCenters as $costCenter) {
            if ($costCenter && in_array($costCenter->project_coordinator_user_id, $userIdsToCheck)) {
                $costCenterCode = $costCenter->cost_center_code;
                break;
            }
        }
        
        $costCenterCodesCache[$userId] = $costCenterCode;
    }
}
@endphp

@section('content')
    <h4 class="py-3 mb-2">
        <span class="text-muted fw-light">Piszkozat módosítása /</span> Felvételi kérelem
    </h4>

    <!-- Back Button -->
    <div class="mb-4">
        <button onclick="window.location.href='/hr/felveteli-kerelem/piszkozatok'" class="btn btn-secondary">Vissza</button>
    </div>

    <div class="mb-2" style="font-size: larger;">
        <div class="">ID: <b>P{{ $draft->pseudo_id }}/{{ \Carbon\Carbon::parse($draft->created_at)->format('Y') }}</b></div>
    </div>

    <div class="row">
        <div class="col">
            <div class="mb-3">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_review" role="tab" aria-selected="true">Adatok</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_status_history" role="tab" aria-selected="false">Státusztörténet</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active show" id="tab_review" role="tabpanel">
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
                                            <span class="bs-stepper-title">Bérelemek</span>
                                            <span class="bs-stepper-subtitle">Bérelemek megadása</span>
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
                                    <input type="hidden" id="draft_id" name="draft_id" value="{{ isset($id) ? $id : '' }}" />
                                    <input type="hidden" id="recruitmentCreatedAt" name="recruitmentCreatedAt" value="{{ $draft->created_at }}">

                                    <!-- Data section 1 -->
                                    <div id="data-section-1" class="content">
                                        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Alapadatok</h5>
                                                <small>Add meg az alapadatokat</small>
                                            </div>
                                            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal1" title="Segítség"></i>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label class="form-label" for="name">Név</label>
                                                <input type="text" id="name" class="form-control" name="name" value="{{ $draft->name }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="form-label" for="birth_date">Születési dátum</label>
                                                <input type="text" id="birth_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="birth_date" value="{{ str_replace('-', '.', $draft->birth_date) }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="form-label" for="social_security_number">TAJ szám</label>
                                                <input type="text" id="social_security_number" class="form-control" name="social_security_number" value="{{ $draft->social_security_number }}" />
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label" for="address">Lakcím (irányítószám, település neve, köztér neve és jellege, házszám)</label>
                                                <input type="text" id="address" class="form-control" name="address" value="{{ $draft->address }}" />
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="job_ad_exists" {{ $draft->job_ad_exists == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="job_ad_exists">Felvétel álláshirdetéssel történt?</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="has_prior_employment" {{ $draft->has_prior_employment == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="has_prior_employment">Volt már munkajogviszonya a Kutatóközponttal?</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="has_current_volunteer_contract" {{ $draft->has_current_volunteer_contract == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="has_current_volunteer_contract">Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal?</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="is_retired" {{ $draft->is_retired == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_retired">Jelenleg nyugdíjas?</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="applicants_female_count" class="form-label">Álláshirdetésre jelentkezett nők száma</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="applicants_female_count" name="applicants_female_count" value="{{ $draft->applicants_female_count }}" />
                                                    <span class="ms-2">Fő</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="applicants_male_count" class="form-label">Álláshirdetésre jelentkezett férfiak száma</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="applicants_male_count" name="applicants_male_count" value="{{ $draft->applicants_male_count }}" />
                                                    <span class="ms-2">Fő</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="citizenship" class="form-label">Állampolgárság</label>
                                                <select class="form-select" id="citizenship" name="citizenship">
                                                    <option value="Magyar" {{ $draft->citizenship == 'Magyar' ? 'selected' : '' }}>Magyar</option>
                                                    <option value="EGT tagállambeli" {{ $draft->citizenship == 'EGT tagállambeli' ? 'selected' : '' }}>EGT tagállambeli</option>
                                                    <option value="Harmadik országbeli" {{ $draft->citizenship == 'Harmadik országbeli' ? 'selected' : '' }}>Harmadik országbeli</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="workgroup_id_1" class="form-label">Csoport 1</label>
                                                <input type="hidden" id="workgroup_id_1_value" name="workgroup_id_1_value" value="{{ $draft->workgroup_id_1 }}" />
                                                <select class="form-select select2" id="workgroup_id_1" name="workgroup_id_1" data-placeholder="Válassz csoportot">
                                                    <option value="" selected>Válassz csoportot</option>
                                                    @foreach($workgroups1 as $workgroup)
                                                        <option value="{{ $workgroup->id }}" data-workgroup="{{ $workgroup->workgroup_number }}" title="{{ $workgroup->leader_name }}">{{ $workgroup->workgroup_number . ' - ' .  $workgroup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="workgroup_id_2" class="form-label">Csoport 2</label>
                                                <input type="hidden" id="workgroup_id_2_value" name="workgroup_id_2_value" value="{{ $draft->workgroup_id_2 }}" />
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
                                        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Jogviszony</h5>
                                                <small>Add meg az jogviszony adatokat</small>
                                            </div>
                                            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal2" title="Segítség"></i>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label for="position_type" class="form-label">Munkakör típusa</label>
                                                <select class="form-select" id="position_type" name="position_type">
                                                    <option value="kutatói">Kutatói</option>
                                                    <option value="nem-kutatói">Nem kutatói</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="position_id" class="form-label">Munkakör</label>
                                                <input type="hidden" id="position_id_value" name="position_id_value" value="{{ $draft->position_id }}" />
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
                                                @if(!empty($draft->job_description))
                                                    <div>
                                                        <a href="/dokumentumok/{{ $draft->job_description }}" class="btn btn-link" id="job_description_file_link" target="_blank">Fájl megtekintése</a>
                                                        <span class="text-danger upload-file-delete job_description_file" data-file="job_description_file" data-type="job_description"><i class="fa fa-times" style="cursor: pointer"></i></span>
                                                    </div>
                                                @endif
                                                <input type="hidden" id="job_description_file" data-original-name="" name="job_description_file" data-existing="{{ $draft->job_description }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="employment_type" class="form-label">Jogviszony típusa</label>
                                                <select class="form-select" id="employment_type" name="employment_type">
                                                    <option value="Határozott" {{ $draft->employment_type == 'Határozott' ? 'selected' : '' }}>Határozott</option>
                                                    <option value="Határozatlan" {{ $draft->employment_type == 'Határozatlan' ? 'selected' : '' }}>Határozatlan</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-8">
                                                <label for="task" class="form-label">Feladat</label>
                                                <textarea class="form-control" id="task" rows="3" name="task">{{ $draft->task ?? '' }}</textarea>                              
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="employment_start_date" class="form-label">Jogviszony kezdete</label>
                                                <input type="text" id="employment_start_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="employment_start_date" value="{{ str_replace('-', '.', $draft->employment_start_date) }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="employment_end_date" class="form-label">Jogviszony vége</label>
                                                <input type="text" id="employment_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="employment_end_date" value="{{ str_replace('-', '.', $draft->employment_end_date) }}" />
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
                                        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Munkaidő</h5>
                                                <small>Add meg a munkaidő adatokat</small>
                                            </div>
                                            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal4" title="Segítség"></i>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label for="weekly_working_hours" class="form-label">Heti munkaóraszám</label>
                                                <select class="form-select" id="weekly_working_hours" name="weekly_working_hours">
                                                    <option value="40" {{ $draft->weekly_working_hours == '40' ? 'selected' : '' }}>40 óra</option>
                                                    <option value="36" {{ $draft->weekly_working_hours == '36' ? 'selected' : '' }}>36 óra</option>
                                                    <option value="30" {{ $draft->weekly_working_hours == '30' ? 'selected' : '' }}>30 óra</option>
                                                    <option value="25" {{ $draft->weekly_working_hours == '25' ? 'selected' : '' }}>25 óra</option>
                                                    <option value="20" {{ $draft->weekly_working_hours == '20' ? 'selected' : '' }}>20 óra</option>
                                                    <option value="16" {{ $draft->weekly_working_hours == '16' ? 'selected' : '' }}>16 óra</option>
                                                    <option value="15" {{ $draft->weekly_working_hours == '15' ? 'selected' : '' }}>15 óra</option>
                                                    <option value="10" {{ $draft->weekly_working_hours == '10' ? 'selected' : '' }}>10 óra</option>
                                                    <option value="8" {{ $draft->weekly_working_hours == '8' ? 'selected' : '' }}>8 óra</option>
                                                    <option value="5" {{ $draft->weekly_working_hours == '5' ? 'selected' : '' }}>5 óra</option>
                                                </select>
                                            </div>

                                            <p class="mb-0 mt-4"><strong>Munkaidő</strong></p>
                                            <div class="col-sm-4">
                                                <label for="work_start_monday" class="form-label">Hétfő - munkaidő kezdete</label>
                                                <input type="text" id="work_start_monday" placeholder="ÓÓ:PP" class="form-control" name="work_start_monday" value="{{ $draft->work_start_monday ?? '' }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="work_end_monday" class="form-label">Hétfő - munkaidő vége</label>
                                                <input type="text" id="work_end_monday" placeholder="ÓÓ:PP" class="form-control" name="work_end_monday" value="{{ $draft->work_end_monday ?? '' }}" />
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
                                                <input type="text" id="work_start_tuesday" placeholder="ÓÓ:PP" class="form-control" name="work_start_tuesday" value="{{ $draft->work_start_tuesday ?? '' }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="work_end_tuesday" class="form-label">Kedd - munkaidő vége</label>
                                                <input type="text" id="work_end_tuesday" placeholder="ÓÓ:PP" class="form-control" name="work_end_tuesday" value="{{ $draft->work_end_tuesday ?? '' }}" />
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
                                                <input type="text" id="work_start_wednesday" placeholder="ÓÓ:PP" class="form-control" name="work_start_wednesday" value="{{ $draft->work_start_wednesday ?? '' }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="work_end_wednesday" class="form-label">Szerda - munkaidő vége</label>
                                                <input type="text" id="work_end_wednesday" placeholder="ÓÓ:PP" class="form-control" name="work_end_wednesday" value="{{ $draft->work_end_wednesday ?? '' }}" />
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
                                                <input type="text" id="work_start_thursday" placeholder="ÓÓ:PP" class="form-control" name="work_start_thursday" value="{{ $draft->work_start_thursday ?? '' }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="work_end_thursday" class="form-label">Csütörtök - munkaidő vége</label>
                                                <input type="text" id="work_end_thursday" placeholder="ÓÓ:PP" class="form-control" name="work_end_thursday" value="{{ $draft->work_end_thursday ?? '' }}" />
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
                                                <input type="text" id="work_start_friday" placeholder="ÓÓ:PP" class="form-control" name="work_start_friday" value="{{ $draft->work_start_friday ?? '' }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="work_end_friday" class="form-label">Péntek - munkaidő vége</label>
                                                <input type="text" id="work_end_friday" placeholder="ÓÓ:PP" class="form-control" name="work_end_friday" value="{{ $draft->work_end_friday ?? '' }}" />
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

                                    <!-- Data section 4 -->
                                    <div id="data-section-4" class="content">
                                        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Bérelemek</h5>
                                                <small>Add meg a bérelemeket</small>
                                            </div>
                                            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal3" title="Segítség"></i>
                                        </div>
                                        <div class="row g-3">
                                            <p class="mb-0"><strong>Alapbér</strong></p>
                                            <div class="col-sm-6">
                                                <label for="base_salary_cost_center_1" class="form-label">Költséghely 1</label>
                                                <input type="hidden" id="base_salary_cost_center_1_value" name="base_salary_cost_center_1_value" value="{{ $draft->base_salary_cost_center_1 }}" />
                                                <select class="form-select select2" id="base_salary_cost_center_1" name="base_salary_cost_center_1">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="base_salary_monthly_gross_1" class="form-label">Havi bruttó alapbér 1</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="base_salary_monthly_gross_1" name="base_salary_monthly_gross_1" value="{{ (int)$draft->base_salary_monthly_gross_1 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="base_salary_cost_center_2" class="form-label">Költséghely 2</label>
                                                <input type="hidden" id="base_salary_cost_center_2_value" name="base_salary_cost_center_2_value" value="{{ $draft->base_salary_cost_center_2 }}" />
                                                <select class="form-select select2" id="base_salary_cost_center_2" name="base_salary_cost_center_2">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="base_salary_monthly_gross_2" class="form-label">Havi bruttó alapbér 2</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="base_salary_monthly_gross_2" name="base_salary_monthly_gross_2" value="{{ (int)$draft->base_salary_monthly_gross_2 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="base_salary_cost_center_3" class="form-label">Költséghely 3</label>
                                                <input type="hidden" id="base_salary_cost_center_3_value" name="base_salary_cost_center_3_value" value="{{ $draft->base_salary_cost_center_3 }}" />
                                                <select class="form-select select2" id="base_salary_cost_center_3" name="base_salary_cost_center_3">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="base_salary_monthly_gross_3" class="form-label">Havi bruttó alapbér 3</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="base_salary_monthly_gross_3" name="base_salary_monthly_gross_3" value="{{ (int)$draft->base_salary_monthly_gross_3 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>

                                            <hr class="my-4" />

                                            <p class="mb-0 mt-0"><strong>Egészségügyi pótlék</strong></p>
                                            <div class="col-sm-6">
                                                <label for="health_allowance_cost_center_4" class="form-label">Költséghely</label>
                                                <input type="hidden" id="health_allowance_cost_center_4_value" name="health_allowance_cost_center_4_value" value="{{ $draft->health_allowance_cost_center_4 }}" />
                                                <select class="form-select select2" id="health_allowance_cost_center_4" name="health_allowance_cost_center_4">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="health_allowance_monthly_gross_4" class="form-label">Havi bruttó egészségügyi pótlék</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="health_allowance_monthly_gross_4" name="health_allowance_monthly_gross_4" value="{{ (int)$draft->health_allowance_monthly_gross_4 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>

                                            <hr class="my-4" />

                                            <p class="mb-0 mt-0"><strong>Vezetői pótlék</strong></p>
                                            <div class="col-sm-4">
                                                <label for="management_allowance_cost_center_5" class="form-label">Költséghely</label>
                                                <input type="hidden" id="management_allowance_cost_center_5_value" name="management_allowance_cost_center_5_value" value="{{ $draft->management_allowance_cost_center_5 }}" />
                                                <select class="form-select select2" id="management_allowance_cost_center_5" name="management_allowance_cost_center_5">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="management_allowance_monthly_gross_5" class="form-label">Havi bruttó vezetői pótlék</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="management_allowance_monthly_gross_5" name="management_allowance_monthly_gross_5" value="{{ (int)$draft->management_allowance_monthly_gross_5 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="management_allowance_end_date" class="form-label">Időtartam vége</label>
                                                <input type="text" id="management_allowance_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="management_allowance_end_date" value="{{ str_replace('-', '.', $draft->management_allowance_end_date) }}" />
                                            </div>

                                            <hr class="my-4" />

                                            <p class="mb-0 mt-0"><strong>Bérpótlék 1</strong></p>
                                            <div class="col-sm-4">
                                                <label for="extra_pay_1_cost_center_6" class="form-label">Költséghely</label>
                                                <input type="hidden" id="extra_pay_1_cost_center_6_value" name="extra_pay_1_cost_center_6_value" value="{{ $draft->extra_pay_1_cost_center_6 }}" />
                                                <select class="form-select select2" id="extra_pay_1_cost_center_6" name="extra_pay_1_cost_center_6">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="extra_pay_1_monthly_gross_6" class="form-label">Havi bruttó illetménykiegészítés 1</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="extra_pay_1_monthly_gross_6" name="extra_pay_1_monthly_gross_6" value="{{ (int)$draft->extra_pay_1_monthly_gross_6 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="extra_pay_1_end_date" class="form-label">Időtartam vége</label>
                                                <input type="text" id="extra_pay_1_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="extra_pay_1_end_date" value="{{ str_replace('-', '.', $draft->extra_pay_1_end_date) }}" />
                                            </div>

                                            <hr class="my-4" />

                                            <p class="mb-0 mt-0"><strong>Bérpótlék 2</strong></p>
                                            <div class="col-sm-4">
                                                <label for="extra_pay_2_cost_center_7" class="form-label">Költséghely</label>
                                                <input type="hidden" id="extra_pay_2_cost_center_7_value" name="extra_pay_2_cost_center_7_value" value="{{ $draft->extra_pay_2_cost_center_7 }}" />
                                                <select class="form-select select2" id="extra_pay_2_cost_center_7" name="extra_pay_2_cost_center_7">
                                                    <option value="" selected>Válassz költséghelyet</option>
                                                    @foreach($costcenters as $costcenter)
                                                        <option value="{{ $costcenter->id }}" data-workgroup="{{ substr($costcenter->cost_center_code, strrpos($costcenter->cost_center_code, ' ') + 1) }}" title="{{ $costcenter->leader_name }}">{{ $costcenter->cost_center_code . " - " . $costcenter->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="extra_pay_2_monthly_gross_7" class="form-label">Havi bruttó illetménykiegészítés 2</label>
                                                <div class="d-flex align-items-center">
                                                    <input class="form-control numeral-mask" type="text" id="extra_pay_2_monthly_gross_7" name="extra_pay_2_monthly_gross_7" value="{{ (int)$draft->extra_pay_2_monthly_gross_7 }}" />
                                                    <span class="ms-2">Ft</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="extra_pay_2_end_date" class="form-label">Időtartam vége</label>
                                                <input type="text" id="extra_pay_2_end_date" placeholder="ÉÉÉÉ.HH.NN" class="form-control" name="extra_pay_2_end_date" value="{{ str_replace('-', '.', $draft->extra_pay_2_end_date) }}" />
                                            </div>

                                            <div class="col-12">
                                                <div>
                                                    <span><strong>Bruttó munkabér összesen: <span id="totalGross">0</span> Ft / hó</strong></span>
                                                    <input type="hidden" id="employer_contribution" name="employer_contribution" value="{{ $employerContributionRate }}">
                                                </div>
                                            </div>

                                            <div class="col-12 mt-3">
                                                <div>
                                                    <span><strong>Fedezetigazolandó összeg:</strong></span>
                                                </div>
                                                <div class="table-responsive mt-2">
                                                    <table id="coverageSummaryTable" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Költséghely</th>
                                                                <th id="year-header-0"></th>
                                                                <th id="year-header-1"></th>
                                                                <th id="year-header-2"></th>
                                                                <th id="year-header-3"></th>
                                                                <th>Összesen</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="coverageSummaryBody">
                                                            <!-- Itt lesznek a költséghelyenkénti sorok dinamikusan generálva -->
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-secondary">
                                                                <th>Éves összesen</th>
                                                                <th id="year-total-0">0 Ft</th>
                                                                <th id="year-total-1">0 Ft</th>
                                                                <th id="year-total-2">0 Ft</th>
                                                                <th id="year-total-3">0 Ft</th>
                                                                <th id="grand-total">0 Ft</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
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
                                        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Egyéb adatok</h5>
                                                <small>Add meg a jogosultságokat, használandó eszközöket</small>
                                            </div>
                                            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal5" title="Segítség"></i>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label class="form-label" for="email">Javasolt email cím</label>
                                                <input type="text" id="email" class="form-control" placeholder="vezeteknev.keresztnev@ttk.hu" name="email" value="{{ $draft->email }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="entry_permissions" class="form-label">Belépési jogosultságok</label>
                                                <select class="form-select select2" id="entry_permissions" placeholder="&nbsp;" name="entry_permissions[]" data-style="btn-default" multiple data-icon-base="bx" data-tick-icon="bx-check text-primary">
                                                    <optgroup label="Általános belépési engedélyek">
                                                        <option value="auto" @if(in_array('auto', explode(',', $draft->entry_permissions))) selected @endif>Autó behajtás</option>
                                                        <option value="kerekpar" @if(in_array('kerekpar', explode(',', $draft->entry_permissions))) selected @endif>Kerékpár behajtás</option>
                                                    </optgroup>
                                                    <optgroup label="Intézeti belépési engedélyek">
                                                        <option value="SZKI_osszes_labor" data-workgroup="1XX">SZKI összes labor</option>
                                                        <option value="AKI_osszes_labor" data-workgroup="3XX">AKI összes labor</option>
                                                        <option value="MEI2_osszes_labor" data-workgroup="4XX">MÉI 2. emelet összes labor</option>
                                                        <option value="MEI3_osszes_labor" data-workgroup="4XX">MÉI 3. emelet összes labor</option>
                                                        <option value="KPI_osszes_iroda" data-workgroup="5XX">KPI összes iroda</option>
                                                        <option value="KPI_osszes_labor" data-workgroup="5XX">KPI összes labor</option>
                                                    </optgroup>
                                                    <optgroup label="Csoportszintű belépési engedélyek">
                                                        @foreach($rooms as $room)
                                                            <option value="{{ $room->room_number }}" @if(in_array($room->room_number, explode(',', $draft->entry_permissions))) selected @endif data-workgroup="{{ $room->workgroup_number }}">{{ $room->room_number }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="form-label" for="license_plate">Rendszám</label>
                                                <input type="email" id="license_plate" class="form-control" name="license_plate" value="{{ $draft->license_plate }}" />
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="employee_room" class="form-label">Dolgozószoba</label>
                                                <input type="hidden" id="employee_room_value" name="employee_room_value" value="{{ $draft->employee_room }}" />
                                                <select class="form-select select2" id="employee_room" name="employee_room" data-placeholder="Válassz dolgozószobát">
                                                    @foreach($rooms as $room)
                                                        <option value="{{ $room->room_number }}" data-workgroup="{{ $room->workgroup_number }}">{{ $room->room_number }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="form-label" for="phone_extension">Telefon mellék</label>
                                                <input type="email" id="phone_extension" class="form-control" name="phone_extension" value="{{ $draft->phone_extension }}" />
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
                                                    @php
                                                        $selectedTools = explode(',', $draft->required_tools);
                                                    @endphp
                                                    <option value="asztal" @if(in_array('asztal', $selectedTools)) selected @endif>asztal</option>
                                                    <option value="szek" @if(in_array('szek', $selectedTools)) selected @endif>szék</option>
                                                    <option value="asztali_szamitogep" @if(in_array('asztali_szamitogep', $selectedTools)) selected @endif>asztali számítógép</option>
                                                    <option value="laptop" @if(in_array('laptop', $selectedTools)) selected @endif>laptop</option>
                                                    <option value="laptop_taska" @if(in_array('laptop_taska', $selectedTools)) selected @endif>laptop táska</option>
                                                    <option value="monitor" @if(in_array('monitor', $selectedTools)) selected @endif>monitor</option>
                                                    <option value="billentyuzet" @if(in_array('billentyuzet', $selectedTools)) selected @endif>billentyűzet</option>
                                                    <option value="eger" @if(in_array('eger', $selectedTools)) selected @endif>egér</option>
                                                    <option value="dokkolo" @if(in_array('dokkolo', $selectedTools)) selected @endif>dokkoló</option>
                                                    <option value="mobiltelefon" @if(in_array('mobiltelefon', $selectedTools)) selected @endif>mobiltelefon</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="available_tools" class="form-label">Munkavégzéshez rendelkezésre álló eszközök</label>
                                                <input type="hidden" id="available_tools_value" name="available_tools_value" value="{{ $draft->available_tools }}" />
                                                <select class="form-select select2" id="available_tools" placeholder="&nbsp;" name="available_tools" data-style="btn-default" multiple data-icon-base="bx" data-tick-icon="bx-check text-primary" multiple>
                                                    <!-- dynamically updated -->
                                                </select>
                                            </div>
                                            <div class="col-sm-4 dynamic-tools-container">
                                                <input type="hidden" id="inventory_numbers_of_available_tools" name="dynamic_tools" value="{{ $draft->inventory_numbers_of_available_tools }}" />
                                                <!-- Dynamic tools will be added here -->
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
                                        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">Dokumentumok</h5>
                                                <small>Töltsd fel a szükséges dokumentumokat</small>
                                            </div>
                                            <i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal6" title="Segítség"></i>
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
                                                @if(!empty($draft->personal_data_sheet))
                                                    <div>
                                                        <a href="/dokumentumok/{{ $draft->personal_data_sheet }}" class="btn btn-link" id="personal_data_sheet_file_link" target="_blank">Fájl megtekintése</a>
                                                        <span class="text-danger upload-file-delete personal_data_sheet_file" data-file="personal_data_sheet_file" data-type="personal_data_sheet"><i class="fa fa-times" style="cursor: pointer"></i></span>
                                                    </div>
                                                @endif
                                                <input type="hidden" id="personal_data_sheet_file" data-original-name="" name="personal_data_sheet_file" data-existing="{{ $draft->personal_data_sheet }}" />
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="student_status_verification" class="form-label">Hallgatói jogviszony igazolás</label>
                                                <form action="/file/upload" class="dropzone needsclick" id="student_status_verification" name="student_status_verification">
                                                    @csrf
                                                    <div class="dz-message needsclick">
                                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                                    </div>
                                                </form>
                                                @if(!empty($draft->student_status_verification))
                                                    <div>
                                                        <a href="/dokumentumok/{{ $draft->student_status_verification }}" class="btn btn-link" id="student_status_verification_file_link" target="_blank">Fájl megtekintése</a>
                                                        <span class="text-danger upload-file-delete student_status_verification_file" data-file="student_status_verification_file" data-type="student_status_verification"><i class="fa fa-times" style="cursor: pointer"></i></span>
                                                    </div>
                                                @endif
                                                <input type="hidden" id="student_status_verification_file" data-original-name="" name="student_status_verification_file" data-existing="{{ $draft->student_status_verification }}" />
                                            </div>
                                            <div class="col-sm-12">
                                                <label for="certificates" class="form-label">Bizonyítványok</label>
                                                <form action="/file/upload" class="dropzone needsclick" id="certificates" name="certificates">
                                                    @csrf
                                                    <div class="dz-message needsclick">
                                                        Húzd ide a fájlt, vagy kattints a feltöltéshez.
                                                    </div>
                                                </form>
                                                @if(!empty($draft->certificates))
                                                    <div>
                                                        <a href="/dokumentumok/{{ $draft->certificates }}" class="btn btn-link" id="certificates_file_link" target="_blank">Fájl megtekintése</a>
                                                        <span class="text-danger upload-file-delete certificates_file" data-file="certificates_file" data-type="certificates"><i class="fa fa-times" style="cursor: pointer"></i></span>
                                                    </div>
                                                @endif
                                                <input type="hidden" id="certificates_file" data-original-name="" name="certificates_file" data-existing="{{ $draft->certificates }}" />
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
                                                @if(!empty($draft->commute_support_form))
                                                    <div>
                                                        <a href="/dokumentumok/{{ $draft->commute_support_form }}" class="btn btn-link" id="commute_support_form_link" target="_blank">Fájl megtekintése</a>
                                                        <span class="text-danger upload-file-delete commute_support_form_file" data-file="commute_support_form_file" data-type="commute_support_form"><i class="fa fa-times" style="cursor: pointer"></i></span>
                                                    </div>
                                                @endif
                                                <input type="hidden" id="commute_support_form_file" data-original-name="" name="commute_support_form_file" data-existing="{{ $draft->commute_support_form }}" />
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="form-label" for="initiator_comment">Megjegyzés</label>
                                                <textarea id="initiator_comment" class="form-control" name="initiator_comment" rows="3">{{ $draft->initiator_comment ?? '' }}</textarea>
                                                <small class="text-muted">Maximum 2000 karakter</small>
                                            </div>
                                        
                                            <div class="nav-align-top">
                                                <!-- placeholder for error messages -->
                                            </div>

                                            <div class="col-12 d-flex justify-content-between">
                                                <button class="btn btn-primary btn-prev">
                                                    <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                                    <span class="align-middle d-sm-inline-block d-none">Vissza</span>
                                                </button>
                                                <button class="btn btn-danger btn-delete-draft">
                                                    Kérelem törlése
                                                </button>
                                                @if ($isSecretary || $isWorkgroupLeader)
                                                    <button class="btn btn-primary me-2 btn-submit-draft" type="submit" name="draft">
                                                        Mentés piszkozatként
                                                    </button>
                                                @endif
                                                @if($isSecretary)
                                                    <button class="btn btn-success btn-submit">
                                                        Kérelem leadása
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab_status_history" role="tabpanel">
                        <div id="status_history">
                            <table class="table">
                                <thead>
                                    <tr style="background-color: rgba(105,108,255,.16)">
                                        <th>Döntés</th>
                                        <th>Dátum</th>
                                        <th>Felhasználó</th>
                                        <th>Státusz</th>
                                        <th>Üzenet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($history as $history_entry)
                                    <tr>
                                        <td>
                                            <span class="badge bg-label-{{ 
                                                $history_entry['decision'] == 'approve' ? 'success' : 
                                                ($history_entry['decision'] == 'reject' ? 'danger' : 
                                                ($history_entry['decision'] == 'suspend' ? 'warning' : 
                                                ($history_entry['decision'] == 'start' ? 'success' : 
                                                ($history_entry['decision'] == 'restart' ? 'success' : 
                                                ($history_entry['decision'] == 'delete' ? 'danger' :
                                                ($history_entry['decision'] == 'update' ? 'success' :  
                                                ($history_entry['decision'] == 'cancel' ? 'danger' : 'info'))))))) }} me-1">
                                                {{ 
                                                    $history_entry['decision'] == 'approve' ? 'Jóváhagyás' : 
                                                    ($history_entry['decision'] == 'reject' ? 'Elutasítás' : 
                                                    ($history_entry['decision'] == 'suspend' ? 'Felfüggesztés' : 
                                                    ($history_entry['decision'] == 'start' ? 'Indítás' : 
                                                    ($history_entry['decision'] == 'restart' ? 'Újraindítás' : 
                                                    ($history_entry['decision'] == 'delete' ? 'Törlés' : 
                                                    ($history_entry['decision'] == 'update' ? 'Módosítás' : 
                                                    ($history_entry['decision'] == 'cancel' ? 'Sztornózás' : 'Visszaállítás'))))))) }}
                                            </span>
                                        </td>
                                        <td>{{ date('Y-m-d H:i:s', strtotime($history_entry['datetime'])) }}</td>
                                        <td>{{ $history_entry['user_name'] }}</td>
                                        <td>{{ $history_entry['decision'] == 'update' ? 'Módosítás' : __('states.' . $history_entry['status']) }}</td>
                                        <td>{{ $history_entry['message'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div class="modal fade" id="deleteConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan szeretnéd törölni a fájlt?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete" data-recruitment-id="{{ $draft->id }}" class="btn btn-primary">Jóváhagyás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete workflow case confirmation modal -->
    <div class="modal fade" id="deleteWorkflowConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan szeretnéd törölni az ügyet?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete_case" data-recruitment-id="{{ $draft->id }}" class="btn btn-primary">Jóváhagyás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete workflow case confirmation modal -->
    <div class="modal fade" id="deleteWorkflowDraftConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan szeretnéd törölni ezt a piszkozatot?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" id="confirm_delete_draft" data-recruitment-id="{{ $draft->id }}" class="btn btn-primary">Jóváhagyás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal 1 -->
    <div class="modal fade" id="helpModal1" tabindex="-1" aria-labelledby="helpModal1Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Ezen a lapon tudsz megadni néhány alapadatot a felvételi kérelemhez kapcsolódóan.</p>
                    <p>Az <i>'Álláshirdetésre jelentkezett nők száma'</i> és <i>'Álláshirdetésre jelentkezett férfiak száma'</i> mező csak szám értéket fogad el.</p>
                    <p>A <i>'Csoport 1'</i> és <i>'Csoport 2'</i> legördülő listáknál, kattintás után a lista tetején látható szövegdobozba írva keresni lehet a lista elemei között.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal 2 -->
    <div class="modal fade" id="helpModal2" tabindex="-1" aria-labelledby="helpModal2Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Ezen a lapon a jogviszonyhoz kapcsolódó adatokat adhatod meg.</p>
                    <p>A <i>'Munkakör'</i> lista annak megfelelően frissül, hogy melyik munkakör típust választottad.</p>
                    <p>A fájl feltöltésnél 1 db pdf fájl feltöltésére van lehetőség, aminek a mérete maximálisan 20MB lehet.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal 3 -->
    <div class="modal fade" id="helpModal3" tabindex="-1" aria-labelledby="helpModal3Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Itt adhatod meg az egyes típusok szerinti vérelemeket<./p>
                    <p>A bér mezőkbe csak szám érték adható meg.</p>
                    <p>A választható költséghelyek az első lapon megadott csoportszámoktól függenek, amennyiben kiválasztasz egy költséghelyet, akkor összeget (és ahol van, dátumot) is meg kell adnod.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal 4 -->
    <div class="modal fade" id="helpModal4" tabindex="-1" aria-labelledby="helpModal4Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Ezen a lapon adhatot meg a munkaidőhöz kapcsolódó adatokat hétköznap napi bontásban. A napi munkaidő adatok összegének meg kell egyeznie a kiválasztott heti munkaidő mértékével</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal 5 -->
    <div class="modal fade" id="helpModal5" tabindex="-1" aria-labelledby="helpModal5Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Ezen a lapon néhány további, fontos adat adható meg. A belépési jogosultságok lista az első lapon kiválasztott csoportoktól függ.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal 6 -->
    <div class="modal fade" id="helpModal6" tabindex="-1" aria-labelledby="helpModal6Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Ezen a lapon további, szükséges dokumentum feltöltésére van lehetőséged</p>
                    <p>Mindegyik mezőnél 1 db pdf formátumú fájl tölthető fel és a feltöltött fájlok mérete egyenként maximum 20MB lehet.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>
@endsection
