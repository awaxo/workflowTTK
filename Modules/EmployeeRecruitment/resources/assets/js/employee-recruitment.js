import moment from 'moment';
import DropzoneManager from '/resources/js/dropzone-manager';
import GLOBALS from '../../../../../resources/js/globals.js';
import { min } from 'lodash';

var cleaveInstances = {};
var fv;
var employerContributionRate = 13;

$(function () {
    cleaveInstances = GLOBALS.initNumberInputs();

    // set datepicker date fields
    $("#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: new Date(),
        endDate: '+4Y',
        language: 'hu',
        weekStart: 1,
        todayBtn: true
    });
    $("#birth_date").datepicker({
        language: 'hu',
        weekStart: 1,
        todayBtn: true
    });

    // Format the social security number input
    var socialSecurityNumberField = document.getElementById('social_security_number');
    if (socialSecurityNumberField) {
        var cleaveSSN = new Cleave(socialSecurityNumberField, {
            numericOnly: true,
            blocks: [3, 3, 3],
            delimiters: [' ', ' '],
        });

        cleaveInstances[socialSecurityNumberField.id] = cleaveSSN;
    }

    $('#job_ad_exists').on('change', function() {
        toggleApplicantCountInputs($(this).is(':checked'));
    });

    $('#employment_type').on('change', function() {
        toggleTaskInput($(this).val());
    });

    $('#weekly_working_hours').on('change', function() {
        setWorkingHoursWeekdays();
        $('#health_allowance_monthly_gross_4').val('');
    });

    $('#entry_permissions').on('change', function() {
        let selectedOptions = $(this).val() || [];
        let filteredOptions = selectedOptions.filter(option => option !== 'auto' && option !== 'kerekpar');

        $('#employee_room').empty();
        $('#employee_room').append(new Option('', ''));
        filteredOptions.forEach(function(option) {
            $('#employee_room').append(new Option(option, option)).trigger('change');
        });

        // set license plate input based on selected entry permission
        if (selectedOptions.includes('auto')) {
            $('#license_plate').prop('disabled', false);
        } else {
            $('#license_plate').val('').prop('disabled', true);
        }
    });

    // add or remove available_tools based on selected required_tools
    $('#required_tools').on('change', function () {
        updateAvailableTools();
    });

    // file uploads
    DropzoneManager.init('job_description');
    DropzoneManager.init('personal_data_sheet');
    DropzoneManager.init('student_status_verification');
    DropzoneManager.init('certificates');
    DropzoneManager.init('commute_support_form');

    // Initially hide the commute support file upload
    $('.commute-support-form').hide();

    // Clear hidden file inputs on page load
    $('input[type="hidden"]').each(function() {
        if ($(this).attr('name').endsWith('_file')) {
            $(this).val('');
        }
    });

    $('#requires_commute_support').on('change', function() {
        if($(this).is(':checked')) {
            $('.commute-support-form').show();
        } else {
            $('.commute-support-form').hide();
        }
    });

    /**
     * Initialize form for New or Edit
     */
    if ($('#recruitment_id').val() === '') {    // If creating a new recruitment
        $('#job_ad_exists').prop('checked', true);
        
        // set datepicker date fields start
        $("#citizenship").on('change', function() {
            $("#employment_start_date, #employment_end_date").val('');
        
            // Ellenőrizzük, hogy a kiválasztott munkakör egyetemi hallgató-e
            let selectedPositionName = $('#position_id option:selected').text();
            let startDate;
            
            if (selectedPositionName === 'egyetemi hallgató') {
                // Egyetemi hallgató esetén mindig +3 hét
                startDate = '+21D';
            } else {
                // Más munkakörök esetén az állampolgárság szerint
                startDate = $(this).val() == 'Harmadik országbeli' ? '+60D' : '+21D';
            }
            
            $("#employment_start_date").datepicker('destroy').datepicker({
                format: "yyyy.mm.dd",
                startDate: isTitkar9Role ? null : startDate,
                language: 'hu',
                weekStart: 1,
            });
        });
        
        var startDate = $("#citizenship").val() == 'Harmadik országbeli' ? '+60D' : '+21D';
        $("#employment_start_date").datepicker({
            format: "yyyy.mm.dd",
            startDate: isTitkar9Role ? new Date() : startDate,
            endDate: '+30Y',
            language: 'hu',
            weekStart: 1,
            autoclose: true,
        });
        $("#employment_end_date").datepicker({
            format: "yyyy.mm.dd",
            startDate: isTitkar9Role ? null : '+200D',
            endDate: '+30Y',
            language: 'hu',
            weekStart: 1,
            autoclose: true,
        });
        
        $("#citizenship").on('change', function() {
            updateStartDateSettings();
        });
        
        $('#position_id').on('change', function() {
            updateStartDateSettings();
        });
        
        $("#employment_start_date").on('change', function() {
            updateEndDateBasedOnPosition();
        });
        // set datepicker date fields end
    } else {    // If editing an existing recruitment
        $('#workgroup_id_1').val($('#workgroup_id_1_value').val()).trigger('change');
        $('#workgroup_id_2').val($('#workgroup_id_2_value').val()).trigger('change');

        let positionType = $('#position_id option[value="' + $('#position_id_value').val() + '"]').data('type');
        $('#position_type').val(positionType).trigger('change');

        $('.upload-file-delete').on('click', function() {
            $('#deleteConfirmation').modal('show');
            $('#confirm_delete').data('file-id', $(this).data('file'));
        });

        $('#confirm_delete').on('click', function () {
            $('.' + $(this).data('file-id')).parent().hide();
            $('#' + $(this).data('file-id')).val('');
            $('#deleteConfirmation').modal('hide');
        });

        $('.btn-delete').on('click', function() {
            $('#deleteWorkflowConfirmation').modal('show');
        });

        $('.btn-delete-draft').on('click', function(event) {
            $('#deleteWorkflowDraftConfirmation').modal('show');
        });

        setTimeout(function() {
            $('#position_id').val($('#position_id_value').val()).trigger('change');

            $('#base_salary_cost_center_1').val($('#base_salary_cost_center_1_value').val()).trigger('change');
            $('#base_salary_cost_center_2').val($('#base_salary_cost_center_2_value').val()).trigger('change');
            $('#base_salary_cost_center_3').val($('#base_salary_cost_center_3_value').val()).trigger('change');
            $('#health_allowance_cost_center_4').val($('#health_allowance_cost_center_4_value').val()).trigger('change');
            $('#management_allowance_cost_center_5').val($('#management_allowance_cost_center_5_value').val()).trigger('change');
            $('#extra_pay_1_cost_center_6').val($('#extra_pay_1_cost_center_6_value').val()).trigger('change');
            $('#extra_pay_2_cost_center_7').val($('#extra_pay_2_cost_center_7_value').val()).trigger('change');

            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            days.forEach(day => {
                $(`#work_start_${day}`).val($(`#work_start_${day}`).attr('value').slice(0, -3)).trigger('change');
                $(`#work_end_${day}`).val($(`#work_end_${day}`).attr('value').slice(0, -3)).trigger('change');
            });

            $('#employee_room').val($('#employee_room_value').val()).trigger('change');
            
            let availableTools = $('#available_tools_value').val().split(',');
            $('#available_tools').val(availableTools).trigger('change');
            
            var inventoryNumbersJson = $('#inventory_numbers_of_available_tools').val();
            var inventoryNumbersArray = [];

            if (inventoryNumbersJson && inventoryNumbersJson.trim() !== '') {
                try {
                    inventoryNumbersArray = JSON.parse(inventoryNumbersJson);
                } catch (e) {
                    console.error('Hiba a leltári számok JSON feldolgozásakor:', e);
                    inventoryNumbersArray = [];
                }
            }

            if (Array.isArray(inventoryNumbersArray) && inventoryNumbersArray.length > 0) {
                inventoryNumbersArray.forEach(function(item) {
                    for (let tool in item) {
                        let inventoryNumber = item[tool];
                        $('#inventory_numbers_of_available_tools_' + tool).val(inventoryNumber);
                    }
                });
            }

            $('#job_description_file').val($('#job_description_file').data('existing'));
            $('#personal_data_sheet_file').val($('#personal_data_sheet_file').data('existing'));
            $('#student_status_verification_file').val($('#student_status_verification_file').data('existing'));
            $('#certificates_file').val($('#certificates_file').data('existing'));
            $('#commute_support_form_file').val($('#commute_support_form_file').data('existing'));
        }, 400);

        // set datepicker date fields start
        var recruitmentCreatedAt = $('#recruitmentCreatedAt').val();
        var baseDate = recruitmentCreatedAt ? new Date(recruitmentCreatedAt) : new Date();

        function calculateStartDate() {
            return $("#citizenship").val() == 'Harmadik országbeli' ? moment(baseDate).add(60, 'days') : moment(baseDate).add(21, 'days');
        }

        $("#citizenship").on('change', function() {
            $("#employment_start_date, #employment_end_date").val('');

            var startDate = calculateStartDate().format('YYYY.MM.DD');
            $("#employment_start_date").datepicker('destroy').datepicker({
                format: "yyyy.mm.dd",
                startDate: startDate,
                language: 'hu',
                weekStart: 1,
            });
        });

        // Initialize with recruitmentCreatedAt if available
        var initialStartDate = isTitkar9Role ? 
            null : 
            calculateStartDate().format('YYYY.MM.DD');
        $("#employment_start_date").datepicker({
            format: "yyyy.mm.dd",
            startDate: initialStartDate,
            endDate: moment(baseDate).add(30, 'years').format('YYYY.MM.DD'),
            language: 'hu',
            weekStart: 1,
            autoclose: true,
        });

        var initialEndDate = isTitkar9Role ? 
            null : 
            moment(baseDate).add(6, 'months').format('YYYY.MM.DD');
        $("#employment_end_date").datepicker({
            format: "yyyy.mm.dd",
            startDate: initialEndDate,
            endDate: moment(baseDate).add(30, 'years').format('YYYY.MM.DD'),
            language: 'hu',
            weekStart: 1,
            autoclose: true,
        });

        $("#citizenship").on('change', function() {
            updateStartDateSettings();
        });
        
        $('#position_id').on('change', function() {
            updateStartDateSettings();
        });
        
        $("#employment_start_date").on('change', function() {
            updateEndDateBasedOnPosition();
        });

        // set datepicker date fields end

        if (isSuspendedReview) {
            // Minden input mező legyen readonly vagy disabled
            $('input:not([type="hidden"]):not(.dz-hidden-input):not(#employment_start_date), select, textarea').attr('readonly', true).attr('disabled', true);
        }
    }
    /**
     * End of Initialize form for New or Edit
     */
    
    toggleApplicantCountInputs($('#job_ad_exists').is(':checked'));
    // Disable task input if employment type is fixed
    toggleTaskInput($('#employment_type').val());
    // Initialize popover on a target element
    citizenshipPopover();
    // Filter position options based on selected type
    filterPositionOptions();
    // Filter controls based on selected workgroups
    filterByWorkgroups();
    // Set working hours and calculate duration
    setWorkingHoursWeekdays();
    // Filter controls based on selected position
    filterByPosition();

    // Revalidate social security number when citizenship changes
    $('#citizenship').on('change', function() {
        if (fv) {
            fv.revalidateField('social_security_number');
        }
    });

    // Submit employee recruitment form
    $('.btn-submit').on('click', function (event) {
        event.preventDefault();

        $('.invalid-feedback').remove();
        fv = validateEmployeeRecruitment();

        // revalidate fields when their values change
        revalidateOnChange(fv, 'name');
        revalidateOnChange(fv, 'birth_date');
        revalidateOnChange(fv, 'social_security_number');
        revalidateOnChange(fv, 'address');
        revalidateOnChange(fv, 'applicants_female_count');
        revalidateOnChange(fv, 'applicants_male_count');
        revalidateOnChange(fv, 'workgroup_id_1');
        revalidateOnChange(fv, 'workgroup_id_2');
        revalidateOnChange(fv, 'position_id');
        revalidateOnChange(fv, 'job_description_file');
        revalidateOnChange(fv, 'task');
        revalidateOnChange(fv, 'employment_start_date');
        revalidateOnChange(fv, 'employment_end_date');
        revalidateOnChange(fv, 'base_salary_cost_center_1');
        revalidateOnChange(fv, 'base_salary_monthly_gross_1');
        revalidateOnChange(fv, 'base_salary_cost_center_2');
        revalidateOnChange(fv, 'base_salary_monthly_gross_2');
        revalidateOnChange(fv, 'base_salary_cost_center_3');
        revalidateOnChange(fv, 'base_salary_monthly_gross_3');
        revalidateOnChange(fv, 'health_allowance_cost_center_4');
        revalidateOnChange(fv, 'health_allowance_monthly_gross_4');
        revalidateOnChange(fv, 'management_allowance_cost_center_5');
        revalidateOnChange(fv, 'management_allowance_monthly_gross_5');
        revalidateOnChange(fv, 'management_allowance_end_date');
        revalidateOnChange(fv, 'extra_pay_1_cost_center_6');
        revalidateOnChange(fv, 'extra_pay_1_monthly_gross_6');
        revalidateOnChange(fv, 'extra_pay_1_end_date');
        revalidateOnChange(fv, 'extra_pay_2_cost_center_7');
        revalidateOnChange(fv, 'extra_pay_2_monthly_gross_7');
        revalidateOnChange(fv, 'extra_pay_2_end_date');
        revalidateOnChange(fv, 'email');
        revalidateOnChange(fv, 'entry_permissions');
        revalidateOnChange(fv, 'employee_room');
        revalidateOnChange(fv, 'license_plate');
        revalidateOnChange(fv, 'phone_extension');
        revalidateOnChange(fv, 'available_tools');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_asztal');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_szek');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_asztali_szamitogep');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_laptop');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_laptop_taska');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_monitor');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_billentyuzet');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_eger');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_dokkolo');
        revalidateOnChange(fv, 'inventory_numbers_of_available_tools_mobiltelefon');
        revalidateOnChange(fv, 'personal_data_sheet_file');
        revalidateOnChange(fv, 'student_status_verification_file');
        revalidateOnChange(fv, 'certificates_file');
        revalidateOnChange(fv, 'commute_support_form_file');
        revalidateOnChange(fv, 'initiator_comment');
        

        // enable/disable validators based on other field values
        enableOnChange(fv, 'task', 'employment_type', function() { return $('#employment_type').val() == 'Határozott'});
        enableOnChange(fv, 'employment_end_date', 'employment_type', function() { return $('#employment_type').val() == 'Határozott'});
        
        enableOnChange(fv, 'base_salary_cost_center_2', 'base_salary_monthly_gross_2', function() {
            return cleaveInstances['base_salary_monthly_gross_2'].getRawValue() != "" && cleaveInstances['base_salary_monthly_gross_2'].getRawValue() > 0
        });
        enableOnChange(fv, 'base_salary_monthly_gross_2', 'base_salary_cost_center_2', function() { return $('#base_salary_cost_center_2').val() != ""});
        
        enableOnChange(fv, 'base_salary_cost_center_3', 'base_salary_monthly_gross_3', function() { 
            return cleaveInstances['base_salary_monthly_gross_3'].getRawValue() != "" && cleaveInstances['base_salary_monthly_gross_3'].getRawValue() > 0
        });
        enableOnChange(fv, 'base_salary_monthly_gross_3', 'base_salary_cost_center_3', function() { return $('#base_salary_cost_center_3').val() != ""});
        
        enableOnChange(fv, 'health_allowance_cost_center_4', 'health_allowance_monthly_gross_4', function() {
            return cleaveInstances['health_allowance_monthly_gross_4'].getRawValue() != "" && cleaveInstances['health_allowance_monthly_gross_4'].getRawValue() > 0
        });
        enableOnChange(fv, 'health_allowance_monthly_gross_4', 'health_allowance_cost_center_4', function() { return $('#health_allowance_cost_center_4').val() != ""});
        
        enableOnChange(fv, 'management_allowance_cost_center_5', 'management_allowance_monthly_gross_5', function() { 
            return cleaveInstances['management_allowance_monthly_gross_5'].getRawValue() != "" && cleaveInstances['management_allowance_monthly_gross_5'].getRawValue() > 0
        });
        enableOnChange(fv, 'management_allowance_monthly_gross_5', 'management_allowance_cost_center_5', function() { return $('#management_allowance_cost_center_5').val() != ""});
        enableOnChange(fv, 'management_allowance_end_date', 'management_allowance_cost_center_5', function() { return $('#management_allowance_cost_center_5').val() != ""});
        
        enableOnChange(fv, 'extra_pay_1_cost_center_6', 'extra_pay_1_monthly_gross_6', function() {
            return cleaveInstances['extra_pay_1_monthly_gross_6'].getRawValue() != "" && cleaveInstances['extra_pay_1_monthly_gross_6'].getRawValue() > 0
        });
        enableOnChange(fv, 'extra_pay_1_monthly_gross_6', 'extra_pay_1_cost_center_6', function() { return $('#extra_pay_1_cost_center_6').val() != ""});
        enableOnChange(fv, 'extra_pay_1_end_date', 'extra_pay_1_cost_center_6', function() { return $('#extra_pay_1_cost_center_6').val() != ""});

        enableOnChange(fv, 'extra_pay_2_cost_center_7', 'extra_pay_2_monthly_gross_7', function() {
            return cleaveInstances['extra_pay_2_monthly_gross_7'].getRawValue() != "" && cleaveInstances['extra_pay_2_monthly_gross_7'].getRawValue() > 0
        });
        enableOnChange(fv, 'extra_pay_2_monthly_gross_7', 'extra_pay_2_cost_center_7', function() { return $('#extra_pay_2_cost_center_7').val() != ""});
        enableOnChange(fv, 'extra_pay_2_end_date', 'extra_pay_2_cost_center_7', function() { return $('#extra_pay_2_cost_center_7').val() != ""});
        
        enableOnChange(fv, 'license_plate', 'entry_permissions', function() { return $('#entry_permissions').val().includes('auto') });
        enableOnChange(fv, 'student_status_verification_file', 'position_id', function() {
            const selectedPositionName = $('#position_id option:selected').text();
            return selectedPositionName === 'egyetemi hallgató' || selectedPositionName === 'tudományos segédmunkatárs';
        });
        enableOnChange(fv, 'commute_support_form_file', 'requires_commute_support', function() { return $('#requires_commute_support').val() == true });

        let validationErrors = [];

        // Ellenőrzés: Bruttó bér összeg
        if (!validateCostCenterSum()) {
            validationErrors.push('Teljes havi bruttó bér összegét ezerre kerekítve szükséges megadni!');
        }

        // Ellenőrzés: Munkaidők sorrendje
        if (!validateWorkdayTimes()) {
            validationErrors.push('Munkanapok munkaideje kezdetének korábbinak kell lennie, mint a végének!');
        }

        // Ellenőrzés: Munkaidők összege
        if (!validateWorkingHours()) {
            validationErrors.push('Munkanapok munkaidejének összege nem egyezik a heti munkaóraszámmal!');
        }

        fv.validate().then(async function(status) {
            if(status === 'Valid') {
                // Disable the button to prevent double clicks
                $(".btn-submit").prop('disabled', true);

                var formData = {};
                formData['recruitment_id'] = $('#recruitment_id').val();
                $('#new-recruitment :input').each(function() {
                    var id = $(this).attr('id');
                    var value = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
                    formData[id] = value;
                });

                var url = isSuspendedReview
                    ? '/employee-recruitment/' + $('#recruitment_id').val() + '/restore'
                    : '/employee-recruitment';
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        var redirect = data.redirectUrl || data.url;
                        if (redirect) {
                            window.location.href = redirect;
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 401 || jqXHR.status === 419) {
                            alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                            window.location.href = '/login';
                        }

                        var errors = jqXHR.responseJSON.errors;
                        var errorAlertMessage = '';
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorAlertMessage += errors[key] + '<br>';
                            }
                        }
                        GLOBALS.AJAX_ERROR(errorAlertMessage, jqXHR, textStatus, errorThrown);
                    }
                });
            } else if (status === 'Invalid') {
                const fields = fv.getFields();
                for (const name of Object.keys(fields)) {
                    try {
                        const fieldStatus = await fv.validateField(name);
                        if (fieldStatus === 'Invalid') {
                        console.log('Field:', name, 'Status:', fieldStatus);
                        validationErrors.push(
                            'Hibás adat(ok) vagy hiányzó mező(k) vannak az űrlapon, kérjük ellenőrizd!'
                        );
                        break;
                        }
                    } catch (err) {
                        console.error('Validation error on', name, err);
                        throw err;
                    }
                }

                GLOBALS.AJAX_ERROR(validationErrors.join('<br>'));
            }
        });
    });

    // Submit draft employee recruitment form
    $('.btn-submit-draft').on('click', function (event) {
        event.preventDefault();

        // Disable the button to prevent double clicks
        $(".btn-submit-draft").prop('disabled', true);

        var formData = {};
        formData['recruitment_id'] = $('#recruitment_id').val();
        $('#new-recruitment :input').each(function() {
            var id = $(this).attr('id');
            var value = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
            formData[id] = value;
        });
        
        $.ajax({
            url: '/employee-recruitment/draft',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                var redirect = data.redirectUrl || data.url;
                if (redirect) {
                    window.location.href = redirect;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                var errors = jqXHR.responseJSON.errors;
                var errorAlertMessage = '';
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        errorAlertMessage += errors[key] + '<br>';
                    }
                }
                GLOBALS.AJAX_ERROR(errorAlertMessage, jqXHR, textStatus, errorThrown);
            }
        });
    });

    $('#confirm_delete_case').on('click', function (event) {
        $('#deleteWorkflowConfirmation').modal('hide');

        var formData = {};
        formData['recruitment_id'] = $('#recruitment_id').val();
        
        $.ajax({
            url: '/employee-recruitment/' + $('#recruitment_id').val() + '/delete',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }

                var errors = jqXHR.responseJSON.errors;
                var errorAlertMessage = '';
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        errorAlertMessage += errors[key] + '<br>';
                    }
                }
                GLOBALS.AJAX_ERROR(errorAlertMessage, jqXHR, textStatus, errorThrown);
            }
        });
    });

    $('#confirm_delete_draft').on('click', function(event) {
        $('#deleteWorkflowDraftConfirmation').modal('hide');
        
        var draftId = $('#recruitment_id').val();
        
        $(this).prop('disabled', true);
        
        $.ajax({
            url: '/employee-recruitment/draft/' + draftId + '/delete',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 401 || jqXHR.status === 419) {
                    alert('Lejárt a munkamenet. Kérjük, jelentkezz be újra.');
                    window.location.href = '/login';
                }
                
                var errors = jqXHR.responseJSON.errors;
                var errorAlertMessage = '';
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        errorAlertMessage += errors[key] + '<br>';
                    }
                }
                GLOBALS.AJAX_ERROR(errorAlertMessage, jqXHR, textStatus, errorThrown);
            }
        });
    });

    // calculate monthly gross sallary sum
    $('#totalGross').text(getGrossSalarySum().toLocaleString('en-US', {maximumFractionDigits: 2}).replace(/,/g, ' '));
    ['base_salary_monthly_gross_1', 'base_salary_monthly_gross_2', 'base_salary_monthly_gross_3', 'health_allowance_monthly_gross_4', 'management_allowance_monthly_gross_5', 'extra_pay_1_monthly_gross_6', 'extra_pay_2_monthly_gross_7'].forEach(function(field) {
        $('#' + field).on('change', function() {
            $('#totalGross').text(getGrossSalarySum().toLocaleString('en-US', {maximumFractionDigits: 2}).replace(/,/g, ' '));
        });
    });

    if ($('#employer_contribution').length) {
        employerContributionRate = parseFloat($('#employer_contribution').val());
    }
    
    // Fedezetigazolandó összeg táblázat inicializálása
    initCoverageTable();
    
    // Értékek átvétele a szerverről, ha szerkesztés módban vagyunk
    if ($('#recruitment_id').val() !== '') {
        setTimeout(function() {
            updateCoverageTable();
        }, 600); // Kicsit később futtatjuk, mint a többi inicializálást (400ms után), hogy a többi érték már be legyen töltve
    }

    $('#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date').on('change', function() {
        console.log('Allowance end date changed:', $(this).attr('id'), $(this).val());
        updateCoverageTable();
    });

    $('#workgroup_id_1, #workgroup_id_2').on('change', syncWorkgroupOptions);
    syncWorkgroupOptions();
    
    $('#base_salary_cost_center_1, #base_salary_cost_center_2, #base_salary_cost_center_3')
        .on('change', function() {
            syncCostCenterOptions();
            // ha FormValidation fut, revalidáljuk a callback mezőket
            if (typeof fv !== 'undefined') {
                fv.revalidateField('base_salary_cost_center_2');
                fv.revalidateField('base_salary_cost_center_3');
            }
        });
    syncCostCenterOptions();
});

