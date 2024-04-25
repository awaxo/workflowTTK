import moment from 'moment';
import DropzoneManager from '/resources/js/dropzone-manager';

$(function () {
    // set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true
        });
    });

    // set datepicker date fields
    $("#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: new Date(),
        endDate: '+4Y',
    });

    $('#job_ad_exists').on('change', function() {
        toggleApplicantCountInputs($(this).is(':checked'));
    });

    $('#employment_type').on('change', function() {
        toggleTaskInput($(this).val());
    });

    $("#citizenship").on('change', function() {
        var startDate = $(this).val() === 'Harmadik országbeli' ? '+3M' : '+21D';
        $("#employment_start_date").datepicker('setStartDate', startDate);
    });
    
    // set datepicker date fields
    $("#employment_start_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: '+21D',
        endDate: '+30Y',
    });
    $("#employment_start_date").on('change', function() {
        var startDate = $(this).datepicker('getDate');
        if (startDate) {
            startDate.setMonth(startDate.getMonth() + 6);
            startDate.setDate(startDate.getDate() + 20);
    
            $("#employment_end_date").datepicker('setStartDate', startDate);
        }
    });
    $("#employment_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: '+200D',
        endDate: '+30Y',
    });
    // set datepicker date fields

    $('#weekly_working_hours').on('change', function() {
        setWorkingHoursWeekdays();
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


    $('.btn-submit').on('click', function (event) {
        event.preventDefault();

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
                // redirect needed on the server side
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#errorAlertMessage').text('Hiba történt az ügy rögzítése során!');
                $('#errorAlert').removeClass('d-none');
                console.log(textStatus, errorThrown);
            }
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
        return optionWorkgroup !== selectedWorkgroup1;
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
    let instituteCode1 = String(selectedWorkgroup1).charAt(0);
    let instituteCode2 = String(selectedWorkgroup2).charAt(0);

    $('#entry_permissions').html(originalEntryPermissionsOptions);
    $('#employee_room').html(originalEmployeeRoomOptions);

    $('#entry_permissions option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        let optionInstituteCode = String(optionWorkgroup).charAt(0);

        return $(this).val() !== 'auto' && $(this).val() !== 'kerekpar' && optionInstituteCode !== instituteCode1 && optionInstituteCode !== instituteCode2;
    }).remove();

    $('#employee_room option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        return optionWorkgroup !== selectedWorkgroup1 && optionWorkgroup !== selectedWorkgroup2;
    }).remove();

    // Select options where data-workgroup is equal to selectedWorkgroup1 or selectedWorkgroup2
    $('#entry_permissions option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        return $(this).val() !== 'auto' && $(this).val() !== 'kerekpar' && (optionWorkgroup === selectedWorkgroup1 || optionWorkgroup === selectedWorkgroup2);
    }).prop('selected', true);
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
        defaultEnd = '16:30';
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
    let finalValue = (hoursValue === '' && minutesValue === '') ? '' : `${hoursValue}:${minutesValue}`;

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
                                        '<input type="text" id="' + inputId + '" class="form-control" placeholder="Leltári szám" />' +
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
    console.log(selectedPositionName);

    if (selectedPositionName === 'egyetemi hallgató' || selectedPositionName === 'tudományos segédmunkatárs') {
        $('#student_status_verification').prop('disabled', false).parent('div').show();
    } else {
        $('#student_status_verification').prop('disabled', true).parent('div').hide();
    }
}