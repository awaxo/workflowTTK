import GLOBALS from '../../../../../resources/js/globals.js';
import DropzoneManager from '../../../../../resources/js/dropzone-manager';
import { trim } from 'lodash';

$(function () {
    // Set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true
        });
    });

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
        }

        $('#approveConfirmation').modal('show');
    });

    $('#confirm_approve').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        if ($('#state').val() === 'group_lead_approval') {
            let fv = validateHealthAllowance();
            fv.validate().then(function(status) {
                if(status === 'Valid') {
                    var formData = {};

                    $('#health_allowance').find('input, select, textarea').each(function() {
                        var key = $(this).attr('name');
                        var value;
                    
                        if ($(this).is(':checkbox')) {
                            value = $(this).is(':checked');
                        } else if ($(this).is(':radio')) {
                            if ($(this).is(':checked')) {
                                value = $(this).val();
                            }
                        } else if ($(this).is('select[multiple]')) {
                            value = $(this).val();
                        } else {
                            value = $(this).val();
                        }
                    
                        if (key && value !== undefined) {
                            formData[key] = value;
                        }
                    });

                    formData['_token'] = $('meta[name="csrf-token"]').attr('content');
                    formData['message'] = $('#message').val();
                    
                    $.ajax({
                        url: '/employee-recruitment/' + recruitmentId + '/approve',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            window.location.href = response.redirectUrl;
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            if (xhr.status === 401) {
                                alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                                window.location.href = '/login';
                            }

                            console.log(textStatus, errorThrown);
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
            $.ajax({
                url: '/employee-recruitment/' + recruitmentId + '/approve',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    probation_period: $('#probation_period').val(),
                    post_financed_application: $('#post_financed_application').val(),
                    contract_file: $('#contract_file').val(),
                    message: $('#message').val()
                },
                success: function (response) {
                    window.location.href = response.redirectUrl;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (xhr.status === 401) {
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
                window.location.href = response.redirectUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (xhr.status === 401) {
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
                window.location.href = response.redirectUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (xhr.status === 401) {
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
    $('#' + source).on('change', function () {
        if ($(this).val() !== '' && $(this).val() !== 'nincs') {
            $('.' + target).removeClass('d-none');
        } else {
            $('.' + target).addClass('d-none');
        }
    });
    $('#' + source).trigger('change');
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