$(function() {
    const $healthAllowanceDiv = $('#health_allowance');
    const medicalDataRaw = $healthAllowanceDiv.attr('data-medical');
    
    let medicalData = null;
    try {
        if (medicalDataRaw && medicalDataRaw.trim() !== '') {
            medicalData = JSON.parse(medicalDataRaw);
        }
    } catch (e) {
        console.log('Error parsing medical data:', e);
    }
    
    $('.select2').select2({
        width: '100%',
        placeholder: 'Válasszon...',
        allowClear: true,
        language: 'hu'
    });
    
    if (medicalData) {
        // Form elemek feltöltése
        $.each(medicalData, function(key, value) {
            // Radio input kezelése
            const $radioInput = $('input[name="' + key + '"][value="' + value + '"]');
            if ($radioInput.length) {
                $radioInput.prop('checked', true);
            }
            
            // Select mező kezelése
            const $selectElement = $('#' + key);
            if ($selectElement.length && $selectElement.is('select')) {
                if ($selectElement.prop('multiple')) {
                    // Select2 mező kezelése
                    if (Array.isArray(value)) {
                        $selectElement.val(value).trigger('change');
                        // Trigger special handling for chemical hazards
                        if (key === 'chemical_hazards_exposure') {
                            handleChemicalHazardsVisibility(value);
                        }
                    }
                } else {
                    $selectElement.val(value).trigger('change');
                }
            }
            
            // Textarea kezelése
            const $textareaElement = $('#' + key);
            if ($textareaElement.length && $textareaElement.is('textarea')) {
                $textareaElement.val(value);
            }
        });
    }
    
    // MINDIG futtassuk le a láthatósági ellenőrzést
    setTimeout(function() {
        // Ellenőrizzük minden lehetséges főmezőt
        const mainFields = ['manual_handling', 'increased_accident_risk', 'stressful_workplace_climate', 'chemicals_exposure', 'dust_exposure', 'others'];
        
        mainFields.forEach(function(fieldName) {
            const $checkedRadio = $('input[name="' + fieldName + '"]:checked');
            
            if ($checkedRadio.length) {
                const value = $checkedRadio.val();
                
                if (value !== 'nincs') {
                    // DIRECT class manipulation - bypass all functions
                    const $elements = $('.' + fieldName + '.d-none');
                    
                    $elements.each(function() {
                        $(this).removeClass('d-none');
                    });
                    
                    // Speciális textarea mezők az egyszerű almezőkhöz
                    if (fieldName === 'dust_exposure') {
                        $('#dust_exposure_description').removeClass('d-none');
                    }
                    
                    if (fieldName === 'others') {
                        $('#planned_other_health_risk_factors').removeClass('d-none');
                    }
                }
            }
        });
        
        // Almezők is
        const subFields = ['carcinogenic_substances_exposure', 'other_risks'];
        
        subFields.forEach(function(fieldName) {
            const $checkedRadio = $('input[name="' + fieldName + '"]:checked');
            if ($checkedRadio.length) {
                const value = $checkedRadio.val();
                
                if (value !== 'nincs') {
                    const $elements = $('.' + fieldName + '.d-none');
                    
                    $elements.each(function() {
                        $(this).removeClass('d-none');
                    });
                }
            }
        });
        
        // 1. other_chemicals_description - chemicals_exposure != 'nincs' ÉS chemical_hazards_exposure tartalmazza az 'egyeb'-et
        const $chemicalsExposure = $('input[name="chemicals_exposure"]:checked');
        if ($chemicalsExposure.length && $chemicalsExposure.val() !== 'nincs') {
            const $chemicalHazardsSelect = $('#chemical_hazards_exposure');
            const selectedHazards = $chemicalHazardsSelect.val();
            
            if (selectedHazards && selectedHazards.includes && selectedHazards.includes('egyeb')) {
                $('.other_chemicals_details.d-none').removeClass('d-none');
                $('#other_chemicals_description').removeClass('d-none');
            }
        }
        
        // 2. planned_carcinogenic_substances_list - chemicals_exposure != 'nincs' ÉS carcinogenic_substances_exposure != 'nincs'
        if ($chemicalsExposure.length && $chemicalsExposure.val() !== 'nincs') {
            const $carcinogenicExposure = $('input[name="carcinogenic_substances_exposure"]:checked');
            if ($carcinogenicExposure.length && $carcinogenicExposure.val() !== 'nincs') {
                $('.carcinogenic_details.d-none').removeClass('d-none');
                $('#planned_carcinogenic_substances_list').removeClass('d-none');
            }
        }
    }, 100);

    // Event handlerek a dinamikus megjelenítéshez - Radio inputs
    $('input[type="radio"]').on('change', function() {
        const fieldName = $(this).attr('name');
        
        if (isMainField(fieldName)) {
            handleMainFieldVisibility(fieldName);
            
            // Ha "nincs" érték van kiválasztva, reseteljük az almezőket
            if ($(this).val() === 'nincs') {
                resetSubfields(fieldName);
            }
        }
        
        // Handle subfield radio inputs (like carcinogenic_substances_exposure)
        if (isSubField(fieldName)) {
            handleSubFieldVisibility(fieldName);
            
            if ($(this).val() === 'nincs') {
                resetSubfields(fieldName);
            }
        }
    });

    // Event handlerek a select mezőkhöz (select2 mezők)
    $('select').on('change', function() {
        const fieldName = $(this).attr('name') || $(this).attr('id');
        
        // Handle chemical_hazards_exposure special case
        if (fieldName === 'chemical_hazards_exposure') {
            handleChemicalHazardsVisibility($(this).val());
        }
    });

    // Initialize Select2 change events after setup
    $('.select2').on('select2:select select2:unselect', function() {
        const fieldName = $(this).attr('name') || $(this).attr('id');
        if (fieldName === 'chemical_hazards_exposure') {
            handleChemicalHazardsVisibility($(this).val());
        }
    });
});

