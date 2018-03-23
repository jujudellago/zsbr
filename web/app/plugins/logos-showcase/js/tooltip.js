jQuery.noConflict();
jQuery(function() {
jQuery('.lshowcase-tooltip').tooltip({
items: "img[alt]",
position: {
my: "center bottom-20",
at: "center top",
using: function( position, feedback ) {
jQuery( this ).css( position );
jQuery( "<div>" )
.addClass( "lsarrow" )
.addClass( feedback.vertical )
.addClass( feedback.horizontal )
.appendTo( this );
}
}
});
});