function syncWorkgroupOptions() {
    const $wg1 = $('#workgroup_id_1');
    const $wg2 = $('#workgroup_id_2');
    const val1 = $wg1.val();
    const val2 = $wg2.val();

    // Minden option engedélyezése
    $wg1.find('option').prop('disabled', false);
    $wg2.find('option').prop('disabled', false);

    // Ha van kiválasztott 1-es, tiltjuk ugyanazt a 2-esen
    if (val1) {
        $wg2.find(`option[value="${val1}"]`).prop('disabled', true);
    }
    // Ha van nem -1-es 2-es, tiltjuk az 1-esen
    if (val2 && val2 !== '-1') {
        $wg1.find(`option[value="${val2}"]`).prop('disabled', true);
    }

    // Frissítjük a select2 megjelenést
    $wg1.trigger('change.select2');
    $wg2.trigger('change.select2');
}

function syncCostCenterOptions() {
    const $cc1 = $('#base_salary_cost_center_1');
    const $cc2 = $('#base_salary_cost_center_2');
    const $cc3 = $('#base_salary_cost_center_3');

    const v1 = $cc1.val();
    const v2 = $cc2.val();
    const v3 = $cc3.val();

    // Először mindhárom select összes optionját engedélyezzük
    $cc1.add($cc2).add($cc3)
        .find('option').prop('disabled', false);

    // Tiltjuk a duplikációkat
    if (v1) {
        $cc2.find(`option[value="${v1}"]`).prop('disabled', true);
        $cc3.find(`option[value="${v1}"]`).prop('disabled', true);
    }
    if (v2) {
        $cc1.find(`option[value="${v2}"]`).prop('disabled', true);
        $cc3.find(`option[value="${v2}"]`).prop('disabled', true);
    }
    if (v3) {
        $cc1.find(`option[value="${v3}"]`).prop('disabled', true);
        $cc2.find(`option[value="${v3}"]`).prop('disabled', true);
    }

    // Frissíti a select2 UI-t anélkül, hogy újrainicializálná
    $cc1.trigger('change.select2');
    $cc2.trigger('change.select2');
    $cc3.trigger('change.select2');
}

