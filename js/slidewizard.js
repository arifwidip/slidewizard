/* ===================================================================
  SlideWizard jQuery Plugin
  Depend on CarouFredsel jquery plugin
=================================================================== */
(function($){

$.slidewizard = function( options, element ) {
  this.options = options;
  this.$element = $(element);
  this._init();
}

$.slidewizard.prototype = {
  /**
   * SlideWizard Options
   * If theme exists, use options overrided from that theme
   * 
   * @return {object} SlideWizard options
   */
  _options: function() {
    var _this = this,
        opts = {
          width: this.options.width,
          height: this.options.height,
          circular: this.options.continous_slide,
          infinite: this.options.continous_slide,
          direction: this.options.direction,
          onCreate: function( data ) {
            _this._onCreate( data );
          },
          scroll: {
            fx: this.options.slide_transition,
            duration: this.options.animation_speed,
            onBefore: function( data ) {
              _this._onBeforeScroll( data );
            },
            onAfter: function( data ) {
              _this._onAfterScroll( data );
            }
          },
          auto: {
            play: this.options.autoplay_slide,
            timeoutDuration: this.options.autoplay_interval
          },
          pagination: {
            container: this.$element.parent().find('.slidewizard-navigation')
          },
          prev: {
            button: this.$element.parent().find('.slidewizard-controls-prev')
          },
          next: {
            button: this.$element.parent().find('.slidewizard-controls-next')
          }
        };

    if( this.options.theme && this['_options_' + this.options.theme] !== undefined ) {
      return this['_options_'+this.options.theme].call(this, opts);
    } else {
      return opts;
    }
  },

  /**
   * Function that will be called after the SlideWizard has been created
   *
   * @param {object} data A map of Slide data
   */
  _onCreate: function( data ) {
    var opts = this._options();

    if( this.options.theme && this['_onCreate_' + this.options.theme] !== undefined ) {
      return this['_onCreate_'+this.options.theme].call(this, opts, data);
    }
  },

  /**
   * Function that will be called right before the slidewizard starts 
   * scrolling.
   * 
   * @param {object} data A map of Slide data
   */
  _onBeforeScroll: function( data ) {
    var opts = this._options();

    if( this.options.theme && this['_onBeforeScroll_' + this.options.theme] !== undefined ) {
      return this['_onBeforeScroll_'+this.options.theme].call(this, opts, data);
    }
  },

  /**
   * Function that will be called right after the slidewizard finished 
   * scrolling.
   * 
   * @param {object} data A map of Slide data
   */
  _onAfterScroll: function( data ) {
    var opts = this._options();

    if( this.options.theme && this['_onAfterScroll_' + this.options.theme] !== undefined ) {
      return this['_onAfterScroll_'+this.options.theme].call(this, opts, data);
    }
  },

  /**
   * Initialize plugin
   */
  _init: function() {
    var _this = this;

    $(window).bind('load', function(){
      _this.$element.carouFredSel( _this._options() );
    });
  }
}

$.fn.slidewizard = function( options ) {
  return this.each(function(){
    var instance = new $.slidewizard( options, this );
  });
}

})(jQuery);