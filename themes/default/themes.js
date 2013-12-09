(function($){
  $.extend( $.slidewizard.prototype, {
    _options_default: function( opts ) {
      return opts;
    },
    _afterInit_default: function( opts, $element ) {
      var _this = this;

      // Play Youtube Video on click
      this.$element.on('click', '.play-youtube-video', function(e) {
        e.preventDefault();
        var $play_btn = $(e.currentTarget),
            video_id = $play_btn.data('video-id');

        $play_btn
          .fadeOut()
          .prev('.youtube-video-thumbnail').fadeOut()
          .closest('.slidewizard-slide-item').find('.slide-description-wrapper').fadeOut(function(){
            _this.players[ video_id ].playVideo();
          });
      });

      // Play Vimeo Video on click
      this.$element.on('click', '.play-vimeo-video', function(e){
        e.preventDefault();
        var $play_btn = $(e.currentTarget),
            video_id = $play_btn.data('video-id');

        $play_btn
          .fadeOut()
          .prev('.vimeo-video-thumbnail').fadeOut()
          .closest('.slidewizard-slide-item').find('.slide-description-wrapper').fadeOut(function(){
            _this.players[ video_id ].api('play');
          });
      });
    }
  });



})(jQuery);