function toggleApplicantCountInputs(isChecked) {
    if (isChecked) {
        $('#applicants_female_count').prop('disabled', false);
        $('#applicants_male_count').prop('disabled', false);
    } else {
        $('#applicants_female_count').val('').prop('disabled', true);
        $('#applicants_male_count').val('').prop('disabled', true);
    }
}

function toggleTaskInput(employmentType) {
    if (employmentType === 'Határozatlan') {
        $('#task').val('').prop('disabled', true);
        $('#employment_end_date').val('').prop('disabled', true);
    } else {
        $('#task').prop('disabled', false);
        $('#employment_end_date').prop('disabled', false);
    }
}

function citizenshipPopover() {
    $('#citizenship').on('change', function () {
        if ($(this).val() === 'EGT tagállambeli') {
            $(this).popover({
                content: 'EGT tagjai az Európai Unió tagállamai mellett: Izland, Norvégia és Liechtenstein',
                placement: 'bottom',
                trigger: 'focus'
            }).popover('show');
        } else {
            $(this).popover('dispose');
        }
    });
}

// Filtering position
function filterPositionOptions() {
    filterPositionIdOptions('kutatói');

    $('#position_type').on('change', function() {
        let selectedType = $(this).val().toLowerCase();
        filterPositionIdOptions(selectedType);
    });
}

