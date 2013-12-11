(function($){
  $.extend( $.slidewizard.prototype, {
    _options_thumbnail: function( opts ) {
      // Change pagination Settings
      opts.pagination.container = false;
      opts.pagination.anchorBuilder = false;
      // opts.syncronise = this.$element.parent().parent().find('.slidewizard-nav-inner');

      return opts;
    },

    // On Before Slider Scroll
    _onBeforeScroll_thumbnail: function( opts, data ) {
      this.$element.delay(200);
      data.items.old
        .find('.slide-title')
          .transition({ opacity: 0, top: -20 }, 300).end()
        .find('.slide-description-content')
          .delay(50)
          .transition({ opacity: 0, top: -20 }, 300);

      data.items.visible
        .find('.slide-title')
          .transition({ opacity: 0, top: -20 }, 300).end()
        .find('.slide-description-content')
          .delay(50)
          .transition({ opacity: 0, top: -20 }, 300);
    },

    // On After Slider Scroll
    _onAfterScroll_thumbnail: function( opts, data ) {
      data.items.visible
        .find('.slide-title')
          .transition({ opacity: 1, top: 0 }, 300).end()
        .find('.slide-description-content')
          .delay(50)
          .transition({ opacity: 1, top: 0 }, 300);
    },

    // On Init
    _init_thumbnail: function() {
      // Set thumbnail as a carousel
      var _self = this,
          $nav = this.$element.parent().parent().find('.slidewizard-nav-inner');

      // Create thumbnail carousel
      $nav.carouFredSel({
        infinite: false,
        circular: false,
        auto: false,
        width: '100%',
        scroll: {
          items: 'page'
        },
        items: {
          visible: {
            min: 2,
            max: 5
          }
        },
        // syncronise: _self.$element,
        next: $nav.parent().find('.thumb-next'),
        prev: $nav.parent().find('.thumb-prev'),
        onCreate: function( data ) {
          var active_index = _self.$element.triggerHandler('currentPosition'),
              $target_item = $(this).find('[data-index="'+ active_index +'"]');
              $target_item.addClass('selected');
          $(this).trigger('slideTo', $target_item);
        }
      }, {
        transition: true
      });

      // Setup thumbnail click event
      $nav.on('click', 'a', $.proxy(function( e ){
        e.preventDefault();
        var $link = $(e.currentTarget),
            index = $link.data('index');

        $link.addClass('selected').siblings().removeClass('selected');

        this.$element.trigger('slideTo', index);
      }, this));
    }
  });
})(jQuery);