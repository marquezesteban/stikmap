document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const title = form.dataset.confirmDelete || 'esta canción';

    if (!window.confirm(`¿Eliminar “${title}”? Esta acción no se puede deshacer.`)) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('[data-confirm-marker-delete]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const marker = form.dataset.confirmMarkerDelete || 'esta marca';

    if (!window.confirm(`¿Eliminar la marca ${marker}?`)) {
      event.preventDefault();
    }
  });
});

const notice = document.querySelector('[data-auto-dismiss]');
if (notice) {
  window.setTimeout(() => notice.classList.add('is-hiding'), 3200);
}

document.querySelectorAll('[data-audio-input]').forEach((input) => {
  input.addEventListener('change', () => {
    const form = input.closest('form');
    const label = form?.querySelector('[data-file-label]');
    const file = input.files?.[0];

    if (label && file) {
      label.textContent = file.name;
    }
  });
});
