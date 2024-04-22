class DropzoneConfig {
    static previewTemplate = `
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

    static genericDropzoneOptions = {
        previewTemplate: DropzoneConfig.previewTemplate,
        parallelUploads: 1,
        addRemoveLinks: true,

        dictRemoveFile: 'Törlés',
        dictFileTooBig: 'A fájl mérete túl nagy ({{filesize}}MiB). Maximum: {{maxFilesize}}MiB.',
        dictMaxFilesExceeded: 'Maximum {{maxFiles}} fájl tölthető fel.',
        dictInvalidFileType: 'Nem tölthető fel ilyen típusú fájl.',
        dictResponseError: 'Szerver hiba történt. Kérjük próbálja újra később.',
        dictCancelUpload: 'Mégse'
    };
}
