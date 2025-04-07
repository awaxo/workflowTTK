<div class="card accordion-item">
    <h2 class="accordion-header" id="heading_base_data">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_base_data" aria-expanded="false" aria-controls="collapse_base_data">Alapadatok</button>
    </h2>
    <div id="collapse_base_data" class="accordion-collapse collapse show" aria-labelledby="heading_base_data">
        <div class="accordion-body">
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Név</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->name }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Születési dátum</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->birth_date }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Folyamatindító intézet</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->initiatorInstitute ? $recruitment->initiatorInstitute->group_level . ' - ' . $recruitment->initiatorInstitute->name : '' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Felvétel álláshirdetéssel történt</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->job_ad_exists)
                        <span class="badge bg-success">Igen</span>
                    @else
                        <span class="badge bg-secondary">Nem</span>
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Álláshirdetésre jelentkezett nők száma</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->applicants_female_count }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Álláshirdetésre jelentkezett férfiak száma</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->applicants_male_count }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Volt már munkajogviszonya a Kutatóközponttal</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->has_prior_employment)
                        <span class="badge bg-success">Igen</span>
                    @else
                        <span class="badge bg-secondary">Nem</span>
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->has_current_volunteer_contract)
                        <span class="badge bg-success">Igen</span>
                    @else
                        <span class="badge bg-secondary">Nincs</span>
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Jelenleg nyugdíjas</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->is_retired)
                        <span class="badge bg-success">Igen</span>
                    @else
                        <span class="badge bg-secondary">Nem</span>
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Állampolgárság</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->citizenship }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Csoport 1</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->workgroup1 ? $recruitment->workgroup1->workgroup_number . ' - ' . $recruitment->workgroup1->name : '' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Csoport 2</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->workgroup2 ? $recruitment->workgroup2->workgroup_number . ' - ' . $recruitment->workgroup2->name : '-' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card accordion-item">
    <h2 class="accordion-header" id="heading_legal_relationship">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_legal_relationship" aria-expanded="false" aria-controls="collapse_legal_relationship">Jogviszony</button>
    </h2>
    <div id="collapse_legal_relationship" class="accordion-collapse collapse show" aria-labelledby="heading_legal_relationship">
        <div class="accordion-body">
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkakör típusa</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->position ? $recruitment->position->type : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkakör</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->position ? $recruitment->position->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkaköri leírás</label>
                @if($recruitment->job_description)
                    <span class="fw-bold ms-1 text-break"><a href="/dokumentumok/{{ $recruitment->job_description }}" target="_blank">munkaköri leírás megtekintése</a></span>
                @else
                    <span class="fw-bold ms-1 text-break">-</span>
                @endif
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Jogviszony típusa</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->employment_type }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Feladat</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->task ? $recruitment->task : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Jogviszony kezdete</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->employment_start_date }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Jogviszony vége</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->employment_end_date }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkáltatói járulék</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->employer_contribution }} %</span>
            </div>
        </div>
    </div>
</div>

