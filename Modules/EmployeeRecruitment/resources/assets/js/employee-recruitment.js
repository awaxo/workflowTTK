import moment from 'moment';

$(function () {
    $("#management_allowance_end_date, #extra_pay_1_end_date, #extra_pay_2_end_date").datepicker({
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

    function setWorkingHours(startId, endId, durationId) {
        $(`${startId}`).timepicker({
            minTime: '08:00',
            maxTime: '12:00',
            listWidth: 1,
            show2400: true,
            timeFormat: 'H:i'
        }).val('08:00');

        $(`${endId}`).timepicker({
            minTime: '13:00',
            maxTime: '18:00',
            listWidth: 1,
            show2400: true,
            timeFormat: 'H:i'
        }).val('16:30');

        $(`${startId}, ${endId}`).on('change', function () {
            calculateDuration(startId, endId, durationId);
        });
    }

    function calculateDuration(startId, endId, durationId) {
        let start = moment($(startId).val(), 'HH:mm');
        let end = moment($(endId).val(), 'HH:mm');

        let hours = end.diff(start, 'hours');
        let minutes = end.subtract(hours, 'hours').diff(start, 'minutes');
        let paddedMinutes = String(minutes).padStart(2, '0');

        $(durationId).val(`${hours}:${paddedMinutes}`);
    }
});