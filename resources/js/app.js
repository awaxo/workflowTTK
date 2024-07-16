/*
  Add custom scripts here
*/
import.meta.glob([
    '../assets/img/**',
    // '../assets/json/**',
    '../assets/vendor/fonts/**'
]);

$(function() {
    // This sorting type will replace DataTables' default string sort with one that will use a locale aware collator
    $.fn.dataTable.ext.order.intl = function ( locales, options ) {
        if ( window.Intl ) {
            var collator = new Intl.Collator( locales, options );
            var types = $.fn.dataTable.ext.type;
    
            delete types.order['string-pre'];
            types.order['string-asc'] = collator.compare;
            types.order['string-desc'] = function ( a, b ) {
                return collator.compare( a, b ) * -1;
            };
        }
    };

    // search tooltip
    $(document).on('mouseenter', '.dataTables_filter input', function() {
        if (!$(this).data('bs.tooltip')) {
            $(this).attr({
                'data-bs-toggle': 'tooltip',
                'data-bs-offset': '0,4',
                'data-bs-html': 'true',
                'title': 'Pontosan egyező találat megjelenítéséhez a keresőszöveget idézőjelek közé kell írni'
            }).tooltip();

            // remove the title attribute to prevent the default tooltip from showing
            $(this).attr('title', '');

            $(this).tooltip('show');
        }
    });

    // sidebar menu event handling
    const menuElement = document.querySelector('#sideMenu');
    if (menuElement) {
        const menu = new Menu(menuElement, {
            onOpen: (item, toggleLink, menu) => {
                window.location.href = menu.href;
            },
            onClose: () => {},
        });
    } else {
        console.error('Menu element not found in the DOM.');
    }
});