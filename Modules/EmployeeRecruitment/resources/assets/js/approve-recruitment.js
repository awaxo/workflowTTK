$(function () {
    $('#confirm_approve').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/approve',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
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
        var recruitmentId = $(this).data('recruitment-id');
        var decisionMessage = $('#decision_message').val();

        if (decisionMessage.length === 0) {
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
                decisionMessage: $('#decision_message').val()
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