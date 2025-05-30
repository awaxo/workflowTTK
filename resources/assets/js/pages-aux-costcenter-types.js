import moment from 'moment';
import GLOBALS from '../../js/globals.js';

var fv;

$(function() {
    fv = validateCostCenterType();

    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-costcenter-types').DataTable({
        ajax: '/api/costcenter-types',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'name' },
            { 
                data: 'tender',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
                    } else {
                        return data;
                    }
                }
            },
            { data: 'financial_countersign' },
            { data: 'clause_template' },
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
                title: window.isWg910Or911 ? 'Műveletek' : '',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    if (window.isWg910Or911) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm text-primary btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></a>' +
                            '<ul class="dropdown-menu dropdown-menu-end">' +
                            (!full.deleted ? '<li><a href="javascript:;" class="dropdown-item modify-costcenter-type" data-bs-toggle="offcanvas" data-bs-target="#new_costcenter_type">Módosítás</a></li>' : '') +
                            (full.deleted ? '<li><a href="javascript:;" class="dropdown-item restore-costcenter-type">Visszaállítás</a></li>' : '') +
                            (!full.deleted ? '<div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-costcenter-type">Törlés</a></li>' : '') +
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
        dom: window.isWg910Or911 
            ? '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
            : '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új költséghely típus</span>',
                className: 'create-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#new_costcenter_type'
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
                $('.datatables-costcenter-types').DataTable().draw();
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

    // delete costcenter type
    $(document).on('click', '.delete-costcenter-type', function() {
        var row = $(this).closest('tr');
        var costcenterId = $('.datatables-costcenter-types').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-costcenter-type-id', costcenterId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm delete costcenter type
    $('#confirm_delete').on('click', function () {
        var costcenterId = $(this).data('costcenter-type-id');

        $.ajax({
            url: '/api/costcenter-type/' + costcenterId + '/delete',
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

    //restore costcenter type
    $(document).on('click', '.restore-costcenter-type', function() {
        var row = $(this).closest('tr');
        var costcenterId = $('.datatables-costcenter-types').DataTable().row(row).data().id;

        $('#confirm_restore').attr('data-costcenter-type-id', costcenterId);
        $('#restoreConfirmation').modal('show');
    });

    // confirm restore costcenter type
    $('#confirm_restore').on('click', function () {
        var costcenterId = $(this).data('costcenter-type-id');

        $.ajax({
            url: '/api/costcenter-type/' + costcenterId + '/restore',
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

    // modify costcenter type
    $(document).on('click', '.modify-costcenter-type', function() {
        var row = $(this).closest('tr');
        var costcenterType = $('.datatables-costcenter-types').DataTable().row(row).data();

        $('#new_costcenter_type_label').text('Költséghely típus módosítás');

        $('#name').val(costcenterType.name);
        if (costcenterType.tender) {
            $('#tender').prop('checked', true);
        } else {
            $('#tender').prop('checked', false);
        }
        $('#financial_countersign').val(costcenterType.financial_countersign).trigger('change');
        $('#clause_template').val(costcenterType.clause_template).trigger('change');
        $('.data-submit').attr('data-costcenter-type-id', costcenterType.id);

        fv.revalidateField('name');
    });

    // submit costcenter type
    $('.data-submit').on('click', function() {
        var costcenterTypeId = $(this).data('costcenter-type-id');
        var url = costcenterTypeId ? '/api/costcenter-type/' + costcenterTypeId + '/update' : '/api/costcenter-type/create';

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
                        tender: $('#tender').is(':checked'),
                        financial_countersign: $('#financial_countersign').val(),
                        clause_template: $('#clause_template').val()
                    },
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }
                        
                        bootstrap.Offcanvas.getInstance(document.getElementById('new_costcenter_type')).hide();
                        $('.data-submit').attr('data-costcenter-type-id', null);
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
        $('#new_costcenter_type_label').text('Új költséghely típus');
        $('.data-submit').attr('data-costcenter-type-id', null);
        $('#name').val('');
        $('#clause_template').val('');
        $('#tender').prop('checked', false);
        $('#financial_countersign').val($('#financial_countersign option:first').val()).trigger('change');

        fv?.resetForm(true);
    });
});

function validateCostCenterType() {
    return FormValidation.formValidation(
        document.getElementById('new_costcenter_type'),
        {
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghely típus nevét'
                        },
                        stringLength: {
                            max: 255,
                            message: 'A költséghely típus neve maximum 255 karakter lehet'
                        },
                        regexp: {
                            regexp: /^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s,-]+$/,
                            message: 'A költséghely típus neve csak betűket, szóközt, vesszőt és kötőjelet tartalmazhat'
                        },
                        remote: {
                            url: '/api/costcenter-type/check-name-unique',
                            method: 'POST',
                            data: function() {
                                return {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    name: $('#name').val(),
                                    costcenter_type_id: $('.data-submit').data('costcenter-type-id') || null
                                };
                            },
                            message: 'Ez a költséghely típus név már használatban van'
                        }
                    }
                },
                financial_countersign: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a pénzügyi ellenjegyzőt'
                        },
                    }
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger({
                    event: {
                        name: 'blur change',
                        financial_countersign: 'change'
                    },
                }),
                bootstrap: new FormValidation.plugins.Bootstrap5(),
                autoFocus: new FormValidation.plugins.AutoFocus(),
            },
        }
    );
}