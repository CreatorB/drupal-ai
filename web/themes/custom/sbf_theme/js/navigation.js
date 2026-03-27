(function (Drupal, once) {
  Drupal.behaviors.sbfNavigation = {
    attach(context) {
      once('sbf-nav', '[data-nav-toggle]', context).forEach((toggle) => {
        const nav = document.querySelector('.site-nav');
        if (!nav) {
          return;
        }

        toggle.addEventListener('click', () => {
          nav.classList.toggle('is-open');
        });
      });
    }
  };
})(Drupal, once);
