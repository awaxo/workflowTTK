$(function () {
    if ($('.numeral-mask').length > 0) {
        new Cleave(".numeral-mask", {
            numeral: true
        });
    }

    $('#approve').on('click', function () {
        if ($('#state').val() === 'hr_lead_approval' && ($('#probation_period').val() < 7 || $('#probation_period').val() > 90)) {
            $('#probationMissing').modal('show');
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
});