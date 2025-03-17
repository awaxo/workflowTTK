/**
 * Simple section validation indicator for multi-step forms
 * Directly integrates with existing validation and stepper
 */
$(function() {
    // Add CSS styles
    const style = `
        .step-error {
            position: absolute;
            top: 50%;
            right: -10px;
            width: 20px;
            height: 20px;
            background-color: #dc3545;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            z-index: 100;
            transform: translateY(-50%);
        }
        
        .step[data-has-errors="true"] .bs-stepper-circle {
            border: 2px solid #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
    `;
    
    $('<style>').text(style).appendTo('head');
    
    // Define field to section mapping
    const fieldMapping = {
        // Section 1 - Alapadatok
        'data-section-1': [
            'name', 'birth_date', 'social_security_number', 'address', 
            'workgroup_id_1', 'applicants_female_count', 'applicants_male_count'
        ],
        
        // Section 2 - Jogviszony
        'data-section-2': [
            'position_id', 'job_description_file', 'employment_type',
            'task', 'employment_start_date', 'employment_end_date'
        ],
        
        // Section 3 - Munkaidő
        'data-section-3': [
            'weekly_working_hours', 
            'work_start_monday', 'work_end_monday',
            'work_start_tuesday', 'work_end_tuesday',
            'work_start_wednesday', 'work_end_wednesday',
            'work_start_thursday', 'work_end_thursday',
            'work_start_friday', 'work_end_friday'
        ],
        
        // Section 4 - Bérelemek
        'data-section-4': [
            'base_salary_cost_center_1', 'base_salary_monthly_gross_1',
            'base_salary_cost_center_2', 'base_salary_monthly_gross_2',
            'base_salary_cost_center_3', 'base_salary_monthly_gross_3',
            'health_allowance_cost_center_4', 'health_allowance_monthly_gross_4',
            'management_allowance_cost_center_5', 'management_allowance_monthly_gross_5', 'management_allowance_end_date',
            'extra_pay_1_cost_center_6', 'extra_pay_1_monthly_gross_6', 'extra_pay_1_end_date',
            'extra_pay_2_cost_center_7', 'extra_pay_2_monthly_gross_7', 'extra_pay_2_end_date'
        ],
        
        // Section 5 - Egyéb adatok
        'data-section-5': [
            'email', 'entry_permissions', 'employee_room', 'license_plate', 
            'phone_extension', 'required_tools', 'available_tools'
        ],
        
        // Section 6 - Dokumentumok
        'data-section-6': [
            'personal_data_sheet_file', 'certificates_file', 
            'student_status_verification_file', 'commute_support_form_file'
        ]
    };
    
    // Track form errors
    let formErrors = {};
    
    // Initialize error indicators for each step
    $('.step').each(function(index) {
        $(this).attr('data-has-errors', 'false');
        
        // Create error icon
        const errorIcon = $('<div class="step-error" style="display:none;"><i class="fas fa-exclamation"></i></div>');
        $(this).css('position', 'relative').append(errorIcon);
    });
    
    // Main function to update error status
    function updateSectionErrorStatus() {
        // Reset sections
        $('.step').attr('data-has-errors', 'false').find('.step-error').hide();
        
        // Check which sections have errors
        Object.keys(fieldMapping).forEach(sectionId => {
            const fields = fieldMapping[sectionId];
            let hasError = false;
            
            fields.forEach(fieldId => {
                if (formErrors[fieldId]) {
                    hasError = true;
                }
                
                // Check for inventory number fields
                if (fieldId === 'available_tools') {
                    const availableTools = $('#available_tools').val() || [];
                    availableTools.forEach(tool => {
                        const inventoryFieldId = `inventory_numbers_of_available_tools_${tool}`;
                        if (formErrors[inventoryFieldId]) {
                            hasError = true;
                        }
                    });
                }
            });
            
            // Update section status
            if (hasError) {
                $(`.step[data-target="#${sectionId}"]`)
                    .attr('data-has-errors', 'true')
                    .find('.step-error')
                    .show();
            }
        });
    }
    
    // Capture validation events
    $('.btn-submit').on('click', function() {
        // Delay check to allow validation to happen
        setTimeout(captureValidationErrors, 100);
    });
    
    $('.btn-next, .btn-prev').on('click', function() {
        // Delay check to allow validation to happen
        setTimeout(captureValidationErrors, 300);
    });
    
    function captureValidationErrors() {
        // Clear previous errors
        formErrors = {};
        
        // Capture all invalid fields
        $('.is-invalid').each(function() {
            const fieldId = $(this).attr('id');
            if (fieldId) {
                formErrors[fieldId] = true;
            }
        });
        
        // Also check for any elements with .fv-plugins-message-container children with non-empty text
        $('[id]').each(function() {
            const fieldId = $(this).attr('id');
            const $messageContainer = $(this).siblings('.fv-plugins-message-container');
            
            if (fieldId && $messageContainer.length && $messageContainer.text().trim()) {
                formErrors[fieldId] = true;
            }
        });
        
        // Update section statuses
        updateSectionErrorStatus();
    }
    
    // Do a check initially after a delay
    setTimeout(captureValidationErrors, 1000);
    
    // Check when DOM changes (for dynamic validation messages)
    // Use MutationObserver to watch for validation changes
    const observer = new MutationObserver(function(mutations) {
        let shouldUpdate = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.target.classList && 
                (mutation.target.classList.contains('is-invalid') || 
                 mutation.target.classList.contains('fv-plugins-message-container'))) {
                shouldUpdate = true;
            }
        });
        
        if (shouldUpdate) {
            captureValidationErrors();
        }
    });
    
    // Start observing the document
    observer.observe(document.querySelector('#new-recruitment'), {
        attributes: true,
        childList: true,
        characterData: true,
        subtree: true
    });
});