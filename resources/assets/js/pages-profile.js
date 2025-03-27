import moment from 'moment';
import GLOBALS from '../../js/globals.js';
import { is } from 'immutable';

'use strict';

$(function () {
    const instances = GLOBALS.initNumberInputs();

    $("#delegation_start_date, #delegation_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: new Date(),
        language: 'hu',
        weekStart: 1,
    });

    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });

    // Status handling utility
    const StatusUtils = {
        // Map status codes to display status
        getStatusDisplay: function(status, deleted) {
            // Ha deleted = 1, akkor mindig "Érvénytelen"
            if (deleted === 1) {
                return 'Érvénytelen';
            }
            
            // Ha nincs státusz, alapértelmezetten "Elfogadásra vár"
            if (!status) {
                return 'Elfogadásra vár';
            }
            
            // A státusz már lefordított formátumban érkezik a szervertől
            return status;
        },
        
        // Get badge class based on status
        getStatusBadgeClass: function(status) {
            if (status === 'Érvényes') {
                return 'bg-label-success';
            } else if (status === 'Érvénytelen') {
                return 'bg-label-danger';
            } else {
                return 'bg-label-warning';
            }
        },
        
        // Check if status is valid
        isStatusValid: function(status) {
            return status === 'Érvényes';
        },
        
        // Check if status is invalid or delegation is deleted
        isStatusInvalid: function(status, deleted) {
            return status === 'Érvénytelen' || deleted === 1;
        },
        
        // Check if status is waiting for acceptance
        isStatusWaiting: function(status, deleted) {
            return status === 'Elfogadásra vár' && deleted !== 1;
        }
    };

    // Initialize delegates DataTable
    const delegatesTable = $('.datatables-delegates').DataTable({
        ajax: {
            url: '/api/delegations', 
            data: function (d) {
                d.show_deleted = $('#show_deleted_my_delegations').is(':checked');
            }
        },
        autoWidth: false,
        dom: 'rtip',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'delegate_name' },
            { data: 'readable_type' },
            { 
                data: 'start_date',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD');
                }
            },
            {
                data: 'end_date',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD');
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    const status = StatusUtils.getStatusDisplay(data, row.deleted);
                    const badgeClass = StatusUtils.getStatusBadgeClass(status);
                    return '<span class="badge ' + badgeClass + '">' + status + '</span>';
                }
            },
            { data: '' }
        ],
        columnDefs: [
            {
                // Actions
                targets: -1,
                title: 'Törlés',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    // Ha a státusz érvénytelen vagy törölt, ne jelenítsen meg gombot
                    if (StatusUtils.isStatusInvalid(full.status, full.deleted)) {
                        return '';
                    }
                    
                    return (
                        '<div class="d-inline-block">' +
                        '<a href="javascript:;" class="btn btn-sm text-danger btn-icon delete-delegation"><i class="bx bx-trash"></i></a>' +
                        '</div>'
                    );
                }
            }
        ],
        order: [[1, 'asc']],
        buttons: [],
        displayLength: 10,
        lengthMenu: [5, 10, 25],
        language: GLOBALS.DATATABLE_TRANSLATION
    });

    // Initialize delegated to me DataTable
    const delegatedToMeTable = $('.datatables-delegated-to-me').DataTable({
        ajax: {
            url: '/api/delegations/delegations-to-me',
            data: function (d) {
                d.show_deleted = $('#show_deleted_delegations').is(':checked');
            }
        },
        autoWidth: false,
        dom: 'rtip',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'original_user_name' },
            { data: 'readable_type' },
            { 
                data: 'start_date',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD');
                }
            },
            {
                data: 'end_date',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD');
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    const status = StatusUtils.getStatusDisplay(data, row.deleted);
                    const badgeClass = StatusUtils.getStatusBadgeClass(status);
                    return '<span class="badge ' + badgeClass + '">' + status + '</span>';
                }
            },
            { 
                data: '',  
                render: function(data, type, full, meta) {
                    // Ha a státusz érvénytelen vagy törölt, ne jelenítsen meg gombot
                    if (StatusUtils.isStatusInvalid(full.status, full.deleted)) {
                        return '';
                    }
                    
                    // Ha a státusz érvényes, csak a törlés gomb jelenjen meg
                    if (StatusUtils.isStatusValid(full.status)) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm text-danger btn-icon reject-delegation" data-bs-toggle="tooltip" title="Törlés">' +
                            '<i class="bx bx-trash"></i>' +
                            '</a>' +
                            '</div>'
                        );
                    }
                    
                    // Ha elfogadásra vár, mindkét gomb jelenjen meg
                    return (
                        '<div class="d-inline-block">' +
                        '<a href="javascript:;" class="btn btn-sm text-success btn-icon accept-delegation me-1" data-bs-toggle="tooltip" title="Elfogadás">' +
                        '<i class="bx bx-check"></i>' +
                        '</a>' +
                        '<a href="javascript:;" class="btn btn-sm text-danger btn-icon reject-delegation" data-bs-toggle="tooltip" title="Törlés">' +
                        '<i class="bx bx-trash"></i>' +
                        '</a>' +
                        '</div>'
                    );
                }
            }
        ],
        order: [[1, 'asc']],
        buttons: [],
        displayLength: 10,
        lengthMenu: [5, 10, 25],
        language: GLOBALS.DATATABLE_TRANSLATION,
        drawCallback: function() {
            // Initialize tooltips after table is drawn
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Handle checkbox change for "Helyettesek beállítása" deleted delegations
    $('#show_deleted_my_delegations').on('change', function() {
        delegatesTable.ajax.reload();
    });

    // Handle checkbox change for deleted delegations
    $('#show_deleted_delegations').on('change', function() {
        delegatedToMeTable.ajax.reload();
    });

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    // Handle delegation type change
    $('#delegation_type').on('change', function() {
        if ($(this).val() !== '') {
            let type = $(this).val();
            
            // Clear previous delegates
            $('#delegated_user').empty();
            
            $.ajax({
                url: '/api/delegates/' + type,
                type: 'GET',
                success: function(response) {
                    let options = '';
                    if (response) {
                        if (Array.isArray(response)) {
                            response.forEach(function(user) {
                                options += `<option value="${user.id}">${user.name}</option>`;
                            });
                        } else {
                            options += `<option value="${response.id}">${response.name}</option>`;
                        }
                    }
                    $('#delegated_user').html(options);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 401 || jqXHR.status === 419) {
                        alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                        window.location.href = '/login';
                    }

                    GLOBALS.AJAX_ERROR('Hiba történt a helyettesítők betöltése során!', jqXHR, textStatus, errorThrown);
                }
            });
        }
    });
    
    // Trigger change if value exists on page load
    if ($('#delegation_type').val()) {
        $('#delegation_type').trigger('change');
    }

    // Save delegation handler
    $('#save_delegation').on('click', function() {
        // Clear any existing validation messages
        $('.invalid-feedback').remove();
        $('.is-invalid').removeClass('is-invalid');
        
        let fv = validateDelegation();
    
        fv.validate().then(function(status) {
            if(status === 'Valid') {
                // Additional manual validation for end date
                const startDate = $('#delegation_start_date').val();
                const endDate = $('#delegation_end_date').val();
                
                if (startDate && endDate) {
                    const startMoment = moment(startDate, 'YYYY.MM.DD');
                    const maxEndDate = moment(startMoment).add(2, 'months');
                    const endMoment = moment(endDate, 'YYYY.MM.DD');
                    
                    // Check date order
                    if (endDate < startDate) {
                        showEndDateError('A helyettesítés vége nem lehet korábban a helyettesítés kezdténél');
                        return;
                    }
                    
                    // Check 2 month limit
                    if (endMoment.isAfter(maxEndDate)) {
                        showEndDateError('A helyettesítés vége nem lehet 2 hónapnál későbbi a kezdő dátumnál');
                        return;
                    }
                }
                
                $.ajax({
                    url: 'api/delegation/create',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        type: $('#delegation_type').val(),
                        delegate_user_id: $('#delegated_user').val(),
                        start_date: $('#delegation_start_date').val(),
                        end_date: $('#delegation_end_date').val()
                    },
                    success: function() {
                        // Reset form fields
                        $('#delegation_start_date').val('');
                        $('#delegation_end_date').val('');
                        $('#delegation_type').val(null).trigger('change');
                        $('#delegated_user').val(null).trigger('change');
    
                        // Reload datatables
                        $('.datatables-delegates').DataTable().ajax.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }
    
                        if (jqXHR.status === 409) {
                            try {
                                // Parse the response to get detailed information
                                const response = JSON.parse(jqXHR.responseText);
                                
                                if (response.overlapping && response.overlapping.length > 0) {
                                    // Format the dates for better readability
                                    const formatDates = (overlapping) => {
                                        return overlapping.map(item => {
                                            // Create readable date format
                                            const startDate = moment(item.start_date).format('YYYY.MM.DD');
                                            const endDate = moment(item.end_date).format('YYYY.MM.DD');
                                            return `${startDate} - ${endDate}`;
                                        }).join(', ');
                                    };
                                    
                                    const formattedDates = formatDates(response.overlapping);
                                    const errorMessage = `Átfedő időszak! Már létezik helyettesítés a kiválasztott funkciókhoz és helyetteshez a következő időszakokban: ${formattedDates}`;
                                    
                                    GLOBALS.AJAX_ERROR(errorMessage, jqXHR, textStatus, errorThrown);
                                } else {
                                    GLOBALS.AJAX_ERROR('A megadott adatokkal már van helyettesítés rögzítve', jqXHR, textStatus, errorThrown);
                                }
                            } catch (e) {
                                // If parsing fails, fallback to the generic message
                                GLOBALS.AJAX_ERROR('A megadott adatokkal már van helyettesítés rögzítve', jqXHR, textStatus, errorThrown);
                            }
                        } else if (jqXHR.status === 422) {
                            try {
                                // Parse validation errors
                                const response = JSON.parse(jqXHR.responseText);
                                
                                if (response.errors && response.errors.end_date) {
                                    // Display specific error about 2-month limit
                                    const errorMessage = response.errors.end_date[0];
                                    GLOBALS.AJAX_ERROR(errorMessage, jqXHR, textStatus, errorThrown);
                                    
                                    // Also show error in the form
                                    showEndDateError(errorMessage);
                                } else {
                                    GLOBALS.AJAX_ERROR(response.message || 'Érvénytelen adatok!', jqXHR, textStatus, errorThrown);
                                }
                            } catch (e) {
                                GLOBALS.AJAX_ERROR('Érvénytelen adatok!', jqXHR, textStatus, errorThrown);
                            }
                        } else {
                            GLOBALS.AJAX_ERROR('Hiba történt a helyettesítés mentése során!', jqXHR, textStatus, errorThrown);
                        }
                    }
                });
            }
        });
    });

    // Delete delegation handler
    $(document).on('click', '.delete-delegation', function() {
        var row = $(this).closest('tr');
        var delegationId = $('.datatables-delegates').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-delegation-id', delegationId);
        $('#confirm_delete').data('row', row);
        $('#deleteConfirmation').modal('show');
    });

    // Confirm delete handler
    $('#confirm_delete').on('click', function() {
        let delegationId = $(this).attr('data-delegation-id');
        let row = $(this).data('row');
        let delegationData = $('.datatables-delegates').DataTable().row(row).data();
        
        // Function to delete a single delegation
        const deleteDelegation = (id) => {
            return $.ajax({
                url: '/api/delegation/' + id + '/delete',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            });
        };
        
        // Check if we have multiple delegations in a group
        if (delegationData.delegations && delegationData.delegations.length > 0) {
            // Create an array of promises for each deletion
            const deletePromises = delegationData.delegations.map(id => deleteDelegation(id));
            
            // Wait for all deletions to complete
            Promise.all(deletePromises)
                .then(() => {
                    $('#deleteConfirmation').modal('hide');
                    $('.datatables-delegates').DataTable().row(row).remove().draw();
                })
                .catch((error) => {
                    if (error.status === 401 || error.status === 419) {
                        alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                        window.location.href = '/login';
                    }
                    
                    $('#deleteConfirmation').modal('hide');
                    GLOBALS.AJAX_ERROR('Hiba történt a törlés során!', error, 'error', '');
                });
        } else {
            // Delete a single delegation (original behavior)
            deleteDelegation(delegationId)
                .done(function(response) {
                    $('#deleteConfirmation').modal('hide');
                    $('.datatables-delegates').DataTable().row(row).remove().draw();
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 401 || jqXHR.status === 419) {
                        alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                        window.location.href = '/login';
                    }

                    $('#deleteConfirmation').modal('hide');
                    GLOBALS.AJAX_ERROR('Hiba történt a törlés során!', jqXHR, textStatus, errorThrown);
                });
        }
    });

    // Accept delegation handler
    $(document).on('click', '.accept-delegation', function() {
        var row = $(this).closest('tr');
        var delegationId = delegatedToMeTable.row(row).data().id;

        $('#action-confirmation-text').text('Biztosan elfogadod ezt a helyettesítést?');
        $('#confirm_action').attr('data-delegation-id', delegationId);
        $('#confirm_action').attr('data-action', 'accept');
        $('#delegationActionConfirmation').modal('show');
    });

    // Reject delegation handler
    $(document).on('click', '.reject-delegation', function() {
        var row = $(this).closest('tr');
        var delegationId = delegatedToMeTable.row(row).data().id;

        $('#action-confirmation-text').text('Biztosan törlöd ezt a helyettesítést?');
        $('#confirm_action').attr('data-delegation-id', delegationId);
        $('#confirm_action').attr('data-action', 'reject');
        $('#delegationActionConfirmation').modal('show');
    });

    // Confirm action handler (for accept/reject)
    $('#confirm_action').on('click', function() {
        let delegationId = $(this).attr('data-delegation-id');
        let action = $(this).attr('data-action');
        
        let url = action === 'accept' 
            ? '/api/delegation/' + delegationId + '/accept'
            : '/api/delegation/' + delegationId + '/reject';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#delegationActionConfirmation').modal('hide');
                
                // Reload both tables to reflect changes
                delegatedToMeTable.ajax.reload();
                delegatesTable.ajax.reload();
                
                // Show success message
                const successMessage = action === 'accept' 
                    ? 'Helyettesítés sikeresen elfogadva!'
                    : 'Helyettesítés sikeresen törölve!';
                    
                Swal.fire({
                    title: 'Sikeres művelet!',
                    text: successMessage,
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                $('#delegationActionConfirmation').modal('hide');
                GLOBALS.AJAX_ERROR('Hiba történt a művelet során!', jqXHR, textStatus, errorThrown);
            }
        });
    });

    // Save notification settings
    $('.btn-submit').on('click', function() {
        $.ajax({
            url: '/api/notification-settings/update',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                approval_notification: $('#approval_notification').is(':checked') ? 'true' : 'false',
            },
            success: function() {
                //
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                GLOBALS.AJAX_ERROR('Hiba történt a mentés során!', jqXHR, textStatus, errorThrown);
            }
        });
    });
});

