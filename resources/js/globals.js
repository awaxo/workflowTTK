class GLOBALS {
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

    static AJAX_ERROR = function(message, jqXHR, textStatus, errorThrown) {
        $('<div class="alert alert-danger alert-dismissible" role="alert">' +
            '<span>' + message + '</span>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>').insertBefore('.nav-align-top');
                
        console.log(textStatus, errorThrown);
    };

    static AJAX_SUCCESS = function(message) {
        $('<div class="alert alert-success alert-dismissible" role="alert">' +
            '<span>' + message + '</span>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>').insertBefore('.nav-align-top');
    };
}

export default GLOBALS;