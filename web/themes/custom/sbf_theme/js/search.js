(function (Drupal, once) {
  Drupal.behaviors.sbfHeroSearch = {
    attach(context) {
      once('sbf-hero-search', '.hero-search input[type="search"]', context).forEach((input) => {
        input.addEventListener('focus', () => input.parentElement.classList.add('is-focused'));
        input.addEventListener('blur', () => input.parentElement.classList.remove('is-focused'));
      });
    }
  };
})(Drupal, once);
