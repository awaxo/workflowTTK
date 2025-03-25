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

    // Initialize delegates DataTable
    const delegatesTable = $('.datatables-delegates').DataTable({
        ajax: '/api/delegations',
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
        $('.invalid-feedback').remove();
        let fv = validateDelegation();
    
        fv.validate().then(function(status) {
            if(status === 'Valid') {
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

/**
 * Validate delegation form fields
 * @returns {object} FormValidation instance
 */
function validateDelegation() {
    return FormValidation.formValidation(
        document.getElementById('navs-pills-delegations'),
        {
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
                        callback: {
                            message: 'A helyettesítés vége nem lehet korábban a helyettesítés kezdténél',
                            callback: function(input) {
                                if (input.value === '') {
                                    return true;
                                }

                                return input.value >= $('#delegation_start_date').val();
                            }
                        }
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    ).on('core.field.invalid', function(field) {
        $(`#${field}`).next().addClass('is-invalid');
    }).on('core.field.valid', function(field) {
        $(`#${field}`).next().removeClass('is-invalid');
    });
}