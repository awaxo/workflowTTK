import moment from 'moment';
import GLOBALS from '../../js/globals.js';
import { transform } from 'lodash';

var fv;

$(function() {
    // set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: ' '
        });
    });

    $('#due_date').datepicker({
        format: "yyyy.mm.dd",
        language: 'hu',
        weekStart: 1,
        startDate: new Date()
    });

    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });

    let dataTable = $('.datatables-costcenters').DataTable({
        ajax: '/api/costcenters',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'cost_center_code' },
            { data: 'name' },
            { data: 'type_name' },
            { data: 'lead_user_name' },
            { data: 'project_coordinator_user_name' },
            {
                data: 'due_date',
                render: function(data, type, row) {
                    return data ? moment(data).format('YYYY.MM.DD') : '';
                }
            },
            { 
                data: 'minimal_order_limit',
                render: function(data, type, row) {
                    return data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
                }
            },
            { 
                data: 'valid_employee_recruitment',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
                    } else {
                        return data;
                    }
                }
            },
            { 
                data: 'deleted',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data ? '<i class="fas fa-times text-danger"></i>' : '<i class="fas fa-check text-success"></i>';
                    } else {
                        return data;
                    }
                }
            },
            { data: 'updated_by_name' },
            { 
                data: 'updated_at',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD HH:mm:ss');
                }
            },
            { data: 'created_by_name' },
            { 
                data: 'created_at',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD HH:mm:ss');
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
                title: window.isWg910Or911User ? 'Műveletek' : '',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    if (window.isWg910Or911User) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm text-primary btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                            '<ul class="dropdown-menu dropdown-menu-end">' +
                            (!full.deleted ? '<li><a href="javascript:;" class="dropdown-item modify-costcenter" data-bs-toggle="offcanvas" data-bs-target="#new_costcenter">Módosítás</a></li>' : '') +
                            (full.deleted ? '<li><a href="javascript:;" class="dropdown-item restore-costcenter">Visszaállítás</a></li>' : '') +
                            (!full.deleted ? '<div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-costcenter">Törlés</a></li>' : '') +
                            '</ul>' +
                            '</div>'
                        );
                    } else {
                        return '';
                    }
                }
            }
        ],
        order: [[1, 'asc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        dom: window.isWg910Or911User 
            ? '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
            : '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                text: '<i class="bx bx-import me-1"></i> <span class="d-none d-lg-inline-block">Import</span>',
                className: 'create-new btn btn-primary me-4',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#import_costcenter'
                },
            },
            {
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új költséghely</span>',
                className: 'create-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#new_costcenter'
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
            var checkboxHtml = `
                <div class="form-check form-switch show-inactive">
                    <input class="form-check-input" type="checkbox" role="switch" id="show_inactive">
                    <label class="form-check-label" for="show_inactive">Inaktívak megjelenítése</label>
                </div>
            `;
            var parent = $(this).closest('.dataTables_wrapper').find('.dataTables_length').parent();
            parent.css('display', 'flex').css('align-items', 'center');
            parent.find('.dataTables_length').css('margin-right', '20px');
            parent.find('.dataTables_length').after(checkboxHtml);

            $('#show_inactive').on('change', function() {
                $('.datatables-costcenters').DataTable().draw();
            });
        }
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

    // delete costcenter
    $(document).on('click', '.delete-costcenter', function() {
        var row = $(this).closest('tr');
        var costcenterId = $('.datatables-costcenters').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-costcenter-id', costcenterId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm delete costcenter
    $('#confirm_delete').on('click', function () {
        var costcenterId = $(this).data('costcenter-id');

        $.ajax({
            url: '/api/costcenter/' + costcenterId + '/delete',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
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
                GLOBALS.AJAX_ERROR('Hiba történt a törlés során!', jqXHR, textStatus, errorThrown);
            }
        });
    });

    //restore costcenter
    $(document).on('click', '.restore-costcenter', function() {
        var row = $(this).closest('tr');
        var costcenterId = $('.datatables-costcenters').DataTable().row(row).data().id;

        $('#confirm_restore').attr('data-costcenter-id', costcenterId);
        $('#restoreConfirmation').modal('show');
    });

    // confirm restore costcenter
    $('#confirm_restore').on('click', function () {
        var costcenterId = $(this).data('costcenter-id');

        $.ajax({
            url: '/api/costcenter/' + costcenterId + '/restore',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                $('#restoreConfirmation').modal('hide');
                GLOBALS.AJAX_ERROR('Hiba történt a visszaállítás során!', jqXHR, textStatus, errorThrown);
            }
        });
    });

    // modify costcenter
    $(document).on('click', '.modify-costcenter', function() {
        var row = $(this).closest('tr');
        var costcenter = $('.datatables-costcenters').DataTable().row(row).data();

        $('#new_costcenter_label').text('Költséghely módosítás');

        $('#cost_center_code').val(costcenter.cost_center_code);
        $('#name').val(costcenter.name);
        $('#type_id').val(costcenter.type_id).trigger('change');
        $('#lead_user_id').val(costcenter.lead_user_id).trigger('change');
        $('#project_coordinator_user_id').val(costcenter.project_coordinator_user_id).trigger('change');
        if (costcenter.due_date) {
            $('#due_date').val(moment(costcenter.due_date).format('YYYY.MM.DD'));
        }
        $('#minimal_order_limit').val(costcenter.minimal_order_limit);
        $('#valid_employee_recruitment').val(costcenter.valid_employee_recruitment);
        if (costcenter.valid_employee_recruitment) {
            $('#valid_employee_recruitment').prop('checked', true);
        } else {
            $('#valid_employee_recruitment').prop('checked', false);
        }
        $('.data-submit').attr('data-costcenter-id', costcenter.id);
    });

    // submit costcenter
    $('.data-submit').on('click', function() {
        var costcenterId = $(this).data('costcenter-id');
        var url = costcenterId ? '/api/costcenter/' + costcenterId + '/update' : '/api/costcenter/create';

        $('.invalid-feedback').remove();
        fv = validateCostCenter();

        $('#cost_center_code, #name, #due_date, #minimal_order_limit').on('change', function() {
            fv.revalidateField('cost_center_code');
            fv.revalidateField('name');
            fv.revalidateField('due_date');
            fv.revalidateField('minimal_order_limit');
        });

        fv.validate().then(function(status) {
            if(status === 'Valid') {
                // Disable the button to prevent double clicks
                $(".data-submit").prop('disabled', true);
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        cost_center_code: $('#cost_center_code').val(),
                        name: $('#name').val(),
                        type_id: $('#type_id').val(),
                        lead_user_id: $('#lead_user_id').val(),
                        project_coordinator_user_id: $('#project_coordinator_user_id').val(),
                        due_date: $('#due_date').val(),
                        minimal_order_limit: $('#minimal_order_limit').val(),
                        valid_employee_recruitment: $('#valid_employee_recruitment').is(':checked'),
                    },
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }
                        
                        bootstrap.Offcanvas.getInstance(document.getElementById('new_costcenter')).hide();
                        $('.data-submit').attr('data-costcenter-id', null);
                        var errors = jqXHR.responseJSON.errors;
                        var errorMessages = "";
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorMessages += errors[key] + '<br>';
                            }
                        }
                        GLOBALS.AJAX_ERROR(errorMessages, jqXHR, textStatus, errorThrown);
                    }
                });
            }
        });
    });

    // import costcenter
    $('.upload-csv-btn').on('click', function() {
        // Disable the button to prevent multiple clicks
        $(".upload-csv-btn").prop('disabled', true);
    
        var formData = new FormData();
        formData.append('csv_file', $('#csv_file')[0].files[0]);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // CSRF token for Laravel
    
        $.ajax({
            url: '/api/costcenter/import',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $(".upload-csv-btn").prop('disabled', false);
                $('#csv_file').val('');
                $('.cancel').trigger('click');
                GLOBALS.AJAX_SUCCESS('Költséghelyek importálása sikeres');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $(".upload-csv-btn").prop('disabled', false);
    
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                $('.cancel').trigger('click');
    
                var errors = jqXHR.responseJSON.errors;
                var errorMessages = "";
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        errorMessages += errors[key] + '<br>';
                    }
                }

                if (errorMessages === '') {
                    GLOBALS.AJAX_ERROR('Ismeretlen hiba történt az importálás során', jqXHR, textStatus, errorThrown);
                } else {
                    GLOBALS.AJAX_ERROR(errorMessages, jqXHR, textStatus, errorThrown);
                }
            }
        });
    });

    $('.create-new').on('click', function() {
        $('#new_costcenter_label').text('Új költséghely');
        $('.data-submit').attr('data-costcenter-id', null);
        $('#cost_center_code').val('');
        $('#name').val('');
        $('#due_date').val('');
        $('#minimal_order_limit').val('0');
        $('#valid_employee_recruitment').prop('checked', true);
        $('#type_id').val($('#type_id option:first').val()).trigger('change');
        $('#lead_user_id').val($('#lead_user_id option:first').val()).trigger('change');
        $('#project_coordinator_user_id').val($('#project_coordinator_user_id option:first').val()).trigger('change');

        fv?.resetForm(true);
    });
});

function validateCostCenter() {
    return FormValidation.formValidation(
        document.getElementById('new_costcenter'),
        {
            fields: {
                cost_center_code: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        },
                        stringLength: {
                            max: 50,
                            message: 'A költséghely maximum 50 karakter lehet'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a megnevezést'
                        },
                        stringLength: {
                            max: 255,
                            message: 'A megnevezés maximum 255 karakter lehet'
                        }
                    }
                },
                due_date: {
                    validators: {
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        }
                    }
                },
                minimal_order_limit: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a minimális rendelési limitet'
                        }
                    }
                },
            },
            plugins: {
                transformer: new FormValidation.plugins.Transformer({
                    minimal_order_limit: {
                        integer: function(field, element, validator) {
                            return element.value.replace(/\s+/g, '');
                        }
                    }
                }),
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    );
}