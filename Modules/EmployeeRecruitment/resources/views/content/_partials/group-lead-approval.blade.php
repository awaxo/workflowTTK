<div class="card">
    <div class="card-body" id="health_allowance">
        <div class="content-header mb-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">A munkakör (munkahely) főbb egészségkárosító kockázatai</h5>
                <small>33/1998 (VI. 24.) NM rendelet szerint</small>
            </div>
            <!--<i class="fas fa-question-circle fa-2x help-icon" data-bs-toggle="modal" data-bs-target="#helpModal" title="Segítség"></i>-->
        </div>

        <div class="row">
            <div class="g-3 col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Kézi anyagmozgatás</span>
                    </div>
                    <div class="col-6">
                        <select id="manual_handling" name="manual_handling" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none manual_handling" />
                
                <div class="row align-items-center d-none manual_handling">
                    <div class="col-6">
                        <span>5 kg – 20 kg</span>
                    </div>
                    <div class="col-6">
                        <select id="manual_handling_weight_5_20" name="manual_handling_weight_5_20" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none manual_handling" />
                
                <div class="row align-items-center d-none manual_handling">
                    <div class="col-6">
                        <span>20 kg – 50 kg</span>
                    </div>
                    <div class="col-6">
                        <select id="manual_handling_weight_20_50" name="manual_handling_weight_20_50" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none manual_handling" />
                
                <div class="row align-items-center d-none manual_handling">
                    <div class="col-6">
                        <span>> 50kg</span>
                    </div>
                    <div class="col-6">
                        <select id="manual_handling_weight_over_50" name="manual_handling_weight_over_50" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkaidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="g-3 col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Fokozott baleseti veszély (tűz- és robbanásveszély, feszültség alatti munka, magasban végzett munka, egyéb)</span>
                    </div>
                    <div class="col-6">
                        <select id="increased_accident_risk" name="increased_accident_risk" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none increased_accident_risk" />
                
                <div class="row align-items-center d-none increased_accident_risk">
                    <div class="col-6">
                        <span>Tűz- és robbanásveszély</span>
                    </div>
                    <div class="col-6">
                        <select id="fire_and_explosion_risk" name="fire_and_explosion_risk" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none increased_accident_risk" />
                
                <div class="row align-items-center d-none increased_accident_risk">
                    <div class="col-6">
                        <span>Feszültség alatti munka</span>
                    </div>
                    <div class="col-6">
                        <select id="live_electrical_work" name="live_electrical_work" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none increased_accident_risk" />
                
                <div class="row align-items-center d-none increased_accident_risk">
                    <div class="col-6">
                        <span>Magasban végzett munka</span>
                    </div>
                    <div class="col-6">
                        <select id="high_altitude_work" name="high_altitude_work" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkaidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none increased_accident_risk" />
                
                <div class="row align-items-center d-none increased_accident_risk">
                    <div class="col-6">
                        <span>Egyéb</span>
                        <span class="d-none other_risks"> fokozott baleseti veszéllyel járó kockázati tényező felsorolása</span>
                        <textarea id="other_risks_description" name="other_risks_description" class="form-control d-none other_risks" rows="3"></textarea>
                    </div>
                    <div class="col-6">
                        <select id="other_risks" name="other_risks" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkaidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="g-3 col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Kényszertesthelyzet (görnyedés, guggolás)</span>
                    </div>
                    <div class="col-6">
                        <select id="forced_body_position" name="forced_body_position" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Ülés</span>
                    </div>
                    <div class="col-6">
                        <select id="sitting" name="sitting" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Állás</span>
                    </div>
                    <div class="col-6">
                        <select id="standing" name="standing" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Járás</span>
                    </div>
                    <div class="col-6">
                        <select id="walking" name="walking" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkaidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <hr class="mt-3" />

        <div class="row">
            <div class="col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Terhelő munkahelyi klíma (meleg, hideg, nedves, változó)</span>
                    </div>
                    <div class="col-6">
                        <select id="stressful_workplace_climate" name="stressful_workplace_climate" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none stressful_workplace_climate" />
                
                <div class="row align-items-center d-none stressful_workplace_climate">
                    <div class="col-6">
                        <span>Hőexpozíció (a munkahelyi hőmérséklet meghaladja a 24 °C korrigált effektív hőmérsékletet)</span>
                    </div>
                    <div class="col-6">
                        <select id="heat_exposure" name="heat_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none stressful_workplace_climate" />
                
                <div class="row align-items-center d-none stressful_workplace_climate">
                    <div class="col-6">
                        <span>Hideg expozíció (zárt térben +10 °C alatti munkavégzés)</span>
                    </div>
                    <div class="col-6">
                        <select id="cold_exposure" name="cold_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Zaj (85 dB Aeq felett)</span>
                    </div>
                    <div class="col-6">
                        <select id="noise_exposure" name="noise_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Ionizáló sugárzás</span>
                    </div>
                    <div class="col-6">
                        <select id="ionizing_radiation_exposure" name="ionizing_radiation_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Nem-ionizáló sugárzás</span>
                    </div>
                    <div class="col-6">
                        <select id="non_ionizing_radiation_exposure" name="non_ionizing_radiation_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Helyileg ható vibráció</span>
                    </div>
                    <div class="col-6">
                        <select id="local_vibration_exposure" name="local_vibration_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Egésztest-vibráció</span>
                    </div>
                    <div class="col-6">
                        <select id="whole_body_vibration_exposure" name="whole_body_vibration_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Ergonómiai tényezők</span>
                    </div>
                    <div class="col-6">
                        <select id="ergonomic_factors_exposure" name="ergonomic_factors_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Porok</span><br />
                        <span class="d-none dust_exposure">Használni tervezett porok megnevezése</span>
                        <textarea id="dust_exposure_description" name="dust_exposure_description" class="form-control d-none dust_exposure" rows="3"></textarea>
                    </div>
                    <div class="col-6">
                        <select id="dust_exposure" name="dust_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Vegyi anyagok</span>
                    </div>
                    <div class="col-6">
                        <select id="chemicals_exposure" name="chemicals_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none chemicals_exposure" />
                
                <div class="row align-items-center d-none chemicals_exposure">
                    <div class="col-6">
                        <span>Kémiai kóroki tényezők</span>
                    </div>
                    <div class="col-6">
                        <select id="chemical_hazards_exposure" name="chemical_hazards_exposure" class="form-select select2" multiple>
                            @foreach($chemicalFactors as $factor)
                                <option value="{{ $factor->id }}">{{ $factor->factor }}</option>
                            @endforeach
                            <option value="egyeb">Egyéb</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none chemical_hazards_exposure" />
                
                <div class="row align-items-center d-none chemical_hazards_exposure">
                    <div class="col-12">
                        <span>Egyéb vegyi anyagok megnevezése</span>
                        <textarea id="other_chemicals_description" name="other_chemicals_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none chemicals_exposure" />
                
                <div class="row align-items-center d-none chemicals_exposure">
                    <div class="col-6">
                        <span>Rákkeltő anyagok</span>
                    </div>
                    <div class="col-6">
                        <select id="carcinogenic_substances_exposure" name="carcinogenic_substances_exposure" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none carcinogenic_substances_exposure" />
                
                <div class="row align-items-center d-none carcinogenic_substances_exposure">
                    <div class="col-12">
                        <span>Használni tervezett rákkeltő anyagok felsorolása</span>
                        <textarea id="planned_carcinogenic_substances_list" name="planned_carcinogenic_substances_list" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <hr class="mt-3" />

        <div class="row">
            <div class="col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Járványügyi érdekből kiemelt munkakör (egészségügyi könyvhöz kötött munkakör)</span>
                    </div>
                    <div class="col-6">
                        <select id="epidemiological_interest_position" name="epidemiological_interest_position" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Fertőzésveszély, biológiai kóroki tényezők (pl. leptospirózis, egyéb zoonozis, baktériumok, vér, szennyvíz stb.)</span>
                    </div>
                    <div class="col-6">
                        <select id="infection_risk" name="infection_risk" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Fokozott pszichés terhelés (felelősség emberekért, anyagi értékekért, alkotó szellemi munka)</span>
                    </div>
                    <div class="col-6">
                        <select id="psychological_stress" name="psychological_stress" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Képernyő előtt végzett munka (napi 4 óra vagy annál több)</span>
                    </div>
                    <div class="col-6">
                        <select id="screen_time" name="screen_time" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Éjszakai műszakban végzett munka</span>
                    </div>
                    <div class="col-6">
                        <select id="night_shift_work" name="night_shift_work" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Pszichoszociális tényezők</span>
                    </div>
                    <div class="col-6">
                        <select id="psychosocial_factors" name="psychosocial_factors" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Egyéni védőeszköz általi terhelés</span>
                    </div>
                    <div class="col-6">
                        <select id="personal_protective_equipment_stress" name="personal_protective_equipment_stress" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Családtól tartósan távol munkát végzők</span>
                    </div>
                    <div class="col-6">
                        <select id="work_away_from_family" name="work_away_from_family" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Időskor (nyugdíj melletti munkavégzés)</span>
                    </div>
                    <div class="col-6">
                        <select id="working_alongside_pension" name="working_alongside_pension" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1" />
                
                <div class="row align-items-center">
                    <div class="col-6">
                        <span>Egyéb</span>
                    </div>
                    <div class="col-6">
                        <select id="others" name="others" class="form-select">
                            <option value="" selected></option>
                            <option value="nincs">Nincs</option>
                            <option value="egesz">A munkaidő egészében</option>
                            <option value="resz">A munkidő egy részében</option>
                        </select>
                    </div>
                </div>

                <hr class="mt-1 mb-1 d-none others" />
                
                <div class="row align-items-center d-none others">
                    <div class="col-12">
                        <span>Egyéb egészségkárosító kockázatok megnevezése</span>
                        <textarea id="planned_other_health_risk_factors" name="planned_other_health_risk_factors" class="form-control" rows="3"></textarea>
                    </div>
                </div>
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