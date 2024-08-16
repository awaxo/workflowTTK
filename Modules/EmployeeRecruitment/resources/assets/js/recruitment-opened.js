import moment from 'moment';
import GLOBALS from '../../../../../resources/js/globals.js';

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-recruitments').DataTable({
        ajax: '/employee-recruitment/opened',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'workgroup1' },
            { data: 'workgroup2' },
            { data: 'base_salary_cost_center_1' },
            { data: 'position_type' },
            { data: 'position_name' },
            { data: 'employment_type' },
            { data: 'employment_start_date' },
            {
                data: 'created_at',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD HH:mm:ss');
                }
            },
            { data: 'state' },
            {
                data: 'is_manager_user', 
                searchable: false, 
                orderable: false,
                render: function(data, type, row, meta) {
                    return data ? '<a href="javascript:;" class="delete-workflow"><i class="fas fa-minus text-danger" title="Sztornózás"></i></a>' : '';
                }
            },
        ],
        columnDefs: [
            {
                targets: 0,
                render: function(data, type, full, meta) {
                    let $is_user_responsible = full['is_user_responsible'];

                    var $row_output = '';

                    if ($is_user_responsible) {
                        if (full['state_name'] === 'suspended') {
                            $row_output = `<a href="/folyamat/visszaallitas/${full['id']}"><span class="badge bg-label-info m-1" style="font-size: 15px;">${data}</span></a>`;
                        } else {
                            $row_output = `<a href="/folyamat/jovahagyas/${full['id']}"<span class="badge bg-label-info m-1" style="font-size: 15px;">${data}</span></a>`;
                        }
                    } else {
                        if (full['is_initiator_role']) {
                            $row_output = `<a href="/folyamat/megtekintes/${full['id']}"><span class="badge bg-label-info m-1" style="font-size: 15px;">${data}</span>`;
                        } else {
                            $row_output = `<a href="/folyamat/megtekintes/${full['id']}"><span class="badge bg-label-secondary m-1" style="font-size: 15px;">${data}</span>`;
                        }
                    }

                    return $row_output;
                }
            },
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
                targets: 10,
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
        order: [[0, 'desc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        dom: window.isSecretary 
            ? '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
            : '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új folyamat</span>',
                className: 'create-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#new_workgroup'
                },
            }
        ],
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
        language: GLOBALS.DATATABLE_TRANSLATION,
        initComplete: function() {
            var checkboxOwnHtml = `
                <div class="form-check form-switch show-own-cases">
                    <input class="form-check-input" type="checkbox" role="switch" id="show_only_own">
                    <label class="form-check-label" for="show_only_own">Csak saját jóváhagyásra váró ügyek listázása</label>
                </div>
            `;
            var checkboxClosedHtml = `
                <div class="form-check form-switch show-closed-cases ms-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="show_closed">
                    <label class="form-check-label" for="show_closed">Lezárt folyamatok megjelenítése is</label>
                </div>
            `;
            var parent = $(this).closest('.dataTables_wrapper').find('.dataTables_length').parent();
            parent.css('display', 'flex').css('align-items', 'center');
            parent.find('.dataTables_length').css('margin-right', '20px');
            parent.find('.dataTables_length').after(checkboxClosedHtml).after(checkboxOwnHtml);

            $('#show_only_own, #show_closed').on('change', function() {
                $('.datatables-recruitments').DataTable().draw();
            }).trigger('change');
        }
    });

    // refresh number of rows on show only own checkbox change
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var showOnlyOwn = $('#show_only_own').prop('checked');
            var rowData = dataTable.row(dataIndex).data();
            
            if (showOnlyOwn) {
                return rowData.is_user_responsible;
            } else {
                return true;
            }
        }
    );

    // refresh number of rows on show closed checkbox change
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var showClosedAlso = $('#show_closed').prop('checked');
            var rowData = dataTable.row(dataIndex).data();
            
            if (showClosedAlso) {
                return true;
            } else {
                return !rowData.is_closed;
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
                    if (jqXHR.status === 401 || jqXHR.status === 419) {
                        alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                        window.location.href = '/login';
                    }

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

    $('.create-new').on('click', function() {
        window.location.href = '/hr/felveteli-kerelem/uj';
    });
});
