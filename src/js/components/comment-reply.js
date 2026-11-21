const replyParent = document.querySelector('#respond #comment_parent');
const replyAvatar = document.querySelector('#reply-title .glaze-reply-avatar');

if (replyParent && replyAvatar) {
  function updateReplyAvatar() {
    const comment = document.getElementById('div-comment-' + replyParent.value);
    const source = comment?.querySelector('.comment-avatar img');
    replyAvatar.hidden = !source;
    if (source) replyAvatar.src = source.currentSrc || source.src;
    else replyAvatar.removeAttribute('src');
  }

  updateReplyAvatar();
  new MutationObserver(updateReplyAvatar).observe(replyParent, { attributeFilter: ['value'] });
}