function filterPositionIdOptions(type) {
    // Show only options where data-type = selected type
    $('#position_id option').each(function() {
        let optionType = $(this).data('type');
        if (optionType === type) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    // Reset the selected option
    $('#position_id').val('');
}
// End of filtering position

// Filtering by workgroups
let originalEntryPermissionsOptions = $('#entry_permissions').html();
let originalEmployeeRoomOptions = $('#employee_room').html();
let originalBaseSalaryCostCenter1Options = $('#base_salary_cost_center_1').html();
let originalBaseSalaryCostCenter2Options = $('#base_salary_cost_center_2').html();
let originalBaseSalaryCostCenter3Options = $('#base_salary_cost_center_3').html();
let originalHealthAllowanceCostCenter4Options = $('#health_allowance_cost_center_4').html();
let originalManagementAllowanceCostCenter5Options = $('#management_allowance_cost_center_5').html();
let originalExtraPay1CostCenter6Options = $('#extra_pay_1_cost_center_6').html();
let originalExtraPay2CostCenter7Options = $('#extra_pay_2_cost_center_7').html();

function filterByWorkgroups() {
    filterRoomOptions();
    filterCostCenters();
    filterExternalAccess();

    $('#workgroup_id_1, #workgroup_id_2').on('change', filterRoomOptions);
    $('#workgroup_id_1, #workgroup_id_2').on('change', filterCostCenters);
    $('#workgroup_id_1').on('change', filterExternalAccess);
}

function filterCostCenters() {
    let selectedWorkgroup1 = $('#workgroup_id_1').find(':selected').data('workgroup');
    let selectedWorkgroup2 = $('#workgroup_id_2').find(':selected').data('workgroup');
    let filterOptions = function(selector) {
        $(selector + ' option').filter(function() {
            let optionWorkgroup = $(this).data('workgroup');
            return optionWorkgroup !== undefined && optionWorkgroup !== selectedWorkgroup1 && optionWorkgroup !== selectedWorkgroup2;
        }).remove();
    }

    // Restore the original options in the base_salary_cost_center_1 select
    $('#base_salary_cost_center_1').html(originalBaseSalaryCostCenter1Options);
    $('#base_salary_cost_center_2').html(originalBaseSalaryCostCenter2Options);
    $('#base_salary_cost_center_3').html(originalBaseSalaryCostCenter3Options);
    $('#health_allowance_cost_center_4').html(originalHealthAllowanceCostCenter4Options);
    $('#management_allowance_cost_center_5').html(originalManagementAllowanceCostCenter5Options);
    $('#extra_pay_1_cost_center_6').html(originalExtraPay1CostCenter6Options);
    $('#extra_pay_2_cost_center_7').html(originalExtraPay2CostCenter7Options);

    // Filter and remove options from cc1
    $('#base_salary_cost_center_1 option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        return optionWorkgroup !== undefined && optionWorkgroup !== selectedWorkgroup1;
    }).remove();

    // Filter and remove options from all cc except cc1
    filterOptions('#base_salary_cost_center_2');
    filterOptions('#base_salary_cost_center_3');
    filterOptions('#health_allowance_cost_center_4');
    filterOptions('#management_allowance_cost_center_5');
    filterOptions('#extra_pay_1_cost_center_6');
    filterOptions('#extra_pay_2_cost_center_7');

    // Refresh Select2 to apply changes
    $('#base_salary_cost_center_1').select2();
    $('#base_salary_cost_center_2').select2();
    $('#base_salary_cost_center_3').select2();
    $('#health_allowance_cost_center_4').select2();
    $('#management_allowance_cost_center_5').select2();
    $('#extra_pay_1_cost_center_6').select2();
    $('#extra_pay_2_cost_center_7').select2();
}

function filterRoomOptions() {
    let selectedWorkgroup1 = $('#workgroup_id_1').find(':selected').data('workgroup');
    let selectedWorkgroup2 = $('#workgroup_id_2').find(':selected').data('workgroup');
    
    // Get first digits of selected workgroups if they exist
    let firstDigit1 = selectedWorkgroup1 ? String(selectedWorkgroup1).charAt(0) : null;
    let firstDigit2 = selectedWorkgroup2 ? String(selectedWorkgroup2).charAt(0) : null;

    // Reset options to original state
    $('#entry_permissions').html(originalEntryPermissionsOptions);
    $('#employee_room').html(originalEmployeeRoomOptions);

    // Filter entry permissions
    $('#entry_permissions option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        let optionValue = $(this).val();
        let optgroupLabel = $(this).parent('optgroup').attr('label');
        
        // Always keep "auto" and "kerekpar" options
        if (optionValue === 'auto' || optionValue === 'kerekpar') {
            return false; // Don't remove
        }
        
        // For Institute-level permissions, filter based on first digit
        if (optgroupLabel === "Intézeti belépési engedélyek") {
            // Get first digit from data-workgroup (format "1XX", "3XX", etc.)
            let optionFirstDigit = optionWorkgroup ? String(optionWorkgroup).charAt(0) : null;
            
            // Keep if matches either of the selected workgroups' first digits
            return !(optionFirstDigit === firstDigit1 || optionFirstDigit === firstDigit2);
        }
        
        // For Group-level permissions, keep existing logic
        return optionWorkgroup !== selectedWorkgroup1 && optionWorkgroup !== selectedWorkgroup2;
    }).remove();

    // Keep existing employee_room filter logic
    $('#employee_room option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        return optionWorkgroup !== selectedWorkgroup1 && optionWorkgroup !== selectedWorkgroup2;
    }).remove();

    $('#entry_permissions').trigger('change');
}

function filterExternalAccess() {
    let selectedWorkgroup1 = $('#workgroup_id_1').find(':selected').data('workgroup');

    if (String(selectedWorkgroup1).charAt(0) === '9') {
        $('#external_access_rights').prop('disabled', false).parent('div').parent('div').show();
    } else {
        $('#external_access_rights').prop('disabled', true).parent('div').parent('div').hide();
    }
}
// End of filtering by workgroups

// Employer contribution rate
function initCoverageTable() {
    console.log('Initializing coverage table');
    
    // Évek beállítása a táblázat fejlécében
    const currentYear = new Date().getFullYear();
    for (let i = 0; i < 4; i++) {
        $(`#year-header-${i}`).text(currentYear + i);
    }

    // Eseménykezelők hozzáadása minden fizetésmező és költséghely változásához
    const salaryFields = [
        'base_salary_monthly_gross_1', 'base_salary_monthly_gross_2', 'base_salary_monthly_gross_3',
        'health_allowance_monthly_gross_4', 'management_allowance_monthly_gross_5',
        'extra_pay_1_monthly_gross_6', 'extra_pay_2_monthly_gross_7'
    ];
    
    const costCenterFields = [
        'base_salary_cost_center_1', 'base_salary_cost_center_2', 'base_salary_cost_center_3',
        'health_allowance_cost_center_4', 'management_allowance_cost_center_5',
        'extra_pay_1_cost_center_6', 'extra_pay_2_cost_center_7'
    ];

    // Nyugdíjas státusz változás figyelése
    $('#is_retired').on('change', function() {
        console.log('Retired status changed:', $(this).is(':checked'));
        updateCoverageTable();
    });
    
    // Foglalkoztatás típus változás figyelése
    $('#employment_type').on('change', function() {
        console.log('Employment type changed:', $(this).val());
        updateCoverageTable();
    });
    
    // Foglalkoztatás kezdő és vég dátum változás figyelése
    $('#employment_start_date').on('change', function() {
        console.log('Start date changed:', $(this).val());
        updateCoverageTable();
    });
    
    $('#employment_end_date').on('change', function() {
        console.log('End date changed:', $(this).val());
        updateCoverageTable();
    });

    // Fizetés mezők változásának figyelése
    salaryFields.forEach(field => {
        $(`#${field}`).on('change', function() {
            console.log(`Salary field ${field} changed:`, $(this).val());
            updateCoverageTable();
        });
    });

    // Költséghely mezők változásának figyelése
    costCenterFields.forEach(field => {
        $(`#${field}`).on('change', function() {
            console.log(`Cost center field ${field} changed:`, $(this).val());
            updateCoverageTable();
        });
    });

    // Alapértelmezett járulék érték beállítása
    employerContributionRate = 13;  // Ha nincs megadva, 13% az alapértelmezett
    
    // Opcionális: a controllerből jövő járulék érték beállítása
    if ($('#default_employer_contribution').length) {
        employerContributionRate = parseFloat($('#default_employer_contribution').val());
        console.log('Employer contribution rate set from server:', employerContributionRate);
    }

    // Első futtatás a tábla feltöltéséhez
    updateCoverageTable();
    
    console.log('Coverage table initialized');
}

// Munkáltatói járulék értékének lekérése
function getEmployerContribution() {
    // Ha nyugdíjas, nincs járulék
    if ($('#is_retired').is(':checked')) {
        return 0;
    }
    
    // Ha van egyedi érték megadva a rejtett mezőben
    if ($('#employer_contribution').val() && $('#employer_contribution').val() !== '') {
        return parseFloat($('#employer_contribution').val());
    }
    
    // Alapértelmezett érték (ez jön az Option táblából a backend oldalon)
    return employerContributionRate;
}

