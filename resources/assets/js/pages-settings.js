import GLOBALS from '../../js/globals.js';

$(function() {
    const instances = GLOBALS.initNumberInputs();
    let currentWorkflowDeadlines = [];

    // Initialize form fields to empty state on page load
    function initializeFormFields() {
        $('#workflows').val('0').trigger('change');
        $('#workflow_states').empty().append('<option value="0" selected>Válassz státuszt</option>');
        $('#workflow_state_deadline').val('');
        $('#deadlines-table-container').hide();
        currentWorkflowDeadlines = [];
    }

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

    /**
     * Load and display deadlines table for selected workflow
     * @param {string} workflowKey - The workflow identifier
     */
    function loadWorkflowDeadlines(workflowKey) {
        if (workflowKey === '0' || !workflowKey) {
            $('#deadlines-table-container').hide();
            currentWorkflowDeadlines = [];
            return;
        }

        $.ajax({
            url: '/api/settings/workflow/' + workflowKey + '/deadlines',
            type: 'GET',
            success: function(response) {
                currentWorkflowDeadlines = response.data;
                renderDeadlinesTable();
                $('#deadlines-table-container').show();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }
                console.log('Error loading workflow deadlines:', textStatus, errorThrown);
                GLOBALS.AJAX_ERROR('Hiba a határidők betöltése során!');
            }
        });
    }

    /**
     * Render the deadlines table
     */
    function renderDeadlinesTable() {
        const tbody = $('#deadlines-table-body');
        tbody.empty();

        if (currentWorkflowDeadlines.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center text-muted">Nincsenek beállított határidők</td></tr>');
            return;
        }

        currentWorkflowDeadlines.forEach(function(item) {
            const deadlineValue = item.deadline || '';
            const row = `
                <tr data-state="${item.state}">
                    <td>${item.state_name}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <input class="form-control numeral-mask deadline-input" 
                                   type="text" 
                                   value="${deadlineValue}" 
                                   data-state="${item.state}"
                                   data-option-name="${item.option_name}"
                                   placeholder="0" />
                            <span class="ms-2">Óra</span>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary save-deadline-btn" 
                                data-state="${item.state}">
                            Mentés
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Initialize number inputs for the new deadline inputs
        GLOBALS.initNumberInputs('.deadline-input');
    }

    /**
     * Update deadline in the current data array
     * @param {string} state - State identifier
     * @param {string} newDeadline - New deadline value
     */
    function updateCurrentDeadline(state, newDeadline) {
        const index = currentWorkflowDeadlines.findIndex(item => item.state === state);
        if (index !== -1) {
            currentWorkflowDeadlines[index].deadline = newDeadline;
        }
    }

    /**
     * Save individual deadline from table
     * @param {string} state - State identifier
     * @param {string} deadline - Deadline value
     */
    function saveTableDeadline(state, deadline) {
        const workflowKey = $('#workflows').val();
        
        if (!workflowKey || workflowKey === '0') {
            GLOBALS.AJAX_ERROR('Folyamat kiválasztása kötelező!');
            return;
        }

        $.ajax({
            url: '/api/settings/update-deadline',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                workflow: workflowKey,
                state: state,
                deadline: deadline,
            },
            success: function(response) {
                GLOBALS.AJAX_SUCCESS('Határidő mentve');
                updateCurrentDeadline(state, deadline);
                
                // Update the dropdown selection if this state is currently selected
                if ($('#workflow_states').val() === state) {
                    $('#workflow_state_deadline').val(deadline);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }
                
                GLOBALS.AJAX_ERROR('Hiba történt a határidő mentése során!', jqXHR, textStatus, errorThrown);
            }
        });
    }

    // Event handlers for generic settings
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

    // Validation handlers
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

    // Workflow selection change handler - updated to load deadlines table
    $('#workflows').on('change', function() {
        const workflowKey = $(this).val();
        
        if (workflowKey == 0) {
            $('#workflow_states').empty();
            $('#workflow_states').append('<option value="0" selected>Válassz státuszt</option>');
            loadWorkflowDeadlines('0');
            return;
        }

        // Load workflow states
        $.ajax({
            url: '/api/workflow/' + workflowKey + '/states',
            type: 'GET',
            success: function(response) {
                $('#workflow_states').empty();

                $('#workflow_states').append('<option value="0" selected>Válassz státuszt</option>');
                Object.entries(response.data).forEach(function([key, state]) {
                    // Filter out states that cannot be deadlined
                    if (key == 'new_request' || key == 'completed' || key == 'rejected' || key == 'suspended') {
                        return;
                    }

                    $('#workflow_states').append('<option value="' + key + '">' + state + '</option>');
                });
                
                // Load deadlines table
                loadWorkflowDeadlines(workflowKey);
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

    // Workflow state selection change handler
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

    // Submit deadline button handler - updated to refresh table
    $('.btn-submit-deadline').on('click', function(e) {
        if ($('#workflows').val() == 0 || $('#workflow_states').val() == 0) {
            GLOBALS.AJAX_ERROR('Folyamat és státusz kiválasztása kötelező!');
            return;
        }

        const state = $('#workflow_states').val();
        const deadline = $('#workflow_state_deadline').val();

        $.ajax({
            url: '/api/settings/update-deadline',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                workflow: $('#workflows').val(),
                state: state,
                deadline: deadline,
            },
            success: function(response) {
                GLOBALS.AJAX_SUCCESS('Határidő mentve');
                
                // Update the table
                updateCurrentDeadline(state, deadline);
                renderDeadlinesTable();
                
                // Update the input in the table if it exists
                $(`input[data-state="${state}"]`).val(deadline);
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

    // Event delegation for dynamically created table elements
    $(document).on('click', '.save-deadline-btn', function() {
        const state = $(this).data('state');
        const deadlineInput = $(this).closest('tr').find('.deadline-input');
        const deadline = GLOBALS.cleanNumber(deadlineInput.val());
        
        saveTableDeadline(state, deadline);
    });

    // Optional: Save on Enter key press in deadline inputs
    $(document).on('keypress', '.deadline-input', function(e) {
        if (e.which === 13) { // Enter key
            const state = $(this).data('state');
            const deadline = GLOBALS.cleanNumber($(this).val());
            saveTableDeadline(state, deadline);
        }
    });

    // Optional: Auto-save on blur (when user leaves the input field)
    $(document).on('blur', '.deadline-input', function() {
        const state = $(this).data('state');
        const deadline = GLOBALS.cleanNumber($(this).val());
        const currentDeadline = currentWorkflowDeadlines.find(item => item.state === state)?.deadline || '';
        
        // Only save if value changed
        if (deadline !== currentDeadline) {
            saveTableDeadline(state, deadline);
        }
    });

    // Initialize form fields on page load
    initializeFormFields();
});