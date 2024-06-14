import DropzoneManager from '../../../../../resources/js/dropzone-manager';

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
                window.location.href = response.redirectUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
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
                console.log(textStatus, errorThrown);
            }
        });
    });

    $('.print-icon').on('click', function() {
        $('#message_parent, #action_buttons').removeClass('d-none').addClass('d-md-block');
    });
});

function dynamicControls(source, target) {
    $('#' + source).on('change', function () {
        if ($(this).val() !== '') {
            $('.' + target).removeClass('d-none');
        } else {
            $('.' + target).addClass('d-none');
        }
    });
    $('#' + source).trigger('change');
}