/**
 * Handle visibility of main fields and their direct subfields
 */
function handleMainFieldVisibility(fieldName) {
    const $checked = $('input[name="'+fieldName+'"]:checked');
    if (!$checked.length) {
        console.log('No checked radio found for:', fieldName);
        return;
    }
    
    const val = $checked.val();
    
    // Special handling for chemicals_exposure
    if (fieldName === 'chemicals_exposure') {
        handleChemicalsExposureVisibility(val);
        return;
    }
    
    // General handling for other main fields
    const $wrapper = $('.'+fieldName);
    
    if (val && val !== 'nincs') {
        $wrapper.removeClass('d-none');
        $wrapper.find('textarea').removeClass('d-none');
    } else {
        $wrapper.addClass('d-none');
        $wrapper.find('textarea').addClass('d-none');
    }
}

/**
 * Special handling for chemicals_exposure field hierarchy
 */
function handleChemicalsExposureVisibility(value) {
    const $chemicalsWrapper = $('.chemicals_exposure');
    
    if (value && value !== 'nincs') {
        // Show the chemicals_exposure wrapper
        $chemicalsWrapper.removeClass('d-none');
        
        // Show only the chemical_hazards_exposure select (first level)
        const $chemicalHazardsRow = $chemicalsWrapper.find('.row').first();
        $chemicalHazardsRow.removeClass('d-none');
        
        // Keep other subfields hidden initially
        hideChemicalSubfields();
        
    } else {
        // Hide everything and reset
        $chemicalsWrapper.addClass('d-none');
        resetChemicalFields();
    }
}

/**
 * Handle visibility of chemical hazards and related textareas
 */
function handleChemicalHazardsVisibility(selectedValues) {
    // Handle "Egyéb vegyi anyagok megnevezése" textarea
    const $otherChemicalsTextarea = $('.other_chemicals_details');
    
    if (selectedValues && selectedValues.length > 0) {
        // If "egyeb" is selected, show the textarea
        if (selectedValues.includes('egyeb')) {
            $otherChemicalsTextarea.removeClass('d-none');
        } else {
            $otherChemicalsTextarea.addClass('d-none');
        }
    } else {
        $otherChemicalsTextarea.addClass('d-none');
    }
}

/**
 * Handle visibility of sub-subfields (like carcinogenic details)
 */
function handleSubFieldVisibility(fieldName) {
    const $checkedRadio = $('input[name="' + fieldName + '"]:checked');
    if (!$checkedRadio.length) return;
    
    const value = $checkedRadio.val();
    
    // Special handling for carcinogenic_substances_exposure
    if (fieldName === 'carcinogenic_substances_exposure') {
        const $carcinogenicDetails = $('.carcinogenic_details');
        
        if (value && value !== 'nincs') {
            $carcinogenicDetails.removeClass('d-none');
        } else {
            $carcinogenicDetails.addClass('d-none');
            // Reset the textarea
            $('#planned_carcinogenic_substances_list').val('');
        }
        return;
    }
    
    // General handling for other subfields
    const $elements = $('.' + fieldName);
    if (value && value !== 'nincs') {
        $elements.removeClass('d-none');
    } else {
        $elements.addClass('d-none');
    }
}

/**
 * Hide all chemical subfields initially
 */
function hideChemicalSubfields() {
    $('.other_chemicals_details').addClass('d-none');
    $('.carcinogenic_details').addClass('d-none');
}

/**
 * Reset all chemical-related fields
 */
function resetChemicalFields() {
    // Reset chemical hazards select
    const $chemicalHazardsSelect = $('#chemical_hazards_exposure');
    if ($chemicalHazardsSelect.hasClass('select2')) {
        $chemicalHazardsSelect.val(null).trigger('change');
    }
    
    // Reset carcinogenic substances radio
    $('input[name="carcinogenic_substances_exposure"]').prop('checked', false);
    
    // Reset textareas
    $('#other_chemicals_description').val('');
    $('#planned_carcinogenic_substances_list').val('');
    
    // Hide all subfields
    hideChemicalSubfields();
}

/**
 * Check if a field is a sub-field that has its own textarea
 */
function isSubField(fieldName) {
    return [
        'carcinogenic_substances_exposure',
        'other_risks'
    ].includes(fieldName);
}

/**
 * Reset all subfields within a given field group
 */
function resetSubfields(fieldName) {
    // Special handling for chemicals_exposure
    if (fieldName === 'chemicals_exposure') {
        resetChemicalFields();
        return;
    }
    
    // General reset for other fields
    const $elements = $('.' + fieldName);
    
    // Reset radio inputs in subfields
    $elements.find('input[type="radio"]').prop('checked', false);
    
    // Reset select2 fields
    $elements.find('select').each(function() {
        const $select = $(this);
        if ($select.hasClass('select2')) {
            $select.val(null).trigger('change');
        } else {
            $select.val('').trigger('change');
        }
    });
    
    // Reset textareas
    $elements.find('textarea').val('');
    
    // Hide nested subfields
    $elements.find('.d-none').addClass('d-none');
}

/**
 * Check if a field is a main field that has subfields
 */
function isMainField(fieldName) {
    return [
        'manual_handling',
        'increased_accident_risk', 
        'stressful_workplace_climate',
        'chemicals_exposure',
        'dust_exposure',
        'others'
    ].includes(fieldName);
}