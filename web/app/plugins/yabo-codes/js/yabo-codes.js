

jQuery.noConflict()(function($){
$(document).ready(function() {
	
	var artistName=$('.artist-detail h2').html();
	var pageTitle=$(document).find("title").text();
	if (artistName!="null" && artistName!=null){
		//pageTitle=pageTitle+" - "+$(artistName).text();
		pageTitle=pageTitle+" - "+artistName;
		console.log("I have an artist name ="+pageTitle);
		document.title=pageTitle;
	}

	
	$('#youtube_wpress_widget-2 .widget-heading').append("<p><a href='/the-summit/videos/'>All videos here</a></p>");
	
	
	$("p").filter( function() {
	    return $.trim($(this).html()) == '';
	}).remove();
	$("h5").prev("br").remove();
	

  //  $('.program_description ul.countries li').each(function(){
  //  	var kls=this.className;
  //  	$(this).css("background-image","url(/wp-content/themes/Adventure/images/icons/flags/16/"+kls+".png)");
  //  	$(this).css("background-repeat","no-repeat");
  //   	$(this).css("background-position","0 2px");
  //  	
  //  });
	$('.program_description strong').each(function(){
			var txt=$(this).html();
			if (txt=="_"){
				$(this).remove();
			}
		
	});
	


	$('.program_time').each(function(){
		var txt=$(this).html();
		txt=txt.replace("25:","01:");
		txt=txt.replace("08:00 - 08:05","08:00 onwards");		
		txt=txt.replace("08:05 - 08:10","");		
		txt=txt.replace("14:00 - 15:05","14:00");
		//txt=txt.replace("23:00 - 23:05","23:00");
		//txt=txt.replace("19:00 - 19:15","19:00");
		txt=txt.replace("21:15 - 23:00","21:15");
		txt=txt.replace("22:45 - 22:50","22:45");
		txt=txt.replace("23:00 - 23:05","23:00");
		txt=txt.replace("22:00 - 22:05","22:00");
		txt=txt.replace("19:00 - 19:05","19:00");
		txt=txt.replace("18:45 - 18:50","18:45");
		txt=txt.replace("19:15 - 19:20","19:15");
		txt=txt.replace("14:15 - 14:20","14:15");
		
		//console.log("my text="+txt);
		$(this).html(txt);
	});
	$('.program_room').each(function(){
		var txt=$(this).html();
		txt=txt.replace("_","");
		$(this).html(txt);
	});	
   // $('#content a').each(function(){
   // 	if ($(this).attr('href')){
   //         if ($(this).attr('href').indexOf('.pdf') > -1){
   //                 $(this).addClass('pdf-link');
   //                 this.target="_blank";
   //         }
   // 	}	
   // });

	var subjectFormInput=$('input[name=your-subject]');
	var subject=$.getUrlVar('subject');
	if (subjectFormInput && subject!='undefined' && subject!=undefined  ){
		subjectFormInput.val(decodeURIComponent(subject));
	}


  // $('#menu-main-navigation-1').masonry({
  //   itemSelector: '.parent-level',
  //  columnWidth : 110
  // });



});
});


