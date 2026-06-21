document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.edit-tag-btn');

    const titleInput = document.getElementById('editTagTitle');
    const form = document.getElementById('editTagForm');

    buttons.forEach(btn => {
        btn.addEventListener('click', function () {

            const id = this.dataset.id;
            const title = this.dataset.title;

            titleInput.value = title;

            form.action = `/tag/${id}/edit`;

        });
    });
});
