import moment from 'moment';

$(function() {
    'use strict';

    let apiEndpoint = $('.datatables-users').data('api-endpoint');
  
    $('.datatables-users').DataTable({
        ajax: apiEndpoint,
        columns: [
            { data: '' },
            { data: 'id', visible: false, searchable: false },
            { data: 'name' },
            { data: 'email' },
            { data: 'created_at', visible: false, searchable: false },
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
                targets: -2,
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD HH:mm:ss');
                }
            },
            {
                // Actions
                targets: -1,
                title: '',
                orderable: false,
                searchable: false,
                render: function(data, type, full, meta) {
                    return (
                        '<a href="javascript:;" class="btn btn-sm text-primary btn-icon item-edit"><i class="bx bxs-edit"></i></a>'
                    );
                }
            }
        ],
        order: [[2, 'asc']],
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