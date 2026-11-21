for (const image of document.querySelectorAll('.collection-site-icon > .collection-site-image')) {
  const showImage = () => {
    if (image.naturalWidth) image.parentElement.classList.add('has-site-image');
  };
  if (image.complete) showImage();
  else image.addEventListener('load', showImage, { once: true });
}
