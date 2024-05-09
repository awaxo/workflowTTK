import moment from 'moment';
import GLOBALS from '../../js/globals.js';

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-external-access').DataTable({
        ajax: '/api/external-access',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'external_system' },
            { data: 'admin_group_name' },
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
                title: 'Műveletek',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    // 'visszaállítás' should be visible only if deleted is true
                    return (
                        '<div class="d-inline-block">' +
                        '<a href="javascript:;" class="btn btn-sm text-primary btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                        (!full.deleted ? '<li><a href="javascript:;" class="dropdown-item modify-external-access" data-bs-toggle="offcanvas" data-bs-target="#new_external_access">Módosítás</a></li>' : '') +
                        (full.deleted ? '<li><a href="javascript:;" class="dropdown-item restore-external-access">Visszaállítás</a></li>' : '') +
                        (!full.deleted ? '<div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-external-access">Törlés</a></li>' : '') +
                        '</ul>' +
                        '</div>'
                    );
                }
            }
        ],
        order: [[1, 'asc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        dom: '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új hozzáférési jogosultság</span>',
                className: 'create-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#new_external_access'
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
                $('.datatables-external-access').DataTable().draw();
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

    // delete external access
    $(document).on('click', '.delete-external-access', function() {
        var row = $(this).closest('tr');
        var externalAccessId = $('.datatables-external-access').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-external-access-id', externalAccessId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm delete external access
    $('#confirm_delete').on('click', function () {
        var externalAccessId = $(this).data('external-access-id');

        $.ajax({
            url: '/api/external-access/' + externalAccessId + '/delete',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#deleteConfirmation').modal('hide');
                $('#errorAlertMessage').text('Hiba történt a törlés során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });

    //restore external access
    $(document).on('click', '.restore-external-access', function() {
        var row = $(this).closest('tr');
        var externalAccessId = $('.datatables-external-access').DataTable().row(row).data().id;

        $('#confirm_restore').attr('data-external-access-id', externalAccessId);
        $('#restoreConfirmation').modal('show');
    });

    // confirm restore external access
    $('#confirm_restore').on('click', function () {
        var externalAccessId = $(this).data('external-access-id');

        $.ajax({
            url: '/api/external-access/' + externalAccessId + '/restore',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#restoreConfirmation').modal('hide');
                $('#errorAlertMessage').text('Hiba történt a visszaállítás során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });

    // modify external access
    $(document).on('click', '.modify-external-access', function() {
        var row = $(this).closest('tr');
        var externalAccess = $('.datatables-external-access').DataTable().row(row).data();

        $('#external_system').val(externalAccess.external_system);
        $('#admin_group_number').val(externalAccess.admin_group_number).trigger('change');
        $('#admin_group_number').trigger('change');

        $('.data-submit').attr('data-external-access-id', externalAccess.id);
    });

    // submit external access
    $('.data-submit').on('click', function() {
        var externalAccessId = $(this).data('external-access-id');
        var url = externalAccessId ? '/api/external-access/' + externalAccessId + '/update' : '/api/external-access/create';

        $('.invalid-feedback').remove();
        let fv = validateExternalAccess();

        $('#external_system').on('change', function() {
            fv.revalidateField('external_system');
        });

        fv.validate().then(function(status) {
            if(status === 'Valid') {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        external_system: $('#external_system').val(),
                        admin_group_number: $('#admin_group_number').val()
                    },
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        bootstrap.Offcanvas.getInstance(document.getElementById('new_external_access')).hide();
                        var errors = jqXHR.responseJSON.errors;
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                $('#errorAlertMessage').append(errors[key] + '<br>');
                            }
                        }
                        $('#errorAlert').removeClass('d-none');
                        console.log(textStatus, errorThrown);
                    }
                });
            }
        });
    });
});

function validateExternalAccess() {
    return FormValidation.formValidation(
        document.getElementById('new_external_access'),
        {
            fields: {
                external_system: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük add meg a külső rendszert'
                        },
                        stringLength: {
                            max: 255,
                            message: 'A külső rendszer megnevezése maximum 255 karakter lehet'
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