// Fedezetigazolandó összeg táblázat frissítése
function updateCoverageTable() {
    console.log('Updating coverage table...');
    
    // Alapvető adatok kiolvasása
    const isRetired = $('#is_retired').is(':checked');
    const employmentType = $('#employment_type').val();
    
    // Munkáltatói járulék lekérése a getEmployerContribution függvénnyel
    let contributionRate = getEmployerContribution();
    console.log('Contribution rate:', contributionRate);
    
    // Dátumok feldolgozása
    let startDate = null;
    if ($('#employment_start_date').val()) {
        startDate = moment($('#employment_start_date').val(), 'YYYY.MM.DD');
        console.log('Start date:', startDate.format('YYYY-MM-DD'));
    } else {
        console.log('No start date provided');
        startDate = moment(); // Használjunk mai dátumot alapértelmezettként
    }
    
    let endDate = null;
    if (employmentType === 'Határozott' && $('#employment_end_date').val()) {
        endDate = moment($('#employment_end_date').val(), 'YYYY.MM.DD');
        console.log('End date:', endDate.format('YYYY-MM-DD'));
    } else {
        console.log('No end date or indefinite employment');
    }
    
    // Költséghelyek és fizetések összegyűjtése
    const costCentersAndSalaries = [
        { 
            costCenter: $('#base_salary_cost_center_1').val(), 
            salary: parseSalaryValue('base_salary_monthly_gross_1'), 
            name: $('#base_salary_cost_center_1 option:selected').text(),
            endDate: null // Alap fizetésnek nincs vég dátuma
        },
        { 
            costCenter: $('#base_salary_cost_center_2').val(), 
            salary: parseSalaryValue('base_salary_monthly_gross_2'), 
            name: $('#base_salary_cost_center_2 option:selected').text(),
            endDate: null // Alap fizetésnek nincs vég dátuma
        },
        { 
            costCenter: $('#base_salary_cost_center_3').val(), 
            salary: parseSalaryValue('base_salary_monthly_gross_3'), 
            name: $('#base_salary_cost_center_3 option:selected').text(),
            endDate: null // Alap fizetésnek nincs vég dátuma
        },
        { 
            costCenter: $('#health_allowance_cost_center_4').val(), 
            salary: parseSalaryValue('health_allowance_monthly_gross_4'), 
            name: $('#health_allowance_cost_center_4 option:selected').text(),
            endDate: null // Egészségügyi pótléknak nincs vég dátuma
        },
        { 
            costCenter: $('#management_allowance_cost_center_5').val(), 
            salary: parseSalaryValue('management_allowance_monthly_gross_5'), 
            name: $('#management_allowance_cost_center_5 option:selected').text(),
            endDate: $('#management_allowance_end_date').val() ? moment($('#management_allowance_end_date').val(), 'YYYY.MM.DD') : null // Vezetői pótlék vég dátuma
        },
        { 
            costCenter: $('#extra_pay_1_cost_center_6').val(), 
            salary: parseSalaryValue('extra_pay_1_monthly_gross_6'), 
            name: $('#extra_pay_1_cost_center_6 option:selected').text(),
            endDate: $('#extra_pay_1_end_date').val() ? moment($('#extra_pay_1_end_date').val(), 'YYYY.MM.DD') : null // Bérpótlék 1 vég dátuma
        },
        { 
            costCenter: $('#extra_pay_2_cost_center_7').val(), 
            salary: parseSalaryValue('extra_pay_2_monthly_gross_7'), 
            name: $('#extra_pay_2_cost_center_7 option:selected').text(),
            endDate: $('#extra_pay_2_end_date').val() ? moment($('#extra_pay_2_end_date').val(), 'YYYY.MM.DD') : null // Bérpótlék 2 vég dátuma
        }
    ];
    
    // Debug: Kiírjuk a költséghelyeket és a fizetéseket
    costCentersAndSalaries.forEach(item => {
        if (item.costCenter && item.salary > 0) {
            console.log('Cost Center:', item.costCenter, 'Name:', item.name, 'Salary:', item.salary);
        }
    });
    
    // Csak azok a költséghelyek, ahol van érték és ki van választva költséghely
    const validCostCenters = costCentersAndSalaries.filter(item => 
        item.costCenter && item.salary > 0
    );
    
    console.log('Valid cost centers:', validCostCenters.length);
    
    // Ha nincs egyetlen költséghely sem, akkor üres táblázat
    if (validCostCenters.length === 0) {
        $('#coverageSummaryBody').html('');
        updateTotals([0, 0, 0, 0]);
        return;
    }
    
    // Költséghelyenkénti összegek számítása a 4 évre
    const costCenterYearlyAmounts = {};
    const yearlyTotals = [0, 0, 0, 0];
    
    validCostCenters.forEach(item => {
        if (!costCenterYearlyAmounts[item.costCenter]) {
            costCenterYearlyAmounts[item.costCenter] = {
                name: item.name,
                amounts: [0, 0, 0, 0],
                total: 0
            };
        }
        
        // Éves bontás kiszámítása a 4 évre
        const yearlyAmounts = calculateYearlyAmounts(
            startDate, 
            endDate, 
            parseFloat(item.salary), 
            contributionRate,
            item.endDate // Adjuk át a pótlék vég dátumát is
        );
        
        console.log('Yearly amounts for', item.name, ':', yearlyAmounts);
        
        // Összegek hozzáadása a költséghelyhez
        for (let i = 0; i < 4; i++) {
            costCenterYearlyAmounts[item.costCenter].amounts[i] += yearlyAmounts[i];
            yearlyTotals[i] += yearlyAmounts[i];
            costCenterYearlyAmounts[item.costCenter].total += yearlyAmounts[i];
        }
    });
    
    // Táblázat tartalmának generálása
    let tableContent = '';
    Object.keys(costCenterYearlyAmounts).forEach(costCenter => {
        const data = costCenterYearlyAmounts[costCenter];
        
        tableContent += `<tr>
            <td>${data.name}</td>
            <td>${formatNumber(data.amounts[0])} Ft</td>
            <td>${formatNumber(data.amounts[1])} Ft</td>
            <td>${formatNumber(data.amounts[2])} Ft</td>
            <td>${formatNumber(data.amounts[3])} Ft</td>
            <td>${formatNumber(data.total)} Ft</td>
        </tr>`;
    });
    
    $('#coverageSummaryBody').html(tableContent);
    console.log('Table content updated');
    
    // Összesítések frissítése
    updateTotals(yearlyTotals);
}

// Segédfüggvény a fizetés értékek beolvasásához
function parseSalaryValue(fieldId) {
    const field = $('#' + fieldId);
    
    // Ha van Cleave példány a mezőhöz
    if (cleaveInstances[fieldId] && typeof cleaveInstances[fieldId].getRawValue === 'function') {
        const rawValue = cleaveInstances[fieldId].getRawValue();
        return rawValue ? parseInt(rawValue) : 0;
    }
    
    // Fallback: egyszerű értékolvasás és tisztítás
    const value = field.val();
    if (!value) return 0;
    
    // Szóközök és egyéb nem numerikus karakterek eltávolítása
    return parseInt(value.replace(/\s+/g, '').replace(/[^\d]/g, '')) || 0;
}

