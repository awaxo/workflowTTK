import moment from 'moment';
import GLOBALS from '../../js/globals.js';

$(function() {
    // Set locale for sorting
    $.fn.dataTable.ext.order.intl('hu', {
        sensitivity: 'base'
    });

    let currentReportData = null;
    let currentReportType = null;
    let currentYear = null;

    // Generate Report button click handler
    $('.btn-generate-report').on('click', function(e) {
        e.preventDefault();
        
        const year = $('#report_year').val();
        const reportType = $('#report_type').val();

        // Validation
        if (!year) {
            GLOBALS.AJAX_ERROR('Kérjük válasszon évet!');
            return;
        }

        if (!reportType) {
            GLOBALS.AJAX_ERROR('Kérjük válasszon riport típust!');
            return;
        }

        // Hide previous results
        $('#results-card').hide();
        $('.btn-export-report').prop('disabled', true);

        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Betöltés...');

        generateReport(year, reportType);
    });

    // Export Report button click handler
    $('.btn-export-report').on('click', function(e) {
        e.preventDefault();
        
        if (!currentYear || !currentReportType) {
            GLOBALS.AJAX_ERROR('Először generáljon lekérdezést!');
            return;
        }

        exportReport(currentYear, currentReportType);
    });

    /**
     * Generate report via AJAX
     * @param {string} year 
     * @param {string} reportType 
     */
    function generateReport(year, reportType) {
        $.ajax({
            url: '/api/reports/generate',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                year: year,
                report_type: reportType
            },
            success: function(response) {
                if (response.success) {
                    currentReportData = response.data;
                    currentReportType = response.report_type;
                    currentYear = response.year;
                    
                    displayResults(response.data, response.report_type, response.year);
                    
                    // Enable export button
                    $('.btn-export-report').prop('disabled', false);
                    
                    GLOBALS.AJAX_SUCCESS('Lekérdezés sikeres!');
                } else {
                    GLOBALS.AJAX_ERROR('Hiba történt a lekérdezés során!');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                handleAjaxError(jqXHR, textStatus, errorThrown, 'Hiba történt a lekérdezés során!');
            },
            complete: function() {
                // Reset button state
                $('.btn-generate-report').prop('disabled', false).html('Lekérdezés');
            }
        });
    }

    /**
     * Export report to Excel
     * @param {string} year 
     * @param {string} reportType 
     */
    function exportReport(year, reportType) {
        // Show loading state on export button
        const exportBtn = $('.btn-export-report');
        const originalText = exportBtn.html();
        exportBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Excel generálása...');

        // Create a form and submit it to trigger download
        const form = $('<form>', {
            method: 'POST',
            action: '/api/reports/export'
        });

        // Add CSRF token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));

        // Add year
        form.append($('<input>', {
            type: 'hidden',
            name: 'year',
            value: year
        }));

        // Add report type
        form.append($('<input>', {
            type: 'hidden',
            name: 'report_type',
            value: reportType
        }));

        // Append to body and submit
        form.appendTo('body').submit().remove();

        // Reset button after short delay
        setTimeout(function() {
            exportBtn.prop('disabled', false).html(originalText);
            GLOBALS.AJAX_SUCCESS('Excel fájl letöltése megkezdődött!');
        }, 1000);
    }

    /**
     * Display results based on report type
     * @param {Object} data 
     * @param {string} reportType 
     * @param {string} year 
     */
    function displayResults(data, reportType, year) {
        const resultsCard = $('#results-card');
        const resultsTitle = $('#results-title');
        const resultsContent = $('#results-content');

        // Update title
        const reportTypeNames = {
            'job_advertisement_statistics': 'Álláshirdetési statisztika',
            'chemical_workers': 'Vegyi anyaggal dolgozók',
            'carcinogenic_workers': 'Rákkeltő anyaggal dolgozók'
        };
        resultsTitle.text(`${reportTypeNames[reportType]} - ${year}`);

        // Clear previous content and destroy existing DataTables
        resultsContent.find('.dataTables_wrapper').each(function() {
            const table = $(this).find('table').DataTable();
            table.destroy();
        });
        resultsContent.empty();

        // Generate content based on report type
        switch (reportType) {
            case 'job_advertisement_statistics':
                displayJobAdStatistics(data, resultsContent);
                break;
            case 'chemical_workers':
                displayChemicalWorkers(data, resultsContent);
                break;
            case 'carcinogenic_workers':
                displayCarcinogenicWorkers(data, resultsContent);
                break;
        }

        // Show results card
        resultsCard.show();

        // Scroll to results
        $('html, body').animate({
            scrollTop: resultsCard.offset().top - 100
        }, 500);
    }

    /**
     * Display job advertisement statistics
     * @param {Object} data 
     * @param {jQuery} container 
     */
    function displayJobAdStatistics(data, container) {
        const template = $('#job-ad-stats-template').html();
        const content = $(template);

        content.find('.total-completed').text(data.total_completed || 0);
        content.find('.with-job-ad').text(data.with_job_ad || 0);
        content.find('.female-applicants').text(data.female_applicants || 0);
        content.find('.male-applicants').text(data.male_applicants || 0);

        container.append(content);
    }

    /**
     * Display chemical workers with DataTable
     * @param {Array} data 
     * @param {jQuery} container 
     */
    function displayChemicalWorkers(data, container) {
        const template = $('#chemical-workers-template').html();
        const content = $(template);
        const tbody = content.find('tbody');

        if (data.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center">Nincs adat a megadott évhez</td></tr>');
            container.append(content);
        } else {
            // Populate table with data
            data.forEach(function(worker) {
                const exposureLevelText = getExposureLevelText(worker.exposure_level);
                const chemicalsText = worker.chemicals.join(', ') || 'Nincs megadva';
                
                const row = $(`
                    <tr>
                        <td>${escapeHtml(worker.name)}</td>
                        <td>${exposureLevelText}</td>
                        <td>${escapeHtml(chemicalsText)}</td>
                    </tr>
                `);
                tbody.append(row);
            });

            container.append(content);

            // Initialize DataTable
            setTimeout(() => {
                const table = $('#chemical-workers-table').DataTable({
                    language: GLOBALS.DATATABLE_TRANSLATION,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 75, 100],
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal({
                                header: function(row) {
                                    var data = row.data();
                                    return 'Részletek - ' + data[0]; // Name column
                                }
                            }),
                            type: 'column',
                            renderer: function(api, rowIdx, columns) {
                                var data = $.map(columns, function(col, i) {
                                    return col.title !== ''
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
                    order: [[0, 'asc']], // Sort by name
                    columnDefs: [
                        {
                            targets: [1, 2], // Exposure level and chemicals columns
                            orderable: false
                        }
                    ]
                });

                // Filter form control to default size
                setTimeout(() => {
                    $('.dataTables_filter .form-control').removeClass('form-control-sm');
                    $('.dataTables_length .form-select').removeClass('form-select-sm');
                }, 300);
            }, 100);
        }
    }

    /**
     * Display carcinogenic workers with DataTable
     * @param {Array} data 
     * @param {jQuery} container 
     */
    function displayCarcinogenicWorkers(data, container) {
        const template = $('#carcinogenic-workers-template').html();
        const content = $(template);
        const tbody = content.find('tbody');

        if (data.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center">Nincs adat a megadott évhez</td></tr>');
            container.append(content);
        } else {
            // Populate table with data
            data.forEach(function(worker) {
                const exposureLevelText = getExposureLevelText(worker.exposure_level);
                const substancesText = worker.substances || 'Nincs megadva';
                
                const row = $(`
                    <tr>
                        <td>${escapeHtml(worker.name)}</td>
                        <td>${exposureLevelText}</td>
                        <td>${escapeHtml(substancesText)}</td>
                    </tr>
                `);
                tbody.append(row);
            });

            container.append(content);

            // Initialize DataTable
            setTimeout(() => {
                const table = $('#carcinogenic-workers-table').DataTable({
                    language: GLOBALS.DATATABLE_TRANSLATION,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 75, 100],
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal({
                                header: function(row) {
                                    var data = row.data();
                                    return 'Részletek - ' + data[0]; // Name column
                                }
                            }),
                            type: 'column',
                            renderer: function(api, rowIdx, columns) {
                                var data = $.map(columns, function(col, i) {
                                    return col.title !== ''
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
                    order: [[0, 'asc']], // Sort by name
                    columnDefs: [
                        {
                            targets: [1, 2], // Exposure level and substances columns
                            orderable: false
                        }
                    ]
                });

                // Filter form control to default size
                setTimeout(() => {
                    $('.dataTables_filter .form-control').removeClass('form-control-sm');
                    $('.dataTables_length .form-select').removeClass('form-select-sm');
                }, 300);
            }, 100);
        }
    }

    /**
     * Get human readable exposure level text
     * @param {string} level 
     * @returns {string}
     */
    function getExposureLevelText(level) {
        const levelMap = {
            'resz': 'Munkaidő részében',
            'egesz': 'Munkaidő egészében',
            'nincs': 'Nincs kitettség'
        };
        return levelMap[level] || level;
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text 
     * @returns {string}
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Handle AJAX errors consistently
     * @param {Object} jqXHR 
     * @param {string} textStatus 
     * @param {string} errorThrown 
     * @param {string} defaultMessage 
     */
    function handleAjaxError(jqXHR, textStatus, errorThrown, defaultMessage) {
        if (jqXHR.status === 401 || jqXHR.status === 419) {
            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
            window.location.href = '/login';
            return;
        }

        GLOBALS.AJAX_ERROR(defaultMessage, jqXHR, textStatus, errorThrown);
    }
});