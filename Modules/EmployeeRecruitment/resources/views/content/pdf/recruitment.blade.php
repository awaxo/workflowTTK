@php
use App\Models\ExternalAccessRight;
@endphp

@php
use App\Models\Delegation;
use App\Models\CostCenter;

// Collect all cost centers from recruitment (same logic as in the main view)
$allCostCenters = collect([
    $recruitment->base_salary_cc1,
    $recruitment->base_salary_cc2, 
    $recruitment->base_salary_cc3,
    $recruitment->health_allowance_cc,
    $recruitment->management_allowance_cc,
    $recruitment->extra_pay_1_cc,
    $recruitment->extra_pay_2_cc
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
        
        // Find all cost centers where project_coordinator_user_id matches any of the user IDs
        $costCenterCodes = [];
        foreach ($allCostCenters as $costCenter) {
            if ($costCenter && in_array($costCenter->project_coordinator_user_id, $userIdsToCheck)) {
                $costCenterCodes[] = $costCenter->cost_center_code;
            }
        }
        
        // Store all matching cost center codes for this user
        $costCenterCodesCache[$userId] = $costCenterCodes;
    }
}
@endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            .title {
                font-size: 18px;
                font-weight: bold;
                text-align: center;
            }
            .table-data {
                width: 100%;
                border-collapse: collapse;
                border-spacing: 0;
                margin-top: 10px;
                font-size: 13px;
            }
            .history {
                margin-top: 20px;
                font-size: 13px;
            }
        </style>
    </head>
    <body>
        <div class="title">Felvételi kérelem</div><br />

        <div class="mb-2" style="font-size: larger;">
            <div class="">ID: <b>{{ $recruitment->pseudo_id }}/{{ \Carbon\Carbon::parse($recruitment->created_at)->format('Y') }}</b></div>
        </div>

        <table class="table-data">
            <tbody>                
                <!-- Alapadatok Section -->
                <tr>
                    <th colspan="2" class="fw-bold">Alapadatok</th>
                </tr>
                <tr>
                    <td>Név</td>
                    <td>{{ $recruitment->name }}</td>
                </tr>
                @if($recruitment->initiatorInstitute)
                <tr>
                    <td>Folyamatindító intézet</td>
                    <td>{{ $recruitment->initiatorInstitute->group_level . ' - ' . $recruitment->initiatorInstitute->name }}</td>
                </tr>
                @endif
                <tr>
                    <td>Felvétel álláshirdetéssel történt</td>
                    <td>{{ $recruitment->job_ad_exists ? 'Igen' : 'Nem' }}</td>
                </tr>
                @if($recruitment->applicants_female_count)
                <tr>
                    <td>Álláshirdetésre jelentkezett nők száma</td>
                    <td>{{ $recruitment->applicants_female_count }}</td>
                </tr>
                @endif
                @if($recruitment->applicants_male_count)
                <tr>
                    <td>Álláshirdetésre jelentkezett férfiak száma</td>
                    <td>{{ $recruitment->applicants_male_count }}</td>
                </tr>
                @endif
                <tr>
                    <td>Volt már munkajogviszonya a Kutatóközponttal</td>
                    <td>{{ $recruitment->has_prior_employment ? 'Igen' : 'Nem' }}</td>
                </tr>
                <tr>
                    <td>Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal</td>
                    <td>{{ $recruitment->has_current_volunteer_contract ? 'Igen' : 'Nem' }}</td>
                </tr>
                <tr>
                    <td>Jelenleg nyugdíjas</td>
                    <td>{{ $recruitment->is_retired ? 'Igen' : 'Nem' }}</td>
                </tr>
                @if($recruitment->citizenship)
                <tr>
                    <td>Állampolgárság</td>
                    <td>{{ $recruitment->citizenship }}</td>
                </tr>
                @endif
                @if($recruitment->workgroup1)
                <tr>
                    <td>Csoport 1</td>
                    <td>{{ $recruitment->workgroup1->workgroup_number . ' - ' . $recruitment->workgroup1->name }}</td>
                </tr>
                @endif
                @if($recruitment->workgroup2)
                <tr>
                    <td>Csoport 2</td>
                    <td>{{ $recruitment->workgroup2->workgroup_number . ' - ' . $recruitment->workgroup2->name }}</td>
                </tr>
                @endif
                
                <!-- Jogviszony Section -->
                <tr>
                    <th colspan="2" class="fw-bold">Jogviszony</th>
                </tr>
                @if($recruitment->position && $recruitment->position->type)
                <tr>
                    <td>Munkakör típusa</td>
                    <td>{{ $recruitment->position->type }}</td>
                </tr>
                @endif
                @if($recruitment->position && $recruitment->position->name)
                <tr>
                    <td>Munkakör</td>
                    <td>{{ $recruitment->position->name }}</td>
                </tr>
                @endif
                <tr>
                    <td>Munkaköri leírás</td>
                    <td>{{ $recruitment->job_description ? 'Igen' : 'Nem' }}</td>
                </tr>
                @if($recruitment->employment_type)
                <tr>
                    <td>Jogviszony típusa</td>
                    <td>{{ $recruitment->employment_type }}</td>
                </tr>
                @endif
                @if($recruitment->task)
                <tr>
                    <td>Feladat</td>
                    <td>{{ $recruitment->task }}</td>
                </tr>
                @endif
                @if($recruitment->employment_start_date)
                <tr>
                    <td>Jogviszony kezdete</td>
                    <td>{{ $recruitment->employment_start_date }}</td>
                </tr>
                @endif
                @if($recruitment->employment_end_date)
                <tr>
                    <td>Jogviszony vége</td>
                    <td>{{ $recruitment->employment_end_date }}</td>
                </tr>
                @endif
                @if($recruitment->employer_contribution)
                <tr>
                    <td>Munkáltatói járulék</td>
                    <td>{{ $recruitment->employer_contribution }} %</td>
                </tr>
                @endif
                @if($recruitment->probation_period)
                <tr>
                    <td>Próbaidő hossza</td>
                    <td>{{ $recruitment->probation_period }} nap</td>
                </tr>
                @endif

                <!-- Bérelemek Section -->
                @if($monthlyGrossSalariesSum)
                <tr>
                    <th colspan="2" class="fw-bold">Bérelemek</th>
                </tr>
                <tr>
                    <td>Összesített havi bruttó bér</td>
                    <td>{{ $monthlyGrossSalariesSum }} Ft / hó</td>
                </tr>
                @endif
                
                <!-- Alapbér -->
                @if($recruitment->base_salary_cc1 || $recruitment->base_salary_cc2 || $recruitment->base_salary_cc3)
                <tr>
                    <th colspan="2" class="fw-bold">Alapbér</th>
                </tr>
                @endif
                @if($recruitment->base_salary_cc1)
                <tr>
                    <td>Költséghely 1</td>
                    <td>{{ $recruitment->base_salary_cc1->cost_center_code . ' - ' . $recruitment->base_salary_cc1->name }}</td>
                </tr>
                @endif
                @if($recruitment->base_salary_monthly_gross_1)
                <tr>
                    <td>Havi bruttó alapbér 1</td>
                    <td>{{ number_format($recruitment->base_salary_monthly_gross_1, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                @if($recruitment->base_salary_cc2)
                <tr>
                    <td>Költséghely 2</td>
                    <td>{{ $recruitment->base_salary_cc2->cost_center_code . ' - ' . $recruitment->base_salary_cc2->name }}</td>
                </tr>
                @endif
                @if($recruitment->base_salary_monthly_gross_2)
                <tr>
                    <td>Havi bruttó alapbér 2</td>
                    <td>{{ number_format($recruitment->base_salary_monthly_gross_2, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                @if($recruitment->base_salary_cc3)
                <tr>
                    <td>Költséghely 3</td>
                    <td>{{ $recruitment->base_salary_cc3->cost_center_code . ' - ' . $recruitment->base_salary_cc3->name }}</td>
                </tr>
                @endif
                @if($recruitment->base_salary_monthly_gross_3)
                <tr>
                    <td>Havi bruttó alapbér 3</td>
                    <td>{{ number_format($recruitment->base_salary_monthly_gross_3, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                
                <!-- Egészségügyi pótlék -->
                @if($recruitment->health_allowance_cc || $recruitment->health_allowance_monthly_gross_4)
                <tr>
                    <th colspan="2" class="fw-bold">Egészségügyi pótlék</th>
                </tr>
                @endif
                @if($recruitment->health_allowance_cc)
                <tr>
                    <td>Egészségügyi pótlék költséghely</td>
                    <td>{{ $recruitment->health_allowance_cc->cost_center_code . ' - ' . $recruitment->health_allowance_cc->name }}</td>
                </tr>
                @endif
                @if($recruitment->health_allowance_monthly_gross_4)
                <tr>
                    <td>Havi bruttó egészségügyi pótlék</td>
                    <td>{{ number_format($recruitment->health_allowance_monthly_gross_4, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                
                <!-- Vezetői pótlék -->
                @if($recruitment->management_allowance_cc || $recruitment->management_allowance_monthly_gross_5 || $recruitment->management_allowance_end_date)
                <tr>
                    <th colspan="2" class="fw-bold">Vezetői pótlék</th>
                </tr>
                @endif
                @if($recruitment->management_allowance_cc)
                <tr>
                    <td>Vezetői pótlék költséghely</td>
                    <td>{{ $recruitment->management_allowance_cc->cost_center_code . ' - ' . $recruitment->management_allowance_cc->name }}</td>
                </tr>
                @endif
                @if($recruitment->management_allowance_monthly_gross_5)
                <tr>
                    <td>Havi bruttó vezetői pótlék</td>
                    <td>{{ number_format($recruitment->management_allowance_monthly_gross_5, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                @if($recruitment->management_allowance_end_date)
                <tr>
                    <td>Időtartam vége</td>
                    <td>{{ $recruitment->management_allowance_end_date }}</td>
                </tr>
                @endif
                
                <!-- Bérpótlék 1 -->
                @if($recruitment->extra_pay_1_cc || $recruitment->extra_pay_1_monthly_gross_6 || $recruitment->extra_pay_1_end_date)
                <tr>
                    <th colspan="2" class="fw-bold">Bérpótlék 1</th>
                </tr>
                @endif
                @if($recruitment->extra_pay_1_cc)
                <tr>
                    <td>Bérpótlék 1</td>
                    <td>{{ $recruitment->extra_pay_1_cc->cost_center_code . ' - ' . $recruitment->extra_pay_1_cc->name }}</td>
                </tr>
                @endif
                @if($recruitment->extra_pay_1_monthly_gross_6)
                <tr>
                    <td>Havi bruttó illetménykiegészítés 1</td>
                    <td>{{ number_format($recruitment->extra_pay_1_monthly_gross_6, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                @if($recruitment->extra_pay_1_end_date)
                <tr>
                    <td>Időtartam vége</td>
                    <td>{{ $recruitment->extra_pay_1_end_date }}</td>
                </tr>
                @endif
                
                <!-- Bérpótlék 2 -->
                @if($recruitment->extra_pay_2_cc || $recruitment->extra_pay_1_monthly_gross_7 || $recruitment->extra_pay_2_end_date)
                <tr>
                    <th colspan="2" class="fw-bold">Bérpótlék 2</th>
                </tr>
                @endif
                @if($recruitment->extra_pay_2_cc)
                <tr>
                    <td>Bérpótlék 2</td>
                    <td>{{ $recruitment->extra_pay_2_cc->cost_center_code . ' - ' . $recruitment->extra_pay_2_cc->name }}</td>
                </tr>
                @endif
                @if($recruitment->extra_pay_1_monthly_gross_7)
                <tr>
                    <td>Havi bruttó illetménykiegészítés 2</td>
                    <td>{{ number_format($recruitment->extra_pay_1_monthly_gross_7, 0, ',', ' ') . ' Ft' }}</td>
                </tr>
                @endif
                @if($recruitment->extra_pay_2_end_date)
                <tr>
                    <td>Időtartam vége</td>
                    <td>{{ $recruitment->extra_pay_2_end_date }}</td>
                </tr>
                @endif

                <!-- Munkaidő Section -->
                @if($recruitment->weekly_working_hours)
                <tr>
                    <th colspan="2" class="fw-bold">Munkaidő</th>
                </tr>
                <tr>
                    <td>Heti munkaóraszám</td>
                    <td>{{ $recruitment->weekly_working_hours }}</td>
                </tr>
                @endif
                
                @if($recruitment->work_start_monday || $recruitment->work_start_tuesday || $recruitment->work_start_wednesday || $recruitment->work_start_thursday || $recruitment->work_start_friday)
                <tr>
                    <th colspan="2" class="fw-bold">Munkaidő</th>
                </tr>
                @endif
                @if($recruitment->work_start_monday && $recruitment->work_end_monday)
                <tr>
                    <td>Hétfő</td>
                    <td>{{ Carbon::parse($recruitment->work_start_monday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_monday)->format('H:i') }}</td>
                </tr>
                @endif
                @if($recruitment->work_start_tuesday && $recruitment->work_end_tuesday)
                <tr>
                    <td>Kedd</td>
                    <td>{{ Carbon::parse($recruitment->work_start_tuesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_tuesday)->format('H:i') }}</td>
                </tr>
                @endif
                @if($recruitment->work_start_wednesday && $recruitment->work_end_wednesday)
                <tr>
                    <td>Szerda</td>
                    <td>{{ Carbon::parse($recruitment->work_start_wednesday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_wednesday)->format('H:i') }}</td>
                </tr>
                @endif
                @if($recruitment->work_start_thursday && $recruitment->work_end_thursday)
                <tr>
                    <td>Csütörtök</td>
                    <td>{{ Carbon::parse($recruitment->work_start_thursday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_thursday)->format('H:i') }}</td>
                </tr>
                @endif
                @if($recruitment->work_start_friday && $recruitment->work_end_friday)
                <tr>
                    <td>Péntek</td>
                    <td>{{ Carbon::parse($recruitment->work_start_friday)->format('H:i') }} - {{ Carbon::parse($recruitment->work_end_friday)->format('H:i') }}</td>
                </tr>
                @endif
                
                <!-- Egyéb adatok Section -->
                <tr>
                    <th colspan="2" class="fw-bold">Egyéb adatok</th>
                </tr>
                @if($recruitment->email)
                <tr>
                    <td>Javasolt email cím</td>
                    <td>{{ $recruitment->email }}</td>
                </tr>
                @endif
                @if($recruitment->entry_permissions)
                <tr>
                    <td>Belépési jogosultságok</td>
                    <td>
                        @php
                            $entriesArray = explode(',', $recruitment->entry_permissions);
                            $translatedEntries = array_map(function($entry) {
                                $translation = trans('entries.' . $entry);
                                return $translation === 'entries.' . $entry ? $entry : $translation;
                            }, $entriesArray);
                            $translatedEntries = implode(', ', $translatedEntries);
                        @endphp
                        {{ $translatedEntries }}
                    </td>
                </tr>
                @endif
                @if($recruitment->license_plate)
                <tr>
                    <td>Rendszám</td>
                    <td>{{ $recruitment->license_plate }}</td>
                </tr>
                @endif
                @if($recruitment->employee_room)
                <tr>
                    <td>Dolgozószoba</td>
                    <td>{{ $recruitment->employee_room }}</td>
                </tr>
                @endif
                @if($recruitment->phone_extension)
                <tr>
                    <td>Telefon mellék</td>
                    <td>{{ $recruitment->phone_extension }}</td>
                </tr>
                @endif
                @if($recruitment->external_access_rights)
                <tr>
                    <td>Hozzáférési jogosultságok</td>
                    <td>
                        @php
                            try {
                                // External access rights
                                $externalAccessRightsIds = explode(',', $recruitment->external_access_rights);
                                $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
                                // Extract the external_system fields
                                $externalSystems = $externalAccessRights->pluck('external_system')->toArray();
                                $externalSystemsList = implode(', ', $externalSystems);
                            } catch (\Exception $e) {
                                $externalSystemsList = '';
                            }
                        @endphp
                        {{ $externalSystemsList }}
                    </td>
                </tr>
                @endif
                @if($recruitment->required_tools)
                <tr>
                    <td>Munkavégzéshez szükséges eszközök</td>
                    <td>
                        @php
                            $toolsArray = explode(',', $recruitment->required_tools);
                            $translatedTools = array_map(function($tool) {
                                return trans('tools.' . $tool);
                            }, $toolsArray);
                            $toolsString = implode(', ', $translatedTools);
                        @endphp
                        {{ $toolsString }}
                    </td>
                </tr>
                @endif
                @if($recruitment->available_tools)
                <tr>
                    <td>Munkavégzéshez rendelkezésre álló eszközök</td>
                    <td>
                        @php
                            $toolsArray = explode(',', $recruitment->available_tools);
                            $translatedTools = array_map(function($tool) {
                                return trans('tools.' . $tool);
                            }, $toolsArray);
                            $toolsString = implode(', ', $translatedTools);
                        @endphp
                        {{ $toolsString }}
                    </td>
                </tr>
                @endif
                @if($recruitment->inventory_numbers_of_available_tools)
                <tr>
                    <td>Rendelkezésre álló eszközök leltári száma</td>
                    <td>
                        @php
                            $tools = json_decode($recruitment->inventory_numbers_of_available_tools, true);
                        @endphp
                        @foreach($tools as $tool)
                            @foreach($tool as $key => $value)
                                <span class="ms-1 text-break">{{ ucfirst(trans('tools.' . $key)) . ': ' . $value }}</span><br/>
                            @endforeach
                        @endforeach
                    </td>
                </tr>
                @endif
                <tr>
                    <td>Sugárzó izotóppal fog dolgozni</td>
                    <td>{{ $recruitment->work_with_radioactive_isotopes ? 'Igen' : 'Nem' }}</td>
                </tr>
                <tr>
                    <td>Rákkeltő anyaggal fog dolgozni?</td>
                    <td>{{ $recruitment->work_with_carcinogenic_materials ? 'Igen' : 'Nem' }}</td>
                </tr>
                @if($recruitment->planned_carcinogenic_materials_use)
                <tr>
                    <td>Használni tervezett rákkeltő anyagok felsorolása</td>
                    <td>{{ $recruitment->planned_carcinogenic_materials_use }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <table class="table history">
            <thead>
                <tr>
                    <th colspan="3" class="fw-bold">Státusztörténet</th>
                </tr>
                <tr style="background-color: rgba(105,108,255,.16)">
                    <th width="25%">Döntés</th>
                    <th width="30%">Státusz</th>
                    <th width="45%">Üzenet</th>
                </tr>
            </thead>
            <tbody>
            @foreach($history as $history_entry)
                <tr>
                    <td><span class="badge bg-label-{{ $history_entry['decision'] == 'approve' ? 'success' : ($history_entry['decision'] == 'reject' ? 'danger' : ($history_entry['decision'] == 'suspend' ? 'warning' : ($history_entry['decision'] == 'start' ? 'success' : ($history_entry['decision'] == 'restart' ? 'success' : ($history_entry['decision'] == 'delete' ? 'danger' : 'info'))))) }} me-1">
                        {{ $history_entry['decision'] == 'approve' ? 'Jóváhagyás' : ($history_entry['decision'] == 'reject' ? 'Elutasítás' : ($history_entry['decision'] == 'suspend' ? 'Felfüggesztés' : ($history_entry['decision'] == 'start' ? 'Indítás' : ($history_entry['decision'] == 'restart' ? 'Újraindítás' : ($history_entry['decision'] == 'delete' ? 'Törlés' : 'Visszaállítás'))))) }}
                        <br />{{ $history_entry['datetime'] }}
                        <br />{{ $history_entry['user_name'] }}</span></td>
                    <td>
                        @php
                            $statusText = '';
                            $decision = $history_entry['decision'];
                            $status = $history_entry['status'];
                            
                            // Handle special decision types that don't use standard approval workflow
                            if ($decision == 'start') {
                                $statusText = 'Új kérelem';
                            } elseif ($decision == 'suspend') {
                                $statusText = 'Felfüggesztve';
                            } elseif ($decision == 'restore') {
                                $statusText = 'Visszaállítva';
                            } elseif ($decision == 'reject' || $decision == 'restart') {
                                $statusText = 'Kérelem újraellenőrzésére vár';
                            } elseif ($decision == 'cancel') {
                                $statusText = 'Elutasítva';
                            } else {
                                // For standard approval workflow, use appropriate language file
                                if ($decision == 'approve') {
                                    $statusText = __('states-approved.' . $status);
                                } elseif (in_array($decision, ['reject', 'deny'])) {
                                    $statusText = __('states-rejected.' . $status);
                                } else {
                                    // Default to pending status from original states file
                                    $statusText = __('states.' . $status);
                                }
                            }
                            
                            // Add cost center codes for proof_of_coverage status
                            if ($status == 'proof_of_coverage') {
                                $costCenterCodes = $costCenterCodesCache[$history_entry['user_id']] ?? [];
                                if (!empty($costCenterCodes)) {
                                    $statusText .= ' (' . implode(', ', $costCenterCodes) . ')';
                                }
                            }
                        @endphp
                        {{ $statusText }}
                    </td>
                    <td>{{ $history_entry['message'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>