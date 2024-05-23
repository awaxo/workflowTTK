import moment from 'moment';
import GLOBALS from '/resources/js/globals.js';

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-recruitments').DataTable({
        ajax: '/employee-recruitment/closed',
        columns: [
            { data: 'id', searchable: false },
            { 
                data: 'created_at',
                render: function(data, type, row) {
                    return moment(data).format('YYYY.MM.DD HH:mm:ss');
                }
            },
            { data: 'workgroup1' },
            { data: 'workgroup2' },
            { data: 'base_salary_cost_center_1' },
            { data: 'name' },
            { data: 'position_type' },
            { data: 'position_name' },
            { data: 'employment_type' },
            { data: 'employment_start_date' },
            { data: 'state' }
        ],
        columnDefs: [
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
                targets: -1,
                responsivePriority: 3,
                render: function(data, type, full, meta) {
                    return `<a href="/folyamat/megtekintes/${full['id']}"><span class="badge bg-label-secondary m-1">${data}</span>`;
                }
            },
        ],
        order: [[1, 'asc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
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
        language: GLOBALS.DATATABLE_TRANSLATION
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
});