// Éves bontás számítása egy költséghelyhez
function calculateYearlyAmounts(startDate, endDate, monthlySalary, contributionRate, itemEndDate) {
    console.log('Original dates - start:', startDate ? startDate.format('YYYY-MM-DD') : 'none', 
                'end:', endDate ? endDate.format('YYYY-MM-DD') : 'none',
                'item end:', itemEndDate ? itemEndDate.format('YYYY-MM-DD') : 'none',
                'monthly:', monthlySalary, 
                'rate:', contributionRate);
    
    // Egy teljes hónapnyi érték kiszámítása a munkáltatói járulékkal együtt
    const oneMonthValue = monthlySalary * (1 + contributionRate / 100);
    console.log('One month value with contribution:', oneMonthValue);
    
    const amounts = [0, 0, 0, 0];
    const currentYear = new Date().getFullYear();
    
    // Ha nincs értelmezhető startDate, nem tudjuk kiszámolni
    if (!startDate || !startDate.isValid()) {
        console.log('Invalid start date');
        return amounts;
    }
    
    // Határozott idejű szerződés vagy pótlék időszak kalkuláció
    // Itt a szerződés vég dátuma (endDate) és a pótlék vég dátuma (itemEndDate) közül
    // mindig a korábbit használjuk, ha mindkettő létezik
    let effectiveEndDate = null;
    
    if (endDate && endDate.isValid()) {
        effectiveEndDate = moment(endDate);
    }
    
    if (itemEndDate && itemEndDate.isValid()) {
        if (!effectiveEndDate || itemEndDate.isBefore(effectiveEndDate)) {
            effectiveEndDate = moment(itemEndDate);
            console.log('Using item end date as effective end date');
        }
    }
    
    let hasAppliedDeduction = false;  // Követjük, hogy alkalmaztuk-e már a levonást
    
    // Számítás évenkénti bontásban
    for (let i = 0; i < 4; i++) {
        const yearStart = currentYear + i;
        
        // Az adott év első napja
        let firstDayOfYear = moment([yearStart, 0, 1]);
        // Az adott év utolsó napja
        let lastDayOfYear = moment([yearStart, 11, 31]);
        
        // Ha a foglalkoztatás későbbi, mint az adott év utolsó napja, akkor nincs munkavégzés ebben az évben
        if (startDate.isAfter(lastDayOfYear)) {
            amounts[i] = 0;
            continue;
        }
        
        // Ha a tényleges vég dátum létezik és korábbi, mint az adott év első napja, akkor nincs munkavégzés ebben az évben
        if (effectiveEndDate && effectiveEndDate.isBefore(firstDayOfYear)) {
            amounts[i] = 0;
            continue;
        }
        
        // A tényleges kezdő dátum ebben az évben
        let startForYear = startDate.isAfter(firstDayOfYear) ? moment(startDate) : moment(firstDayOfYear);
        
        // A tényleges záró dátum ebben az évben
        let endForYear;
        if (!effectiveEndDate) {
            endForYear = moment(lastDayOfYear);
        } else if (effectiveEndDate.isAfter(lastDayOfYear)) {
            endForYear = moment(lastDayOfYear);
        } else {
            endForYear = moment(effectiveEndDate);
        }
        
        // Hónapok számának változója
        let months = 0;
        
        // Ha teljes évről van szó (jan 1 - dec 31)
        if (startForYear.isSame(firstDayOfYear, 'day') && endForYear.isSame(lastDayOfYear, 'day')) {
            months = 12;
        } 
        // Egyébként pontos hónap számítás
        else {
            // Számoljuk ki hány teljes hónap van
            let fullMonths = 0;
            
            // Kezdjük a kezdő hónappal, majd haladjunk hónapról hónapra
            let currentDate = moment(startForYear).startOf('month');
            let endOfPeriod = moment(endForYear).endOf('day');
            
            // Haladjunk végig az összes releváns hónapon
            while (currentDate.isBefore(endOfPeriod)) {
                // Ha ez a kezdő hónap
                if (currentDate.month() === startForYear.month() && currentDate.year() === startForYear.year()) {
                    // A hónap végéig hátralévő napok száma (kezdőnapot is beleszámolva)
                    const daysInMonth = startForYear.daysInMonth();
                    const remainingDays = daysInMonth - startForYear.date() + 1;
                    fullMonths += remainingDays / daysInMonth;
                }
                // Ha ez a záró hónap
                else if (currentDate.month() === endForYear.month() && currentDate.year() === endForYear.year()) {
                    // A hónap elejétől a záró napig tartó napok száma (zárónapot is beleszámolva)
                    const daysInMonth = endForYear.daysInMonth();
                    fullMonths += endForYear.date() / daysInMonth;
                }
                // Ha ez egy teljes hónap a kezdő és záró hónap között
                else if (currentDate.isAfter(startForYear, 'month') && currentDate.isBefore(endForYear, 'month')) {
                    fullMonths += 1;
                }
                
                // Lépjünk a következő hónapra
                currentDate.add(1, 'month');
            }
            
            // Ha a kezdő és záró dátum ugyanabban a hónapban van
            if (startForYear.month() === endForYear.month() && startForYear.year() === endForYear.year()) {
                const daysInMonth = startForYear.daysInMonth();
                const daysInPeriod = endForYear.date() - startForYear.date() + 1;
                fullMonths = daysInPeriod / daysInMonth;
            }
            
            months = fullMonths;
        }
        
        console.log(`Year ${yearStart} before adjustment: ${months.toFixed(2)} months, from ${startForYear.format('YYYY-MM-DD')} to ${endForYear.format('YYYY-MM-DD')}`);
        
        // Összeg számítása az eredeti időszakra
        const yearValueBeforeAdjustment = monthlySalary * months * (1 + contributionRate / 100);
        
        // Fizetési eltolás miatti korrekció: kivonunk egy havi értéket a legelső nem-nulla évből
        let adjustedYearValue = yearValueBeforeAdjustment;
        
        // Az első nem-nulla évre alkalmazzuk a levonást
        if (!hasAppliedDeduction && yearValueBeforeAdjustment > 0) {
            console.log(`Applying deduction to year ${yearStart} - original value: ${yearValueBeforeAdjustment}`);
            adjustedYearValue = yearValueBeforeAdjustment - oneMonthValue;
            // Ha az eredmény negatív lenne, nullára állítjuk
            adjustedYearValue = Math.max(0, adjustedYearValue);
            hasAppliedDeduction = true;  // Megjegyezzük, hogy már alkalmaztuk a levonást
            console.log(`After deduction: ${adjustedYearValue}`);
        }
        
        console.log(`Year ${yearStart} final amount: ${adjustedYearValue.toFixed(2)}`);
        
        // Kerekítés egész számra
        amounts[i] = Math.round(adjustedYearValue);
    }
    
    console.log('Calculated yearly amounts:', amounts);
    return amounts;
}

// Összesítő számok frissítése
function updateTotals(yearlyTotals) {
    console.log('Updating totals with:', yearlyTotals);
    
    let grandTotal = 0;
    
    for (let i = 0; i < 4; i++) {
        $(`#year-total-${i}`).text(formatNumber(yearlyTotals[i]) + ' Ft');
        grandTotal += yearlyTotals[i];
    }
    
    $('#grand-total').text(formatNumber(grandTotal) + ' Ft');
}

// Szám formázása ezres elválasztóval
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}
// End of employer contribution rate

// Setting working hours
function setWorkingHoursWeekdays() {
    setWorkingHours("#work_start_monday", "#work_end_monday", "#monday_duration");
    setWorkingHours("#work_start_tuesday", "#work_end_tuesday", "#tuesday_duration");
    setWorkingHours("#work_start_wednesday", "#work_end_wednesday", "#wednesday_duration");
    setWorkingHours("#work_start_thursday", "#work_end_thursday", "#thursday_duration");
    setWorkingHours("#work_start_friday", "#work_end_friday", "#friday_duration");

    calculateDuration("#work_start_monday", "#work_end_monday", "#monday_duration");
    calculateDuration("#work_start_tuesday", "#work_end_tuesday", "#tuesday_duration");
    calculateDuration("#work_start_wednesday", "#work_end_wednesday", "#wednesday_duration");
    calculateDuration("#work_start_thursday", "#work_end_thursday", "#thursday_duration");
    calculateDuration("#work_start_friday", "#work_end_friday", "#friday_duration");
}

// Function to set working hours and calculate duration
function setWorkingHours(startId, endId, durationId) {
    let defaultStart = '';
    let defaultEnd = '';
    
    if ($('#weekly_working_hours').val() === '40') {
        defaultStart = '08:00';
        defaultEnd = '16:00';
    } else if ($('#weekly_working_hours').val() === '30') {
        defaultStart = '09:00';
        defaultEnd = '15:00';
    }

    $(`${startId}`).timepicker({
        minTime: '07:00',
        maxTime: '17:30',
        listWidth: 1,
        show2400: true,
        timeFormat: 'H:i'
    }).val(defaultStart);

    $(`${endId}`).timepicker({
        minTime: '07:30',
        maxTime: '18:00',
        listWidth: 1,
        show2400: true,
        timeFormat: 'H:i'
    }).val(defaultEnd);

    $(`${startId}, ${endId}`).off('change').on('change', function () {
        calculateDuration(startId, endId, durationId);
    });
}

// Function to calculate duration based on start and end times
function calculateDuration(startId, endId, durationId) {
    let start = moment($(startId).val(), 'HH:mm');
    let end = moment($(endId).val(), 'HH:mm');

    let hours = end.diff(start, 'hours');
    let minutes = end.subtract(hours, 'hours').diff(start, 'minutes');
    let paddedMinutes = String(minutes).padStart(2, '0');

    let hoursValue = isNaN(hours) ? '' : hours;
    let minutesValue = isNaN(paddedMinutes) ? '' : paddedMinutes;
    let finalValue = '';
    if (hoursValue < 0 || minutesValue < 0) {
        finalValue = (hoursValue === '' && minutesValue === '') ? '' : `-${Math.abs(hoursValue)}:${String(Math.abs(minutesValue)).padStart(2, '0')}`;
    } else {
        finalValue = (hoursValue === '' && minutesValue === '') ? '' : `${hoursValue}:${minutesValue}`;
    }

    $(durationId).val(finalValue);
}
// End of setting working hours

// Filtering available tools
function updateAvailableTools() {
    let options = [];
    let selectedValues = $('#required_tools').val();
    let previousSelectedValues = $('#available_tools').val();

    if (selectedValues) {
        selectedValues.forEach(function(value) {
            var text = $('#required_tools option[value="' + value + '"]').text();
            options.push({ id: value, text: text });
        });
    }

    $('#available_tools').empty().select2({
        data: options
    });

    if (previousSelectedValues) {
        $('#available_tools').val(previousSelectedValues).trigger('change');
    }
    updateInventoryNumbersOfAvailableTools();
}
updateAvailableTools();
// End of filtering available tools

// Filtering inventory numbers of available tools
function updateInventoryNumbersOfAvailableTools() {
    let previousSelectedOptions = [];

    $(document).off('change', '#available_tools').on('change', '#available_tools', function (e) {
        let currentSelectedOptions = $(this).val() || [];
        
        // Find out which options have been deselected
        let deselectedOptions = previousSelectedOptions.filter(option => !currentSelectedOptions.includes(option));

        // Remove the input fields corresponding to the deselected options
        if (deselectedOptions.length === 0) {
            $('[id^="group_inventory_numbers_of_available_tools_"]').remove();
        } else {
            deselectedOptions.forEach(option => {
                $('#group_inventory_numbers_of_available_tools_' + option).remove();
            });
        }

        let data = $(this).select2('data');
        if (data.length > 0) {
            data.forEach(function(item) {
                let selectedOptionValue = item.id;
                let selectedOptionText = item.text;

                // ID for the dynamic input corresponding to this option
                let inputId = 'inventory_numbers_of_available_tools_' + selectedOptionValue;

                // If option is selected and the input field does not exist, add an input field
                if (selectedOptionValue && !$('#' + inputId).length) {
                    let inputHtml = '<div class="form-group" id="group_' + inputId + '">' +
                                        '<label class="form-label" for="' + inputId + '">' + selectedOptionText + ' leltári száma</label>' +
                                        '<input type="text" id="' + inputId + '" class="form-control" placeholder="Leltári szám" name="' + inputId + '" />' +
                                    '</div>';
                    $('.dynamic-tools-container').append(inputHtml);
                }
            });
            previousSelectedOptions = currentSelectedOptions;
        }
    });
}
// End of filtering inventory numbers of available tools

