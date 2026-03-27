(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.personalizedTracking = {
    attach(context) {
      once('personalized-tracking', 'body', context).forEach(() => {
        if (!drupalSettings.personalizedWidget || !drupalSettings.personalizedWidget.nodeId) {
          return;
        }

        const nodeId = drupalSettings.personalizedWidget.nodeId;
        const startTime = Date.now();
        let maxScroll = 0;

        $(window).on('scroll.personalizedTracking', function () {
          const totalHeight = $(document).height() - $(window).height();
          const percent = totalHeight > 0 ? ($(window).scrollTop() / totalHeight) * 100 : 0;
          maxScroll = Math.max(maxScroll, Math.floor(percent));
        });

        window.addEventListener('beforeunload', function () {
          const timeSpent = Math.floor((Date.now() - startTime) / 1000);
          const payload = new FormData();
          payload.append('node_id', nodeId);
          payload.append('time_spent', timeSpent);
          payload.append('scroll_depth', maxScroll);

          if (navigator.sendBeacon) {
            navigator.sendBeacon('/api/tracking/page-view', payload);
          }
        }, { once: true });
      });
    }
  };
})(jQuery, Drupal, drupalSettings, once);
