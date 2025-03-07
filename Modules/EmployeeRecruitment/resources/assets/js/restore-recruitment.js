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
});