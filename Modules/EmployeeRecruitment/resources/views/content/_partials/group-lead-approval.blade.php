<div id="health_allowance" 
     data-medical="{{ $medical ? json_encode($medical) : '' }}">
</div>
<div class="card">
    <div class="card-body" id="health_allowance_card">
        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">A munkakör (munkahely) főbb egészségkárosító kockázatai</h5>
                <small>33/1998 (VI. 24.) NM rendelet szerint</small>
            </div>
            <!--<i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal" title="Segítség"></i>-->
        </div>

        @php
        // Left column structure (items 1-13 from PDF)
        $leftColumnStructure = [
            'manual_handling' => [
                'label' => 'Kézi anyagmozgatás',
                'subfields' => [
                    'manual_handling_weight_5_20' => '5 kg – 20 kg',
                    'manual_handling_weight_20_50' => '20 kg – 50 kg',
                    'manual_handling_weight_over_50' => '> 50kg'
                ]
            ],
            'increased_accident_risk' => [
                'label' => 'Fokozott baleseti veszély (tűz- és robbanásveszély, feszültség alatti munka, magasban végzett munka, egyéb)',
                'subfields' => [
                    'fire_and_explosion_risk' => 'Tűz- és robbanásveszély',
                    'live_electrical_work' => 'Feszültség alatti munka',
                    'high_altitude_work' => 'Magasban végzett munka',
                    'other_risks' => [
                        'label' => 'Egyéb',
                        'textarea' => [
                            'id' => 'other_risks_description',
                            'label' => 'fokozott baleseti veszéllyel járó kockázati tényező felsorolása'
                        ]
                    ]
                ]
            ],
            'forced_body_position' => 'Kényszertesthelyzet (görnyedés, guggolás)',
            'sitting' => 'Ülés',
            'standing' => 'Állás',
            'walking' => 'Járás',
            'stressful_workplace_climate' => [
                'label' => 'Terhelő munkahelyi klíma (meleg, hideg, nedves, változó)',
                'subfields' => [
                    'heat_exposure' => 'Hőexpozíció (a munkahelyi hőmérséklet meghaladja a 24 °C korrigált effektív hőmérsékletet)',
                    'cold_exposure' => 'Hideg expozíció (zárt térben +10 °C alatti munkavégzés)'
                ]
            ],
            'noise_exposure' => 'Zaj (85 dB Aeq felett)',
            'ionizing_radiation_exposure' => 'Ionizáló sugárzás',
            'non_ionizing_radiation_exposure' => 'Nem-ionizáló sugárzás',
            'local_vibration_exposure' => 'Helyileg ható vibráció',
            'whole_body_vibration_exposure' => 'Egésztest-vibráció',
            'ergonomic_factors_exposure' => 'Ergonómiai tényezők'
        ];

        // Right column structure (items 14-25 from PDF)
        $rightColumnStructure = [
            'dust_exposure' => [
                'label' => 'Porok',
                'subfields' => [
                    'dust_exposure_details' => [
                        'label' => 'Használni tervezett porok megnevezése',
                        'textarea' => [
                            'id' => 'dust_exposure_description',
                            'label' => 'Használni tervezett porok megnevezése',
                            'fullwidth' => true
                        ]
                    ]
                ]
            ],
            'chemicals_exposure' => [
                'label' => 'Vegyi anyagok',
                'subfields' => [
                    'chemical_hazards_exposure' => [
                        'label' => 'Kémiai kóroki tényezők',
                        'special' => 'select2'
                    ],
                    'other_chemicals_details' => [
                        'label' => 'Egyéb vegyi anyagok megnevezése',
                        'textarea' => [
                            'id' => 'other_chemicals_description',
                            'label' => 'Egyéb vegyi anyagok megnevezése',
                            'fullwidth' => true
                        ]
                    ],
                    'carcinogenic_substances_exposure' => 'Rákkeltő anyagok',
                    'carcinogenic_details' => [
                        'label' => 'Használni tervezett rákkeltő anyagok felsorolása',
                        'textarea' => [
                            'id' => 'planned_carcinogenic_substances_list',
                            'label' => 'Használni tervezett rákkeltő anyagok felsorolása',
                            'fullwidth' => true
                        ]
                    ]
                ]
            ],
            'epidemiological_interest_position' => 'Járványügyi érdekből kiemelt munkakör (egészségügyi könyvhöz kötött munkakör)',
            'infection_risk' => 'Fertőzésveszély, biológiai kóroki tényezők (pl. leptospirózis, egyéb zoonozis, baktériumok, vér, szennyvíz stb.)',
            'psychological_stress' => 'Fokozott pszichés terhelés (felelősség emberekért, anyagi értékekért, alkotó szellemi munka)',
            'screen_time' => 'Képernyő előtt végzett munka (napi 4 óra vagy annál több)',
            'night_shift_work' => 'Éjszakai műszakban végzett munka',
            'psychosocial_factors' => 'Pszichoszociális tényezők',
            'personal_protective_equipment_stress' => 'Egyéni védőeszköz általi terhelés',
            'work_away_from_family' => 'Családtól tartósan távol munkát végzők',
            'working_alongside_pension' => 'Időskor (nyugdíj melletti munkavégzés)',
            'others' => [
                'label' => 'Egyéb',
                'subfields' => [
                    'other_details' => [
                        'label' => 'Egyéb egészségkárosító kockázatok megnevezése',
                        'textarea' => [
                            'id' => 'planned_other_health_risk_factors',
                            'label' => 'Egyéb egészségkárosító kockázatok megnevezése',
                            'fullwidth' => true
                        ]
                    ]
                ]
            ]
        ];
        @endphp

        <div class="row">
            <!-- Left Column (Items 1-13) -->
            <div class="col-xl-6">
                @foreach($leftColumnStructure as $field_id => $field_data)
                    @php
                    $field_label = is_array($field_data) ? $field_data['label'] : $field_data;
                    $has_subfields = is_array($field_data) && isset($field_data['subfields']);
                    $has_textarea = is_array($field_data) && isset($field_data['textarea']);
                    @endphp
                    
                    <div class="row align-items-center mb-3">
                        <div class="col-6">
                            <span>{{ $field_label }}</span>
                            @if($has_textarea)
                                <span class="d-none {{ $field_id }}">{{ $field_data['textarea']['label'] }}</span>
                                <textarea id="{{ $field_data['textarea']['id'] }}" name="{{ $field_data['textarea']['id'] }}" class="form-control d-none {{ $field_id }}" rows="3"></textarea>
                            @endif
                        </div>
                        <div class="col-6">
                            @if(isset($field_data['special']) && $field_data['special'] == 'select2')
                                <select id="{{ $field_id }}" name="{{ $field_id }}" class="form-select select2" multiple>
                                    @foreach($chemicalFactors as $factor)
                                        <option value="{{ $factor->id }}">{{ $factor->factor }}</option>
                                    @endforeach
                                    <option value="egyeb">Egyéb</option>
                                </select>
                            @else
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $field_id }}" id="{{ $field_id }}_nincs" value="nincs">
                                    <label class="form-check-label" for="{{ $field_id }}_nincs">Nincs</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $field_id }}" id="{{ $field_id }}_resz" value="resz">
                                    <label class="form-check-label" for="{{ $field_id }}_resz">A munkaidő egy részében</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $field_id }}" id="{{ $field_id }}_egesz" value="egesz">
                                    <label class="form-check-label" for="{{ $field_id }}_egesz">A munkaidő egészében</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($has_subfields)
                        <hr class="mt-1 mb-1 d-none {{ $field_id }}" />
                        
                        @foreach($field_data['subfields'] as $subfield_id => $subfield_data)
                            @php
                            $subfield_label = is_array($subfield_data) ? $subfield_data['label'] : $subfield_data;
                            $sub_has_textarea = is_array($subfield_data) && isset($subfield_data['textarea']);
                            @endphp
                            
                            <div class="row align-items-center mb-2 d-none {{ $field_id }}">
                                <div class="{{ $sub_has_textarea && isset($subfield_data['textarea']['fullwidth']) && $subfield_data['textarea']['fullwidth'] ? 'col-12' : 'col-6' }}">
                                    <span>{{ $subfield_label }}</span>
                                    @if($sub_has_textarea)
                                        @if(!isset($subfield_data['textarea']['fullwidth']) || !$subfield_data['textarea']['fullwidth'])
                                            <span class="d-none {{ $subfield_id }}">{{ $subfield_data['textarea']['label'] }}</span>
                                        @else
                                            <textarea id="{{ $subfield_data['textarea']['id'] }}" name="{{ $subfield_data['textarea']['id'] }}" class="form-control d-none {{ $subfield_id }}" rows="3"></textarea>
                                        @endif
                                    @endif
                                </div>
                                
                                @if(!$sub_has_textarea || !isset($subfield_data['textarea']['fullwidth']) || !$subfield_data['textarea']['fullwidth'])
                                <div class="col-6">
                                    @if(isset($subfield_data['special']) && $subfield_data['special'] == 'select2')
                                        <select id="{{ $subfield_id }}" name="{{ $subfield_id }}" class="form-select select2" multiple>
                                            @foreach($chemicalFactors as $factor)
                                                <option value="{{ $factor->id }}">{{ $factor->factor }}</option>
                                            @endforeach
                                            <option value="egyeb">Egyéb</option>
                                        </select>
                                    @else
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $subfield_id }}" id="{{ $subfield_id }}_nincs" value="nincs">
                                            <label class="form-check-label" for="{{ $subfield_id }}_nincs">Nincs</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $subfield_id }}" id="{{ $subfield_id }}_resz" value="resz">
                                            <label class="form-check-label" for="{{ $subfield_id }}_resz">A munkaidő egy részében</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $subfield_id }}" id="{{ $subfield_id }}_egesz" value="egesz">
                                            <label class="form-check-label" for="{{ $subfield_id }}_egesz">A munkaidő egészében</label>
                                        </div>
                                    @endif
                                    
                                    @if($sub_has_textarea && (!isset($subfield_data['textarea']['fullwidth']) || !$subfield_data['textarea']['fullwidth']))
                                        <textarea id="{{ $subfield_data['textarea']['id'] }}" name="{{ $subfield_data['textarea']['id'] }}" class="form-control d-none {{ $subfield_id }}" rows="3"></textarea>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            @if(!$loop->last)
                                <hr class="mt-1 mb-1 d-none {{ $field_id }}" />
                            @endif
                        @endforeach
                    @endif

                    @if(!$loop->last)
                        <hr class="mt-2 mb-2" />
                    @endif
                @endforeach
            </div>

            <!-- Right Column (Items 14-25) -->
            <div class="col-xl-6">
                @foreach($rightColumnStructure as $field_id => $field_data)
                    @php
                    $field_label = is_array($field_data) ? $field_data['label'] : $field_data;
                    $has_subfields = is_array($field_data) && isset($field_data['subfields']);
                    $has_textarea = is_array($field_data) && isset($field_data['textarea']);
                    @endphp
                    
                    <div class="row align-items-center mb-3">
                        <div class="col-6">
                            <span>{{ $field_label }}</span>
                            @if($has_textarea)
                                <span class="d-none {{ $field_id }}">{{ $field_data['textarea']['label'] }}</span>
                                <textarea id="{{ $field_data['textarea']['id'] }}" name="{{ $field_data['textarea']['id'] }}" class="form-control d-none {{ $field_id }}" rows="3"></textarea>
                            @endif
                        </div>
                        <div class="col-6">
                            @if(isset($field_data['special']) && $field_data['special'] == 'select2')
                                <select id="{{ $field_id }}" name="{{ $field_id }}" class="form-select select2" multiple>
                                    @foreach($chemicalFactors as $factor)
                                        <option value="{{ $factor->id }}">{{ $factor->factor }}</option>
                                    @endforeach
                                    <option value="egyeb">Egyéb</option>
                                </select>
                            @else
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $field_id }}" id="{{ $field_id }}_nincs" value="nincs">
                                    <label class="form-check-label" for="{{ $field_id }}_nincs">Nincs</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $field_id }}" id="{{ $field_id }}_resz" value="resz">
                                    <label class="form-check-label" for="{{ $field_id }}_resz">A munkaidő egy részében</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="{{ $field_id }}" id="{{ $field_id }}_egesz" value="egesz">
                                    <label class="form-check-label" for="{{ $field_id }}_egesz">A munkaidő egészében</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($has_subfields)
                        <hr class="mt-1 mb-1 d-none {{ $field_id }}" />
                        
                        @foreach($field_data['subfields'] as $subfield_id => $subfield_data)
                            @php
                            $subfield_label = is_array($subfield_data) ? $subfield_data['label'] : $subfield_data;
                            $sub_has_textarea = is_array($subfield_data) && isset($subfield_data['textarea']);
                            @endphp
                            
                            <div class="row align-items-center mb-2 d-none {{ $field_id }}">
                                <div class="{{ $sub_has_textarea && isset($subfield_data['textarea']['fullwidth']) && $subfield_data['textarea']['fullwidth'] ? 'col-12' : 'col-6' }}">
                                    <span class="{{ $subfield_id }}">{{ $subfield_label }}</span>
                                    @if($sub_has_textarea)
                                        @if(!isset($subfield_data['textarea']['fullwidth']) || !$subfield_data['textarea']['fullwidth'])
                                            <span class="d-none {{ $subfield_id }}">{{ $subfield_data['textarea']['label'] }}</span>
                                        @else
                                            <textarea id="{{ $subfield_data['textarea']['id'] }}" name="{{ $subfield_data['textarea']['id'] }}" class="form-control d-none {{ $subfield_id }}" rows="3"></textarea>
                                        @endif
                                    @endif
                                </div>
                                
                                @if(!$sub_has_textarea || !isset($subfield_data['textarea']['fullwidth']) || !$subfield_data['textarea']['fullwidth'])
                                <div class="col-6">
                                    @if(isset($subfield_data['special']) && $subfield_data['special'] == 'select2')
                                        <select id="{{ $subfield_id }}" name="{{ $subfield_id }}" class="form-select select2" multiple>
                                            @foreach($chemicalFactors as $factor)
                                                <option value="{{ $factor->id }}">{{ $factor->factor }}</option>
                                            @endforeach
                                            <option value="egyeb">Egyéb</option>
                                        </select>
                                    @else
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $subfield_id }}" id="{{ $subfield_id }}_nincs" value="nincs">
                                            <label class="form-check-label" for="{{ $subfield_id }}_nincs">Nincs</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $subfield_id }}" id="{{ $subfield_id }}_resz" value="resz">
                                            <label class="form-check-label" for="{{ $subfield_id }}_resz">A munkaidő egy részében</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $subfield_id }}" id="{{ $subfield_id }}_egesz" value="egesz">
                                            <label class="form-check-label" for="{{ $subfield_id }}_egesz">A munkaidő egészében</label>
                                        </div>
                                    @endif
                                    
                                    @if($sub_has_textarea && (!isset($subfield_data['textarea']['fullwidth']) || !$subfield_data['textarea']['fullwidth']))
                                        <textarea id="{{ $subfield_data['textarea']['id'] }}" name="{{ $subfield_data['textarea']['id'] }}" class="form-control d-none {{ $subfield_id }}" rows="3"></textarea>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            @if(!$loop->last)
                                <hr class="mt-1 mb-1 d-none {{ $field_id }} {{ $field_id }}_{{ $subfield_id }}" />
                            @endif
                        @endforeach
                    @endif

                    @if(!$loop->last)
                        <hr class="mt-2 mb-2 {{ $field_id }}_{{ $field_id }}" />
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Help Modal -->
        <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Leírás</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Specifikus segítség a kitöltéshez</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bezárás</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>