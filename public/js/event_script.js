export async function openEditEventModal(eventId) {

    const modalEl = document.getElementById('editModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('edit_event_form');

    const response = await fetch(`/event/${eventId}/json`);
    const event = await response.json();

    form.querySelector('[name$="[title]"]').value = event.title ?? '';
    form.querySelector('[name$="[description]"]').value = event.description ?? '';
    form.querySelector('[name$="[location]"]').value = event.location ?? '';
    form.querySelector('[name$="[startDate]"]').value = event.startDate ?? '';
    form.querySelector('[name$="[endDate]"]').value = event.endDate ?? '';
    form.querySelector('[name$="[category]"]').value = event.category ?? '';

    form.action = `/event/${eventId}/edit`;

    modal.show();
}
