import moment from 'moment';
import DropzoneManager from '../../../../../resources/js/dropzone-manager';

$(function () {
    // set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true
        });
    });

    const previewTemplate = `
    <div class="dz-preview dz-file-preview">
        <div class="dz-details">
        <div class="dz-thumbnail">
            <img data-dz-thumbnail>
            <span class="dz-nopreview">Nincs előnézet</span>
            <div class="dz-success-mark"></div>
            <div class="dz-error-mark"></div>
            <div class="dz-error-message"><span data-dz-errormessage></span></div>
            <div class="progress">
            <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
            </div>
        </div>
        <div class="dz-filename" data-dz-name></div>
        <div class="dz-size" data-dz-size></div>
        </div>
    </div>`;

    const genericDropzoneOptions = {
        previewTemplate: previewTemplate,
        parallelUploads: 1,
        addRemoveLinks: true,

        dictRemoveFile: 'Törlés',
        dictFileTooBig: 'A fájl mérete túl nagy ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.',
        dictMaxFilesExceeded: 'Maximum {{maxFiles}} fájl tölthető fel.',
        dictInvalidFileType: 'Nem tölthető fel ilyen típusú fájl.',
        dictResponseError: 'Szerver hiba történt. Kérjük próbálja újra később.',
        dictCancelUpload: 'Mégse'
    };

    // set datepicker date fields
    $("#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date").datepicker({
        format: "yyyy.mm.dd",
        startDate: new Date(),
        endDate: '+4Y',
    });

    $("#citizenship").on('change', function() {
        var startDate = $(this).val() === 'Harmadik országbeli' ? '+3M' : '+21D';
        $("#employment_start_date").datepicker('setStartDate', startDate);
    });
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

    // Function to set working hours and calculate duration
    function setWorkingHours(startId, endId, durationId) {
        $(`${startId}`).timepicker({
            minTime: '07:00',
            maxTime: '17:30',
            listWidth: 1,
            show2400: true,
            timeFormat: 'H:i'
        }).val('08:00');

        $(`${endId}`).timepicker({
            minTime: '07:30',
            maxTime: '18:00',
            listWidth: 1,
            show2400: true,
            timeFormat: 'H:i'
        }).val('16:00');

        $(`${startId}, ${endId}`).on('change', function () {
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

        $(durationId).val(`${hours}:${paddedMinutes}`);
    }

    // add or remove available_tools based on selected required_tools
    $('#required_tools').on('changed.bs.select', function () {
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

    // Initialize popover on a target element
    citizenshipPopover();
    // Filter position options based on selected type
    filterPositionOptions();
    // Filter employee room options based on selected workgroups
    filterEmployeeRoomOptions();


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
                alert('Mentés sikeres!');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors here
            }
        });
    });
});

function citizenshipPopover() {
    $('#citizenship').on('change', function () {
        if ($(this).val() === '2') {
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

// Filtering employee room
let originalEmployeeRoomOptions = $('#employee_room').html();

function filterEmployeeRoomOptions() {
    if (!originalEmployeeRoomOptions) {
        originalEmployeeRoomOptions = $('#employee_room').html();
    }

    $('#workgroup_id_1, #workgroup_id_2').on('change', filterEmployeeRoomIdOptions);

    // Trigger change to refresh the employee_room combo based on current selections
    $('#workgroup_id_1, #workgroup_id_2').trigger('change');
}

function filterEmployeeRoomIdOptions() {
    let selectedWorkgroup1 = $('#workgroup_id_1').find(':selected').data('workgroup');
    let selectedWorkgroup2 = $('#workgroup_id_2').find(':selected').data('workgroup');

    // Restore the original options in the employee room select
    $('#employee_room').html(originalEmployeeRoomOptions);

    // Filter and remove options
    $('#employee_room option').filter(function() {
        let optionWorkgroup = $(this).data('workgroup');
        return optionWorkgroup !== selectedWorkgroup1 && optionWorkgroup !== selectedWorkgroup2;
    }).remove();

    // Refresh Select2 to apply changes
    $('#employee_room').select2();
    // Clear the selection
    $('#employee_room').val(null).trigger('change');
}
// End of filtering employee room

// Filtering available tools
function updateAvailableTools() {
    var optionsHtml = '';

    $('#required_tools option:selected').each(function() {
        optionsHtml += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
    });

    $('#available_tools').html(optionsHtml);
    $('#available_tools').selectpicker('destroy').html(optionsHtml).selectpicker();
    updateInventoryNumbersOfAvailableTools();
}
updateAvailableTools();
// End of filtering available tools

// Filtering inventory numbers of available tools
function updateInventoryNumbersOfAvailableTools() {
    $('#available_tools').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        // Get the selected option value and text
        var selectedOptionValue = $(this).find('option').eq(clickedIndex).val();
        var selectedOptionText = $(this).find('option').eq(clickedIndex).text();

        // ID for the dynamic input corresponding to this option
        var inputId = 'inventory_numbers_of_available_tools_' + selectedOptionValue;

        if (isSelected) {
            // If option is selected, add an input field
            var inputHtml = '<div class="form-group" id="group_' + inputId + '">' +
                                '<label class="form-label" for="' + inputId + '">' + selectedOptionText + ' leltári száma</label>' +
                                '<input type="text" id="' + inputId + '" class="form-control" placeholder="Leltári szám" />' +
                            '</div>';
            $('.dynamic-tools-container').append(inputHtml);
        } else {
            // If option is deselected, remove the corresponding input field
            $('#group_' + inputId).remove();
        }
    });
}
// End of filtering inventory numbers of available tools