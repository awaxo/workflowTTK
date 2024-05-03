import moment from 'moment';
import GLOBALS from '../../js/globals.js';
import { is } from 'immutable';

'use strict';

$(function () {
    $("#delegation_start_date, #delegation_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: new Date()
    });

    $('.datatables-delegates').DataTable({
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
                // For Responsive
                targets: 0,
                className: 'control',
                orderable: false,
                responsivePriority: 2,
                searchable: false,
                render: function(data, type, full, meta) {
                    return '';
                }
            },
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

        language: GLOBALS.DATATABLE_TRANSLATION,
    });

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    $('#save_delegation').on('click', function() {
        $('.invalid-feedback').remove();

        let fv = validateDelegations();

        // Revalidate fields when their values change
        $('#delegation_type, #delegated_user, #delegation_start_date, #delegation_end_date').off('change').on('change', function() {
            fv.revalidateField('delegation_type');
            fv.revalidateField('delegated_user');
            fv.revalidateField('delegation_start_date');
            fv.revalidateField('delegation_end_date');
        });

        fv.validate().then(function(status) {
            if(status === 'Valid') {
                $.ajax({
                    url: 'api/delegation/create',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        type: $('#delegation_type').val(),
                        delegated_user: $('#delegated_user').val(),
                        start_date: $('#delegation_start_date').val(),
                        end_date: $('#delegation_end_date').val()
                    },
                    success: function() {
                        $('.datatables-delegates').DataTable().ajax.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#errorAlertMessage').text('Hiba történt a helyettes kijelölése során!');
                        $('#errorAlert').removeClass('d-none');
                        console.log(textStatus, errorThrown);
                    }
                });
            } else {
                // TODO: Handle the case when the fields are not valid
            }
        });
    });

    $(document).on('click', '.delete-delegation', function() {
        var row = $(this).closest('tr');
        var delegationId = $('.datatables-delegates').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-delegation-id', delegationId);
        $('#confirm_delete').data('row', row);
        $('#deleteConfirmation').modal('show');
    });

    // confirm cancel workflow
    $('#confirm_delete').on('click', function() {
        let delegationId = $(this).data('delegation-id');
        let row = $(this).data('row');

        $.ajax({
            url: '/api/delegation/' + delegationId + '/delete',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#deleteConfirmation').modal('hide');
                $('.datatables-delegates').DataTable().row(row).remove().draw();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#deleteConfirmation').modal('hide');
                $('#errorAlertMessage').text('Hiba történt a törlés során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });
});

function validateDelegations() {
    let fv = FormValidation.formValidation(
        document.getElementById('navs-pills-delegations'),
        {
            fields: {
                delegated_user: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a delegate'
                        }
                    }
                },
                delegation_start_date: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter a start date'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Please enter a valid date in the format YYYY.MM.DD'
                        }
                    }
                },
                delegation_end_date: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter an end date'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Please enter a valid date in the format YYYY.MM.DD'
                        }
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    );

    return fv;
}