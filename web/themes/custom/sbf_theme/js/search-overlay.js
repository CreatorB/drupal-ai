(function (Drupal, once) {
  Drupal.behaviors.searchOverlay = {
    attach(context) {
      once('search-overlay', '[data-search-overlay]', context).forEach((overlay) => {
        const input = overlay.querySelector('[data-search-input]');

        // If we're on /ai-search with a query, auto-open with the query pre-filled
        const urlParams = new URLSearchParams(window.location.search);
        const currentQuery = urlParams.get('q');
        if (window.location.pathname === '/ai-search' && currentQuery && input) {
          input.value = currentQuery;
          overlay.classList.add('is-open');
        }

        // If we're on /ai-search with no query, auto-open empty
        if (window.location.pathname === '/ai-search' && !currentQuery) {
          overlay.classList.add('is-open');
          if (input) {
            setTimeout(function () { input.focus(); }, 200);
          }
        }

        // Toggle: click search icon or AI SEARCH nav link
        document.querySelectorAll('[data-search-trigger]').forEach(function (trigger) {
          trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var isOpen = overlay.classList.toggle('is-open');
            if (isOpen && input) {
              setTimeout(function () { input.focus(); }, 150);
            }
          });
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
            overlay.classList.remove('is-open');
          }
        });

        // Close when clicking outside
        document.addEventListener('click', function (e) {
          if (!overlay.classList.contains('is-open')) return;
          if (!overlay.contains(e.target) && !e.target.closest('[data-search-trigger]')) {
            overlay.classList.remove('is-open');
          }
        });
      });
    }
  };
})(Drupal, once);
