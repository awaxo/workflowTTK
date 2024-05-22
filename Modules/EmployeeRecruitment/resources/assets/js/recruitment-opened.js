import moment from 'moment';
import GLOBALS from '/resources/js/globals.js';

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-recruitments').DataTable({
        ajax: '/employee-recruitment/opened',
        columns: [
            { data: 'id', searchable: false },
            { 
                data: 'created_at',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD HH:mm:ss');
                }
            },
            { data: 'workgroup1' },
            { data: 'workgroup2' },
            { data: 'base_salary_cost_center_1' },
            { data: 'name' },
            { data: 'position_type' },
            { data: 'position_name' },
            { data: 'employment_type' },
            { data: 'employment_start_date' },
            { data: 'state' },
            {
                data: 'is_manager_user', 
                searchable: false, 
                orderable: false,
                render: function(data, type, row, meta) {
                    return data ? '<a href="javascript:;" class="delete-workflow"><i class="fas fa-times text-danger"></i></a>' : '';
                }
            },
        ],
        columnDefs: [
            {
                targets: 2,
                render: function(data, type, full, meta) {
                    return '<span title="' + data + '">' + full['workgroup1_number'] + '</span>';
                }
            },
            {
                targets: 3,
                render: function(data, type, full, meta) {
                    return data ? '<span title="' + data + '">' + full['workgroup2_number'] + '</span>' : '-';
                }
            },
            {
                targets: 4,
                render: function(data, type, full, meta) {
                    return '<span title="' + data + '">' + full['base_salary_cost_center_1_code'] + '</span>';
                }
            },
            {
                // State
                targets: -2,
                responsivePriority: 3,
                render: function(data, type, full, meta) {
                    let $is_user_responsible = full['is_user_responsible'];

                    var $row_output = '';

                    if ($is_user_responsible) {
                        if (full['state_name'] === 'suspended') {
                            $row_output = `<a href="/folyamat/visszaallitas/${full['id']}"><span class="badge bg-label-info m-1">${data}</span></a>`;
                        } else {
                            $row_output = `<a href="/folyamat/jovahagyas/${full['id']}"<span class="badge bg-label-info m-1">${data}</span></a>`;
                        }
                    } else {
                        if (full['is_initiator_role']) {
                            $row_output = `<a href="/folyamat/megtekintes/${full['id']}"><span class="badge bg-label-info m-1">${data}</span>`;
                        } else {
                            $row_output = `<a href="/folyamat/megtekintes/${full['id']}"><span class="badge bg-label-secondary m-1">${data}</span>`;
                        }
                    }

                    return $row_output;
                }
            },
        ],
        order: [[1, 'asc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function(row) {
                        var data = row.data();
                        return 'Details of ' + data['full_name'];
                    }
                }),
                type: 'column',
                renderer: function(api, rowIdx, columns) {
                    var data = $.map(columns, function(col, i) {
                        return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                        ? '<tr data-dt-row="' +
                            col.rowIndex +
                            '" data-dt-column="' +
                            col.columnIndex +
                            '">' +
                            '<td>' +
                            col.title +
                            ':' +
                            '</td> ' +
                            '<td>' +
                            col.data +
                            '</td>' +
                            '</tr>'
                        : '';
                    }).join('');

                    return data ? $('<table class="table"/><tbody />').append(data) : false;
                }
            }
        },
        language: GLOBALS.DATATABLE_TRANSLATION
    });

    // refresh number of rows on show inactive checkbox change
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            let showInactive = $('#show_inactive').prop('checked');
            let isInactive = dataTable.row(dataIndex).data().deleted;
            if (showInactive) {
                return true;
            } else {
                return !isInactive;
            }
        }
    );

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    // cancel workflow
    $(document).on('click', '.delete-workflow', function() {
        var row = $(this).closest('tr');
        var workflowId = $('.datatables-recruitments').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-workflow-id', workflowId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm cancel workflow
    $('#confirm_delete').on('click', function() {
        var textarea = $('#cancel_reason');
        if (!textarea.val().trim()) {
            textarea.addClass('is-invalid');
            textarea.next('.invalid-feedback').remove();
            textarea.after('<div class="invalid-feedback">Kérlek, add meg sztornózás okát</div>');
            return false;
        } else {
            textarea.removeClass('is-invalid');
            textarea.next('.invalid-feedback').remove();
            
            let workflowId = $(this).data('workflow-id');
            let reason = $('#cancel_reason').val();

            $.ajax({
                url: '/employee-recruitment/' + workflowId + '/suspend',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    is_cancel: true,
                    message: reason
                },
                success: function (response) {
                    window.location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#deleteConfirmation').modal('hide');
                    $('#errorAlertMessage').text('Hiba történt a sztornózás során!');
                    $('#errorAlert').removeClass('d-none');
                    console.log(textStatus, errorThrown);
                }
            });
        }
    });

    // Optional: Clear validation message when modal is closed or opened
    $('#deleteConfirmation').on('hidden.bs.modal', function () {
        $('#cancel_reason').removeClass('is-invalid').val('');
        $('.invalid-feedback').remove();
    });
});
