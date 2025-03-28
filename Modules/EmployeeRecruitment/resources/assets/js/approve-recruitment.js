import GLOBALS from '../../../../../resources/js/globals.js';
import DropzoneManager from '../../../../../resources/js/dropzone-manager';
import { trim } from 'lodash';

$(function () {
    const instances = GLOBALS.initNumberInputs();
    const medicalData = $('#health_allowance').data('medical');

    // Format the social security number input if it exists
    var socialSecurityNumberField = document.getElementById('social_security_number');
    if (socialSecurityNumberField) {
        var cleaveSSN = new Cleave(socialSecurityNumberField, {
            numericOnly: true,
            blocks: [3, 3, 3],
            delimiters: [' ', ' '],
        });
    }

    initObligeeNumberField();

    setMedicalData(medicalData);

    $('#chemical_hazards_exposure').select2();

    // dynamically appeared contols
    dynamicControls('manual_handling', 'manual_handling');
    dynamicControls('increased_accident_risk', 'increased_accident_risk');
    dynamicControls('other_risks', 'other_risks');
    dynamicControls('stressful_workplace_climate', 'stressful_workplace_climate');
    dynamicControls('dust_exposure', 'dust_exposure');
    dynamicControls('chemicals_exposure', 'chemicals_exposure');
    $('#chemical_hazards_exposure').on('change', function () {
        if ($(this).val().includes('egyeb')) {
            $('.chemical_hazards_exposure').removeClass('d-none');
        } else {
            $('.chemical_hazards_exposure').addClass('d-none');
        }
    });
    dynamicControls('chemicals_exposure', 'chemicals_exposure');
    dynamicControls('carcinogenic_substances_exposure', 'carcinogenic_substances_exposure');
    dynamicControls('others', 'others');
    // end of dynamically appeared contols

    // file uploads
    DropzoneManager.init('contract');

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

        if ($('#state').val() === 'group_lead_approval') {
            let fv = validateHealthAllowance();
            fv.validate().then(function(status) {
                if(status === 'Valid') {
                    // Egyszerű JavaScript objektum az adatok tárolására
                    var formData = {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        message: $('#message').val(),
                        medical_eligibility: true  // Explicit érték a medical_eligibility mezőhöz
                    };
    
                    // Gyűjtsük össze a form adatait
                    $('#health_allowance input[type="radio"]:checked, #health_allowance select, #health_allowance textarea').each(function() {
                        var name = $(this).attr('name');
                        if (name) {
                            formData[name] = $(this).val();
                        }
                    });
    
                    // Ellenőrizzük, hogy van-e a DOM-ban data-medical attribútum
                    var healthAllowanceElement = document.getElementById('health_allowance');
                    console.log('health_allowance elem:', healthAllowanceElement);
                    console.log('data-medical attribútum:', healthAllowanceElement.getAttribute('data-medical'));
                    
                    // Naplózzuk, hogy mit küldünk
                    console.log('Elküldendő adatok:', formData);
                    
                    $.ajax({
                        url: '/employee-recruitment/' + recruitmentId + '/approve',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            window.location.href = '/folyamat/megtekintes/' + recruitmentId;
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            if (jqXHR.status === 401 || jqXHR.status === 419) {
                                alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                                window.location.href = '/login';
                            }
    
                            console.log('Hiba történt:', textStatus, errorThrown);
                            console.log('Válasz:', jqXHR.responseText);
                        }
                    });
                } else if (status === 'Invalid') {
                    var fields = fv.getFields();
                    Object.keys(fields).forEach(function(name) {
                        fv.validateField(name)
                            .then(function(status) {
                                if (status === 'Invalid') {
                                    console.log('Field:', name, 'Status:', status);
                                    GLOBALS.AJAX_ERROR('Az egészségkárosító kockázati adatoknál hiányzó mező(k) vannak, kérjük ellenőrizd!', null, null, null, '.decision-controls');
                                }
                            });
                    });
                    $('#approveConfirmation').modal('hide');
                }
            });
        } else {
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
        }
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

function dynamicControls(source, target) {
    $('input[name="' + source + '"]').on('change', function () {
        if ($(this).val() !== 'nincs') {
            $('.' + target).removeClass('d-none');
        } else {
            $('.' + target).addClass('d-none');
        }
    });
    $('input[name="' + source + '"]:checked').trigger('change');
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

function validateHealthAllowance() {
    return FormValidation.formValidation(
        document.getElementById('health_allowance'),
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
                        callback: {
                            callback: function(input) {
                                if ($('#manual_handling').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                },
                manual_handling_weight_20_50: {
                    validators: {
                        callback: {
                            callback: function(input) {
                                if ($('#manual_handling').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                },
                manual_handling_weight_over_50: {
                    validators: {
                        callback: {
                            callback: function(input) {
                                if ($('#manual_handling').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
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
                        callback: {
                            callback: function(input) {
                                if ($('#increased_accident_risk').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                },
                live_electrical_work: {
                    validators: {
                        callback: {
                            callback: function(input) {
                                if ($('#increased_accident_risk').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                },
                high_altitude_work: {
                    validators: {
                        callback: {
                            callback: function(input) {
                                if ($('#increased_accident_risk').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                },
                other_risks: {
                    validators: {
                        callback: {
                            callback: function(input) {
                                if ($('#increased_accident_risk').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
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
                        callback: {
                            callback: function(input) {
                                if ($('#stressful_workplace_climate').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                },
                cold_exposure: {
                    validators: {
                        callback: {
                            callback: function(input) {
                                if ($('#stressful_workplace_climate').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
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
                        callback: {
                            callback: function(input) {
                                if ($('#dust_exposure').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
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
                        callback: {
                            callback: function(input) {
                                if ($('#chemicals_exposure').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
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
                        callback: {
                            callback: function(input) {
                                if ($('#others').val() !== 'nincs') {
                                    return {
                                        valid: trim(input.value) !== '',
                                        message: 'A mező kitöltése kötelező'
                                    };
                                } else {
                                    return {
                                        valid: true
                                    }
                                }
                            }
                        }
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    ).on('core.field.invalid', function(field) {
        $(`#${field}`).next().addClass('is-invalid');
    }).on('core.field.valid', function(field) {
        $(`#${field}`).next().removeClass('is-invalid');
    });
}