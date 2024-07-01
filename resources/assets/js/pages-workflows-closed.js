import moment from 'moment';
import GLOBALS from '../../js/globals.js';

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-workflows').DataTable({
        ajax: '/api/workflows/closed',
        columns: [
            { data: 'id', searchable: false },
            { data: 'workflow_type_name' },
            { data: 'state' },
            { data: 'initiator_institute_abbreviation' },
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
        ],
        columnDefs: [
            {
                // State
                targets: 2,
                responsivePriority: 3,
                render: function(data, type, full, meta) {
                    let $is_user_responsible = full['is_user_responsible'];

                    var $row_output = '';

                    $row_output = `<a href="/folyamat/megtekintes/${full['id']}"><span class="badge bg-label-secondary m-1">${data}</span>`;

                    return $row_output;
                }
            },
        ],
        order: [[1, 'asc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
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
        language: GLOBALS.DATATABLE_TRANSLATION
    });

    // refresh number of rows on show inactive checkbox change
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var showOnlyOwn = $('#show_only_own').prop('checked');
            var rowData = dataTable.row(dataIndex).data();
            
            if (showOnlyOwn) {
                return rowData.is_user_responsible;
            } else {
                return true;
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
