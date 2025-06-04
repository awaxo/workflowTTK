import Dropzone from 'dropzone';

class DropzoneManager {
    static previewTemplate = `
        <div class="dz-preview dz-file-preview">
            <div class="dz-details">
                <div class="dz-thumbnail">
                    <img data-dz-thumbnail />
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
        acceptedFiles: '.pdf,application/pdf',
        paramName: 'file',
        dictRemoveFile: 'Törlés',
        dictFileTooBig: 'A fájl mérete túl nagy ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.',
        dictMaxFilesExceeded: 'Maximum {{maxFiles}} fájl tölthető fel.',
        dictInvalidFileType: 'Nem tölthető fel ilyen típusú fájl.',
        dictResponseError: 'Szerver hiba történt. Kérjük próbálja újra később.',
        dictCancelUpload: 'Mégse'
    };

    static init(elementId, options = {}) {
        if (!document.getElementById(elementId)) {
            console.warn(`Element #${elementId} not found`);
            return;
        }

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
            acceptedFiles: '.pdf,application/pdf',
            paramName: 'file'
        });

        // Force update the accepted files immediately
        dropzoneUpload.hiddenFileInput.setAttribute('accept', '.pdf,application/pdf');

        // Add file type validation at the browser level
        dropzoneUpload.on("addedfile", function(file) {
            // Additional client-side validation for PDF only
            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                this.removeFile(file);
                alert('Csak PDF fájlok tölthetők fel!');
                return;
            }
        });

        dropzoneUpload.on("success", function(file, response) {
            const $fileInput = $(`#${elementId}_file`);
            $fileInput.val(response.fileName);
            $fileInput.attr('data-original-name', file.name);
            $fileInput.trigger('change');
        });

        dropzoneUpload.on("removedfile", function(file) {
            const $fileInput = $(`#${elementId}_file`);            
            $fileInput.val('');
            $fileInput.attr('data-original-name', '');
            $fileInput.trigger('change');
        });

        dropzoneUpload.on("error", function(file, message) {
            // Handle file type errors specifically
            if (typeof message === 'string' && message.includes('type')) {
                console.error('File type error:', message);
            }
        });

        return dropzoneUpload;
    }
}

export default DropzoneManager;