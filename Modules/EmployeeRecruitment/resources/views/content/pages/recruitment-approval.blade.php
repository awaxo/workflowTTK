@extends('layouts/layoutMaster')

@section('title', 'Ügyintézés')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss',
        'resources/assets/vendor/libs/dropzone/dropzone.scss',
        'resources/css/app.css'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/select2/i18n/hu.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/cleavejs/cleave.js',
        'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
        'resources/assets/vendor/libs/dropzone/dropzone.min.js',
        'resources/assets/vendor/libs/dropzone/dropzone.js',
    ])
@endsection

@section('page-script')
    @vite([
        'resources/js/app.js',
        'resources/assets/js/form-basic-inputs.js',
        'Modules/EmployeeRecruitment/resources/assets/js/approve-recruitment.js'
    ])
@endsection

@section('content')
<h4 class="py-3 mb-2">Folyamat jóváhagyás / <span class="dynamic-part">{{ $recruitment->name }}</span></h4>

<!-- Back Button -->
<div class="mb-4">
    <button onclick="window.location.href='/hr/felveteli-kerelem'" class="btn btn-secondary">Vissza</button>
</div>

<div class="mb-2" style="font-size: larger;">
    <div class="">ID: <b>{{ $recruitment->id }}</b></div>
</div>

