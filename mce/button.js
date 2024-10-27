( function() {
  tinymce.PluginManager.add( 'ad_short_button', function( editor, url ) {
    editor.addButton( 'ad_short_button', {
      title: 'Ad Short Button',
      icon: 'dashicons-carrot',
      type: 'menubutton',
      menu: [
        {
          text: 'Square',
          value: 'Ad Short Square',
          onclick: function(  ) {
            editor.insertContent( '[ad type="square"]' );
          }
        },
        {
          text: 'Above Fold Square',
          value: 'Ad Short Above Fold Square',
          onclick: function(  ) {
            editor.insertContent( '[ad type="msquare"]' );
          }
        },
        {
          text: 'Banner',
          value: 'Ad Short Banner',
          onclick: function(  ) {
            editor.insertContent( '[ad type="banner"]' );
          }
        },
        {
          text: 'Vertical Link',
          value: 'Ad Short Vertical Link',
          onclick: function(  ) {
            editor.insertContent( '[ad type="vlink"]' );
          }
        },
        {
          text: 'Horizontal Link',
          value: 'Ad Short Horizontal Link',
          onclick: function(  ) {
            editor.insertContent( '[ad type="hlink"]' );
          }
        },
        {
          text: 'Mobile Banner',
          value: 'Ad Short Mobile Banner',
          onclick: function(  ) {
            editor.insertContent( '[ad type="mbanner"]' );
          }
        },
        {
          text: 'Large Mobile Banner',
          value: 'Ad Short Large Mobile Banner',
          onclick: function(  ) {
            editor.insertContent( '[ad type="lmbanner"]' );
          }
        },
      ]
    } );
  } );
})();
