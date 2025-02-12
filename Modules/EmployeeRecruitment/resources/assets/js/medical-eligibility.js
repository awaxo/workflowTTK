$(function() {
    const $healthAllowanceDiv = $('#health_allowance');
    const medicalData = $healthAllowanceDiv.data('medical') || null;

    // Select2 inicializálása
    $('.select2').select2({
        width: '100%',
        placeholder: 'Válasszon...',
        allowClear: true,
        language: 'hu'
    });
    
    if (medicalData) {
        // Form elemek feltöltése
        $.each(medicalData, function(key, value) {
            const $element = $('#' + key);
            
            if ($element.length) {
                if ($element.is('select')) {
                    if ($element.prop('multiple')) {
                        // Select2 mező kezelése
                        if (Array.isArray(value)) {
                            $element.val(value).trigger('change');
                        }
                    } else {
                        $element.val(value).trigger('change');
                    }
                } else if ($element.is('textarea')) {
                    $element.val(value);
                }
            }
        });
        
        // Függőségek láthatóságának beállítása
        const dependentFields = [
            'manual_handling',
            'increased_accident_risk',
            'stressful_workplace_climate',
            'chemicals_exposure',
            'dust_exposure',
            'carcinogenic_substances_exposure',
            'others'
        ];

        $.each(dependentFields, function(i, prefix) {
            handleVisibility(prefix);
        });
    }

    // Event handlerek a dinamikus megjelenítéshez
    $('select').on('change', function() {
        const prefix = $(this).attr('id');
        if (isMainSelect(prefix)) {
            handleVisibility(prefix);
            
            // Ha "nincs" érték van kiválasztva, reseteljük az almezőket
            if ($(this).val() === 'nincs' || !$(this).val()) {
                resetSubfields(prefix);
            }
        }
    });
});

function handleVisibility(prefix) {
    const $mainSelect = $('#' + prefix);
    if (!$mainSelect.length) return;
    
    const value = $mainSelect.val();
    const $elements = $('.' + prefix);
    
    if (value && value !== 'nincs') {
        $elements.removeClass('d-none');
        
        // Ha van chemical_hazards_exposure, akkor annak almezőit is mutatjuk
        if (prefix === 'chemicals_exposure' && value !== 'nincs') {
            $('.chemical_hazards_exposure').removeClass('d-none');
        }
    } else {
        $elements.addClass('d-none');
    }
}

function resetSubfields(prefix) {
    const $elements = $('.' + prefix);
    $elements.find('select').each(function() {
        const $select = $(this);
        if ($select.hasClass('select2')) {
            $select.val(null).trigger('change');
        } else {
            $select.val('').trigger('change');
        }
    });
    $elements.find('textarea').val('');
}

function isMainSelect(prefix) {
    return [
        'manual_handling',
        'increased_accident_risk',
        'stressful_workplace_climate',
        'chemicals_exposure',
        'dust_exposure',
        'carcinogenic_substances_exposure',
        'others'
    ].includes(prefix);
}