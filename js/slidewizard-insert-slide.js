(function($){

SlideWizardInsert = {
  $el: {},

  UIEvents: function() {
    this.$el.list.on('click', 'a', $.proxy(this.insertSlideWizard, this));
  },

  insertSlideWizard: function(e) {
    e.preventDefault();

    var $this = $(e.currentTarget),
        id = $this.next('.slidewizard-action').find('.slidewizard-id span').text(),
        shortcode = '[slidewizard id="'+ id +'"]',
        mce = typeof(parent.tinymce) != 'undefined', 
        qt = typeof(parent.QTags) != 'undefined',
        h = "",
        ed;

    if ( !parent.wpActiveEditor ) {
        if ( mce && parent.tinymce.activeEditor ) {
            ed = parent.tinymce.activeEditor;
            parent.wpActiveEditor = ed.id;
        } else if ( !qt ) {
            return false;
        }
    } else if ( mce ) {
        if ( parent.tinymce.activeEditor && (parent.tinymce.activeEditor.id == 'mce_fullscreen' || parent.tinymce.activeEditor.id == 'wp_mce_fullscreen') )
            ed = parent.tinymce.activeEditor;
        else
            ed = parent.tinymce.get(parent.wpActiveEditor);
    }
    
    if ( ed && !ed.isHidden() ) {
        // restore caret position on IE
        if ( parent.tinymce.isIE && ed.windowManager.insertimagebookmark )
            ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);
        
        // for(var s in shortcodes){
            h += '<p>' + shortcode + '</p>';
        // }
        
        ed.execCommand('mceInsertContent', false, h);
    } else if ( qt ) {
        var sep = "";
        // for( var s in shortcodes){
            h += sep + shortcode;
            sep = "\n\n";
        // }
        
        parent.QTags.insertContent(h);
    } else {
        parent.getElementById(parent.wpActiveEditor).value += h;
    }
    
    try{parent.tb_remove();}catch(e){}
  },

  init: function() {
    this.$el.list = $('.slidewizard-list');

    this.UIEvents();
  }
};

$(document).ready(function(){
  SlideWizardInsert.init();
});

})(jQuery);