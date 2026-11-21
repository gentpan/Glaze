const toolbar = document.querySelector('[data-glaze-comment-emojis]');
const editor = toolbar?.closest('form')?.querySelector('textarea[name="comment"]');

if (editor) {
  toolbar.hidden = false;
  toolbar.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-glaze-comment-emoji]');
    if (!button) return;

    const emoji = button.dataset.glazeCommentEmoji;
    const { selectionStart, selectionEnd, maxLength } = editor;
    const nextLength = editor.value.length - (selectionEnd - selectionStart) + emoji.length;
    editor.focus({ preventScroll: true });
    if (maxLength >= 0 && nextLength > maxLength) return;

    editor.setRangeText(emoji, selectionStart, selectionEnd, 'end');
    editor.dispatchEvent(new Event('input', { bubbles: true }));
  });
}
