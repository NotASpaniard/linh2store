// JS chính của Linh2Store
// Gợi ý: thêm micro-interactions, lazy-load, quick view ở đây

document.addEventListener('DOMContentLoaded', function () {
  // Lazy load ảnh (đơn giản)
  const lazyImages = document.querySelectorAll('img[data-src]');
  const io = 'IntersectionObserver' in window ? new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const img = e.target;
        img.src = img.getAttribute('data-src');
        img.removeAttribute('data-src');
        obs.unobserve(img);
      }
    });
  }) : null;
  lazyImages.forEach(img => {
    if (io) io.observe(img);
  });
});
