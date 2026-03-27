(function (Drupal, once) {
  Drupal.behaviors.drupalBasicSearch = {
    attach(context) {
      once('drupal-basic-search', '.ai-search-page__form input[type="search"]', context).forEach((element) => {
        element.setAttribute('autocomplete', 'off');
      });
    }
  };
})(Drupal, once);