<!-- Form with Tabs -->
<div class="row">
    <div class="col">
        <div class="nav-align-top mb-3">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_decision" role="tab" aria-selected="true">Részletek</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_status_history" role="tab" aria-selected="false">Státusztörténet</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active show" id="tab_decision" role="tabpanel">
                <!-- Recruitment details -->
                <div class="accordion" id="accordion_process_details">
                    @if ($isITHead)
                        @include('EmployeeRecruitment::content._partials.it-head-approval', ['recruitment' => $recruitment])
                    @else
                        @include('EmployeeRecruitment::content._partials.all-approval', ['recruitment' => $recruitment])
                    @endif
                </div>

                <br/>
                <div class="fst-italic">Aktuális státusz: <b>{{ __('states.' . $recruitment->state) }}</b></div>
                <div class="fst-italic">Szükséges jóváhagyók (a lista a jóváhagyókat és az esetleges helyetteseiket is tartalmazza): <b>{{ $usersToApprove ? $usersToApprove : '' }}</b></div>

                <!-- Approval controls -->
                <input type="hidden" id="state" value="{{ $recruitment->state }}">
                @if($recruitment->state == 'group_lead_approval')
                    @include('EmployeeRecruitment::content._partials.group-lead-approval', ['chemicalFactors' => $chemicalFactors])
                @endif
                @if($recruitment->state == 'hr_lead_approval')
                    <div class="col-sm-2 mb-3">
                        <div class="d-flex align-items-center">
                            <label class="form-label" for="probation_period" style="margin-bottom: 0">Próbaidő&nbsp;</label>
                            <input class="form-control numeral-mask" type="text" id="probation_period">
                            <span class="ms-2">Nap</span>
                        </div>
                    </div>
                @endif
                @if($recruitment->state == 'proof_of_coverage' &&
                    (($recruitment->base_salary_cc1 && $recruitment->base_salary_cc1->leadUser == Auth::user() && $recruitment->base_salary_cc1->type && ($recruitment->base_salary_cc1->type->tender || $recruitment->base_salary_cc1->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->base_salary_cc2 && $recruitment->base_salary_cc2->leadUser == Auth::user() && $recruitment->base_salary_cc2->type && ($recruitment->base_salary_cc2->type->tender || $recruitment->base_salary_cc2->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->base_salary_cc3 && $recruitment->base_salary_cc3->leadUser == Auth::user() && $recruitment->base_salary_cc3->type && ($recruitment->base_salary_cc3->type->tender || $recruitment->base_salary_cc3->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->health_allowance_cc && $recruitment->health_allowance_cc->leadUser == Auth::user() && $recruitment->health_allowance_cc->type && ($recruitment->health_allowance_cc->type->tender || $recruitment->health_allowance_cc->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->management_allowance_cc && $recruitment->management_allowance_cc->leadUser == Auth::user() && $recruitment->management_allowance_cc->type && ($recruitment->management_allowance_cc->type->tender || $recruitment->management_allowance_cc->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->extra_pay_1_cc && $recruitment->extra_pay_1_cc->leadUser == Auth::user() && $recruitment->extra_pay_1_cc->type && ($recruitment->extra_pay_1_cc->type->tender || $recruitment->extra_pay_1_cc->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->extra_pay_2_cc && $recruitment->extra_pay_2_cc->leadUser == Auth::user() && $recruitment->extra_pay_2_cc->type && ($recruitment->extra_pay_2_cc->type->tender || $recruitment->extra_pay_2_cc->type->name == "Vállalkozási tevékenység"))))
                    <div class="col-sm-2 mb-3">
                        <input class="form-check-input" type="checkbox" id="post_financed_application">
                        <label class="form-check-label" for="post_financed_application">Utófinanszírozott pályázat?</label>
                    </div>
                @endif
                @if($recruitment->state == 'draft_contract_pending')
                    <div class="col-sm-2 mb-3 mt-4">
                        <a href="{{ route('generate.pdf', ['id' => $id]) }}" class="print-icon-1 me-5" target="_blank" title="Felvételi kérelem">
                            <i class="fa fa-print fs-1"></i>
                        </a>
                        <a href="{{ route('generateMedical.pdf', ['id' => $id]) }}" class="print-icon-2" target="_blank" title="Beutalás munkaköri orvosi alkalmassági vizsgálatra">
                            <i class="fa fa-print fs-1"></i>
                        </a>
                    </div>
                    <div id="message_parent" class="mb-3 d-none">
                        <label class="form-label" for="message">Üzenet</label>
                        <textarea id="message" class="form-control" placeholder="Üzenet..."></textarea>
                    </div>
                @endif
                @if($recruitment->state != 'draft_contract_pending')
                    <div class="mb-3 mt-4">
                        <label class="form-label" for="message">Üzenet</label>
                        <textarea id="message" class="form-control" placeholder="Üzenet..."></textarea>
                    </div>
                @endif
                @if($recruitment->state == 'employee_signature')
                    <div class="mb-3">
                        <label class="form-label" for="contract">Szerződés</label>
                        <form action="/file/upload" class="dropzone needsclick" id="contract" name="contract">
                            @csrf
                            <div class="dz-message needsclick">
                                Húzd ide a fájlt, vagy kattints a feltöltéshez.
                            </div>
                        </form>
                        <input type="hidden" id="contract_file" data-original-name="" name="contract_file" />
                    </div>
                @endif

                <div>
                    Összesített havi bruttó bér: {{ $monthlyGrossSalariesSum }} Ft / hó
                </div>

                @if($recruitment->state == 'draft_contract_pending')
                    <div id="action_buttons" class="d-grid mt-4 d-none">
                        <button type="button" id="approve" class="btn btn-label-success me-2">Jóváhagyás</button>
                        <button type="button" id="reject" class="btn btn-label-danger me-2">Elutasítás</button>
                        <button type="button" id="suspend" class="btn btn-label-warning">Felfüggesztés</button>
                    </div>
                @else
                    <div class="d-grid mt-4 d-md-block decision-controls">
                        <button type="button" id="approve" class="btn btn-label-success me-2">Jóváhagyás</button>
                        <button type="button" id="reject" class="btn btn-label-danger me-2">Elutasítás</button>
                        <button type="button" id="suspend" class="btn btn-label-warning">Felfüggesztés</button>
                    </div>
                @endif    
            </div>
            <div class="tab-pane fade" id="tab_status_history" role="tabpanel">
                <div id="status_history">
                    <table class="table">
                        <thead>
                            <tr>
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
                                <td><span class="badge bg-label-{{ $history_entry['decision'] == 'approve' ? 'success' : ($history_entry['decision'] == 'reject' ? 'danger' : ($history_entry['decision'] == 'suspend' ? 'warning' : ($history_entry['decision'] == 'start' ? 'success' : ($history_entry['decision'] == 'restart' ? 'success' : 'info')))) }} me-1">
                                    {{ $history_entry['decision'] == 'approve' ? 'Jóváhagyás' : ($history_entry['decision'] == 'reject' ? 'Elutasítás' : ($history_entry['decision'] == 'suspend' ? 'Felfüggesztés' : ($history_entry['decision'] == 'start' ? 'Indítás' : ($history_entry['decision'] == 'restart' ? 'Újraindítás' : 'Visszaállítás')))) }}</span></td>                                <td>{{ $history_entry['datetime'] }}</td>
                                <td>{{ $history_entry['user_name'] }}</td>
                                <td>{{ __('states.' . $history_entry['status']) }}</td>
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

<!-- Approve confirmation modal -->
<div class="modal fade" id="approveConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Biztosan szeretnéd jóváhagyni ezt az ügyet?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" id="confirm_approve" data-recruitment-id="{{ $recruitment->id }}" class="btn btn-primary">Jóváhagyás</button>
            </div>
        </div>
    </div>
</div>

<!-- Decision message missing modal -->
<div class="modal fade" id="messageMissing" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p>Amennyiben elutasítod a kérelem jóváhagyását, kérlek írj indoklást az üzenet mezőbe!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Rendben</button>
            </div>
        </div>
    </div>
</div>

<!-- Probation period missing modal -->
<div class="modal fade" id="probationMissing" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p>Amennyiben jóváhagyod a kérelmet, meg kell adnod a próbaidő hosszát, ami 7 és 90 nap között kell legyen!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Rendben</button>
            </div>
        </div>
    </div>
</div>

<!-- Contract missing modal -->
<div class="modal fade" id="contractMissing" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p>Amennyiben jóváhagyod a kérelmet, fel kell töltened a szerződést!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Rendben</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject confirmation modal -->
<div class="modal fade" id="rejectConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Biztosan szeretnéd elutasítani ezt az ügyet és visszaküldeni az indítóhoz?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" id="confirm_reject" data-recruitment-id="{{ $recruitment->id }}" class="btn btn-primary">Elutasítás</button>
            </div>
        </div>
    </div>
</div>

<!-- Suspend confirmation modal -->
<div class="modal fade" id="suspendConfirmation" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Megerősítés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Biztosan szeretnéd felfüggeszteni ezt az ügyet?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" id="confirm_suspend" data-recruitment-id="{{ $recruitment->id }}" class="btn btn-primary">Felfüggesztés</button>
            </div>
        </div>
    </div>
</div>

@endsection
