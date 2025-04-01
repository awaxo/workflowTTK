import GLOBALS from '../../js/globals.js';

$(function() {
    const instances = GLOBALS.initNumberInputs();

    function isValidURL(url) {
        try {
            if (!url || url.trim() === '') {
                return true;
            }
            
            new URL(url);
            
            return /^https?:\/\//.test(url);
        } catch (e) {
            return false;
        }
    }

    $('.btn-submit-generic').on('click', function(e) {
        const apiUrl = $('#notification_api_url').val().trim();
        if (apiUrl !== '' && !isValidURL(apiUrl)) {
            GLOBALS.AJAX_ERROR('Az API URL formátuma érvénytelen. Kérjük, adjon meg egy érvényes URL-t (pl. https://example.com/api).');
            return;
        }
    
        // Get employer contribution value and validate
        const employerContribution = GLOBALS.cleanNumber($('#employer_contribution').val());
        if (employerContribution < 0 || employerContribution > 100) {
            GLOBALS.AJAX_ERROR('A szociális hozzájárulási adó értéke 0 és 100 között kell legyen.');
            return;
        }
    
        $.ajax({
            url: '/api/settings/update',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                settings: {
                    recruitment_auto_suspend_threshold: GLOBALS.cleanNumber($('#recruitment_auto_suspend_threshold').val()),
                    recruitment_director_approve_salary_threshold: GLOBALS.cleanNumber($('#recruitment_director_approve_salary_threshold').val()),
                    notification_api_url: apiUrl,
                    employer_contribution: employerContribution,
                },
            },
            success: function(response) {
                GLOBALS.AJAX_SUCCESS('Beállítások mentve');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }
    
                GLOBALS.AJAX_ERROR('Hiba történt a beállítások mentése során!', jqXHR, textStatus, errorThrown);
            }
        });
    });

    $('#notification_api_url').on('blur', function() {
        const apiUrl = $(this).val().trim();
        if (apiUrl !== '' && !isValidURL(apiUrl)) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Kérjük, adjon meg egy érvényes URL-t (pl. https://example.com/api).</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    $('#employer_contribution').on('blur', function() {
        const value = GLOBALS.cleanNumber($(this).val());
        if (value < 0 || value > 100) {
            $(this).addClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">A százalékérték 0 és 100 között kell legyen.</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
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
                if (jqXHR.status === 401 || jqXHR.status === 419) {
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
                if (jqXHR.status === 401 || jqXHR.status === 419) {
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
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }
                
                GLOBALS.AJAX_ERROR('Hiba történt a határidő mentése során!', jqXHR, textStatus, errorThrown);
            }
        });
    });
});