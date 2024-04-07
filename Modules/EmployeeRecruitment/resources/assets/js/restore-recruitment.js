$(function () {
    $('#restore').on('click', function () {
        $('#restoreConfirmation').modal('show');
    });

    $('#confirm_restore').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/restore',
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
});