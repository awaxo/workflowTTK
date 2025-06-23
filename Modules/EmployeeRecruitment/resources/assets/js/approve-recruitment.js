import GLOBALS from '../../../../../resources/js/globals.js';
import DropzoneManager from '../../../../../resources/js/dropzone-manager';
import { trim } from 'lodash';

$(function () {
    const instances = GLOBALS.initNumberInputs();
    const medicalData = $('#health_allowance_card').data('medical');

    // Format the social security number input if it exists
    var socialSecurityNumberField = document.getElementById('social_security_number');
    if (socialSecurityNumberField) {
        var cleaveSSN = new Cleave(socialSecurityNumberField, {
            numericOnly: true,
            blocks: [3, 3, 3],
            delimiters: [' ', ' '],
        });
    }

    // Format the obligee number sequence input if it exists
    var obligeeNumberSequenceField = document.getElementById('obligee_number_sequence');
    if (obligeeNumberSequenceField) {
        var cleaveObligeeSequence = new Cleave(obligeeNumberSequenceField, {
            numericOnly: true,
            blocks: [7],
            // Custom onValueChanged to ensure leading zeros
            onValueChanged: function(e) {
                // If the value has less than 7 digits, pad with leading zeros
                if (e.target.value.length > 0 && e.target.value.length < 7) {
                    const paddedValue = e.target.value.padStart(7, '0');
                    // Only update if different to avoid infinite loop
                    if (e.target.value !== paddedValue) {
                        e.target.value = paddedValue;
                    }
                }
            }
        });

        // Additional blur event to ensure padding when user leaves the field
        $(obligeeNumberSequenceField).on('blur', function() {
            const currentValue = this.value;
            if (currentValue.length > 0 && currentValue.length < 7) {
                const paddedValue = currentValue.padStart(7, '0');
                this.value = paddedValue;
                // Trigger change event to notify other listeners
                $(this).trigger('change');
            }
        });

        // Input event to restrict to maximum 7 digits
        $(obligeeNumberSequenceField).on('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 7) {
                value = value.substring(0, 7); // Limit to 7 digits
            }
            this.value = value;
        });
    }

    initObligeeNumberField();

    setMedicalData(medicalData);

    $('#chemical_hazards_exposure').select2();

    // file uploads
    if ($('#contract').length) {
        DropzoneManager.init('contract');
    }

    $('#approve').on('click', function () {
        if ($('#state').val() === 'hr_lead_approval' && ($('#probation_period').val() < 7 || $('#probation_period').val() > 90)) {
            $('#probationMissing').modal('show');
            return;
        } else if ($('#state').val() === 'employee_signature' && $('#contract_file').val().length === 0) {
            $('#contractMissing').modal('show');
            return;
        } else if ($('#state').val() === 'employee_signature') {
            const regNumber = $('#contract_registration_number').val();
            if (!regNumber || regNumber.length < 6 || regNumber.length > 12) {
                $('#contractRegistrationNumberMissing').modal('show');
                return;
            }
        } else if ($('#state').val() === 'registration' && 
            (!$('#obligee_number_year').val() || !$('#obligee_number_sequence').val())) {
            $('#obligeeNumberMissing').modal('show');
            return;
        } else if ($('#state').val() === 'registration' && 
            $('#obligee_number_sequence').val() && $('#obligee_number_sequence').val().length !== 7) {
            // Additional validation for obligee number sequence length
            $('#obligeeNumberSequenceInvalid').modal('show');
            return;
        } else if ($('#state').val() === 'draft_contract_pending') {
            // Validate social security number
            const ssn = $('#social_security_number').val();
            // Check if SSN is missing or in correct format (123 456 789)
            if (!ssn || !ssn.match(/^[0-9]{3}\s[0-9]{3}\s[0-9]{3}$/)) {
                $('#ssnMissing').modal('show');
                return;
            }
        }
    
        $('#approveConfirmation').modal('show');
    });

    $('#confirm_approve').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        // csak csoportvezetői jóváhagyásnál validálunk itt
        if ($('#state').val() === 'group_lead_approval') {
            let fv = validateHealthAllowance();

            // Egyelőre még nem törlöm ki, de ezzel az a baj, hogy a nem látható mezőket is validálja
            /*dynamicControls(fv, 'manual_handling', [
                'manual_handling_weight_5_20',
                'manual_handling_weight_20_50',
                'manual_handling_weight_over_50',
            ]);
            dynamicControls(fv, 'increased_accident_risk', [
                'fire_and_explosion_risk',
                'live_electrical_work',
                'high_altitude_work',
                'other_risks',
            ]);
            dynamicControls(fv, 'stressful_workplace_climate', [
                'heat_exposure',
                'cold_exposure',
            ]);
            dynamicControls(fv, 'dust_exposure', [
                'dust_exposure_description',
            ]);
            dynamicControls(fv, 'chemicals_exposure', [
                'other_chemicals_description',
                'planned_carcinogenic_substances_list',
            ]);
            dynamicControls(fv, 'others', [
                'planned_other_health_risk_factors',
            ]);*/

            // Csak a látható mezőket validáljuk: rejtett mezők validátorait kikapcsoljuk
            Object.keys(fv.getFields()).forEach(function(field) {
                const $fld = $(`[name="${field}"]`);
                if ($fld.length && !$fld.is(':visible')) {
                    try {
                        fv.disableValidator(field);
                    } catch (e) {
                        console.warn(`Nem sikerült letiltani a validátort a rejtett mezőnél: ${field}`, e);
                    }
            }
            });

            fv.validate().then(function(status) {
                if (status === 'Valid') {
                    // összegyűjtjük a formadatokat
                    var formData = {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        message: $('#message').val(),
                        medical_eligibility: true
                    };
                    $('#health_allowance_card input[type="radio"]:checked, #health_allowance_card select, #health_allowance_card textarea').each(function() {
                        var name = $(this).attr('name');
                        if (name) {
                            formData[name] = $(this).val();
                        }
                    });

                    // Ajax küldés
                    $.ajax({
                        url: '/employee-recruitment/' + recruitmentId + '/approve',
                        type: 'POST',
                        data: formData,
                        success: function () {
                            window.location.href = '/folyamat/megtekintes/' + recruitmentId;
                        },
                        error: function (jqXHR) {
                            if (jqXHR.status === 401 || jqXHR.status === 419) {
                                alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                                window.location.href = '/login';
                            } else {
                                console.error('Hiba:', jqXHR.responseText);
                            }
                        }
                    });
                } else {
                    // ha nem valid, megjelenítjük a hibaablakot
                    GLOBALS.AJAX_ERROR(
                        'Az egészségkárosító kockázati adatoknál hiányzó mező(k) vannak, kérjük ellenőrizd!',
                        null, null, null, '.decision-controls'
                    );
                    $('#approveConfirmation').modal('hide');
                }
            });

            // megállítjuk a további ágakat
            return;
        }

        // Create an object to hold the data
        var postData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            probation_period: $('#probation_period').val(),
            post_financed_application: $('#post_financed_application').val(),
            contract_file: $('#contract_file').val(),
            message: $('#message').val(),
            initiator_comment: $('#initiator_comment').val()
        };

        // Add social_security_number if present
        if ($('#social_security_number').val()) {
            postData.social_security_number = $('#social_security_number').val();
        }

        // Add obligee_number only if both parts are available
        if ($('#obligee_number_year').val() && $('#obligee_number_sequence').val()) {
            postData.obligee_number = 'SZ/' + $('#obligee_number_year').val() + '/' + $('#obligee_number_sequence').val();
        }

        // Add contract_registration_number if we're in employee_signature state
        if ($('#state').val() === 'employee_signature' && $('#contract_registration_number').val()) {
            postData.contract_registration_number = $('#contract_registration_number').val();
        }

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/approve',
            type: 'POST',
            data: postData,
            success: function (response) {
                window.location.href = '/folyamat/megtekintes/' + recruitmentId;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                console.log(textStatus, errorThrown);
            }
        });
    });

    $('#reject').on('click', function () {
        var message = $('#message').val();

        if (message.length === 0) {
            $('#messageMissing').modal('show');
        } else {
            $('#rejectConfirmation').modal('show');
        }
    });

    $('#confirm_reject').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/reject',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: $('#message').val()
            },
            success: function (response) {
                window.location.href = '/folyamat/megtekintes/' + recruitmentId;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                console.log(textStatus, errorThrown);
            }
        });
    });

    $('#suspend').on('click', function () {
        var message = $('#message').val();

        if (message.length === 0) {
            $('#messageMissing').modal('show');
        } else {
            $('#suspendConfirmation').modal('show');
        }
    });

    $('#confirm_suspend').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/suspend',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: $('#message').val()
            },
            success: function (response) {
                window.location.href = '/folyamat/megtekintes/' + recruitmentId;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                console.log(textStatus, errorThrown);
            }
        });
    });

    // Initialize an object to track clicks
    const printIconClicked = {
        'print-icon-1': false,
        'print-icon-2': false
    };

    // Function to check if both icons have been clicked
    function checkIconsAndShow() {
        if (printIconClicked['print-icon-1'] && printIconClicked['print-icon-2']) {
            $('#message_parent, #action_buttons').removeClass('d-none').addClass('d-md-block');
        }
    }

    // Event listener for print-icon-1
    $('.print-icon-1').on('click', function() {
        printIconClicked['print-icon-1'] = true;
        checkIconsAndShow();
    });

    // Event listener for print-icon-2
    $('.print-icon-2').on('click', function() {
        printIconClicked['print-icon-2'] = true;
        checkIconsAndShow();
    });

    $(document).on('change', 'select', function () {
        if ($(this).val() !== '') {
            $(this).find('option[value=""]').remove();
        }
    });
});

