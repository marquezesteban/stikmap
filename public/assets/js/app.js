document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const title = form.dataset.confirmDelete || 'esta canción';

    if (!window.confirm(`¿Eliminar “${title}”? Esta acción no se puede deshacer.`)) {
      event.preventDefault();
    }
  });
});

const notice = document.querySelector('[data-auto-dismiss]');
if (notice) {
  window.setTimeout(() => notice.classList.add('is-hiding'), 3200);
}
