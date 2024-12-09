import moment from 'moment';
import GLOBALS from '../../js/globals.js';

var fv;

$(function() {
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true
        });
    });

    function waitForIntl(callback) {
        if (typeof $.fn.dataTable.ext.order.intl === 'function') {
            callback();
        } else {
            setTimeout(() => waitForIntl(callback), 150);
        }
    }
    waitForIntl(() => {
        $.fn.dataTable.ext.order.intl('hu', {
            sensitivity: 'base'
        });
    });

    let dataTable = $('.datatables-workgroups').DataTable({
        ajax: '/api/workgroups',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'workgroup_number' },
            { data: 'name' },
            { data: 'leader_name' },
            { data: 'labor_administrator_name' },
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
                title: window.isLeaderOfWg912 ? 'Műveletek' : '',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    if (window.isLeaderOfWg912) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm text-primary btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                            '<ul class="dropdown-menu dropdown-menu-end">' +
                            (!full.deleted ? '<li><a href="javascript:;" class="dropdown-item modify-workgroup" data-bs-toggle="offcanvas" data-bs-target="#new_workgroup">Módosítás</a></li>' : '') +
                            (full.deleted ? '<li><a href="javascript:;" class="dropdown-item restore-workgroup">Visszaállítás</a></li>' : '') +
                            (!full.deleted ? '<div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-workgroup">Törlés</a></li>' : '') +
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
        dom: window.isLeaderOfWg912 
            ? '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
            : '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új csoport</span>',
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
                $('.datatables-workgroups').DataTable().draw();
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

    // delete workgroup
    $(document).on('click', '.delete-workgroup', function() {
        var row = $(this).closest('tr');
        var workgroupId = $('.datatables-workgroups').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-workgroup-id', workgroupId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm delete workgroup
    $('#confirm_delete').on('click', function () {
        var workgroupId = $(this).data('workgroup-id');

        $.ajax({
            url: '/api/workgroup/' + workgroupId + '/delete',
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

    //restore workgroup
    $(document).on('click', '.restore-workgroup', function() {
        var row = $(this).closest('tr');
        var workgroupId = $('.datatables-workgroups').DataTable().row(row).data().id;

        $('#confirm_restore').attr('data-workgroup-id', workgroupId);
        $('#restoreConfirmation').modal('show');
    });

    // confirm restore workgroup
    $('#confirm_restore').on('click', function () {
        var workgroupId = $(this).data('workgroup-id');

        $.ajax({
            url: '/api/workgroup/' + workgroupId + '/restore',
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

    // modify workgroup
    $(document).on('click', '.modify-workgroup', function() {
        var row = $(this).closest('tr');
        var workgroup = $('.datatables-workgroups').DataTable().row(row).data();

        $('#new_workgroup_label').text('Csoport módosítás');

        $('#new_workgroup #workgroup_number').val(workgroup.workgroup_number);
        $('#new_workgroup #name').val(workgroup.name);
        $('#new_workgroup #leader_id').val(workgroup.leader_id).trigger('change');
        $('#new_workgroup #labor_administrator').val(workgroup.labor_administrator).trigger('change');
        $('.data-submit').attr('data-workgroup-id', workgroup.id);
    });

    // submit workgroup
    $('.data-submit').on('click', function() {
        var workgroupId = $(this).data('workgroup-id');
        var url = workgroupId ? '/api/workgroup/' + workgroupId + '/update' : '/api/workgroup/create';

        $('.invalid-feedback').remove();
        fv = validateWorkgroup();

        $('#workgroup_number, #name').on('change', function() {
            fv.revalidateField('workgroup_number');
            fv.revalidateField('name');
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
                        workgroup_number: $('#workgroup_number').val(),
                        name: $('#name').val(),
                        leader_id: $('#leader_id').val(),
                        labor_administrator: $('#labor_administrator').val(),
                        workgroupId: workgroupId
                    },
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }
                        
                        bootstrap.Offcanvas.getInstance(document.getElementById('new_workgroup')).hide();
                        $('.data-submit').attr('data-workgroup-id', null);
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

    $('.create-new').on('click', function() {
        $('#new_workgroup_label').text('Új csoport');
        $('.data-submit').attr('data-workgroup-id', null);
        $('#workgroup_number').val('');
        $('#name').val('');
        $('#leader_id').val($('#leader_id option:first').val()).trigger('change');
        $('#labor_administrator').val($('#labor_administrator option:first').val()).trigger('change');

        fv?.resetForm(true);
    });
});

function validateWorkgroup() {
    return FormValidation.formValidation(
        document.getElementById('new_workgroup'),
        {
            fields: {
                workgroup_number: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a csoportszámot'
                        },
                        numeric: {
                            message: 'A csoportszám csak szám lehet'
                        },
                        stringLength: {
                            max: 5,
                            message: 'A csoportszám maximum 5 karakter hosszú lehet'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a csoport nevét'
                        },
                        stringLength: {
                            max: 255,
                            message: 'A csoport neve maximum 255 karakter hosszú lehet'
                        },
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    );
}