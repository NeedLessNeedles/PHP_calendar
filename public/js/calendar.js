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
        },
        eventDidMount: function(info) {
            const status = info.event.extendedProps.status;

            if (status === 'approved') {
                info.el.style.backgroundColor = '#0d6efd';
            }

            if (status === 'pending') {
                info.el.style.backgroundColor = '#ffc107';
                info.el.style.color = '#000';
                info.el.style.opacity = '0.9';
            }
        }
    });
    calendar.render();
});
