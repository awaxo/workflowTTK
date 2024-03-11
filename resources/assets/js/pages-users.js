import moment from 'moment';

$(function() {
    'use strict';
  
    $('.datatables-users').DataTable({
        ajax: '/api/users',
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
                // Avatar image/badge, Name
                targets: 2,
                responsivePriority: 4,
                render: function(data, type, full, meta) {
                    let $user_img = full['avatar'],
                        $name = full['full_name'],
                        $initials;

                    /*if ($user_img) {
                        // For Avatar image
                        var $output =
                        '<img src="' + assetsPath + '/img/avatars/' + $user_img + '" alt="Avatar" class="rounded-circle">';
                    } else {
                        // For Avatar badge
                        var $state = 'primary';
                        $name = full['full_name'],
                        $initials = $name.match(/\b\w/g) || [];
                        $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
                        $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';
                    }*/

                    // Creates full output for row
                    var $row_output =
                        '<div class="d-flex justify-content-start align-items-center">' +
                            '<div class="d-flex flex-column">' +
                                '<span class="emp_name text-truncate">' +
                                    $name +
                                '</span>' +
                            '</div>' +
                        '</div>';
                    return $row_output;
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