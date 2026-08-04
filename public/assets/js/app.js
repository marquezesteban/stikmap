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

document.querySelectorAll('[data-lyrics-tools]').forEach((tools) => {
  const textarea = tools.parentElement?.querySelector('[name="lyrics"]');
  const sectionSelect = tools.querySelector('[data-lyrics-section]');
  const insertButton = tools.querySelector('[data-lyrics-insert]');

  if (!textarea || !sectionSelect || !insertButton) return;

  sectionSelect.addEventListener('change', () => {
    insertButton.disabled = sectionSelect.value === '';
  });

  insertButton.addEventListener('click', () => {
    const section = sectionSelect.value;
    if (section === '') return;

    const selectedOption = sectionSelect.selectedOptions[0];
    let label = section;

    if (selectedOption?.hasAttribute('data-numbered')) {
      const pattern = new RegExp(`^\\[${section}(?: (\\d+))?\\]$`, 'gmi');
      const numbers = Array.from(textarea.value.matchAll(pattern), (match) => Number(match[1] || 1));
      label = `${section} ${numbers.length === 0 ? 1 : Math.max(...numbers) + 1}`;
    }

    const tag = `[${label}]`;
    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? start;
    const before = textarea.value.slice(0, start);
    const after = textarea.value.slice(end);
    const prefix = before === '' || before.endsWith('\n') ? '' : '\n';
    const suffix = after === '' || after.startsWith('\n') ? '' : '\n';
    const insertion = `${prefix}${tag}${suffix}`;

    textarea.setRangeText(insertion, start, end, 'end');
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
    sectionSelect.value = '';
    insertButton.disabled = true;
  });
});

document.querySelectorAll('[data-print-trigger]').forEach((button) => {
  button.addEventListener('click', () => window.print());
});
