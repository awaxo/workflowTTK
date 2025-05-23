import moment from 'moment';
import GLOBALS from '../../../../../resources/js/globals.js';

$(function() {
    // set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });
    
    let dataTable = $('.datatables-drafts').DataTable({
        ajax: '/employee-recruitment/drafts/opened',
        columns: [
            { data: 'pseudo_id', type: 'num' },
            { data: 'name' },
            { data: 'workgroup1' },
            { data: 'workgroup2' },
            { data: 'position_name' },
            { // Audit column
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, full) {
                    return `<button class="btn btn-sm btn-icon audit-trigger" 
                                    data-pseudo-id="P${full['pseudo_id']}" 
                                    data-created-by="${full['created_by_name'] || 'Ismeretlen'}" 
                                    data-created-at="${full['created_at']}" 
                                    data-updated-by="${full['updated_by_name'] || 'Ismeretlen'}" 
                                    data-updated-at="${full['updated_at']}" 
                                    title="Audit adatok megtekintése">
                                <i class="bx bx-info-circle text-info"></i>
                            </button>`;
                }
            },
            { // Action column
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, full) {
                    return `<div class="d-inline-block text-nowrap">
                        <button class="btn btn-sm btn-icon delete-record" data-id="${full['id']}">
                            <i class="bx bx-trash text-danger"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        columnDefs: [
            {
                targets: 0,
                render: function(data, type, full, meta) {
                    if (type === 'sort') {
                        return parseInt(full['pseudo_id'], 10);
                    }
                    
                    let pseudo_id = full['pseudo_id'];
                    let year = moment(full['created_at']).format('YYYY');
                    let displayValue = `${pseudo_id}/${year}`;
                    
                    return `<a href="/folyamat/megtekintes/piszkozat/${full['id']}"><span class="badge bg-label-primary m-1" style="font-size: 15px;">${displayValue}</span></a>`;
                },
            },
            {
                targets: 1,
                render: function(data, type, full, meta) {
                    return data || '<span class="text-muted">Névtelen piszkozat</span>';
                }
            }
        ],
        order: [[0, 'desc']],
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        dom: '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [],
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function(row) {
                        var data = row.data();
                        return 'Piszkozat részletei: ' + (data['name'] || 'Névtelen piszkozat');
                    }
                }),
                type: 'column',
                renderer: function(api, rowIdx, columns) {
                    var data = $.map(columns, function(col, i) {
                        return col.title !== '' && col.columnIndex != 5 && col.columnIndex != 6 // Kihagyjuk a Audit és Műveletek oszlopokat
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

    // Event handler for audit modal trigger
    $(document).on('click', '.audit-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const pseudoId = $(this).data('pseudo-id');
        const createdBy = $(this).data('created-by');
        const createdAt = $(this).data('created-at');
        const updatedBy = $(this).data('updated-by');
        const updatedAt = $(this).data('updated-at');
        
        // Update modal title
        $('#auditModalLabel').text(`Folyamat audit adatok - ${pseudoId}`);
        
        // Update modal content
        $('#createdByName').text(createdBy || 'Ismeretlen');
        $('#createdAtDate').text(moment(createdAt).format('YYYY.MM.DD HH:mm:ss'));
        $('#updatedByName').text(updatedBy || 'Ismeretlen');
        $('#updatedAtDate').text(moment(updatedAt).format('YYYY.MM.DD HH:mm:ss'));
        
        // Show modal
        $('#auditModal').modal('show');
    });

    // Filter form control to default size
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    // Handle delete button click
    $('.datatables-drafts tbody').on('click', '.delete-record', function() {
        const draftId = $(this).data('id');
        $('#confirm_delete').data('draft-id', draftId);
        $('#deleteConfirmation').modal('show');
    });

    // Handle delete confirmation
    $('#confirm_delete').on('click', function() {
        const draftId = $(this).data('draft-id');
        
        // Show loading state
        const $button = $(this);
        const originalHtml = $button.html();
        $button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Törlés...');
        $button.prop('disabled', true);
        
        // Call API to delete the draft
        $.ajax({
            url: `/employee-recruitment/draft/${draftId}/delete`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Close modal and refresh table
                $('#deleteConfirmation').modal('hide');
                dataTable.ajax.reload();
                
                // Show success message
                toastr.success('A piszkozat sikeresen törölve.');
            },
            error: function(xhr) {
                // Show error message
                let errorMessage = 'Hiba történt a törlés során.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                // Reset button state
                $button.html(originalHtml);
                $button.prop('disabled', false);
            }
        });
    });
});