/**
 * @param {FormValidation.FormValidationInstance} fv  – a validateHealthAllowance() visszatérési értéke
 * @param {string} source                          – a főrádió name-je
 * @param {string[]} targets                       – az almezők name-jei tömbben
 */
function dynamicControls(fv, source, targets = []) {
    const $radios = $(`input[name="${source}"]`);

    $radios.on('change', function () {
        const show = this.checked && this.value !== 'nincs';
        // megjelenítés/elrejtés
        $(`.${source}`).toggleClass('d-none', !show);
        
        // Ha nem kell megjeleníteni, akkor egyszerűen érvénytelenítjük a mezőket a validációs motorban
        if (!show) {
            // A validációs motorban érvénytelenné tesszük a mezőket, így nem fogja őket validálni
            fv.disableValidator(source);
            targets.forEach(name => {
                try {
                    fv.disableValidator(name);
                } catch (e) {
                    console.warn(`Nem sikerült letiltani a validátort: ${name}`, e);
                }
            });
        } else {
            // Ha meg kell jeleníteni, akkor újra engedélyezzük a validátorokat és validáljuk a mezőket
            fv.enableValidator(source);
            targets.forEach(name => {
                try {
                    fv.enableValidator(name);
                    // Csak akkor validáljuk újra, ha látható
                    fv.revalidateField(name);
                } catch (e) {
                    console.warn(`Hiba a validátor kezelésénél: ${name}`, e);
                }
            });
        }
        
        // Mindenképp újravalidáljuk a főrádiót
        fv.revalidateField(source);
    });
    // inicializáló trigger
    $radios.filter(':checked').trigger('change');
}

