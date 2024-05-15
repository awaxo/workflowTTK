$(function() {
    // set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: ' ',
        });
    });

    $('.btn-submit').on('click', function(e) {
        $.ajax({
            url: '/api/settings/update',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                settings: {
                    recruitment_auto_suspend_threshold: $('#recruitment_auto_suspend_threshold').val(),
                    recruitment_director_approve_salary_threshold: $('#recruitment_director_approve_salary_threshold').val(),
                },
            },
            success: function(response) {
                $('#successAlertMessage').text('Beállítások mentve');
                $('#successAlert').removeClass('d-none');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#errorAlertMessage').text('Hiba történt a beállítások mentése során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });
});