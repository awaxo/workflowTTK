@extends('layouts/layoutMaster')

@section('title', 'Folyamatok / ' . $recruitment->name)

@section('vendor-style')
    @vite([
    // Add paths to the necessary CSS files for the page
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/cleavejs/cleave.js',
        'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
    ])
@endsection

@section('page-script')
    @vite([
        'Modules/EmployeeRecruitment/resources/assets/js/approve-recruitment.js'
    ])
@endsection

@section('content')
<h4 class="py-3 mb-4">Folyamat jóváhagyás / <span class="dynamic-part">{{ $recruitment->name }}</span></h4>

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
                <input type="hidden" id="state" value="{{ $recruitment->state }}">
                @if($recruitment->state == 'hr_lead_approval')
                    <div class="col-sm-2 mb-3">
                        <label class="form-label" for="probation_period">Próbaidő</label>
                        <input class="form-control numeral-mask" type="text" id="probation_period" placeholder="Próbaidő...">
                    </div>
                @endif
                @if($recruitment->state == 'proof_of_coverage' &&
                    ($recruitment->base_salary_cc1 && $recruitment->base_salary_cc1->leadUser == Auth::user() && $recruitment->base_salary_cc1->type && ($recruitment->base_salary_cc1->type->tender || $recruitment->base_salary_cc1->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->base_salary_cc2 && $recruitment->base_salary_cc2->leadUser == Auth::user() && $recruitment->base_salary_cc2->type && ($recruitment->base_salary_cc2->type->tender || $recruitment->base_salary_cc2->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->base_salary_cc3 && $recruitment->base_salary_cc3->leadUser == Auth::user() && $recruitment->base_salary_cc3->type && ($recruitment->base_salary_cc3->type->tender || $recruitment->base_salary_cc3->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->health_allowance_cc && $recruitment->health_allowance_cc->leadUser == Auth::user() && $recruitment->health_allowance_cc->type && ($recruitment->health_allowance_cc->type->tender || $recruitment->health_allowance_cc->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->management_allowance_cc && $recruitment->management_allowance_cc->leadUser == Auth::user() && $recruitment->management_allowance_cc->type && ($recruitment->management_allowance_cc->type->tender || $recruitment->management_allowance_cc->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->extra_pay_1_cc && $recruitment->extra_pay_1_cc->leadUser == Auth::user() && $recruitment->extra_pay_1_cc->type && ($recruitment->extra_pay_1_cc->type->tender || $recruitment->extra_pay_1_cc->type->name == "Vállalkozási tevékenység")) ||
                    ($recruitment->extra_pay_2_cc && $recruitment->extra_pay_2_cc->leadUser == Auth::user() && $recruitment->extra_pay_2_cc->type && ($recruitment->extra_pay_2_cc->type->tender || $recruitment->extra_pay_2_cc->type->name == "Vállalkozási tevékenység")))
                    <div class="col-sm-2 mb-3">
                        <input class="form-check-input" type="checkbox" id="post_financed_application">
                        <label class="form-check-label" for="post_financed_application">Utófinanszírozott pályázat?</label>
                    </div>
                @endif
                @if($recruitment->state == 'draft_contract_pending')
                    <div class="col-sm-2 mb-3">
                        <a href="{{ route('generate.pdf', ['id' => $id]) }}" target="_blank">
                            <i class="fa fa-print fs-1"></i>
                        </a>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label" for="message">Üzenet</label>
                    <textarea id="message" class="form-control" placeholder="Üzenet..."></textarea>
                </div>
                <div class="d-grid mt-4 d-md-block">
                    <button type="button" id="approve" class="btn btn-label-success me-2">Jóváhagyás</button>
                    <button type="button" id="reject" class="btn btn-label-danger me-2">Elutasítás</button>
                    <button type="button" id="suspend" class="btn btn-label-warning">Felfüggesztés</button>
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

                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading_documents">
                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_additional_data" aria-expanded="false" aria-controls="collapse_additional_data">Kiegészítő adatok</button>
                        </h2>
                        <div id="collapse_additional_data" class="accordion-collapse collapse" aria-labelledby="heading_additional_data" data-bs-parent="#accordion_process_details">
                            <div class="accordion-body">
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Próbaidő hossza</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->probation_period ? $recruitment->probation_period : '-' }} nap</span>
                                </div>
                                <div class="d-flex">
                                    <label class="form-label col-6 col-md-3">Szerződés</label>
                                    <span class="fw-bold ms-1">{{ $recruitment->contract ? $recruitment->contract : '-' }}</span>
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

<!-- Probation period missing modal -->
<div class="modal fade" id="probationMissing" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p>Amennyiben jóváhagyod a kérelmet, meg kell adnod a próbaidő hosszát!</p>
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
