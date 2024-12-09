import moment from 'moment';
import GLOBALS from '../../js/globals.js';

var fv;

$(function() {
    // Set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true
        });
    });

    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });

    let dataTable = $('.datatables-institutes').DataTable({
        ajax: '/api/institutes',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'group_level' },
            { data: 'name' },
            { data: 'abbreviation' },
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
                            (!full.deleted ? '<li><a href="javascript:;" class="dropdown-item modify-institute" data-bs-toggle="offcanvas" data-bs-target="#new_institute">Módosítás</a></li>' : '') +
                            (full.deleted ? '<li><a href="javascript:;" class="dropdown-item restore-institute">Visszaállítás</a></li>' : '') +
                            (!full.deleted ? '<div class="dropdown-divider"></div><li><a href="javascript:;" class="dropdown-item text-danger delete-institute">Törlés</a></li>' : '') +
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
                text: '<i class="bx bx-plus me-1"></i> <span class="d-none d-lg-inline-block">Új intézet</span>',
                className: 'create-new btn btn-primary',
                attr: {
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#new_institute'
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
                $('.datatables-institutes').DataTable().draw();
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

    // delete institute
    $(document).on('click', '.delete-institute', function() {
        var row = $(this).closest('tr');
        var instituteId = $('.datatables-institutes').DataTable().row(row).data().id;

        $('#confirm_delete').attr('data-institute-id', instituteId);
        $('#deleteConfirmation').modal('show');
    });

    // confirm delete institute
    $('#confirm_delete').on('click', function () {
        var instituteId = $(this).data('institute-id');

        $.ajax({
            url: '/api/institute/' + instituteId + '/delete',
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

    //restore institute
    $(document).on('click', '.restore-institute', function() {
        var row = $(this).closest('tr');
        var instituteId = $('.datatables-institutes').DataTable().row(row).data().id;

        $('#confirm_restore').attr('data-institute-id', instituteId);
        $('#restoreConfirmation').modal('show');
    });

    // confirm restore institute
    $('#confirm_restore').on('click', function () {
        var instituteId = $(this).data('institute-id');

        $.ajax({
            url: '/api/institute/' + instituteId + '/restore',
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

    // modify institute
    $(document).on('click', '.modify-institute', function() {
        var row = $(this).closest('tr');
        var institute = $('.datatables-institutes').DataTable().row(row).data();

        $('#new_institute_label').text('Intézet módosítás');

        $('#group_level').val(institute.group_level);
        $('#name').val(institute.name);
        $('#abbreviation').val(institute.abbreviation);
        $('.data-submit').attr('data-institute-id', institute.id);
    });

    // submit institute
    $('.data-submit').on('click', function() {
        var instituteId = $(this).data('institute-id');
        var url = instituteId ? '/api/institute/' + instituteId + '/update' : '/api/institute/create';

        $('.invalid-feedback').remove();
        fv = validateInstitute();

        $('#group_level, #name, #abbreviation').on('change', function() {
            fv.revalidateField('group_level');
            fv.revalidateField('name');
            fv.revalidateField('abbreviation');
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
                        group_level: $('#group_level').val(),
                        name: $('#name').val(),
                        abbreviation: $('#abbreviation').val()
                    },
                    success: function (response) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }
                        
                        bootstrap.Offcanvas.getInstance(document.getElementById('new_institute')).hide();
                        $('.data-submit').attr('data-institute-id', null);
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
        $('#new_institute_label').text('Új intézet');
        $('.data-submit').attr('data-institute-id', null);
        $('#group_level').val('');
        $('#name').val('');
        $('#abbreviation').val('');

        fv?.resetForm(true);
    });
});

function validateInstitute() {
    return FormValidation.formValidation(
        document.getElementById('new_institute'),
        {
            fields: {
                group_level: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az intézet számát'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az intézet nevét'
                        },
                        stringLength: {
                            max: 255,
                            message: 'Az intézet neve legfeljebb 255 karakter hosszú lehet'
                        }
                    }
                },
                abbreviation: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az intézet rövidítését'
                        },
                        stringLength: {
                            max: 255,
                            message: 'Az intézet rövidítése legfeljebb 255 karakter hosszú lehet'
                        }
                    }
                }
            },
            plugins: {
                bootstrap: new FormValidation.plugins.Bootstrap5()
            },
        }
    );
}