<div class="card accordion-item">
    <h2 class="accordion-header" id="heading_working_hours">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_working_hours" aria-expanded="false" aria-controls="collapse_working_hours">Munkaidő</button>
    </h2>
    <div id="collapse_working_hours" class="accordion-collapse collapse show" aria-labelledby="heading_working_hours">
        <div class="accordion-body">
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Heti munkaóraszám</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->weekly_working_hours }}</span>
            </div>

            <p class="mb-1"><strong>Munkaidő</strong></p>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Hétfő</label>
                <span class="fw-bold ms-1 text-break">{{ Carbon::parse($recruitment->work_start_monday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_monday)->format('H:i') }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Kedd</label>
                <span class="fw-bold ms-1 text-break">{{ Carbon::parse($recruitment->work_start_tuesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_tuesday)->format('H:i') }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Szerda</label>
                <span class="fw-bold ms-1 text-break">{{ Carbon::parse($recruitment->work_start_wednesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_wednesday)->format('H:i') }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Csütörtök</label>
                <span class="fw-bold ms-1 text-break">{{ Carbon::parse($recruitment->work_start_thursday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_thursday)->format('H:i') }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Péntek</label>
                <span class="fw-bold ms-1 text-break">{{ Carbon::parse($recruitment->work_start_friday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_friday)->format('H:i') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card accordion-item">
    <h2 class="accordion-header" id="heading_salary_elements">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_salary_elements" aria-expanded="false" aria-controls="collapse_salary_elements">Bérelemek</button>
    </h2>
    <div id="collapse_salary_elements" class="accordion-collapse collapse show" aria-labelledby="heading_salary_elements">
        <div class="accordion-body">
            <p class="mb-1"><strong>Alapbér</strong></p>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely 1</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->base_salary_cc1 ? $recruitment->base_salary_cc1->cost_center_code . ' - ' . $recruitment->base_salary_cc1->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó alapbér 1</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->base_salary_monthly_gross_1 ? number_format($recruitment->base_salary_monthly_gross_1, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely 2</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->base_salary_cc2 ? $recruitment->base_salary_cc2->cost_center_code . ' - ' . $recruitment->base_salary_cc2->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó alapbér 2</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->base_salary_monthly_gross_2 ? number_format($recruitment->base_salary_monthly_gross_2, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely 3</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->base_salary_cc3 ? $recruitment->base_salary_cc3->cost_center_code . ' - ' . $recruitment->base_salary_cc3->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó alapbér 3</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->base_salary_monthly_gross_3 ? number_format($recruitment->base_salary_monthly_gross_3, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>

            <p class="mb-1"><strong>Egészségügyi pótlék</strong></p>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->health_allowance_cc ? $recruitment->health_allowance_cc->cost_center_code . ' - ' . $recruitment->health_allowance_cc->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó egészségügyi pótlék</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->health_allowance_monthly_gross_4 ? number_format($recruitment->health_allowance_monthly_gross_4, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>

            <p class="mb-1"><strong>Vezetői pótlék</strong></p>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->management_allowance_cc ? $recruitment->management_allowance_cc->cost_center_code . ' - ' . $recruitment->management_allowance_cc->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó vezetői pótlék</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->management_allowance_monthly_gross_5 ? number_format($recruitment->management_allowance_monthly_gross_5, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Időtartam vége</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->management_allowance_end_date }}</span>
            </div>

            <p class="mb-1"><strong>Bérpótlék 1</strong></p>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->extra_pay_1_cc ? $recruitment->extra_pay_1_cc->cost_center_code . ' - ' . $recruitment->extra_pay_1_cc->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó illetménykiegészítés 1</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->extra_pay_1_monthly_gross_6 ? number_format($recruitment->extra_pay_1_monthly_gross_6, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Időtartam vége</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->extra_pay_1_end_date }}</span>
            </div>

            <p class="mb-1"><strong>Bérpótlék 2</strong></p>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Költséghely</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->extra_pay_2_cc ? $recruitment->extra_pay_2_cc->cost_center_code . ' - ' . $recruitment->extra_pay_2_cc->name : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Havi bruttó illetménykiegészítés 2</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->extra_pay_2_monthly_gross_7 ? number_format($recruitment->extra_pay_2_monthly_gross_7, 0, ',', ' ') . ' Ft' : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Időtartam vége</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->extra_pay_2_end_date }}</span>
            </div>

            <hr>
            <p class="mb-1"><strong>Összesített havi bruttó bér: {{ $monthlyGrossSalariesSum }} Ft / hó</strong></p>
            <p class="mb-1"><strong>Fedezetigazolandó összeg:</strong></p>
            <div class="d-flex flex-column" style="width: 50%;">
                <div class="d-flex">
                    <div class="p-2 flex-fill"><strong>Év</strong></div>
                    <div class="p-2 flex-fill"><strong>Összeg</strong></div>
                </div>
                @foreach ($amountToCover as $yearAmount)
                    <div class="d-flex">
                        <div class="p-2 flex-fill">{{ $yearAmount[0] }}</div>
                        <div class="p-2 flex-fill">{{ $yearAmount[1] }} Ft</div>
                    </div>
                @endforeach
            </div>
            <p class="mb-1"><strong>Teljes fedezetigazolandó összeg: {{ $totalAmountToCover }} Ft</strong></p>
        </div>
    </div>
</div>

<div class="card accordion-item">
    <h2 class="accordion-header" id="heading_other_details">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_other_details" aria-expanded="false" aria-controls="collapse_other_details">Egyéb adatok</button>
    </h2>
    <div id="collapse_other_details" class="accordion-collapse collapse show" aria-labelledby="heading_other_details">
        <div class="accordion-body">
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Javasolt email cím</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->email }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Belépési jogosultságok</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->entry_permissions)
                        @php
                            $entriesArray = explode(',', $recruitment->entry_permissions);
                            $translatedEntries = array_map(function($entry) {
                                $translation = trans('entries.' . $entry);
                                return $translation === 'entries.' . $entry ? $entry : $translation;
                            }, $entriesArray);
                            $translatedEntries = implode(', ', $translatedEntries);
                        @endphp
                        {{ $translatedEntries }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Rendszám</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->license_plate ? $recruitment->license_plate : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Dolgozószoba</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->employee_room ? $recruitment->employee_room : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Telefon mellék</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->phone_extension ? $recruitment->phone_extension : '-' }}</span>
            </div>
            @if($recruitment->external_access_rights)
                <div class="d-flex">
                    <label class="form-label col-6 col-md-3">Hozzáférési jogosultságok</label>
                    <span class="fw-bold ms-1 text-break">
                        {{ $externalSystemsList }}
                    </span>
                </div>
            @endif
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkavégzéshez szükséges eszközök</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->required_tools)
                        @php
                            $toolsArray = explode(',', $recruitment->required_tools);
                            $translatedTools = array_map(function($tool) {
                                return trans('tools.' . $tool);
                            }, $toolsArray);
                            $toolsString = implode(', ', $translatedTools);
                        @endphp
                        {{ $toolsString }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkavégzéshez rendelkezésre álló eszközök</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->available_tools)
                        @php
                            $toolsArray = explode(',', $recruitment->available_tools);
                            $translatedTools = array_map(function($tool) {
                                return trans('tools.' . $tool);
                            }, $toolsArray);
                            $toolsString = implode(', ', $translatedTools);
                        @endphp
                        {{ $toolsString }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Rendelkezésre álló eszközök leltári száma</label>
                <div>
                    @if($recruitment->inventory_numbers_of_available_tools)
                        @php
                            $tools = json_decode($recruitment->inventory_numbers_of_available_tools, true);
                        @endphp
                        @foreach($tools as $tool)
                            @foreach($tool as $key => $value)
                                <span class="fw-bold ms-1 text-break">{{ ucfirst(trans('tools.' . $key)) . ': ' . $value }}</span><br/>
                            @endforeach
                        @endforeach
                    @else
                        <span class="fw-bold ms-1 text-break">-</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card accordion-item">
    <h2 class="accordion-header" id="heading_documents">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_documents" aria-expanded="false" aria-controls="collapse_documents">Dokumentumok</button>
    </h2>
    <div id="collapse_documents" class="accordion-collapse collapse show" aria-labelledby="heading_documents">
        <div class="accordion-body">
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Hallgatói jogviszony igazolás</label>
                @if($recruitment->student_status_verification)
                    <span class="fw-bold ms-1 text-break"><a href="/dokumentumok/{{ $recruitment->student_status_verification }}" target="_blank">hallgatói jogviszony igazolás megtekintése</a></span>
                @else
                    <span class="fw-bold ms-1 text-break">-</span>
                @endif
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Bizonyítványok</label>
                @if($recruitment->certificates)
                    <span class="fw-bold ms-1 text-break"><a href="/dokumentumok/{{ $recruitment->certificates }}" target="_blank">bizonyítványok megtekintése</a></span>
                @else
                    <span class="fw-bold ms-1 text-break">-</span>
                @endif
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkába járási támogatást igényel</label>
                <span class="fw-bold ms-1 text-break">
                    @if($recruitment->requires_commute_support)
                        <span class="badge bg-success">Igen</span>
                    @else
                        <span class="badge bg-secondary">Nem</span>
                    @endif
                </span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Munkába járási adatlap</label>
                @if($recruitment->commute_support_form)
                    <span class="fw-bold ms-1 text-break"><a href="/dokumentumok/{{ $recruitment->commute_support_form }}" target="_blank">munkába járási adatlap megtekintése</a></span>
                @else
                    <span class="fw-bold ms-1 text-break">-</span>
                @endif
            </div>
            <div class="d-flex">
                <span class="fw-bold ms-1 text-break">{{ $recruitment->initiator_comment ?: 'Nincs megjegyzés' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card accordion-item mb-5">
    <h2 class="accordion-header" id="heading_documents">
        <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_additional_data" aria-expanded="false" aria-controls="collapse_additional_data">Kiegészítő adatok</button>
    </h2>
    <div id="collapse_additional_data" class="accordion-collapse collapse show" aria-labelledby="heading_additional_data">
        <div class="accordion-body">
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Próbaidő hossza</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->probation_period ? $recruitment->probation_period : '-' }} nap</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Szerződés</label>
                @if($recruitment->contract)
                    <span class="fw-bold ms-1 text-break"><a href="/dokumentumok/{{ $recruitment->contract }}" target="_blank">szerződés megtekintése</a></span>
                @else
                    <span class="fw-bold ms-1 text-break">-</span>
                @endif
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Szerződés kötelezettségvállalási szám</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->obligee_number ? $recruitment->obligee_number : '-' }}</span>
            </div>
            <div class="d-flex">
                <label class="form-label col-6 col-md-3">Szerződés iktatószám</label>
                <span class="fw-bold ms-1 text-break">{{ $recruitment->contract_registration_number ? $recruitment->contract_registration_number : '-' }}</span>
            </div>
        </div>
    </div>
</div>
