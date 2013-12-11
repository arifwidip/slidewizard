(function($){
  $.extend( $.slidewizard.prototype, {
    _options_thumbnail: function( opts ) {
      // Change pagination Settings
      opts.pagination.anchorBuilder = false;

      return opts;
    },

    // On Before Slider Scroll
    _onBeforeScroll_thumbnail: function( opts, data ) {
      this.$element.delay(200);
      data.items.old
        .find('.slide-title')
          .transition({ opacity: 0, top: -20 }, 300).end()
        .find('.slide-description-content')
          .delay(30)
          .transition({ opacity: 0, top: -20 }, 300);

      data.items.visible
        .find('.slide-title')
          .transition({ opacity: 0, top: -20 }, 300).end()
        .find('.slide-description-content')
          .delay(30)
          .transition({ opacity: 0, top: -20 }, 300);
    },

    // On After Slider Scroll
    _onAfterScroll_thumbnail: function( opts, data ) {
      data.items.visible
        .find('.slide-title')
          .transition({ opacity: 1, top: 0 }, 300).end()
        .find('.slide-description-content')
          .delay(30)
          .transition({ opacity: 1, top: 0 }, 300);
    },

    // On Slider creation
    _onCreate_thumbnail: function( opts, data ) {
      data.items
        .find('.slide-title')
          .transition({ opacity: 1, top: 0 }, 300).end()
        .find('.slide-description-content')
          .delay(30)
          .transition({ opacity: 1, top: 0 }, 300);
    },

    // On Init
    _init_thumbnail: function() {
      // Set thumbnail as a carousel
      var $nav = this.$element.parent().parent().find('.slidewizard-navigation');

      $nav.carouFredSel({
        'width': '100%'
      });
    }
  });
})(jQuery);