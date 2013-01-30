/**
 * Script for handling Preview update for SlideWizard
 * 
 */

(function($){
window.SlideWizardPreview = {
  $el: {},
  outerWidth: 0,
  outerHeight: 0,
  updates: {},
  validations: {},
  ajaxOptions: [
    "options[size]",
    "options[width]",
    "options[height]",
    "options[number_of_slides]",
    "options[excerpt_length_with_media]",
    "options[excerpt_length_no_media]",
    "options[continous_slide]",
    "options[direction]",
    "options[starting_slide]",
    "options[randomize]",
    "options[autoplay_slide]",
    "options[autoplay_interval]",
    "options[animation_speed]",
    "options[slide_transition]",
    "options[show_slide_controls]",
    "options[navigation_type]",
    "options[navigation_position]",
    "options[date_format]"
  ],
  isLoadingAjax: false,

  /**
   * Event Binding
   */
  eventBinding: function() {
    var _this = this;

    // Bind event on form all input
    this.$el.form
      .on( 'change', 'select', $.proxy( this.events.inputSelect, this ) )
      .on( 'blur change', 'input[type="text"]', $.proxy( this.events.inputText, this ) )
      .on( 'keydown', 'input[type="text"]', this.events.preventEnterKey )
      .on( 'click', 'input[type="radio"], input[type="checkbox"]', $.proxy( this.events.inputRadioCheckbox, this ) )
      .on( 'click', '.slidewizard-ajax-update', function(event){
        event.preventDefault();
        $('.slidewizard-source-config').hide();
        _this.ajaxUpdate();
      });

    this.$el.previewIframe.bind('load', $.proxy( this.events.iframeOnLoad, this ) );
  },

  /**
   * Place all event change here
   */
  events: {

    /**
     * Event for input text
     */
    inputText: function(event) {
      this.update(event.currentTarget, event.currentTarget.value);
    },

    /**
     * Event for input select
     */
    inputSelect: function(event) {
      this.update(event.currentTarget, event.currentTarget.value);
    },

    /**
     * Event for input radio and checkbox
     */
    inputRadioCheckbox: function(event) {
      this.update(event.currentTarget, event.currentTarget.value);
    },

    /**
     * Prevent enter key from submitting text fields
     */
    preventEnterKey: function(event) {
      if(13 == event.keyCode){
        event.preventDefault();
        return false;
      }
    },

    /**
     * Iframe On Load
     */
    iframeOnLoad: function() {
      this.$el.iframeContents = this.$el.previewIframe.contents();
      this.$el.iframeBody = this.$el.iframeContents.find('body');
      this.$el.slidewizardWrapper = this.$el.iframeBody.find('.slidewizard-wrapper');
      this.$el.slidewizard = this.$el.slidewizardWrapper.find('.slidewizard');

      this.$el.iframeBody.find('.overlay-loader').removeClass('loading');

      this.$el.slidewizard.find('.slidewizard-slide-item a').click(function(event){
        event.preventDefault();
        return false;
      }).attr('title', 'Links disabled for preview');
    }
  },

  /**
   * Realtime iframe update
   */
  realtime: function(elem, value) {
    var $elem = $.data(elem, '$elem'),
        name;

    if( !$elem ) {
      $elem = $(elem);
      $.data(elem, '$elem', $elem);
    }

    name = $elem.attr('name');

    if( typeof(this.updates[name]) == 'function' ) {
      this.updates[name].call(this, $elem, value);
    }
  },

  /**
   * Update changes
   */
  update: function(elem, value) {
    var realtime = true;

    // Return false if user input same value
    if( elem.type == "text" ) {
      var previousValue = $.data(elem, 'previousValue');
      if(previousValue == value){
        return false;
      } else {
        $.data(elem, 'previousValue', value);
      }
    }

    // If element defined in ajaxOptions, call ajaxUpdate
    for( var i = 0; i < this.ajaxOptions.length; i++ ) {
      if( this.ajaxOptions[i] == elem.name ) {
        realtime = false;
      }
    }

    // If element is defined in in updates object, do not call ajax
    for( var k in this.updates ) {
      if( k == elem.name ) {
        realtime = true;
      }
    }

    if( this.validate(elem, value) ) {
      if( realtime ) {
        this.realtime(elem, value);
      } else {
        this.ajaxUpdate();
      }
    }
  },

  /**
   * Validate Input
   */
  validate: function(elem, value) {
    var _return = true;
    
    if(typeof(this.validations[elem.name]) == "function"){
      _return = this.validations[elem.name](elem, value);
    }
    
    return _return;
  },

  /**
   * Ajax update
   */
  ajaxUpdate: function(elem, value) {
    var _this = this;
    
    if( _this.isLoadingAjax ) {
      return false;
    }

    _this.isLoadingAjax = true;

    // Prevent Race condition
    setTimeout(function() {
      var data = _this.$el.form.serialize();
          data = data.replace(/action\=([a-zA-Z0-9\-_]+)/gi, 'action=slidewizard_preview-iframe-update');

      _this.$el.iframeBody.find('.overlay-loader').addClass('loading');

      $.ajax({
        url: ajaxurl,
        type: "GET",
        dataType: "json",
        data: data,
        cache: false,
        success: function(data) {
          var adjustDimensions = false;
          if(_this.outerWidth != data.outer_width || _this.outerHeight != data.outer_height) {
              _this.outerWidth = data.outer_width;
              _this.outerHeight = data.outer_height;
              adjustDimensions = true;
          }

          if( adjustDimensions ) {
            _this.$el.previewIframe.animate({
              width: parseInt( data.outer_width, 10 ),
              height: parseInt( data.outer_height, 10 )
            }, 500, function(){
              _this.$el.previewIframe[0].src = data.url;
            });
          } else {
            _this.$el.previewIframe[0].src = data.url;
          }
          _this.isLoadingAjax = false;
        }
      });
    });
  },

  /**
   * Initialize script
   */
  init: function() {
    var _this = this;

    this.$el.previewIframe = $('#slidewizard-preview-iframe');
    this.$el.form = $('#slidewizard-slide-form');

    // Return false if slide form not exists
    if( !this.$el.form.length ) {
      return false;
    }

    this.eventBinding();
  }
};


/**
 * Setup realtime event
 */
SlideWizardPreview.updates["options[show_title]"] = function( $elem, value ) {
  value = value == 1 ? true : false;
  if( value ) {
    this.$el.slidewizardWrapper.addClass('slidewizard-show-title');
  } else {
    this.$el.slidewizardWrapper.removeClass('slidewizard-show-title');
  }
}

SlideWizardPreview.updates["options[show_avatar]"] = function( $elem, value ) {
  value = value == 1 ? true : false;
  if( value ) {
    this.$el.slidewizardWrapper.addClass('slidewizard-show-avatar');
  } else {
    this.$el.slidewizardWrapper.removeClass('slidewizard-show-avatar');
  }
}

SlideWizardPreview.updates["options[show_excerpt]"] = function( $elem, value ) {
  value = value == 1 ? true : false;
  if( value ) {
    this.$el.slidewizardWrapper.addClass('slidewizard-show-excerpt');
  } else {
    this.$el.slidewizardWrapper.removeClass('slidewizard-show-excerpt');
  }
}

SlideWizardPreview.updates["options[show_readmore]"] = function( $elem, value ) {
  value = value == 1 ? true : false;
  if( value ) {
    this.$el.slidewizardWrapper.addClass('slidewizard-show-readmore');
  } else {
    this.$el.slidewizardWrapper.removeClass('slidewizard-show-readmore');
  }
}


$(document).ready(function(){
  SlideWizardPreview.init();
});

})(jQuery);