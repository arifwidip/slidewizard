(function($){
  $.extend( $.slidewizard.prototype, {
    _options_rounded: function( opts ) {
      opts.height = 'auto';

      opts.pagination = {
        container: this.$element.parent().parent().find('.slider-rounded-pager'),
        anchorBuilder: function( index ) {
          var imgsrc = jQuery(this).find('img').attr('src');
          return '<a href="#" class="thumb' + index + '" style="background-image: url(' + imgsrc + ')"><img src="' + imgsrc + '" width="50" /></a>';
        }
      };
      
      return opts;
    },

    _onCreate_rounded: function( opts, data ) {
      var $item = $(data.items[0]),
          $item_title = $item.find('.slide-title');
          $item_text = $item.find('.slide-description'),
          $target_title = this.$element.parent().parent().parent().find('.slider-rounded-desc > .slide-title'),
          $target_text = this.$element.parent().parent().parent().find('.slider-rounded-desc > .slide-text');
          
      if( $item_title.length > 0 ) {
        $target_title.append( $item_title.html() );
      }

      if( $item_text.length > 0 ) {
        $target_text.append( $item_text.html() );
      }
      console.log($item_title);
    },

    _onBeforeScroll_rounded: function( opts, data ) {
      var $target_title = this.$element.parent().parent().parent().find('.slider-rounded-desc > .slide-title'),
          $target_text = this.$element.parent().parent().parent().find('.slider-rounded-desc > .slide-text');

      $target_title.transition({ y: -10, opacity: 0 });
      $target_text.transition({ y: -10, opacity: 0, delay: 100 });
    },

    _onAfterScroll_rounded: function( opts, data ) {
      var $item = $(data.items.visible[0]),
          $item_title = $item.find('.slide-title');
          $item_text = $item.find('.slide-description'),
          $target_title = this.$element.parent().parent().parent().find('.slider-rounded-desc > .slide-title'),
          $target_text = this.$element.parent().parent().parent().find('.slider-rounded-desc > .slide-text');

      if( $item_title.length > 0 ) {
        $target_title.html( $item_title.html() );
      }

      if( $item_text.length > 0 ) {
        $target_text.html( $item_text.html() );
      }

      $target_title.transition({ y: 0, opacity: 1 });
      $target_text.transition({ y: 0, opacity: 1, delay: 100 });
    }
  });

})(jQuery);