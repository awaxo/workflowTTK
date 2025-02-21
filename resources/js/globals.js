class GLOBALS {
    static cleaveInstances = {};

    static DATATABLE_TRANSLATION = {
        "sEmptyTable":     "Nincs rendelkezésre álló adat",
        "sInfo":           "Találatok: _START_ - _END_ Összesen: _TOTAL_",
        "sInfoEmpty":      "Nulla találat",
        "sInfoFiltered":   "(_MAX_ összes rekord közül szűrve)",
        "sInfoPostFix":    "",
        "sInfoThousands":  " ",
        "sLengthMenu":     "_MENU_ találat oldalanként",
        "sLoadingRecords": "Betöltés...",
        "sProcessing":     "Feldolgozás...",
        "sSearch":         "Keresés:",
        "sZeroRecords":    "Nincs a keresésnek megfelelő találat",
        "oPaginate": {
            "sFirst":    "Első",
            "sPrevious": "Előző",
            "sNext":     "Következő",
            "sLast":     "Utolsó"
        },
        "oAria": {
            "sSortAscending":  ": aktiválja a növekvő rendezéshez",
            "sSortDescending": ": aktiválja a csökkenő rendezéshez"
        }
    };

    static AJAX_ERROR = function(message, jqXHR = null, textStatus = null, errorThrown = null, insertBefore = '.nav-align-top') {
        if ($('.alert-danger:visible').length === 0) {
            $('<div class="alert alert-danger alert-dismissible" role="alert">' +
                '<span>' + message + '</span>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>').insertBefore(insertBefore);
            
            console.log(textStatus, errorThrown);
        }
    };

    static AJAX_SUCCESS = function(message) {
        if ($('.alert-success:visible').length === 0) {
            $('<div class="alert alert-success alert-dismissible" role="alert">' +
                '<span>' + message + '</span>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>').insertBefore('.nav-align-top');
        }
    };

    /**
     * Removes thousand separators and other non-numeric characters from the number
     * @param {string|number} value - The value to clean
     * @returns {string|null} - The cleaned number or null
     */
    static cleanNumber = function(value) {
        if (value === null || value === undefined || value === '') {
            return null;
        }
        return value.toString().replace(/[^\d.-]/g, '');
    };

    /**
     * Format number inputs using Cleave.js
     * @param {string} selector - jQuery selector for the input fields to format
     */
    static initNumberInputs = function(selector = '.numeral-mask') {
        $(selector).toArray().forEach(function(field) {
            const cleave = new Cleave(field, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                delimiter: ' ',
            });

            // Store instance only if field has an ID
            if (field.id) {
                GLOBALS.cleaveInstances[field.id] = cleave;
            }
        });

        return GLOBALS.cleaveInstances;
    };

    /**
     * Get a specific Cleave instance by field ID
     * @param {string} fieldId - The ID of the input field
     * @returns {Cleave|null} - The Cleave instance or null if not found
     */
    static getCleaveInstance = function(fieldId) {
        return GLOBALS.cleaveInstances[fieldId] || null;
    };
}

export default GLOBALS;