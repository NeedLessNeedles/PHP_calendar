document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: '77vh',
        events: '/event/json',

        eventClick: function(info) {
            window.location.href = '/event/' + info.event.id;
        },
        dateClick: function(info) {
            const modalElement = document.getElementById('eventModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            const startInput = document.getElementById('event_startDate');
            if (startInput) {
                startInput.value = info.dateStr + "T12:00";
            }
        }
    });
    calendar.render();
});
