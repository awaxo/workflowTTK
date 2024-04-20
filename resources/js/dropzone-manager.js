import Dropzone from 'dropzone';

class DropzoneManager {
    static previewTemplate = `
        <div class="dz-preview dz-file-preview">
            <div class="dz-details">
                <div class="dz-thumbnail">
                    <img data-dz-thumbnail />
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

    static defaultOptions = {
        previewTemplate: this.previewTemplate,
        parallelUploads: 1,
        addRemoveLinks: true,
        maxFilesize: 20,
        maxFiles: 1,
        acceptedFiles: 'application/pdf',
        paramName: 'file',
        dictRemoveFile: 'Törlés',
        dictFileTooBig: 'A fájl mérete túl nagy ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.',
        dictMaxFilesExceeded: 'Maximum {{maxFiles}} fájl tölthető fel.',
        dictInvalidFileType: 'Nem tölthető fel ilyen típusú fájl.',
        dictResponseError: 'Szerver hiba történt. Kérjük próbálja újra később.',
        dictCancelUpload: 'Mégse'
    };

    static init(elementId, options = {}) {
        const uploadContainer = $(`#${elementId}.dropzone`);
        if (uploadContainer.length === 0) {
            console.error(`Dropzone container not found for element #${elementId}`);
            return;
        }

        const dropzoneUpload = Dropzone.getElement(`#${elementId}.dropzone`).dropzone;
        dropzoneUpload.options = Object.assign(dropzoneUpload.options, {
            ...this.defaultOptions,
            maxFilesize: 20,
            maxFiles: 1,
            acceptedFiles: 'application/pdf',
            paramName: 'file'
        });

        dropzoneUpload.on("success", function(file, response) {
            const $contractFileInput = $(`#${elementId}_file`);
            $contractFileInput.val(response.fileName);
            $contractFileInput.attr('data-original-name', file.name);
        });

        dropzoneUpload.on("removedfile", function(file) {
            const $contractFileInput = $(`#${elementId}_file`);
            if (file.name === $contractFileInput.data('original-name')) {
                $contractFileInput.val('');
                $contractFileInput.attr('data-original-name', '');
            }
        });
    }
}

export default DropzoneManager;