const editor = document.getElementById('talk-compose');
const confirmDelete = document.getElementById('talk-delete');

if (typeof editor?.showModal === 'function' && confirmDelete) {
  const form = editor.querySelector('form');
  const deleteForm = confirmDelete.querySelector('form');
  const content = form.elements.glaze_note_content;
  const keywords = form.elements.glaze_note_keywords;
  const upload = form.querySelector('input[type="file"]');
  const count = editor.querySelector('[data-glaze-talk-count]');
  const markup = editor.querySelector('[data-glaze-talk-markup]');
  const existing = editor.querySelector('[data-glaze-talk-images]');
  const previews = editor.querySelector('[data-glaze-talk-previews]');
  const tile = editor.querySelector('[data-glaze-talk-image-tile]');
  const error = editor.querySelector('.talk-editor-error');
  const submit = form.querySelector('button[type="submit"]');
  const types = new Set(upload?.accept.split(',') || []);
  let added = [];
  let opener;

  function report(message = '', target = error) {
    target.textContent = message;
    target.hidden = !message;
  }

  function updateCount() {
    count.textContent = content.value.length + ' / ' + content.maxLength;
  }

  function renderExisting(images) {
    existing.replaceChildren(...images.map(({ id, url, alt }) => {
      const item = tile.content.firstElementChild.cloneNode(true);
      const image = item.querySelector('img');
      image.src = url;
      image.alt = alt;
      item.querySelector('input').value = id;
      return item;
    }));
    existing.hidden = !images.length;
  }

  function syncFiles() {
    const transfer = new DataTransfer();
    added.forEach(({ file }) => transfer.items.add(file));
    upload.files = transfer.files;
  }

  function renderPreviews() {
    previews.replaceChildren(...added.map(({ file, url }, index) => {
      const item = document.createElement('li');
      const image = document.createElement('img');
      const remove = document.createElement('button');
      item.className = 'talk-editor-image squircle';
      image.src = url;
      image.alt = file.name;
      remove.type = 'button';
      remove.textContent = tile.content.querySelector('label span').textContent;
      remove.addEventListener('click', () => {
        URL.revokeObjectURL(url);
        added.splice(index, 1);
        syncFiles();
        renderPreviews();
        report();
        (previews.querySelector('button') || upload).focus();
      });
      item.append(image, remove);
      return item;
    }));
    previews.hidden = !added.length;
  }

  function openEditor(note = {}) {
    form.elements.glaze_note_id.value = note.id || '0';
    form.elements.glaze_note_version.value = note.version || '';
    content.value = note.content || '';
    content.maxLength = Math.max(4000, content.value.length);
    if (keywords) {
      keywords.value = note.keywords || '';
      keywords.maxLength = Math.max(100, keywords.value.length);
    }
    markup.hidden = !note.markup;
    document.getElementById('talk-compose-title').textContent = note.id ? editor.dataset.editTitle : editor.dataset.newTitle;
    submit.textContent = note.id ? editor.dataset.saveLabel : editor.dataset.publishLabel;

    added.forEach(({ url }) => URL.revokeObjectURL(url));
    added = [];
    if (upload) upload.value = '';
    renderExisting(note.images || []);
    renderPreviews();
    report();
    updateCount();
    editor.showModal();
  }

  if (upload && typeof DataTransfer === 'function') {
    upload.addEventListener('change', () => {
      const chosen = Array.from(upload.files);
      const kept = existing.querySelectorAll('input:not(:checked)').length;
      let message = '';
      if (kept + added.length + chosen.length > 4) {
        message = editor.dataset.uploadCountError;
      } else if (chosen.some(file => file.size > Number(upload.dataset.limit))) {
        message = editor.dataset.uploadSizeError;
      } else if (chosen.some(file => file.type && !types.has(file.type))) {
        message = editor.dataset.uploadFormatError;
      }
      if (!message) {
        added.push(...chosen.map(file => ({ file, url: URL.createObjectURL(file) })));
        renderPreviews();
      }
      syncFiles();
      report(message);
    });
  }

  for (const dialog of [editor, confirmDelete]) {
    const dialogForm = dialog.querySelector('form');
    for (const close of dialog.querySelectorAll('[data-glaze-talk-close]')) {
      close.addEventListener('click', event => {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        if (!dialogForm.hasAttribute('aria-busy')) dialog.close();
      });
    }
    dialog.addEventListener('cancel', event => {
      if (dialogForm.hasAttribute('aria-busy')) event.preventDefault();
    });
    dialog.addEventListener('click', event => {
      if (event.target !== dialog || dialogForm.hasAttribute('aria-busy')) return;
      const rect = dialog.getBoundingClientRect();
      if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
      opener?.focus({ preventScroll: true });
      opener = undefined;
    });
    dialogForm.addEventListener('submit', async event => {
      if (typeof fetch !== 'function') return;
      event.preventDefault();
      const button = dialogForm.querySelector('button[type="submit"]');
      if (button.disabled) return;
      const feedback = dialog.querySelector('.talk-editor-error');
      button.disabled = true;
      dialogForm.setAttribute('aria-busy', 'true');
      report('', feedback);

      try {
        const response = await fetch(dialogForm.action, {
          method: 'POST',
          headers: { 'X-Glaze-Talk': '1' },
          body: new FormData(dialogForm),
        });
        const result = await response.json();
        if (!result.success || !result.data?.location) {
          report(result.data?.message || editor.dataset.networkError, feedback);
        } else {
          const destination = new URL(result.data.location, location.href);
          if (destination.origin !== location.origin) throw new Error();
          if (destination.pathname === location.pathname && destination.search === location.search) {
            history.replaceState(history.state, '', destination.href);
            location.reload();
          } else {
            location.assign(destination.href);
          }
          return;
        }
      } catch {
        report(editor.dataset.networkError, feedback);
      }
      button.disabled = false;
      dialogForm.removeAttribute('aria-busy');
      feedback.scrollIntoView({ block: 'nearest' });
    });
    if (dialog.open) {
      dialog.removeAttribute('open');
      dialog.showModal();
    }
  }

  document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-glaze-talk-compose], [data-glaze-talk-edit], [data-glaze-talk-delete]');
    if (!trigger || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    opener = trigger;
    if (trigger.hasAttribute('data-glaze-talk-delete')) {
      deleteForm.elements.glaze_note_id.value = trigger.dataset.glazeTalkDelete;
      deleteForm.elements.glaze_note_version.value = trigger.dataset.version;
      report('', confirmDelete.querySelector('.talk-editor-error'));
      confirmDelete.showModal();
    } else {
      const note = trigger.dataset.glazeTalkEdit;
      openEditor(note ? JSON.parse(note) : undefined);
    }
  });

  content.addEventListener('input', updateCount);
  count.hidden = false;
  updateCount();
}
