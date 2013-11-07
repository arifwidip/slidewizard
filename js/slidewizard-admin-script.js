/**
 * Admin Script for SlideWizard
 * 
 */
(function($){
window.SlideWizardAdmin = {
  $el: {},

  /**
   * Event Binding
   */
  UIbinding: function() {
    $('body').on( 'click', '#slidewizard-create-button', $.proxy( this.source_modal, this ));
    $('body').on( 'click', '.slidewizard-delete', $.proxy( this.delete_slide, this ));
    $('body').on( 'click', '.configure-source', $.proxy( this.show_source_option, this ));
  },

  /**
   * Show source options for each source
   */
  show_source_option: function(e) {
    e.preventDefault();
    $('.slidewizard-source-config').toggle();
  },

  /**
   * Initiate twitter bootstrap tooltip
   */
  tooltips: function() {
    $('.tooltips').tooltip();
  },

  /**
   * Twitter bootstrap popover on manage slidewizard page,
   * show shortcode when "use slide" button clicked
   */
  use_slide_popover: function() {
    $('.slidewizard-use').popover({
      placement: 'left',
      html: true,
      content: function(){
        return slidewizard_message.use_slide + '<pre>[slidewizard id='+ $(this).data('id') +']</pre>';
      }
    }).parent().mouseleave(function(){
      $(this).find('.slidewizard-use').popover('hide');
    });
  },

  /**
   * For input type slider
   */
  input_slider: function() {
    $('.input-slider').each(function(){
      var $el = $(this),
          min = ($el.data('min')) ? $el.data('min') : '',
          max = ($el.data('max')) ? $el.data('max') : '',
          step = ($el.data('step')) ? $el.data('step'): 0;

      $el.slider({
        min: min,
        max: max,
        step: step,
        value: $el.next('input').val(),
        slide: function(event, ui) {
          $el.next('input').val( ui.value );
        },
        stop: function(event, ui) {
          $el.next('input').trigger('change');
        }
      });
    });
  },

  /**
   * For input type radio
   */
  input_radio: function() {
    $('.control-option-radio .input-radio').each(function(){
      var $el = $(this),
          show_hide_btn = function( btn_data ) {
            if( btn_data.trigger && btn_data.target ) {
              if( btn_data.value == btn_data.trigger ) {
                $(btn_data.target).show();
              } else {
                $(btn_data.target).hide();
              }
            }
          }


      $el.find('button').on('click', function(){
        var $btn = $(this),
            btn_data = $btn.data();

        $el.parent().find('[value="'+ btn_data.value +'"]').trigger('click');

        // If this button has data-trigger and data-target
        show_hide_btn( btn_data );
      });
    });

    // Special case for size options
    // Show width and height options when size is custom
    if( $('.control-size [data-value="custom"].active[data-trigger="custom"]').length > 0 ) {
      $('.control-width, .control-height').show();
    }
  },

  /**
   * Modal box that will shown when "create slide" button clicked
   */
  source_modal: function(e) {
    var _this = this;
    
    e.preventDefault();
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: 'action=slidewizard_source-modal',
      success: function( res ) {
        var $result = $(res),
            resultId = '#' + $result.attr('id');

        // Only append modal when modal is not exist
        if( !$(resultId).length ) {
          $result.insertAfter( _this.$el.container );
        }
        $(resultId).modal();
      }
    });
  },

  /**
   * Action for deleting slide
   */
  delete_slide: function(e) {
    var $el = $(e.currentTarget),
        id = $el.parent().find('.slidewizard-id span').text(),
        nonce = $el.find('.nonce').text(),
        confirmed = confirm( slidewizard_message.confirm_delete );
    
    // If user confirmed to delete the slide
    if( confirmed ) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'slidewizard_delete-slide',
          nonce: nonce,
          id: id
        },
        beforeSend: function() {
          $el.parents('.slidewizard-item').animate({'opacity': 0.4});
        },
        success: function() {
          $el.parents('.slidewizard-item').slideUp(function(){
            $(this).remove();
          });
        }
      });
    }
  },

  /**
   * Initialize script
   */
  init: function() {
    // Cache jquery object
    this.$el = {
      body: $('body'),
      container: $('.slidewizard-container')
    };

    this.UIbinding();
    this.tooltips();
    this.use_slide_popover();
    this.input_slider();
    this.input_radio();
  }
};


// Fire script on document ready
// -----------------------------
$(document).ready(function(){
  SlideWizardAdmin.init();
	
	/* Twitter Stream ticker
	----------------------------------------------------------------- */
	var $t_stream = $('.slidewizard-twitter-stream'),
			$t_stream_list = $t_stream.find('ul');

	// Only run this script when twitter feed fetched
	if( $t_stream_list.length > 0 ) {
		var $item = $t_stream_list.find('li'),
				item_length = $item.length,
				current_visible = $item.filter(':visible').index();

		// Hide all list except the first one
		$t_stream_list.find('li:not(:first)').hide();
		setInterval(function(){
			var next_visible = current_visible + 1;
			if( next_visible > item_length - 1 ) {
				next_visible = 0;
			}
			current_visible = next_visible;
			$item.hide();
			$item.eq(next_visible).fadeTo(250, 1);
		}, 5000);
	}
});

})(jQuery);