function filterByPosition()
{
    filterStudentStatus();
    $('#position_id').on('change', filterStudentStatus);
}
function filterStudentStatus() {
    let selectedPositionName = $('#position_id option:selected').text();

    if (selectedPositionName === 'egyetemi hallgató' || selectedPositionName === 'tudományos segédmunkatárs') {
        $('#student_status_verification').prop('disabled', false).parent('div').show();
    } else {
        $('#student_status_verification').prop('disabled', true).parent('div').hide();
    }
}

function revalidateOnChange(fv, targetId) {
    $('#' + targetId).on('change', function() {
        fv.revalidateField(targetId);
    });
}

function enableOnChange(fv, targetId, changerId, condition) {
    if ($('#' + targetId).length === 0) {
        return;
    }

    condition() ? fv.enableValidator(targetId) : fv.disableValidator(targetId);

    $('#' + changerId).on('change', function() {
        condition() ? fv.enableValidator(targetId) : fv.disableValidator(targetId);
        fv.revalidateField(targetId);
    });
}

function getGrossSalarySum() {
    let sum = 0;
    let fields = ['base_salary_monthly_gross_1', 'base_salary_monthly_gross_2', 'base_salary_monthly_gross_3', 'health_allowance_monthly_gross_4', 'management_allowance_monthly_gross_5', 'extra_pay_1_monthly_gross_6', 'extra_pay_2_monthly_gross_7'];

    fields.forEach(function(field) {
        sum += parseInt($('#' + field).val().replace(/\s/g, '')) || 0;
    });

    return sum;
}

// Közös függvény a kezdő dátum beállításához
function updateStartDateSettings() {
    let selectedPositionName = $('#position_id option:selected').text();
    let citizenship = $("#citizenship").val();
    let startDate;
    
    if (selectedPositionName === 'egyetemi hallgató') {
        // Egyetemi hallgató esetén mindig +3 hét
        startDate = '+21D';
    } else {
        // Más munkakörök esetén az állampolgárság szerint
        startDate = citizenship == 'Harmadik országbeli' ? '+60D' : '+21D';
    }
    
    // A már meglévő dátumot mentsük
    let currentDate = $("#employment_start_date").datepicker('getDate');
    
    // Állítsuk be az új kezdő dátum feltételeket
    $("#employment_start_date").datepicker('destroy').datepicker({
        format: "yyyy.mm.dd",
        startDate: isTitkar9Role ? null : startDate,
        endDate: '+30Y',
        language: 'hu',
        weekStart: 1,
        autoclose: true,
    });
    
    // Ha volt korábban beállított dátum és az még mindig valid, állítsuk vissza
    if (currentDate) {
        try {
            $("#employment_start_date").datepicker('setDate', currentDate);
        } catch (e) {
            // Ha nem valid a korábbi dátum, töröljük
            $("#employment_start_date").datepicker('setDate', null);
        }
    }
    
    // Frissítsük a végdátumot is
    updateEndDateBasedOnPosition();
}

// Függvény a végdátum frissítéséhez
function updateEndDateBasedOnPosition() {
    let startDate = $("#employment_start_date").datepicker('getDate');
    if (startDate) {
        let selectedPositionName = $('#position_id option:selected').text();
        let endDate;
        
        if (selectedPositionName === 'egyetemi hallgató') {
            // Egyetemi hallgató esetén 1 hónap - 1 nap
            endDate = isTitkar9Role ? 
                    moment(startDate).add(1, 'days').toDate() : 
                    moment(startDate).add(1, 'months').subtract(1, 'days').toDate();
        } else {
            // Egyéb munkakörök esetén 6 hónap - 1 nap
            endDate = isTitkar9Role ? 
                    moment(startDate).add(1, 'days').toDate() : 
                    moment(startDate).add(6, 'months').subtract(1, 'days').toDate();
        }
        
        $("#employment_end_date").datepicker('setDate', null);
        $("#employment_end_date").datepicker('setStartDate', endDate);
    }
}

// before submit validation functions
function validateCostCenterSum() {
    const raw = parseFloat(getGrossSalarySum()) || 0;
    const sum = Math.round(raw);
    return sum % 1000 === 0;
}

function validateWorkdayTimes() {
    const workdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    for (const day of workdays) {
        const startVal = $('#work_start_' + day).val();
        const endVal   = $('#work_end_'   + day).val();

        // Ha üres a mező, az is hibás
        if (!startVal || !endVal) {
            return false;
        }

        // Pontos, strict formátum ellenőrzés
        const start = moment(startVal, 'HH:mm', true);
        const end   = moment(endVal,   'HH:mm', true);

        // invalid date vagy fordított sorrend → false
        if (!start.isValid() || !end.isValid() || start.isAfter(end)) {
            return false;
        }
    }

    // Ha végig semmi gond nem volt:
    return true;
}

function validateWorkingHours() {
    const fields = [
      'monday_duration', 'tuesday_duration',
      'wednesday_duration', 'thursday_duration',
      'friday_duration'
    ];
    // Összeg percben:
    let sumMinutes = 0;
    
    fields.forEach(field => {
      const val = $('#' + field).val() || "0:0";
      const [hStr, mStr] = val.split(':');
      const hours   = parseInt(hStr, 10) || 0;
      const minutes = parseInt(mStr, 10) || 0;
      sumMinutes += hours * 60 + minutes;
    });

    // Hétről is percekben:
    const weeklyVal = ($('#weekly_working_hours').val() || "0:0").split(':');
    const weeklyHours   = parseInt(weeklyVal[0], 10) || 0;
    const weeklyMinutes = parseInt(weeklyVal[1], 10) || 0;
    const expectedMinutes = weeklyHours * 60 + weeklyMinutes;

    return sumMinutes === expectedMinutes;
}

// Fields to use numeric transformer for
const fields = [
        'applicants_female_count',
        'applicants_male_count',
        'base_salary_monthly_gross_1',
        'base_salary_monthly_gross_2',
        'base_salary_monthly_gross_3',
        'health_allowance_monthly_gross_4',
        'management_allowance_monthly_gross_5',
        'extra_pay_1_monthly_gross_6',
        'extra_pay_2_monthly_gross_7'
    ];
const transformers = fields.reduce((acc, fieldName) => {
    acc[fieldName] = createNumericTransformer(fieldName);
    return acc;
}, {});

