import Dropzone from 'dropzone';

$(function () {
    // Set numeral mask to number fields
    $('.numeral-mask').toArray().forEach(function(field){
        new Cleave(field, {
            numeral: true
        });
    });

    // TODO: kirakni pl. egy globális osztályba
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

    // file uploads
    if ($('.dropzone').length > 0) {
        let contractUpload = Dropzone.getElement('.dropzone').dropzone;
        contractUpload.options = Object.assign(contractUpload.options, {
            ...genericDropzoneOptions,
            maxFilesize: 20,
            maxFiles: 1,
            acceptedFiles: 'application/pdf',
            paramName: 'file'
        });

        contractUpload.on("success", (file, response) => {
            $('#contract_file').val(response.fileName);
            $('#contract_file').attr('data-original-name', file.name);
        });

        contractUpload.on("removedfile", (file) => {
            if (file.name === $('#contract_file').data('original-name')) {
                $('#contract_file').val('');
                $('#contract_file').attr('data-original-name', '');    
            }
        });
    }

    $('#approve').on('click', function () {
        if ($('#state').val() === 'hr_lead_approval' && ($('#probation_period').val() < 7 || $('#probation_period').val() > 90)) {
            $('#probationMissing').modal('show');
            return;
        } else if ($('#state').val() === 'employee_signature' && $('#contract_file').val().length === 0) {
            $('#contractMissing').modal('show');
            return;
        }

        $('#approveConfirmation').modal('show');
    });

    $('#confirm_approve').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/approve',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                probation_period: $('#probation_period').val(),
                post_financed_application: $('#post_financed_application').val(),
                contract_file: $('#contract_file').val(),
                message: $('#message').val()
            },
            success: function (response) {
                window.location.href = response.redirectUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $('#reject').on('click', function () {
        var message = $('#message').val();

        if (message.length === 0) {
            $('#messageMissing').modal('show');
        } else {
            $('#rejectConfirmation').modal('show');
        }
    });

    $('#confirm_reject').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/reject',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: $('#message').val()
            },
            success: function (response) {
                window.location.href = response.redirectUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $('#suspend').on('click', function () {
        var message = $('#message').val();

        if (message.length === 0) {
            $('#messageMissing').modal('show');
        } else {
            $('#suspendConfirmation').modal('show');
        }
    });

    $('#confirm_suspend').on('click', function () {
        var recruitmentId = $(this).data('recruitment-id');

        $.ajax({
            url: '/employee-recruitment/' + recruitmentId + '/suspend',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                message: $('#message').val()
            },
            success: function (response) {
                window.location.href = response.redirectUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });

    $('.print-icon').on('click', function() {
        $('#message_parent').removeClass('d-none');
    });
});