function initObligeeNumberField() {
    const yearSelect = $('#obligee_number_year');
    const selectedOption = yearSelect.find('option[selected]');
    
    if (selectedOption.length > 0) {
        yearSelect.val(selectedOption.val());
    } else {
        yearSelect.val('');
    }
}

function setMedicalData(medicalData) {
    if (medicalData) {
        // Végigmegyünk a medical adatokon és beállítjuk a megfelelő radio gombokat
        Object.keys(medicalData).forEach(function(key) {
            const value = medicalData[key];
            if (value && $(`input[name="${key}"][type="radio"]`).length) {
                // Radio gombok beállítása
                $(`input[name="${key}"][value="${value}"]`).prop('checked', true);
                
                // Dinamikus megjelenítés/elrejtés triggerelése
                if (typeof dynamicControls === 'function') {
                    $(`input[name="${key}"]:checked`).trigger('change');
                }
            } else if (value && $(`textarea[name="${key}"]`).length) {
                // Textarea kitöltése
                $(`textarea[name="${key}"]`).val(value);
            } else if (value && $(`select[name="${key}"]`).length) {
                // Select mező beállítása
                $(`select[name="${key}"]`).val(value);
                
                // Ha többszörös select, akkor triggereljük a change eseményt
                if ($(`select[name="${key}"]`).is('[multiple]')) {
                    $(`select[name="${key}"]`).trigger('change');
                }
            }
        });
    }
}

function revalidateOnChange(fv, targetId) {
    $('#' + targetId).on('change', function() {
        fv.revalidateField(targetId);
    });
}

/**
 * Validates the health allowance form.
 * @returns {FormValidation.FormValidationInstance} – FormValidation instance
 * @description
 * This function sets up validation for the health allowance form fields.
 * It checks that all required fields are filled out and displays error messages
 * if any fields are left empty. The validation is done using the FormValidation library.
 */
function validateHealthAllowance() {
    return FormValidation.formValidation(
        document.getElementById('health_allowance_card'),
        {
            fields: {
                manual_handling: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                manual_handling_weight_5_20: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                manual_handling_weight_20_50: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                manual_handling_weight_over_50: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                increased_accident_risk: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                fire_and_explosion_risk: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                live_electrical_work: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                high_altitude_work: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                other_risks: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                forced_body_position: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                sitting: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                standing: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                walking: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                stressful_workplace_climate: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                heat_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                cold_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                noise_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                ionizing_radiation_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                non_ionizing_radiation_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                local_vibration_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                whole_body_vibration_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                ergonomic_factors_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                dust_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                dust_exposure_description: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                chemicals_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                carcinogenic_substances_exposure: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                epidemiological_interest_position: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                infection_risk: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                psychological_stress: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                screen_time: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                night_shift_work: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                psychosocial_factors: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                personal_protective_equipment_stress: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                work_away_from_family: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                working_alongside_pension: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                others: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                },
                planned_other_health_risk_factors: {
                    validators: {
                        notEmpty: {
                            message: 'A mező kitöltése kötelező'
                        }
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5(),
                trigger:   new FormValidation.plugins.Trigger(),
            },
        }
    ).on('core.field.invalid', function(field) {
        $(`#${field}`).next().addClass('is-invalid');
    }).on('core.field.valid', function(field) {
        $(`#${field}`).next().removeClass('is-invalid');
    });
}