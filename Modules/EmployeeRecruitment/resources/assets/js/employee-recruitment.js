import Dropzone from 'dropzone';
import moment from 'moment';

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
    url: "/file/upload",

    dictRemoveFile: 'Törlés',
    dictFileTooBig: 'A fájl mérete túl nagy ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.',
    dictMaxFilesExceeded: 'Maximum {{maxFiles}} fájl tölthető fel.',
    dictInvalidFileType: 'Nem tölthető fel ilyen típusú fájl.',
    dictResponseError: 'Szerver hiba történt. Kérjük próbálja újra később.',
    dictCancelUpload: 'Mégse'
};

$(function () {
    $("#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date, #employment_start_date, #employment_end_date").datepicker({
        format: "yyyy.mm.dd",
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

    // add or remove inventory_numbers_of_available_tools based on selected available_tools
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
    Dropzone.autoDiscover = false;
    new Dropzone('#job_description', {
        ...genericDropzoneOptions,
        maxFilesize: 20,
        maxFiles: 1,
        acceptedFiles: 'application/pdf',
        paramName: 'file',
        sending: function(file, xhr, formData) {
            console.log("Sending file", file.name);
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("type", "job_description");
        },
    });

    new Dropzone('#personal_data_sheet', {
        ...genericDropzoneOptions,
        maxFilesize: 20,
        maxFiles: 1,
        acceptedFiles: 'application/pdf',
        sending: function(file, xhr, formData) {
            console.log("Sending file", file.name);
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("type", "personal_data_sheet");
        },
    });
    
    new Dropzone('#student_status_verification', {
        ...genericDropzoneOptions,
        maxFilesize: 20,
        maxFiles: 1,
        acceptedFiles: 'application/pdf',
        sending: function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("type", "student_status_verification");
        },
    });

    new Dropzone('#certificates', {
        ...genericDropzoneOptions,
        maxFilesize: 20,
        maxFiles: 5,
        acceptedFiles: 'application/pdf',
        sending: function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("type", "certificates");
        },
    });

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
