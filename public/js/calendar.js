document.addEventListener('DOMContentLoaded', () => {

    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',
        height: '77vh',
        // events: '/event/json',
        //events: EVENTS_URL,
        events: '/~21_miszczyk/app/event/json',
        //events: window.location.pathname.replace('/calendar', '') + '/json',

        eventClick: async function(info) {
            console.log(document.getElementById('editModal'));
            info.jsEvent.preventDefault();
            const modalElement = document.getElementById('editModal');

            const editModal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const form = document.getElementById('edit_event_form');

            const response = await fetch(`/~21_miszczyk/app/event/${info.event.id}/json`);
            const event = await response.json();

            form.querySelector('[name$="[title]"]').value = event.title ?? '';
            form.querySelector('[name$="[description]"]').value = event.description ?? '';
            form.querySelector('[name$="[location]"]').value = event.location ?? '';
            form.querySelector('[name$="[startDate]"]').value = event.startDate ?? '';
            form.querySelector('[name$="[endDate]"]').value = event.endDate ?? '';
            form.querySelector('[name$="[category]"]').value = event.category ?? '';

            form.action = `/event/${info.event.id}/edit`;

            editModal.show();
        },

        dateClick: function(info) {

            const eventModal = new bootstrap.Modal(
                document.getElementById('eventModal')
            );

            const startInput = document.getElementById('event_startDate');

            if (startInput) {
                startInput.value = info.dateStr + "T12:00";
            }

            eventModal.show();
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
