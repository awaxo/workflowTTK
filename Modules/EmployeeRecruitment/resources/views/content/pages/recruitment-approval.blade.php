@extends('layouts/layoutMaster')

@section('title', 'Folyamatok / ' . $recruitment->name)

@section('vendor-style')
    @vite([
    // Add paths to the necessary CSS files for the page
    ])
@endsection

@section('vendor-script')
    @vite([
    // Add paths to the necessary JavaScript files for the page
    ])
@endsection

@section('page-script')
    @vite([
        'Modules/EmployeeRecruitment/resources/assets/js/approve-recruitment.js'
    ])
@endsection

@section('content')
<h4 class="py-3 mb-4">Folyamatok / <span class="dynamic-part">{{ $recruitment->name }}</span></h4>

<!-- Form with Tabs -->
<div class="row">
    <div class="col">
        <div class="nav-align-top mb-3">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_decision" role="tab" aria-selected="true">Jóváhagyás</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_process_details" role="tab" aria-selected="false">Folyamat részletek</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active show" id="tab_decision" role="tabpanel">
            <form>
                <div class="mb-3">
                    <label class="form-label" for="decision_message">Üzenet</label>
                    <textarea id="decision_message" class="form-control" placeholder="Üzenet..."></textarea>
                </div>
                <div class="d-grid mt-4 d-md-block">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#approveConfirmation" class="btn btn-label-success me-2">Jóváhagyás</button>
                    <button type="button" id="reject" class="btn btn-label-danger me-2">Elutasítás</button>
                    <button type="button" class="btn btn-label-warning">Felfüggesztés</button>
                </div>
            </form>
            </div>
            <div class="tab-pane fade" id="tab_process_details" role="tabpanel">
                <div class="accordion" id="accordion_process_details">
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_base_data">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_base_data" aria-expanded="false" aria-controls="collapse_base_data">Alapadatok</button>
                        </h2>
                        <div id="collapse_base_data" class="accordion-collapse collapse" aria-labelledby="heading_base_data" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Név</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->name }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Folyamatindító intézet</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->initiatorInstitute ? $recruitment->initiatorInstitute->group_level . ' - ' . $recruitment->initiatorInstitute->name : '' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Felvétel álláshirdetéssel történt</label>
                                    <span class="fw-bold ms-1">
                                        @if($recruitment->job_ad_exists)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Álláshirdetésre jelentkezett nők száma</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->applicants_female_count }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Álláshirdetésre jelentkezett férfiak száma</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->applicants_male_count }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Volt már munkajogviszonya a Kutatóközponttal</label>
                                    <span class="fw-bold ms-1">
                                        @if($recruitment->has_prior_employment)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal</label>
                                    <span class="fw-bold ms-1">
                                        @if($recruitment->has_current_volunteer_contract)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Állampolgárság</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->citizenship }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Csoport 1</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->workgroup1 ? $recruitment->workgroup1->workgroup_number . ' - ' . $recruitment->workgroup1->name : '' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Csoport 2</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->workgroup2 ? $recruitment->workgroup2->workgroup_number . ' - ' . $recruitment->workgroup2->name : '-' }}</span>
                                </div>                                
                            </div>
                        </div>
                    </div>
                
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_legal_relationship">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_legal_relationship" aria-expanded="false" aria-controls="collapse_legal_relationship">Jogviszony</button>
                        </h2>
                        <div id="collapse_legal_relationship" class="accordion-collapse collapse" aria-labelledby="heading_legal_relationship" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Munkakör típusa</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->position ? $recruitment->position->type : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Munkakör</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->position ? $recruitment->position->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Munkaköri leírás</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->job_description }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Jogviszony típusa</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->employment_type }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Feladat</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->task ? $recruitment->task : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Jogviszony kezdete</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->employment_start_date }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Jogviszony vége</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->employment_end_date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_salary_elements">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_salary_elements" aria-expanded="false" aria-controls="collapse_salary_elements">Bérelemek</button>
                        </h2>
                        <div id="collapse_salary_elements" class="accordion-collapse collapse" aria-labelledby="heading_salary_elements" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <p class="mb-1"><strong>Alapbér</strong></p>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 1</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->base_salary_cc1 ? $recruitment->base_salary_cc1->cost_center_code . ' - ' . $recruitment->base_salary_cc1->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 1</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->base_salary_monthly_gross_1 ? number_format($recruitment->base_salary_monthly_gross_1, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 2</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->base_salary_cc2 ? $recruitment->base_salary_cc2->cost_center_code . ' - ' . $recruitment->base_salary_cc2->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 2</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->base_salary_monthly_gross_2 ? number_format($recruitment->base_salary_monthly_gross_2, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 3</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->base_salary_cc3 ? $recruitment->base_salary_cc3->cost_center_code . ' - ' . $recruitment->base_salary_cc3->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 3</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->base_salary_monthly_gross_3 ? number_format($recruitment->base_salary_monthly_gross_3, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>

                                <p class="mb-1"><strong>Egészségügyi pótlék</strong></p>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 4</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->health_allowance_cc ? $recruitment->health_allowance_cc->cost_center_code . ' - ' . $recruitment->health_allowance_cc->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 4</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->health_allowance_monthly_gross_4 ? number_format($recruitment->health_allowance_monthly_gross_4, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>

                                <p class="mb-1"><strong>Vezetői pótlék</strong></p>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 5</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->management_allowance_cc ? $recruitment->management_allowance_cc->cost_center_code . ' - ' . $recruitment->management_allowance_cc->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 5</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->management_allowance_monthly_gross_5 ? number_format($recruitment->management_allowance_monthly_gross_5, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Időtartam vége</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->management_allowance_end_date }}</span>
                                </div>

                                <p class="mb-1"><strong>Bérpótlék 1</strong></p>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 6</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->extra_pay_1_cc ? $recruitment->extra_pay_1_cc->cost_center_code . ' - ' . $recruitment->extra_pay_1_cc->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 6</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->extra_pay_1_monthly_gross_6 ? number_format($recruitment->extra_pay_1_monthly_gross_6, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Időtartam vége</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->extra_pay_1_end_date }}</span>
                                </div>

                                <p class="mb-1"><strong>Bérpótlék 2</strong></p>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Költséghely 7</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->extra_pay_2_cc ? $recruitment->extra_pay_2_cc->cost_center_code . ' - ' . $recruitment->extra_pay_2_cc->name : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Havi bruttó bér 7</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->extra_pay_1_monthly_gross_7 ? number_format($recruitment->extra_pay_1_monthly_gross_7, 0, ',', ' ') . ' Ft' : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Időtartam vége</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->extra_pay_2_end_date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_working_hours">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_working_hours" aria-expanded="false" aria-controls="collapse_working_hours">Munkaidő</button>
                        </h2>
                        <div id="collapse_working_hours" class="accordion-collapse collapse" aria-labelledby="heading_working_hours" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Heti munkaóraszám</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->weekly_working_hours }}</span>
                                </div>

                                <p class="mb-1"><strong>Munkaidő</strong></p>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Hétfő</label>
                                    <span class="fw-bold ms-1">{{ Carbon::parse($recruitment->work_start_monday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_monday)->format('H:i') }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Kedd</label>
                                    <span class="fw-bold ms-1">{{ Carbon::parse($recruitment->work_start_tuesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_tuesday)->format('H:i') }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Szerda</label>
                                    <span class="fw-bold ms-1">{{ Carbon::parse($recruitment->work_start_wednesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_wednesday)->format('H:i') }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Csütörtök</label>
                                    <span class="fw-bold ms-1">{{ Carbon::parse($recruitment->work_start_thursday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_thursday)->format('H:i') }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Péntek</label>
                                    <span class="fw-bold ms-1">{{ Carbon::parse($recruitment->work_start_friday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_friday)->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_other_details">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_other_details" aria-expanded="false" aria-controls="collapse_other_details">Egyéb adatok</button>
                        </h2>
                        <div id="collapse_other_details" class="accordion-collapse collapse" aria-labelledby="heading_other_details" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Javasolt email cím</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->email }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Belépési jogosultságok</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->entry_permissions ? $recruitment->entry_permissions : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Rendszám</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->license_plate ? $recruitment->license_plate : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Dolgozószoba</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->employee_room ? $recruitment->employee_room : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Telefon mellék</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->phone_extension ? $recruitment->phone_extension : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Hozzáférési jogosultságok</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->required_tools ? $recruitment->required_tools : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Munkavégzéshez rendelkezésre álló eszközök</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->available_tools ? $recruitment->available_tools : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Rendelkezésre álló eszközök leltári száma</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->inventory_numbers_of_available_tools ? $recruitment->inventory_numbers_of_available_tools : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Sugárzó izotóppal fog dolgozni</label>
                                    <span class="fw-bold ms-1">
                                        @if($recruitment->work_with_radioactive_isotopes)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Rákkeltő anyaggal fog dolgozni?</label>
                                    <span class="fw-bold ms-1">
                                        @if($recruitment->work_with_carcinogenic_materials)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Használni tervezett rákkeltő anyagok felsorolása</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->planned_carcinogenic_materials_use ? $recruitment->planned_carcinogenic_materials_use : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_documents">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_documents" aria-expanded="false" aria-controls="collapse_documents">Dokumentumok</button>
                        </h2>
                        <div id="collapse_documents" class="accordion-collapse collapse" aria-labelledby="heading_documents" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Személyi adatlap</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->personal_data_sheet ? $recruitment->personal_data_sheet : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Hallgatói jogviszony igazolás</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->student_status_verification ? $recruitment->student_status_verification : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Bizonyítványok</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->certificates ? $recruitment->certificates : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Munkába járási támogatást igényel</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->requires_commute_support ? $recruitment->requires_commute_support : '-' }}</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Munkába járási adatlap</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->commute_support_form ? $recruitment->commute_support_form : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
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

@endsection