//    jQuery(document).ready(function($){
//    	if (navigator.platform == "iPad") return;
//    	jQuery("img").not(".cycle img, img.nolazyload, img.lsshowcase_img").lazyload({
//      		effect:"fadeIn",
//      		placeholder: "/wp-content/themes/Adventure/images/grey.gif"
//    	});
//    	
//    	function remove_cufon(selector) {
//    	    $(selector).html( cufon_text(selector) );
//    	    return true;
//    	}
//    
//    	function cufon_text(selector) {
//    	    var g = '';
//    	    $(selector +' cufon cufontext').each(function() {
//    	        g = g + $(this).html();
//    	    }); 
//    	    return $.trim(g);
//    	}
//    	
//        //Cufon.replace('.with_gradient .regular-list li, .with_gradient p, .with_gradient td, .with_gradient .dd_content_wrap ', { fontFamily: 'Myriad Pro Regular', hover: true }); 
//     	// #footer ul li a,
//    	//Cufon.replace('h1, h2, h3, h4, h5,  span.artist-name a, ul.megaMenu li ul li a, .with_gradient th ,  #blog_sidebar a', { fontFamily: 'Myriad Pro Bold', hover: true }); 
//    
//    
//    	Cufon.replace('h1, h2, h3, h4, h5,  span.artist-name a, .with_gradient th ,  #blog_sidebar a', { fontFamily: 'Myriad Pro Bold', hover: true }); 
//    
//    	//remove_cufon('.oe_textdirection');
//    	
//    });
//    
//    /***************************************************
//    	GRID PORTFOLIO/GRID HOMEPAGE  IMAGE HOVER
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {
//    		$(".item-hover").hover(function(){
//    		$(this).find(".portfolio-thumbnail").stop(true, true).animate({ opacity: 'show' }, 1000);
//    	}, function() {
//    		$(this).find(".portfolio-thumbnail").stop(true, true).animate({ opacity: 'hide' }, 1000);		
//    	});
//    		
//    	});
//    	});
//    /***************************************************
//    	DATA REL PRETTYPHOTO JQUERY
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    	$(document).ready(function() { 
//    		$('a.portfolio-item-preview').each(function() {
//            	$(this).removeAttr('data-rel').attr('rel', 'prettyPhoto');
//    		});
//    		$('.gallery-icon a').each(function() {
//            	$(this).attr('rel', 'prettyPhoto');
//    		});
//    		$("a.fancybox").prettyPhoto({opacity:0.80,default_width:853,default_height:480,theme:'light_rounded',hideflash:false,modal:false});
//    	});
//    });
//    /***************************************************
//    					TABIFY 
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {		
//    			$(document).ready(function () {
//    				$('#menu').tabify()
//    				
//    			});		
//    	});
//    		});
//    
//    /***************************************************
//    		FORM VALIDATION JAVASCRIPT
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {
//    	$('form#contact-form').submit(function() {
//    		$('form#contact-form .error').remove();
//    		var hasError = false;
//    		$('.requiredField').each(function() {
//    			if(jQuery.trim($(this).val()) == '') {
//                	var labelText = $(this).prev('label').text();
//                	$(this).parent().append('<div class="error">You forgot to enter your '+labelText+'</div>');
//                	$(this).addClass('inputError');
//                	hasError = true;
//                } else if($(this).hasClass('email')) {
//                	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
//                	if(!emailReg.test(jQuery.trim($(this).val()))) {
//                		var labelText = $(this).prev('label').text();
//                		$(this).parent().append('<div class="error">You entered an invalid '+labelText+'</div>');
//                		$(this).addClass('inputError');
//                		hasError = true;
//                	}
//                }
//    		});
//    		if(!hasError) {
//    			$('form#contact-form input.submit').fadeOut('normal', function() {
//    				$(this).parent().append('');
//    			});
//    			var formInput = $(this).serialize();
//    			$.post($(this).attr('action'),formInput, function(data){
//    				$('form#contact-form').slideUp("fast", function() {
//    					$(this).before('<div class="simple-success">Your email was successfully sent. We will contact you as soon as possible.</div>');
//    				});
//    			});
//    		}
//    
//    		return false;
//    
//    	});
//    });
//    });
//    
//    /***************************************************
//    		CYCLE SLIDER
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {
//        $('#details').cycle({
//    		fx:     'fade', 
//            prev:    '#prev',
//            next:    '#next',
//    		speedIn:  800, 
//    		speedOut: 800, 
//    		delay:   7000
//           
//    	});
//    	
//    	});
//    });
//    
//    /***************************************************
//    	  ADDITIONAL CODE FOR FILTER NAVIGATION
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function($){
//    	$('ul#filterable a').click(function() {
//    		$(this).css('outline','none');
//    		$('ul#filterable .current').removeClass('current');
//    		$(this).parent().addClass('current');
//    	
//    		return false;
//    	});
//    });
//    });
//    
//    /***************************************************
//    		PORTFOLIO IMAGE HOVER
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {  
//                $('.portfolio-img, .gallery-icon').each(function() {
//                    $(this).hover(
//                        function() {
//                            $(this).stop().find('img, span.video-image').animate({ opacity: 0.5 }, 400);
//                        },
//                       function() {
//                           $(this).stop().find('img, span.video-image').animate({ opacity: 1.0 }, 400);
//                       })
//                    });
//    });
//    });
//    	jQuery.noConflict()(function($){
//    $(document).ready(function() { 
//     $('.portfolio-img').each(function() {
//     $(this).hover(
//     function() {
//     $(this).stop().animate({ opacity: 0.5 }, 400);
//     },
//     function() {
//     $(this).stop().animate({ opacity: 1.0 }, 400);
//     })
//     });
//    });
//    });
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {  
//                $('.portfolio-img-fancy').each(function() {
//                    $(this).hover(
//                        function() {
//                            $(this).stop().animate({ opacity: 0.7 }, 400);
//                        },
//                       function() {
//                           $(this).stop().animate({ opacity: 1.0 }, 400);
//                       })
//                    });
//    });
//    });
//    /***************************************************
//    		TWITTER FEEDS
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() { 
//    $(".tweet").tweet({
//                username: "trendywebstar",/*CHANGE trendyWebStar WITH YOUR OWN USERNAME*/
//                join_text: null,
//                avatar_size: null,/*AVATAR*/
//                count: 1,/*NUMBER OF TWEETS*/
//                auto_join_text_default: "we said,", 
//                auto_join_text_ed: "we",
//                auto_join_text_ing: "we were",
//                auto_join_text_reply: "we replied to",
//                auto_join_text_url: "we were checking out",
//                loading_text: "loading tweets..."
//        });
//    	});
//    });
//    
//    /***************************************************
//    		CYCLE SLIDE
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {
//        $('#slider-two-third').cycle({
//    		fx:'fade',
//    		speedIn:  1000, 
//    		speedOut: 1000, 
//    		delay:   2000
//    		
//    	});
//    });
//    });
//    
//    /***************************************************
//    		PRETTY PHOTO
//    ***************************************************/
//    jQuery.noConflict()(function($){
//    $(document).ready(function() {  
//    
//    		$('#google-picasa-album').photoMosaic({
//    			input: 'html',
//    		    columns: 4,
//    		    width: '950',
//    		    height: 'auto',
//    		    padding: 15,
//    		    random: false,
//    		    modal_name: 'prettyPhoto',
//    		    modal_group: true,
//                modal_ready_callback: function($pm) {
//    				$('ul.blog-pagination, .google-picasa-container .horizontal-line').css("opacity","1");	
//    				$('.google-picasa-container').css("background","transparent");	
//    				$('.google-picasa-container').css("background-image","none");	
//                    $('a[rel^="prettyPhoto"]', $pm).prettyPhoto(
//    					{
//    					opacity:0.80,
//    					default_width:500,
//    					default_height:344,
//    					theme:'light_rounded',
//    					hideflash:false
//    					}
//    				);
//                }		
//    		});
//    
//    							
//    
//    
//    
//    	$('#google-picasa-album-toto').photoMosaic({
//    		input: 'html',
//    	    columns: 4,
//    	    width: '960',
//    	    height: 'auto',
//    	    padding: 5,
//    	    random: false,
//    	    modal_name: 'prettyPhoto',
//    	    modal_group: true,
//    	    modal_callback: function($photomosaic) {
//    //	    	alert("modal callback !");
//    //	 		$('a[rel^="lightbox_evo"]', $photomosaic).lightbox();
//    	    	$("a[rel^='prettyPhoto']", $photomosaic ).prettyPhoto({opacity:0.80,default_width:500,default_height:344,theme:'light_rounded',hideflash:false});
//    	    },
//    	   //
//    	   // modal_ready_function : function($photomosaic){
//    	   // 	alert("modal ready");
//    	   //    $("a[rel^='prettyPhoto']").prettyPhoto({opacity:0.80,default_width:500,default_height:344,theme:'light_rounded',hideflash:false,modal:false});
//    	   // }
//    	});
//    
//    
//    
//    	$("a[rel^='prettyPhoto']").prettyPhoto({opacity:0.80,default_width:500,default_height:344,theme:'light_rounded',hideflash:false,modal:false});
//    
//    });
//    });
//    --------------------------------------------------------------------*/
//    /*PORTFOLIO FILTERABLE CODE*/
//    /*----------------------------------------------------------------------*/
//    jQuery.noConflict()(function($){
//    	jQuery(document).ready(function($){
//    		var 
//    		speed = 700,   // animation speed
//    		$wall = $('#portfolio').find('.portfolio-container ul')
//    		;
//    		$wall.masonry({
//    			singleMode: true,
//    			// only apply masonry layout to visible elements
//    			itemSelector: '.one-fourth:not(.invis)',
//    			animate: true,
//    			animationOptions: {
//    				duration: speed,
//    				queue: false
//    			}
//    		});
//    		$('#filterable a').click(function(){
//    			var colorClass = '.' + $(this).attr('class');
//    			if(colorClass=='.all') {
//    				// show all hidden boxes
//    				$wall.children('.invis')
//    				.toggleClass('invis').fadeIn(speed);
//    			} else {  
//    				// hide visible boxes 
//    				$wall.children().not(colorClass).not('.invis')
//    				.toggleClass('invis').fadeOut(speed);
//    				// show hidden boxes
//    				$wall.children(colorClass+'.invis')
//    				.toggleClass('invis').fadeIn(speed);
//    			}
//    			$wall.masonry();
//    			return false;
//    		});
//    	});
//    });
//    
//    
//    jQuery.noConflict()(function($){
//    	jQuery(document).ready(function($){
//    		var 
//    		speed = 700,   // animation speed
//    		$wall = $('#portfolio').find('.portfolio-container ul')
//    		;
//    		$wall.masonry({
//    			singleMode: true,
//    			// only apply masonry layout to visible elements
//    			itemSelector: '.one-third:not(.invis)',
//    			animate: true,
//    			animationOptions: {
//    				duration: speed,
//    				queue: false
//    			}
//    		});
//    		$('#filterable a').click(function(){
//    			var colorClass = '.' + $(this).attr('class');
//    			if(colorClass=='.all') {
//    				// show all hidden boxes
//    				$wall.children('.invis')
//    				.toggleClass('invis').fadeIn(speed);
//    			} else {  
//    				// hide visible boxes 
//    				$wall.children().not(colorClass).not('.invis')
//    				.toggleClass('invis').fadeOut(speed);
//    				// show hidden boxes
//    				$wall.children(colorClass+'.invis')
//    				.toggleClass('invis').fadeIn(speed);
//    			}
//    			$wall.masonry();
//    			return false;
//    		});
//    	});
//    });
//    
//    
//    jQuery.noConflict()(function($){
//    	jQuery(document).ready(function($){
//    		var 
//    		speed = 700,   // animation speed
//    		$wall = $('#portfolio').find('.portfolio-container ul')
//    		;
//    		$wall.masonry({
//    			singleMode: true,
//    			// only apply masonry layout to visible elements
//    			itemSelector: '.one-fifth:not(.invis)',
//    			animate: true,
//    			animationOptions: {
//    				duration: speed,
//    				queue: false
//    			}
//    		});
//    		$('#filterable a').click(function(){
//    			var colorClass = '.' + $(this).attr('class');
//    			if(colorClass=='.all') {
//    				// show all hidden boxes
//    				$wall.children('.invis')
//    				.toggleClass('invis').fadeIn(speed);
//    			} else {  
//    				// hide visible boxes 
//    				$wall.children().not(colorClass).not('.invis')
//    				.toggleClass('invis').fadeOut(speed);
//    				// show hidden boxes
//    				$wall.children(colorClass+'.invis')
//    				.toggleClass('invis').fadeIn(speed);
//    			}
//    			$wall.masonry();
//    			return false;
//    		});
//    	});
//    });
//    
//    
//    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
//    
 
 jQuery.extend({
   getUrlVars: function(){
     var vars = [], hash;
     var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
     for(var i = 0; i < hashes.length; i++)
     {
       hash = hashes[i].split('=');
       vars.push(hash[0]);
       vars[hash[0]] = hash[1];
     }
     return vars;
   },
   getUrlVar: function(name){
     return jQuery.getUrlVars()[name];
   }
 });
 




	
	
	
