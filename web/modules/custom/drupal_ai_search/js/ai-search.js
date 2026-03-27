(function (Drupal, once) {
  Drupal.behaviors.drupalAiSearch = {
    attach(context) {
      once('drupal-ai-search', '.ai-search-page__form input[type="search"]', context).forEach((element) => {
        element.setAttribute('autocomplete', 'off');
      });
    }
  };
})(Drupal, once);
