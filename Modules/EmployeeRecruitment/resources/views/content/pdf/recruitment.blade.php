<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felvételi kérelem</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
        }
        .title {
            text-align: center;
            margin-top: 20px;
            font-size: 24px;
        }
        .fw-bold {
            font-weight: bold;
        }

        .history {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="title">Felvételi kérelem</div>

    <table>
        <tbody>
            <!-- Alapadatok Section -->
            <tr>
                <th colspan="2" class="fw-bold">Alapadatok</th>
            </tr>
            <tr>
                <td>Név</td>
                <td>{{ $recruitment->name }}</td>
            </tr>
            <tr>
                <td>Folyamatindító intézet</td>
                <td>{{ $recruitment->initiatorInstitute ? $recruitment->initiatorInstitute->group_level . ' - ' . $recruitment->initiatorInstitute->name : '' }}</td>
            </tr>
            <tr>
                <td>Felvétel álláshirdetéssel történt</td>
                <td>{{ $recruitment->job_ad_exists ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Álláshirdetésre jelentkezett nők száma</td>
                <td>{{ $recruitment->applicants_female_count }}</td>
            </tr>
            <tr>
                <td>Álláshirdetésre jelentkezett férfiak száma</td>
                <td>{{ $recruitment->applicants_male_count }}</td>
            </tr>
            <tr>
                <td>Volt már munkajogviszonya a Kutatóközponttal</td>
                <td>{{ $recruitment->has_prior_employment ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal</td>
                <td>{{ $recruitment->has_current_volunteer_contract ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Állampolgárság</td>
                <td>{{ $recruitment->citizenship }}</td>
            </tr>
            <tr>
                <td>Csoport 1</td>
                <td>{{ $recruitment->workgroup1 ? $recruitment->workgroup1->workgroup_number . ' - ' . $recruitment->workgroup1->name : '' }}</td>
            </tr>
            <tr>
                <td>Csoport 2</td>
                <td>{{ $recruitment->workgroup2 ? $recruitment->workgroup2->workgroup_number . ' - ' . $recruitment->workgroup2->name : '-' }}</td>
            </tr>
            
            <!-- Jogviszony Section -->
            <tr>
                <th colspan="2" class="fw-bold">Jogviszony</th>
            </tr>
            <tr>
                <td>Munkakör típusa</td>
                <td>{{ $recruitment->position ? $recruitment->position->type : '-' }}</td>
            </tr>
            <tr>
                <td>Munkakör</td>
                <td>{{ $recruitment->position ? $recruitment->position->name : '-' }}</td>
            </tr>
            <tr>
                <td>Munkaköri leírás</td>
                <td>{{ $recruitment->job_description ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Jogviszony típusa</td>
                <td>{{ $recruitment->employment_type }}</td>
            </tr>
            <tr>
                <td>Feladat</td>
                <td>{{ $recruitment->task ? $recruitment->task : '-' }}</td>
            </tr>
            <tr>
                <td>Jogviszony kezdete</td>
                <td>{{ $recruitment->employment_start_date }}</td>
            </tr>
            <tr>
                <td>Jogviszony vége</td>
                <td>{{ $recruitment->employment_end_date }}</td>
            </tr>
            
            <!-- Bérelemek Section -->
            <tr>
                <th colspan="2" class="fw-bold">Bérelemek</th>
            </tr>
            <!-- Alapbér -->
            <tr>
                <th colspan="2" class="fw-bold">Alapbér</th>
            </tr>
            <tr>
                <td>Költséghely 1</td>
                <td>{{ $recruitment->base_salary_cc1 ? $recruitment->base_salary_cc1->cost_center_code . ' - ' . $recruitment->base_salary_cc1->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 1</td>
                <td>{{ $recruitment->base_salary_monthly_gross_1 ? number_format($recruitment->base_salary_monthly_gross_1, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            <tr>
                <td>Költséghely 2</td>
                <td>{{ $recruitment->base_salary_cc2 ? $recruitment->base_salary_cc2->cost_center_code . ' - ' . $recruitment->base_salary_cc2->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 2</td>
                <td>{{ $recruitment->base_salary_monthly_gross_2 ? number_format($recruitment->base_salary_monthly_gross_2, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            <tr>
                <td>Költséghely 3</td>
                <td>{{ $recruitment->base_salary_cc3 ? $recruitment->base_salary_cc3->cost_center_code . ' - ' . $recruitment->base_salary_cc3->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 3</td>
                <td>{{ $recruitment->base_salary_monthly_gross_3 ? number_format($recruitment->base_salary_monthly_gross_3, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            
            <!-- Egészségügyi pótlék -->
            <tr>
                <th colspan="2" class="fw-bold">Egészségügyi pótlék</th>
            </tr>
            <tr>
                <td>Költséghely 4</td>
                <td>{{ $recruitment->health_allowance_cc ? $recruitment->health_allowance_cc->cost_center_code . ' - ' . $recruitment->health_allowance_cc->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 4</td>
                <td>{{ $recruitment->health_allowance_monthly_gross_4 ? number_format($recruitment->health_allowance_monthly_gross_4, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            
            <!-- Vezetői pótlék -->
            <tr>
                <th colspan="2" class="fw-bold">Vezetői pótlék</th>
            </tr>
            <tr>
                <td>Költséghely 5</td>
                <td>{{ $recruitment->management_allowance_cc ? $recruitment->management_allowance_cc->cost_center_code . ' - ' . $recruitment->management_allowance_cc->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 5</td>
                <td>{{ $recruitment->management_allowance_monthly_gross_5 ? number_format($recruitment->management_allowance_monthly_gross_5, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            <tr>
                <td>Időtartam vége</td>
                <td>{{ $recruitment->management_allowance_end_date }}</td>
            </tr>
            
            <!-- Bérpótlék 1 -->
            <tr>
                <th colspan="2" class="fw-bold">Bérpótlék 1</th>
            </tr>
            <tr>
                <td>Költséghely 6</td>
                <td>{{ $recruitment->extra_pay_1_cc ? $recruitment->extra_pay_1_cc->cost_center_code . ' - ' . $recruitment->extra_pay_1_cc->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 6</td>
                <td>{{ $recruitment->extra_pay_1_monthly_gross_6 ? number_format($recruitment->extra_pay_1_monthly_gross_6, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            <tr>
                <td>Időtartam vége</td>
                <td>{{ $recruitment->extra_pay_1_end_date }}</td>
            </tr>
            
            <!-- Bérpótlék 2 -->
            <tr>
                <th colspan="2" class="fw-bold">Bérpótlék 2</th>
            </tr>
            <tr>
                <td>Költséghely 7</td>
                <td>{{ $recruitment->extra_pay_2_cc ? $recruitment->extra_pay_2_cc->cost_center_code . ' - ' . $recruitment->extra_pay_2_cc->name : '-' }}</td>
            </tr>
            <tr>
                <td>Havi bruttó bér 7</td>
                <td>{{ $recruitment->extra_pay_1_monthly_gross_7 ? number_format($recruitment->extra_pay_1_monthly_gross_7, 0, ',', ' ') . ' Ft' : '-' }}</td>
            </tr>
            <tr>
                <td>Időtartam vége</td>
                <td>{{ $recruitment->extra_pay_2_end_date }}</td>
            </tr>
            
            <!-- Munkaidő Section -->
            <tr>
                <th colspan="2" class="fw-bold">Munkaidő</th>
            </tr>
            <tr>
                <td>Heti munkaóraszám</td>
                <td>{{ $recruitment->weekly_working_hours }}</td>
            </tr>
            <tr>
                <th colspan="2" class="fw-bold"><strong>Munkaidő</strong></th>
            </tr>
            <tr>
                <td>Hétfő</td>
                <td>{{ Carbon::parse($recruitment->work_start_monday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_monday)->format('H:i') }}</td>
            </tr>
            <tr>
                <td>Kedd</td>
                <td>{{ Carbon::parse($recruitment->work_start_tuesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_tuesday)->format('H:i') }}</td>
            </tr>
            <tr>
                <td>Szerda</td>
                <td>{{ Carbon::parse($recruitment->work_start_wednesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_wednesday)->format('H:i') }}</td>
            </tr>
            <tr>
                <td>Csütörtök</td>
                <td>{{ Carbon::parse($recruitment->work_start_thursday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_thursday)->format('H:i') }}</td>
            </tr>
            <tr>
                <td>Péntek</td>
                <td>{{ Carbon::parse($recruitment->work_start_friday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_friday)->format('H:i') }}</td>
            </tr>
            
            <!-- Egyéb adatok Section -->
            <tr>
                <th colspan="2" class="fw-bold">Egyéb adatok</th>
            </tr>
            <tr>
                <td>Javasolt email cím</td>
                <td>{{ $recruitment->email }}</td>
            </tr>
            <tr>
                <td>Belépési jogosultságok</td>
                <td>{{ $recruitment->entry_permissions ? $recruitment->entry_permissions : '-' }}</td>
            </tr>
            <tr>
                <td>Rendszám</td>
                <td>{{ $recruitment->license_plate ? $recruitment->license_plate : '-' }}</td>
            </tr>
            <tr>
                <td>Dolgozószoba</td>
                <td>{{ $recruitment->employee_room ? $recruitment->employee_room : '-' }}</td>
            </tr>
            <tr>
                <td>Telefon mellék</td>
                <td>{{ $recruitment->phone_extension ? $recruitment->phone_extension : '-' }}</td>
            </tr>
            <tr>
                <td>Hozzáférési jogosultságok</td>
                <td>
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
                </td>
            </tr>
            <tr>
                <td>Munkavégzéshez rendelkezésre álló eszközök</td>
                <td>
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
                </td>
            </tr>
            <tr>
                <td>Rendelkezésre álló eszközök leltári száma</td>
                <td>
                    @if($recruitment->inventory_numbers_of_available_tools)
                        @php
                            $tools = json_decode($recruitment->inventory_numbers_of_available_tools, true);
                        @endphp
                        @foreach($tools as $tool)
                            @foreach($tool as $key => $value)
                                <span class="ms-1 text-break">{{ ucfirst(trans('tools.' . $key)) . ': ' . $value }}</span><br/>
                            @endforeach
                        @endforeach
                    @else
                        <span class="ms-1 text-break">-</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Sugárzó izotóppal fog dolgozni</td>
                <td>{{ $recruitment->work_with_radioactive_isotopes ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Rákkeltő anyaggal fog dolgozni?</td>
                <td>{{ $recruitment->work_with_carcinogenic_materials ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Használni tervezett rákkeltő anyagok felsorolása</td>
                <td>{{ $recruitment->planned_carcinogenic_materials_use ? $recruitment->planned_carcinogenic_materials_use : '-' }}</td>
            </tr>
            
            <!-- Dokumentumok Section -->
            <tr>
                <th colspan="2" class="fw-bold">Dokumentumok</th>
            </tr>
            <tr>
                <td>Személyi adatlap</td>
                <td>{{ $recruitment->personal_data_sheet ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Hallgatói jogviszony igazolás</td>
                <td>{{ $recruitment->student_status_verification ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Bizonyítványok</td>
                <td>{{ $recruitment->certificates ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Munkába járási támogatást igényel</td>
                <td>{{ $recruitment->requires_commute_support ? 'Igen' : 'Nem' }}</td>
            </tr>
            <tr>
                <td>Munkába járási adatlap</td>
                <td>{{ $recruitment->commute_support_form ? 'Igen' : 'Nem' }}</td>
            </tr>
            
            <!-- Kiegészítő adatok Section -->
            <tr>
                <th colspan="2" class="fw-bold">Kiegészítő adatok</th>
            </tr>
            <tr>
                <td>Próbaidő hossza</td>
                <td>{{ $recruitment->probation_period ? $recruitment->probation_period : '-' }} nap</td>
            </tr>
            <tr>
                <td>Szerződés</td>
                <td>{{ $recruitment->contract ? 'Igen' : 'Nem' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table history">
        <thead>
            <tr>
                <th colspan="5" class="fw-bold">Státusztörténet</th>
            </tr>
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
                <td><span class="badge bg-label-{{ $history_entry['decision'] == 'approve' ? 'success' : ($history_entry['decision'] == 'reject' ? 'danger' : ($history_entry['decision'] == 'suspend' ? 'warning' : ($history_entry['decision'] == 'start' ? 'success' : ($history_entry['decision'] == 'restart' ? 'success' : ($history_entry['decision'] == 'delete' ? 'danger' : 'info'))))) }} me-1">
                    {{ $history_entry['decision'] == 'approve' ? 'Jóváhagyás' : ($history_entry['decision'] == 'reject' ? 'Elutasítás' : ($history_entry['decision'] == 'suspend' ? 'Felfüggesztés' : ($history_entry['decision'] == 'start' ? 'Indítás' : ($history_entry['decision'] == 'restart' ? 'Újraindítás' : ($history_entry['decision'] == 'delete' ? 'Törlés' : 'Visszaállítás'))))) }}</span></td>
                <td>{{ $history_entry['datetime'] }}</td>
                <td>{{ $history_entry['user_name'] }}</td>
                <td>{{ __('states.' . $history_entry['status']) }}</td>
                <td>{{ $history_entry['message'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
