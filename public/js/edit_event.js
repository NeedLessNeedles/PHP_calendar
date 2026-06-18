document.addEventListener('DOMContentLoaded', () => {

    const buttons = document.querySelectorAll('.edit-event-btn');

    const modal = new bootstrap.Modal(
        document.getElementById('editModal')
    );

    const form = document.getElementById('edit_event_form');

    buttons.forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;

            const response = await fetch(`/~21_miszczyk/app/event/${event.id}/json`);
            const event = await response.json();

            const titleField = form.querySelector('[id$="_title"]');
            titleField.value = event.title ?? '';

            const descriptionField = form.querySelector('[id$="_description"]');
            descriptionField.value = event.description ?? '';

            const locationField = form.querySelector('[id$="_location"]');
            locationField.value = event.location ?? '';

            const startDateField = form.querySelector('[id$="_startDate"]');
            startDateField.value = event.startDate ?? '';

            const endDateField = form.querySelector('[id$="_endDate"]');
            endDateField.value = event.endDate ?? '';

            const categoryField = form.querySelector('[id$="_category"]');
            categoryField.value = event.category ?? '';

            form.action = `/event/${event.id}/edit`;

            modal.show();
        });
    });
});
