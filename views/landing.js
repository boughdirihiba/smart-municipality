(() => {
  document.documentElement.classList.add('has-js');

  const landing = document.querySelector('.landing');
  const nav = document.querySelector('.landing-nav');

  if (!landing) return;

  const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const setReady = () => {
    landing.classList.add('is-ready');
  };

  if (reduceMotion) {
    setReady();
  } else {
    requestAnimationFrame(setReady);
  }

  if (!nav) return;

  const syncNav = () => {
    nav.classList.toggle('is-scrolled', window.scrollY > 8);
  };

  window.addEventListener('scroll', syncNav, { passive: true });
  syncNav();
})();
