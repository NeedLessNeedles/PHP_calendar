document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.edit-category-btn');

    const titleInput = document.getElementById('editCategoryTitle');
    const form = document.getElementById('editCategoryForm');

    buttons.forEach(btn => {
        btn.addEventListener('click', function () {

            const id = this.dataset.id;
            const title = this.dataset.title;

            titleInput.value = title;

            form.action = `/category/${id}/edit`;

        });
    });
});
