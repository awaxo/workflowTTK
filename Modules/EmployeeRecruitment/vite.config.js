import { glob } from 'glob';

/**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 */
function GetFilesArray(query) {
    return glob.sync(query);
}

const modulePageJsFiles = GetFilesArray('Modules/EmployeeRecruitment/resources/assets/js/*.js');

export const paths = [
    'Modules/EmployeeRecruitment/resources/assets/sass/app.scss',
    ...modulePageJsFiles
];
