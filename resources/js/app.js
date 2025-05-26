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

    // Initialize sticky scrollbar AFTER DataTable is fully loaded
    setupStickyScrollbar();
       
    // Reinitialize after AJAX content changes
    $(document).ajaxComplete(function() {
        setTimeout(setupStickyScrollbar, 200);
    });
});

// HIGH PERFORMANCE STICKY SCROLLBAR - MOVE THIS OUTSIDE THE $(function) BLOCK
function setupStickyScrollbar() {
    const horizontalScrollEl = document.querySelector('.horizontal-scroll');
    
    if (!horizontalScrollEl) {
        console.log('No .horizontal-scroll element found');
        return;
    }
    
    // Create sticky scrollbar if not exists
    let stickyScrollbar = document.getElementById('stickyScrollbar');
    if (!stickyScrollbar) {
        stickyScrollbar = document.createElement('div');
        stickyScrollbar.id = 'stickyScrollbar';
        stickyScrollbar.className = 'fixed-bottom-scrollbar';
        stickyScrollbar.innerHTML = '<div class="fixed-bottom-scrollbar-content" id="stickyScrollbarContent"></div>';
        document.body.appendChild(stickyScrollbar);
    }
    
    const stickyContent = document.getElementById('stickyScrollbarContent');
    const table = document.querySelector('.horizontal-scroll table');
    const sidebar = document.querySelector('.layout-menu');
    
    if (!table) {
        console.log('No .datatables-external-access table found');
        return;
    }
    
    // Cache DOM measurements to avoid repeated reflows
    let cachedMeasurements = {
        tableWidth: 0,
        containerWidth: 0,
        sidebarWidth: 0,
        needsUpdate: true
    };
    
    function updateMeasurements() {
        cachedMeasurements.tableWidth = table.scrollWidth;
        cachedMeasurements.containerWidth = horizontalScrollEl.clientWidth;
        cachedMeasurements.sidebarWidth = sidebar ? sidebar.offsetWidth : 250;
        cachedMeasurements.needsUpdate = false;
    }
    
    function updateScrollbar() {
        if (cachedMeasurements.needsUpdate) {
            updateMeasurements();
        }
        
        const { tableWidth, containerWidth, sidebarWidth } = cachedMeasurements;
        
        // Set dimensions using direct style manipulation (fastest)
        stickyContent.style.width = tableWidth + 'px';
        stickyScrollbar.style.left = sidebarWidth + 'px';
        stickyScrollbar.style.width = containerWidth + 'px';
        
        // Show/hide with direct style manipulation
        if (tableWidth > containerWidth) {
            stickyScrollbar.style.display = 'block';
            horizontalScrollEl.classList.add('needs-scroll');
        } else {
            stickyScrollbar.style.display = 'none';
            horizontalScrollEl.classList.remove('needs-scroll');
        }
    }
    
    // Ultra-fast scroll synchronization
    let isHorizontalScrolling = false;
    let isStickyScrolling = false;
    let animationFrame = null;
    
    // Optimized scroll handler for horizontal scroll
    function handleHorizontalScroll() {
        if (isStickyScrolling) return;
        
        isHorizontalScrolling = true;
        
        // Cancel previous frame if still pending
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        
        animationFrame = requestAnimationFrame(() => {
            stickyScrollbar.scrollLeft = horizontalScrollEl.scrollLeft;
            isHorizontalScrolling = false;
            animationFrame = null;
        });
    }
    
    // Optimized scroll handler for sticky scrollbar
    function handleStickyScroll() {
        if (isHorizontalScrolling) return;
        
        isStickyScrolling = true;
        
        // Cancel previous frame if still pending
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        
        animationFrame = requestAnimationFrame(() => {
            horizontalScrollEl.scrollLeft = stickyScrollbar.scrollLeft;
            isStickyScrolling = false;
            animationFrame = null;
        });
    }
    
    // Remove existing listeners to avoid duplicates
    horizontalScrollEl.removeEventListener('scroll', handleHorizontalScroll);
    stickyScrollbar.removeEventListener('scroll', handleStickyScroll);
    
    // Add optimized event listeners with passive option for better performance
    horizontalScrollEl.addEventListener('scroll', handleHorizontalScroll, { 
        passive: true,
        capture: false 
    });
    
    stickyScrollbar.addEventListener('scroll', handleStickyScroll, { 
        passive: true,
        capture: false 
    });
    
    // Debounced resize handler
    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            cachedMeasurements.needsUpdate = true;
            updateScrollbar();
        }, 100);
    }
    
    window.removeEventListener('resize', handleResize);
    window.addEventListener('resize', handleResize, { passive: true });
    
    // Initial setup
    updateScrollbar();
    
    // Store references for cleanup
    window.nativeStickyScrollbarRefs = {
        horizontalScrollEl,
        stickyScrollbar,
        handleHorizontalScroll,
        handleStickyScroll,
        handleResize
    };
}

// Cleanup function
function cleanupStickyScrollbar() {
    if (window.nativeStickyScrollbarRefs) {
        const refs = window.nativeStickyScrollbarRefs;
        
        refs.horizontalScrollEl?.removeEventListener('scroll', refs.handleHorizontalScroll);
        refs.stickyScrollbar?.removeEventListener('scroll', refs.handleStickyScroll);
        window.removeEventListener('resize', refs.handleResize);
        
        refs.stickyScrollbar?.remove();
        
        if (window.nativeStickyObserver) {
            window.nativeStickyObserver.disconnect();
        }
        
        clearTimeout(window.mutationTimeout);
        
        delete window.nativeStickyScrollbarRefs;
        delete window.nativeStickyObserver;
        delete window.mutationTimeout;
    }
}

$(window).on('beforeunload', function() {
    cleanupStickyScrollbar();
});