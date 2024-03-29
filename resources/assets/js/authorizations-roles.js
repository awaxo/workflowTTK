import moment from 'moment';

$(function() {
    'use strict';

    let apiEndpoint = $('.datatables-roles').data('api-endpoint');
  
    $('.datatables-roles').DataTable({
        ajax: apiEndpoint,
        columns: [
            { data: 'id', visible: false, searchable: false },
            { data: 'name_readable' },
            { data: 'users_count' },
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
                // Users count
                targets: 2,
                responsivePriority: 3,
                render: function(data, type, full, meta) {
                    let $users_count = full['users_count'];

                    var $row_output = '';

                    if ($users_count === 0) {
                        $row_output = '<span class="badge bg-label-warning m-1">' +
                            $users_count +
                            ' felhasználó</span>';
                    } else {
                        $row_output = '<a href="/felhasznalok/szerepkor/' + full['name'] + '">' +
                            '<span class="badge bg-label-primary m-1">' +
                            $users_count +
                            ' felhasználó</span>' +
                            '</a>';
                    }

                    return $row_output;
                }
            },
        ],
        order: [[1, 'asc']],
        displayLength: 7,
        lengthMenu: [7, 10, 25, 50, 75, 100],
        buttons: [],
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
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Hungarian.json'
        },
    });

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});