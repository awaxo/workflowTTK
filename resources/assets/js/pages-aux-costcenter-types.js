import moment from 'moment';
import GLOBALS from '../../js/globals.js';

$(function() {
    'use strict';

    $('.datatables-costcenter-types').DataTable({
        ajax: '/api/costcenter-types',
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'name' },
            { 
                data: 'tender',
                render: function(data, type, row) {
                    if (data) {
                        return '<i class="fas fa-check text-success"></i>';
                    } else {
                        return '<i class="fas fa-times text-danger"></i>';
                    }
                }
            },
            { data: 'clause_template' },
            { 
                data: 'deleted',
                render: function(data, type, row) {
                    if (!data) {
                        return '<i class="fas fa-check text-success"></i>';
                    } else {
                        return '<i class="fas fa-times text-danger"></i>';
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
                        '<li><a href="javascript:;" class="dropdown-item modify-costcenter-type" data-bs-toggle="offcanvas" data-bs-target="#new_costcenter_type">Módosítás</a></li>' +
                        (full.deleted ? '<li><a href="javascript:;" class="dropdown-item restore-costcenter-type">Visszaállítás</a></li>' : '') +
                        '<div class="dropdown-divider"></div>' +
                        '<li><a href="javascript:;" class="dropdown-item text-danger delete-costcenter-type">Törlés</a></li>' +
                        '</ul>' +
                        '</div>'
                    );
                }
            }
        ],
        order: [[1, 'asc']],
        displayLength: 7,
        lengthMenu: [7, 10, 25, 50, 75, 100],
        dom: '<"card-header"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
        },
        drawCallback: function() {
            var table = this.api();
            var showInactive = $('#show_inactive').is(':checked');

            table.rows().every(function() {
                var data = this.data();
                if (showInactive) {
                    $(this.node()).show();
                } else {
                    if (!data.deleted) {
                        $(this.node()).show();
                    } else {
                        $(this.node()).hide();
                    }
                }
            });
        }
    });

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
                $('#deleteConfirmation').modal('hide');
                $('#errorAlertMessage').text('Hiba történt a törlés során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
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
                $('#restoreConfirmation').modal('hide');
                $('#errorAlertMessage').text('Hiba történt a visszaállítás során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });

    // modify costcenter type
    $(document).on('click', '.modify-costcenter-type', function() {
        var row = $(this).closest('tr');
        var costcenterType = $('.datatables-costcenter-types').DataTable().row(row).data();

        $('#name').val(costcenterType.name);
        $('#tender').val(costcenterType.tender);
        $('#clause_template').trigger('clause_template');
        $('.data-submit').attr('data-costcenter-type-id', costcenterType.id);
    });

    // submit costcenter type
    $('.data-submit').on('click', function() {
        var costcenterTypeId = $(this).data('costcenter-type-id');
        var url = costcenterTypeId ? '/api/costcenter-type/' + costcenterTypeId + '/update' : '/api/costcenter-type/create';

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: $('#name').val(),
                tender: $('#tender').is(':checked'),
                clause_template: $('#clause_template').val()
            },
            success: function (response) {
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                bootstrap.Offcanvas.getInstance(document.getElementById('new_costcenter_type')).hide();
                $('#errorAlertMessage').text('Hiba történt a mentés során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
        });
    });
});
