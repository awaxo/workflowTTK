import moment from 'moment';
import DropzoneManager from '/resources/js/dropzone-manager';
import GLOBALS from '../../../../../resources/js/globals.js';
import { min } from 'lodash';

var cleaveInstances = {};
$(function () {
    // set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        var cleave = new Cleave(field, {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: ' ',
        });

        cleaveInstances[field.id] = cleave;
    });

    // set datepicker date fields
    $("#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: new Date(),
        endDate: '+4Y',
        language: 'hu',
        weekStart: 1,
    });

    $('#job_ad_exists').on('change', function() {
        toggleApplicantCountInputs($(this).is(':checked'));
    });

    $('#employment_type').on('change', function() {
        toggleTaskInput($(this).val());
    });

    // set datepicker date fields start
    $("#citizenship").on('change', function() {
        $("#employment_start_date, #employment_end_date").val('');

        var startDate = $(this).val() == 'Harmadik országbeli' ? '+60D' : '+21D';
        $("#employment_start_date").datepicker('destroy').datepicker({
            format: "yyyy.mm.dd",
            startDate: startDate,
            language: 'hu',
            weekStart: 1
        });
        
        $("#employment_end_date").datepicker({
            format: "yyyy.mm.dd",
            startDate: '+6M',
            language: 'hu',
            weekStart: 1
        });
    });
    
    var startDate = $("#citizenship").val() == 'Harmadik országbeli' ? '+60D' : '+21D';
    $("#employment_start_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: startDate,
        endDate: '+30Y',
        language: 'hu',
        weekStart: 1,
        autoclose: true
    });
    $("#employment_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: '+200D',
        endDate: '+30Y',
        language: 'hu',
        weekStart: 1,
        autoclose: true
    });
    $("#employment_start_date").on('change', function() {
        var startDate = $("#employment_start_date").datepicker('getDate');
        if (startDate) {
            var endDate = moment(startDate).add(6, 'months').toDate();
            $("#employment_end_date").datepicker('setDate', null);
            $("#employment_end_date").datepicker('setStartDate', endDate);
        }
    });
    // set datepicker date fields end

    $('#weekly_working_hours').on('change', function() {
        setWorkingHoursWeekdays();
        $('#health_allowance_monthly_gross_4').val('');
    });

    $('#entry_permissions').on('change', function() {
        let selectedOptions = $(this).val() || [];
        let filteredOptions = selectedOptions.filter(option => option !== 'auto' && option !== 'kerekpar');

        $('#employee_room').empty();
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

    // Initially hide the carcinogenic materials use textarea
    $('.planned-carcinogenic-materials').hide();

    $('#work_with_carcinogenic_materials').on('change', function() {
        if($(this).val() === '1') {
            $('.planned-carcinogenic-materials').show();
        } else {
            $('.planned-carcinogenic-materials').hide();
        }
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

    // Initially show the job ad exists inputs
    $('#job_ad_exists').prop('checked', true);
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


    // Submit employee recruitment form
    $('.btn-submit').on('click', function (event) {
        event.preventDefault();

        $('.invalid-feedback').remove();
        let fv = validateEmployeeRecruitment();

        // revalidate fields when their values change
        revalidateOnChange(fv, 'name');
        revalidateOnChange(fv, 'applicants_female_count');
        revalidateOnChange(fv, 'applicants_male_count');
        revalidateOnChange(fv, 'workgroup_id_1');
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
        revalidateOnChange(fv, 'work_with_radioactive_isotopes');
        revalidateOnChange(fv, 'work_with_carcinogenic_materials');
        revalidateOnChange(fv, 'planned_carcinogenic_materials_use');
        revalidateOnChange(fv, 'personal_data_sheet_file');
        revalidateOnChange(fv, 'student_status_verification_file');
        revalidateOnChange(fv, 'certificates_file');
        revalidateOnChange(fv, 'commute_support_form_file');
        

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
        enableOnChange(fv, 'planned_carcinogenic_materials_use', 'work_with_carcinogenic_materials', function() { return $('#work_with_carcinogenic_materials').val() == 1 });
        enableOnChange(fv, 'student_status_verification_file', 'position_id', function() { return $('#position_id').val() == 11 || $('#position_id').val() == 23 });
        enableOnChange(fv, 'commute_support_form_file', 'requires_commute_support', function() { return $('#requires_commute_support').val() == true });

        if (!validateCostCenterSum()) {
            GLOBALS.AJAX_ERROR('Teljes havi bruttó bér összegét ezerre kerekítve szükséges megadni!');
            
            return;
        } else {
            $('.alert-danger').alert('close');
        }

        if (!validateWorkdayTimes()) {
            GLOBALS.AJAX_ERROR('Munkanapok munkaideje kezdetének korábbinak kell lennie, mint a végének!');
            
            return;
        } else {
            $('.alert-danger').alert('close');
        }

        if (!validateWorkingHours()) {
            GLOBALS.AJAX_ERROR('Munkanapok munkaidejének összege nem egyezik a heti munkaóraszámmal!');
            
            return;
        } else {
            $('.alert-danger').alert('close');
        }

        fv.validate().then(function(status) {
            if(status === 'Valid') {
                var formData = {};
                $('#new-recruitment :input').each(function() {
                    var id = $(this).attr('id');
                    var value = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
                    formData[id] = value;
                });

                $.ajax({
                    url: '/employee-recruitment',
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
                var fields = fv.getFields();
                Object.keys(fields).forEach(function(name) {
                    fv.validateField(name)
                        .then(function(status) {
                            if (status === 'Invalid') {
                                console.log('Field:', name, 'Status:', status);
                                GLOBALS.AJAX_ERROR('Hibás adat(ok) vagy hiányzó mező(k) vannak a formon, kérjük ellenőrizd!');
                            }
                        });
                });
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
});

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

    $('#entry_permissions').html(originalEntryPermissionsOptions);
    $('#employee_room').html(originalEmployeeRoomOptions);

    $('#entry_permissions option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        return $(this).val() !== 'auto' && $(this).val() !== 'kerekpar' && optionWorkgroup !== selectedWorkgroup1 && optionWorkgroup !== selectedWorkgroup2;
    }).remove();

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


// before submit validation functions
function validateCostCenterSum() {
    return getGrossSalarySum() % 1000 === 0;
}

function validateWorkdayTimes() {
    let workdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    let isValid = true;

    workdays.forEach(function(day) {
        let start = moment($('#work_start_' + day).val(), 'HH:mm');
        let end = moment($('#work_end_' + day).val(), 'HH:mm');

        if (start.isAfter(end)) {
            isValid = false;
        }
    });

    return isValid;
}

function validateWorkingHours() {
    let sum = 0;
    let fields = ['monday_duration', 'tuesday_duration', 'wednesday_duration', 'thursday_duration', 'friday_duration'];

    fields.forEach(function(field) {
        let duration = $('#' + field).val().split(':');
        sum += parseInt(duration[0]) + parseInt(duration[1]) / 60;
    });

    return sum === parseInt($('#weekly_working_hours').val());
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
                            message: 'Kérjük, add meg a csoportot'
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
                            min: 50,
                            max: 1000,
                            message: 'A feladat leírásának 50 és 1000 karakter között kell lennie'
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
                            max: 100,
                            message: 'Az email nem lehet hosszabb 100 karakternél'
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
                work_with_radioactive_isotopes: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg, hogy fog-e radioaktív izotópokkal dolgozni'
                        }
                    }
                },
                work_with_carcinogenic_materials: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg, hogy fog-e rákkeltő anyagokkal dolgozni'
                        }
                    }
                },
                planned_carcinogenic_materials_use: {
                    validators: {
                        notEmpty: {
                            message: 'Kérjük, add meg a tervezett rákkeltő anyagok listáját'
                        }
                    },
                    stringLength: {
                        max: 10000,
                        message: 'A rákkeltő anyagok listája nem lehet hosszabb 10000 karakternél'
                    },
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