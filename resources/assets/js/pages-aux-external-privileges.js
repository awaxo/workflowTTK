import moment from 'moment';
import GLOBALS from '../../js/globals.js';

var fv;

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-external-privileges').DataTable({
        ajax: '/api/external-privileges',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'name' },
            { 
                data: 'description',
                render: function(data, type, row) {
                    if (data) {
                        return data.length > 50 ? data.substr(0, 50) + '...' : data;
                    }
                    return '-';
                }
            },
            { data: 'user_count' },
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
                title: window.canManageExternalPrivileges ? 'Műveletek' : '',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    if (window.canManageExternalPrivileges) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm text-primary btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                            '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a href="javascript:;" class="dropdown-item modify-privilege" data-bs-toggle="offcanvas" data-bs-target="#new_external_privilege">Módosítás</a></li>' +
                            '<div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-privilege">Törlés</a></li>' +
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
        dom: window.canManageExternalPrivileges 
            ? '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
            : '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új külsős jog</span>',
                className: 'create-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#new_external_privilege'
                },
            }
        ],
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function(row) {
                        var data = row.data();
                        return 'Részletek: ' + data.name;
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
    });

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    // delete privilege
    $(document).on('click', '.delete-privilege', function() {
        var row = $(this).closest('tr');
        var privilegeId = $('.datatables-external-privileges').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-privilege-id', privilegeId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm delete privilege
    $('#confirm_delete').on('click', function () {
        var privilegeId = $(this).data('privilege-id');

        $.ajax({
            url: '/api/external-privilege/' + privilegeId + '/delete',
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

    // modify privilege
    $(document).on('click', '.modify-privilege', function() {
        var row = $(this).closest('tr');
        var privilege = $('.datatables-external-privileges').DataTable().row(row).data();

        $('#new_external_privilege_label').text('Külsős jog módosítása');

        $('#name').val(privilege.name);
        $('#description').val(privilege.description);
        $('.data-submit').attr('data-privilege-id', privilege.id);
    });

    // submit privilege
    $('.data-submit').on('click', function() {
        var privilegeId = $(this).data('privilege-id');
        var url = privilegeId ? '/api/external-privilege/' + privilegeId + '/update' : '/api/external-privilege/create';

        $('.invalid-feedback').remove();
        fv = validateExternalPrivilege();

        $('#name').on('change', function() {
            fv.revalidateField('name');
        });

        $('#description').on('change', function() {
            fv.revalidateField('description');
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
                        name: $('#name').val(),
                        description: $('#description').val(),
                    },
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }
                        
                        bootstrap.Offcanvas.getInstance(document.getElementById('new_external_privilege')).hide();
                        $('.data-submit').attr('data-privilege-id', null);
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
        $('#new_external_privilege_label').text('Új külsős jog');
        $('.data-submit').attr('data-privilege-id', null);
        $('#name').val('');
        $('#description').val('');

        fv?.resetForm(true);
    });
});

function validateExternalPrivilege() {
    return FormValidation.formValidation(
        document.getElementById('new_external_privilege'),
        {
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a külsős jog nevét'
                        },
                        stringLength: {
                            max: 255,
                            message: 'A külsős jog neve maximum 255 karakter hosszú lehet'
                        }
                    }
                },
                description: {
                    validators: {
                        stringLength: {
                            max: 1000,
                            message: 'A leírás maximum 1000 karakter hosszú lehet'
                        }
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    );
}