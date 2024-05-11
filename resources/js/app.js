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
});