// Helper function to show end date error with custom message
function showEndDateError(message) {
    // Remove any existing error message
    $('#delegation_end_date_error').remove();
    
    // Add the error message
    const errorDiv = $('<div>')
        .attr('id', 'delegation_end_date_error')
        .addClass('invalid-feedback')
        .text(message);
    
    // Add error class to input
    $('#delegation_end_date').addClass('is-invalid');
    
    // Append error message after the input
    $('#delegation_end_date').after(errorDiv);
}

/**
 * Validate delegation form fields
 * @returns {object} FormValidation instance
 */
function validateDelegation() {
    // Define validation rules
    const validationRules = {
        fields: {
            delegation_type: {
                validators: {
                    notEmpty: {
                        message: 'Kérjük válassz helyettesített funkciót'
                    }
                }
            },
            delegated_user: {
                validators: {
                    notEmpty: {
                        message: 'Kérjük válassz helyettesítőt'
                    }
                }
            },
            delegation_start_date: {
                validators: {
                    notEmpty: {
                        message: 'Kérjük add meg a helyettesítés kezdetét'
                    },
                    date: {
                        format: 'YYYY.MM.DD',
                        message: 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD'
                    }
                }
            },
            delegation_end_date: {
                validators: {
                    notEmpty: {
                        message: 'Kérjük add meg a helyettesítés végét'
                    },
                    date: {
                        format: 'YYYY.MM.DD',
                        message: 'Kérjük, valós formában add meg a dátumot: YYYY.MM.DD'
                    },
                    // We'll handle these validations separately, but keep a basic callback
                    // for the FormValidation framework
                    callback: {
                        message: 'A helyettesítés vége érvénytelen',
                        callback: function(input) {
                            // Clear any existing custom error messages
                            $('#delegation_end_date_error').remove();
                            
                            if (input.value === '') {
                                return true;
                            }
                            
                            const startDateValue = $('#delegation_start_date').val();
                            if (startDateValue === '') {
                                return true;
                            }
                            
                            // Check date order
                            if (input.value < startDateValue) {
                                showEndDateError('A helyettesítés vége nem lehet korábban a helyettesítés kezdténél');
                                return false;
                            }
                            
                            // Check 2 month limit
                            const startDate = moment(startDateValue, 'YYYY.MM.DD');
                            const maxEndDate = moment(startDate).add(2, 'months');
                            const endDate = moment(input.value, 'YYYY.MM.DD');
                            
                            if (endDate.isAfter(maxEndDate)) {
                                showEndDateError('A helyettesítés vége nem lehet 2 hónapnál későbbi a kezdő dátumtól');
                                return false;
                            }
                            
                            return true;
                        }
                    }
                }
            }
        },
        plugins: {
            bootstrap: new FormValidation.plugins.Bootstrap5(),
        },
    };
    
    // Add event listeners for date changes
    $('#delegation_start_date, #delegation_end_date').on('change', function() {
        const startDate = $('#delegation_start_date').val();
        const endDate = $('#delegation_end_date').val();
        
        // Clear any existing error
        $('#delegation_end_date_error, div[data-field="delegation_end_date"]').remove();
        $('#delegation_end_date').removeClass('is-invalid');
        
        // Skip validation if either date is empty
        if (!startDate || !endDate) {
            return;
        }
        
        // Check date order
        if (endDate < startDate) {
            showEndDateError('A helyettesítés vége nem lehet korábban a helyettesítés kezdténél');
            return;
        }
        
        // Check 2 month limit
        const startMoment = moment(startDate, 'YYYY.MM.DD');
        const maxEndDate = moment(startMoment).add(2, 'months');
        const endMoment = moment(endDate, 'YYYY.MM.DD');
        
        if (endMoment.isAfter(maxEndDate)) {
            showEndDateError('A helyettesítés vége nem lehet 2 hónapnál későbbi a kezdő dátumtól');
        }
    });
    
    // Initialize FormValidation
    const fv = FormValidation.formValidation(
        document.getElementById('navs-pills-delegations'),
        validationRules
    ).on('core.field.invalid', function(field) {
        $(`#${field}`).addClass('is-invalid');
    }).on('core.field.valid', function(field) {
        $(`#${field}`).removeClass('is-invalid');
    });
    
    return fv;
}