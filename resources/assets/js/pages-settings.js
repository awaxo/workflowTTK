import GLOBALS from '../../js/globals.js';

$(function() {
    // set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: ' ',
        });
    });

    $('.btn-submit-generic').on('click', function(e) {
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
                GLOBALS.AJAX_SUCCESS('Beállítások mentve');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                GLOBALS.AJAX_ERROR('Hiba történt a beállítások mentése során!', jqXHR, textStatus, errorThrown);
            }
        });
    });

    $('#workflows').on('change', function() {
        if ($(this).val() == 0) {
            $('#workflow_states').empty();
            $('#workflow_states').append('<option value="0" selected>Válassz státuszt</option>');
            return;
        }

        $.ajax({
            url: '/api/workflow/' + $(this).val() + '/states',
            type: 'GET',
            success: function(response) {
                $('#workflow_states').empty();

                $('#workflow_states').append('<option value="0" selected>Válassz státuszt</option>');
                Object.entries(response.data).forEach(function([key, state]) {
                    // filter out states that cannot be deadlined
                    if (key == 'new_request' || key == 'completed' || key == 'rejected' || key == 'suspended') {
                        return;
                    }

                    $('#workflow_states').append('<option value="' + key + '">' + state + '</option>');
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                console.log(textStatus, errorThrown);
            }
        });
    });

    $('#workflow_states').on('change', function() {
        if ($(this).val() == 0) {
            $('#workflow_state_deadline').val('');
            return;
        }

        $.ajax({
            url: '/api/settings/' + $('#workflows').val() + '/state/' + $(this).val() + '/deadline',
            type: 'GET',
            success: function(response) {
                $('#workflow_state_deadline').val(response.data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                console.log(textStatus, errorThrown);
            }
        });
    });

    $('.btn-submit-deadline').on('click', function(e) {
        if ($('#workflows').val() == 0 || $('#workflow_states').val() == 0) {
            GLOBALS.AJAX_ERROR('Folyamat és státusz kiválasztása kötelező!');
            return;
        }

        $.ajax({
            url: '/api/settings/update-deadline',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                workflow: $('#workflows').val(),
                state: $('#workflow_states').val(),
                deadline: $('#workflow_state_deadline').val(),
            },
            success: function(response) {
                GLOBALS.AJAX_SUCCESS('Határidő mentve');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }
                
                GLOBALS.AJAX_ERROR('Hiba történt a határidő mentése során!', jqXHR, textStatus, errorThrown);
            }
        });
    });
});