(function (Drupal, once) {
  Drupal.behaviors.sbfTabs = {
    attach(context) {
      once('sbf-tabs', '[data-tab-group]', context).forEach((toolbar) => {
        const buttons = toolbar.querySelectorAll('[data-tab-target]');
        const panels = document.querySelectorAll('[data-tab-panel]');

        buttons.forEach((button) => {
          button.addEventListener('click', () => {
            const target = button.getAttribute('data-tab-target');
            buttons.forEach((item) => item.classList.remove('is-active'));
            panels.forEach((panel) => panel.classList.remove('is-active'));
            button.classList.add('is-active');
            document.querySelector(`[data-tab-panel="${target}"]`)?.classList.add('is-active');
          });
        });
      });
    }
  };
  Drupal.behaviors.sbfStatToggle = {
    attach(context) {
      once('sbf-stat-toggle', '[data-stat-toggle]', context).forEach((card) => {
        card.addEventListener('click', (e) => {
          if (e.target.closest('a')) return;
          card.classList.toggle('is-open');
        });
      });
    }
  };
})(Drupal, once);