function validateEmployeeRecruitment() {
    return FormValidation.formValidation(
        document.getElementById('new-recruitment'),
        {
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a nevet'
                        },
                        stringLength: {
                            max: 100,
                            message: 'A név nem lehet hosszabb 100 karakternél'
                        }
                    }
                },
                birth_date: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a születési dátumot'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        }
                    }
                },
                social_security_number: {
                    validators: {
                        callback: {
                            message: 'Kérjük, add meg a TAJ számot',
                            callback: function(input) {
                                // Ha az állampolgárság "Harmadik országbeli", akkor nem kötelező
                                if ($('#citizenship').val() === 'Harmadik országbeli') {
                                    return true;
                                }
                                
                                // Egyébként kötelező és megfelelő formátumú kell legyen
                                if (!input.value) {
                                    return {
                                        valid: false,
                                        message: 'Kérjük, add meg a TAJ számot'
                                    };
                                }
                                
                                // Formátum ellenőrzése: 123 456 789
                                var regex = /^[0-9]{3}\s[0-9]{3}\s[0-9]{3}$/;
                                if (!regex.test(input.value)) {
                                    return {
                                        valid: false,
                                        message: 'A TAJ szám formátuma: 123 456 789 kell, hogy legyen'
                                    };
                                }
                                
                                return true;
                            }
                        }
                    }
                },
                address: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a lakcímet'
                        },
                        stringLength: {
                            max: 1000,
                            message: 'A név nem lehet hosszabb 1000 karakternél'
                        }
                    }
                },
                applicants_female_count: {
                    validators: {
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#job_ad_exists').is(':checked')) {
                                    var value = cleaveInstances[input.field].getRawValue();
                                    if (value != '' && value >= 0 && value <= 1000) {
                                        return {
                                            valid: true,
                                            message: 'Az érték 0 és 1000 között lehet'
                                        };
                                    } else {
                                        return {
                                            valid: false,
                                            message: 'Kérjük, add meg a női jelentkezők számát'
                                        };
                                    }
                                } else {
                                    return {
                                        valid: true
                                    };
                                }
                            }
                        }
                    }
                },
                applicants_male_count: {
                    validators: {
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#job_ad_exists').is(':checked')) {
                                    var value = cleaveInstances[input.field].getRawValue();
                                    if (value != '' && value >= 0 && value <= 1000) {
                                        return {
                                            valid: true,
                                            message: 'Az érték 0 és 1000 között lehet'
                                        };
                                    } else {
                                        return {
                                            valid: false,
                                            message: 'Kérjük, add meg a férfi jelentkezők számát'
                                        };
                                    }
                                } else {
                                    return {
                                        valid: true
                                    };
                                }
                            }
                        }
                    }
                },
                workgroup_id_1: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a Csoport 1-et'
                        }
                    }
                },
                workgroup_id_2: {
                    validators: {
                        // ha nincs kiválasztva (== -1), az is OK
                        callback: {
                            callback: function(input) {
                                const wg1 = $('#workgroup_id_1').val();
                                const wg2 = input.value;
                                if (wg2 === '' || wg2 === '-1') {
                                    return true;
                                }
                                return wg1 !== wg2;
                            },
                            message: 'A Csoport 1 és Csoport 2 nem lehet ugyanaz'
                        }
                    }
                },
                position_id: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, válaszd ki a munkakört'
                        }
                    }
                },
                job_description_file: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, töltsd fel a munkaköri leírást'
                        }
                    }
                },
                task: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a feladat leírást'
                        },
                        stringLength: {
                            min: 25,
                            max: 1000,
                            message: 'A feladat leírásának 25 és 1000 karakter között kell lennie'
                        }
                    }
                },
                employment_start_date: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a jogviszony kezdetét'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        },
                        callback: {
                            message: 'A dátum nem lehet korábbi az aktuális dátumnál',
                            callback: function(input) {
                                var startDate = moment(input.value, 'YYYY.MM.DD');
                                if (isTitkar9Role) {
                                    var minDate = moment().startOf('day');
                                    return startDate.isSameOrAfter(minDate);
                                } else {
                                    var minDate = $("#citizenship").val() == 'Harmadik országbeli'
                                        ? moment().add(60, 'days').startOf('day')
                                        : moment().add(21, 'days').startOf('day');
                                    return startDate.isSameOrAfter(minDate);
                                }
                            }
                        }
                    }
                },
                employment_end_date: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a jogviszony végét'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        },
                        callback: {
                            message: 'A jogviszony vége nem lehet korábbi, mint a jogviszony kezdete',
                            callback: function(input) {
                                var startDate = moment($("#employment_start_date").val(), 'YYYY.MM.DD');
                                var endDate = moment(input.value, 'YYYY.MM.DD');
                                
                                return endDate.isAfter(startDate);
                            }
                        }
                    }
                },
                base_salary_cost_center_1: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                base_salary_monthly_gross_1: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        between: {
                            min: 1000,
                            max: 3000000,
                            message: 'Az érték 1000 és 3 000 000 között lehet'
                        }
                    }
                },
                base_salary_cost_center_2: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                base_salary_monthly_gross_2: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#base_salary_cost_center_2').val()) {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() >= 1000 && cleaveInstances[input.field].getRawValue() <= 3000000,
                                        message: 'Az érték 1000 és 3 000 000 között lehet'
                                    };
                                } else {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == 0,
                                        message: 'Az érték 0 lehet'
                                    }
                                }
                            }
                        }
                    }
                },
                base_salary_cost_center_3: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                base_salary_monthly_gross_3: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#base_salary_cost_center_3').val()) {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() >= 1000 && cleaveInstances[input.field].getRawValue() <= 3000000,
                                        message: 'Az érték 1000 és 3 000 000 között lehet'
                                    };
                                } else {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == 0,
                                        message: 'Az érték 0 lehet'
                                    }
                                }
                            }
                        }
                    }
                },
                health_allowance_cost_center_4: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                health_allowance_monthly_gross_4: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                var weeklyWorkingHours = $('#weekly_working_hours').val();
                                var exactValue = weeklyWorkingHours * 500;
                                if ($('#health_allowance_cost_center_4').val()) {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == exactValue,
                                        message: 'Az érték csak ' + exactValue + ' lehet'
                                    };
                                } else {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == 0,
                                        message: 'Az érték 0 lehet'
                                    }
                                }
                            }
                        }
                    }
                },
                management_allowance_cost_center_5: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                management_allowance_monthly_gross_5: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#management_allowance_cost_center_5').val()) {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() >= 1000 && cleaveInstances[input.field].getRawValue() <= 300000,
                                        message: 'Az érték 1000 és 300 000 között lehet'
                                    };
                                } else {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == 0,
                                        message: 'Az érték 0 lehet'
                                    }
                                }
                            }
                        }
                    }
                },
                management_allowance_end_date: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az időtartam végét'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        },
                        callback: {
                            message: 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
                            callback: function(input) {
                                var endDate = moment(input.value, 'YYYY.MM.DD');
                                var maxDate = moment().add(4, 'years');
                
                                return endDate.isSameOrBefore(maxDate);
                            }
                        }
                    }
                },
                extra_pay_1_cost_center_6: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                extra_pay_1_monthly_gross_6: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#extra_pay_1_cost_center_6').val() != "") {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() >= 1000 && cleaveInstances[input.field].getRawValue() <= 300000,
                                        message: 'Az érték 1000 és 300 000 között lehet'
                                    };
                                } else {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == 0,
                                        message: 'Az érték 0 lehet'
                                    }
                                }
                            }
                        }
                    }
                },
                extra_pay_1_end_date: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az időtartam végét'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        },
                        callback: {
                            message: 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
                            callback: function(input) {
                                var endDate = moment(input.value, 'YYYY.MM.DD');
                                var maxDate = moment().add(4, 'years');
                
                                return endDate.isSameOrBefore(maxDate);
                            }
                        }
                    }
                },
                extra_pay_2_cost_center_7: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a költséghelyet'
                        }
                    }
                },
                extra_pay_2_monthly_gross_7: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg havi bruttó bér összegét'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        callback: {
                            callback: function(input) {
                                if ($('#extra_pay_2_cost_center_7').val() != "") {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() >= 1000 && cleaveInstances[input.field].getRawValue() <= 300000,
                                        message: 'Az érték 1000 és 300 000 között lehet'
                                    };
                                } else {
                                    return {
                                        valid: cleaveInstances[input.field].getRawValue() == 0,
                                        message: 'Az érték 0 lehet'
                                    }
                                }
                            }
                        }
                    }
                },
                extra_pay_2_end_date: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az időtartam végét'
                        },
                        date: {
                            format: 'YYYY.MM.DD',
                            message: 'Kérjük, valós dátumot adj meg',
                        },
                        callback: {
                            message: 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
                            callback: function(input) {
                                var endDate = moment(input.value, 'YYYY.MM.DD');
                                var maxDate = moment().add(4, 'years');
                
                                return endDate.isSameOrBefore(maxDate);
                            }
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az email címet'
                        },
                        emailAddress: {
                            message: 'Kérjük, valós email címet adj meg'
                        },
                        stringLength: {
                            max: 255,
                            message: 'Az email nem lehet hosszabb 255 karakternél'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9._%+-]+@ttk\.hu$/,
                            message: 'Csak @ttk.hu-ra végződő email cím adható meg'
                        }
                    }
                },
                entry_permissions: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, válaszd ki a szükséges belépési jogosultságokat'
                        }
                    }
                },
                license_plate: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a rendszámot'
                        },
                        stringLength: {
                            max: 9,
                            message: 'A rendszám nem lehet hosszabb 9 karakternél'
                        }
                    }
                
                },
                employee_room: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, válaszd ki a dolgozószobát'
                        }
                    }
                },
                phone_extension: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a telefonszámot'
                        },
                        integer: {
                            message: 'Kérjük, csak egész számot adj meg'
                        },
                        between: {
                            min: 400,
                            max: 999,
                            message: 'Az érték 400 és 999 között kell legyen'
                        }
                    }
                },
                inventory_numbers_of_available_tools_asztal: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az asztal leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_szek: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a szék leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_asztali_szamitogep: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az asztali számítógép leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_laptop: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a laptop leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_laptop_taska: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a laptop táska leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_monitor: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a monitor leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_billentyuzet: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a billentyűzet leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_eger: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg az egér leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_dokkolo: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a dokkoló leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                inventory_numbers_of_available_tools_mobiltelefon: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a mobiltelefon leltári számát'
                        },
                        stringLength: {
                            max: 30,
                            message: 'A leltári szám nem lehet hosszabb 30 karakternél'
                        },
                        regexp: {
                            regexp: /^[0-9 -]+$/,
                            message: 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat'
                        }
                    }
                },
                personal_data_sheet_file: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, töltsd fel a személyi adatlapot'
                        }
                    }
                },
                student_status_verification_file: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, töltsd fel a hallgatói jogviszony igazolást'
                        }
                    }
                },
                certificates_file: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, töltsd fel a bizonyítványokat'
                        }
                    }
                },
                commute_support_form_file: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, töltsd fel a munkába járási adatlapot'
                        }
                    }
                },
                initiator_comment: {
                    validators: {
                        stringLength: {
                            max: 2000,
                            message: 'A megjegyzés nem lehet hosszabb 2000 karakternél'
                        }
                    }
                },
            },
            plugins: {
                transformer: new FormValidation.plugins.Transformer(transformers),
                bootstrap: new FormValidation.plugins.Bootstrap5(),
            },
        }
    ).on('core.field.invalid', function(field) {
        $(`#${field}`).next().addClass('is-invalid');
    }).on('core.field.valid', function(field) {
        $(`#${field}`).next().removeClass('is-invalid');
    });
}

function createNumericTransformer(fieldName) {
    return {
        integer: function(field, element, validator) {
            return element.value.replace(/\s+/g, '');
        },
        between: function(field, element, validator) {
            return element.value.replace(/\s+/g, '');
        }
    };
}