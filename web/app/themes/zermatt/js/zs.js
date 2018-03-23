jQuery.noConflict()(function($){
	$(document).ready(function(){
	//   setFiltersCustom();				
	//	setSubmenu();
	//	
		$(".colorboxes .vc_col-sm-3").matchHeight({
		    byRow: true,
		    property: 'height',
		    target: null,
		    remove: false
		});
		$(".colorboxes .vc_col-sm-4").matchHeight({
		    byRow: true,
		    property: 'height',
		    target: null,
		    remove: false
		});
		//$(".aheight_row .aheight .wpb_wrapper").matchHeight({
		//    byRow: true,
		//    property: 'height',
		//    target: null,
		//    remove: false
		//});
		
		$(".newsletter_row .qbutton").on("click",function(e){
		 	e.preventDefault();
		//	$.prettyPhoto.open("#mc_embed_signup");
			var introtxt=$(".newsletter_row .qbutton").parent(".button_wrapper").parent(".two_columns_75_25").children(".column1").html();
			var formcode=$("#mc_embed_signup").html();
			$(".newsletter_row .qbutton").parent(".button_wrapper").parent(".two_columns_75_25").remove();									
			$("<div class='two_columns_25_75'><div class='column1 text_wrapper'>"+introtxt+"</div><div class='column2 button_wrapper'>"+formcode+"</div></div>").appendTo(".newsletter_row .call_to_action .container_inner");
			
			
			
		});
		$(document).on("click",".newsletter_row .submit_link",function(e){
		 	e.preventDefault();
		 	$('#mc-embedded-subscribe-form').submit();
		});
	   // $(".supporting_member a").on("click",function(e){
	   // 	e.preventDefault();
	   // 	
	   // 	var txt=$(this).parent("span").parent("div").children("div.wpb_text_column").children("div").children("h5").text();
	   // 	$('#form_title').text(txt);
	   // 	$('#contact_subject').val(txt);
	   // 	
	   // 	
	   // 	$.prettyPhoto.open("#support_form_row");
       //
	   // });	
	   // 	
		
		
		
	});
	

});



function setAfterLoad(){
	setSubmenu();
	setFiltersCustom();
}

function setSubmenu(){
	$j('.widget.widget_nav_menu .menu  a').click(function(e){  
		if ($j(this).attr("href")=="#"){
          e.preventDefault();
		}
		


          if ($j(this).parent().children('.sub-menu:first').is(':visible')) {
               $j(this).parent().children('.sub-menu:first').slideUp();
          } else {
               $j(this).parent().children('.sub-menu:first').slideDown();
          }
     });
}

function setFiltersCustom(){
//		console.log("setFiltersCustom");
		$j("#q").keyup(function(){

			// Retrieve the input field text and reset the count to zero
			var filter = $j(this).val(), count = 0;

			// Loop through the comment list
			$j("#speakers-list li").each(function(){

				// If the list item does not contain the text phrase fade it out
				if ($j(this).text().search(new RegExp(filter, "i")) < 0) {
					$j(this).fadeOut();

					// Show the list item if the phrase matches and increase the count by 1
				} else {
					$j(this).show();
					count++;
				}
			});
		});
		$j('.artist-shows a').each(function(){

			if ($j(this).html()=="_"){
				$j(this).html("[view details]");
			}

		})
}