import moment from 'moment';
import GLOBALS from '../../js/globals.js';

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
                        '<a href="javascript:;" class="btn btn-sm text-primary btn-icon"><i class="bx bx-trash"></i></a>' +
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
                alert('Sikeres delegálás');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#errorAlertMessage').text('Hiba történt a helyettes kijelölése során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });
});
