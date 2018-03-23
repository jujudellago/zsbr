/*
	Milkbox - required: mootools.js v1.2 core + more: Assets
	v1.3.1
		- feature: hide milkbox links hover default firefox tooltips
		- feature: hide select and textarea tags while playing
		
		- fix: you can use milkbox with image links that contains url variables (ex. image.jpg?id=77&lang=ita)
		- fix: a little preload problem when using 2-image galleries
		
		
	by Luca Reghellin (http://www.reghellin.com) July 2008, MIT-style license.
	Inspiration from Slimbox by Christophe Beyls (http://www.digitalia.be) 
	and from THE VERY FIRST MAN ON THE MOON: Lokesh Dhakar (http://www.lokeshdhakar.com/projects/lightbox2/)
	AND OF COURSE, SPECIAL THANKS TO THE MOOTOOLS DEVELOPERS
*/

var Milkbox = new Class({

	Implements:Options,
	
	options:{//set all the options here
		overlayOpacity:0.7,
		topPosition:40,
		initialWidth:250,
		initialHeight:250,
		resizeDuration:500,
		resizeTransition:'sine:in:out',/*function (ex. Transitions.Sine.easeIn) or string (ex. 'quint:out')*/
		hoverBackgroundPosition:'0 -23px',
		autoPlay:false,
		autoPlayDelay:7,
		removeTitle:false,
		openOnClick: true,
		blockContextMenu: false,
		imageAttribute: 'href', /* the attribute in the <a> tag that contains the URL to the full image */
		imageTransition: 'crossfade', /* crossfade = crossfade pictures, fade = fade to black then fade new picture in */
		useMusicFile: false, /* true to show a music player button. Expects this.musicFile variable to be filled with URL */
		musicFile: '', /* only set if this.useMusicFile == true */
		constrainSizeToWindow: true
	},
	
	initialize: function(options){
		
		this.setOptions(options);
		this.galleries = [];
		this.currentImage = null;
		this.currentIndex = null;
		this.currentGallery = null;
		
		this.specialDescription = null;//for showThisImage
		this.activated = false;//used in showThisImage 
		
		this.mode = null;//'singleImage','imageGallery','showThisImage'
		this.closed = true;
		this.busy = true;//to control keyboard and autoplay events
		
		this.intObj = null;
		
		this.formtags = null;
		
		this.loadedImages = [];//to check the preloaded images
		
		this.prepareGalleries();
		//if no galleries, stop here and prevent extra memory usage.
		//so you can keep milkbox in every page of a site.
		if(this.galleries.length == 0){ return; };
		
		this.slideshow = 'paused';
				
		this.initMilkbox();
		
	},//end init
	
	initMilkbox:function(){

		this.prepareHTML();
		this.prepareEffects();
		this.prepareEvents();
		
		this.activated = true;
		this.formtags = $$('select','textarea');
		
		if (this.options.blockContextMenu){
            this.center.addEvent('contextmenu',function(e){
                e.stop();
            });
        }

		if (this.options.constrainSizeToWindow){
			this.setMaxSize();
		}
	},
	
	setMaxSize:function(){
		this.max_width = $(window).getSize().x 
			- this.center.getStyle('border-left-width').toInt() 
			- this.center.getStyle('border-right-width').toInt()
			- this.center.getStyle('padding-left').toInt()
			- this.center.getStyle('padding-right').toInt()
			- 20; // Last amount just for good measure
		this.max_height = $(window).getSize().y 
			- this.options.topPosition 
			- this.center.getStyle('border-top-width').toInt() 
			- this.center.getStyle('border-bottom-width').toInt()
			- this.center.getStyle('padding-top').toInt()
			- this.center.getStyle('padding-bottom').toInt()
			- 80; // Last amount is to account for description bar
	},
	
	//runs only 1 time per gallery
	openMilkbox:function(gallery,index){
		if(this.formtags != null && this.formtags.length != 0){ this.formtags.setStyle('display','none') };

		this.overlay.setStyles({ 'top': -$(window).getScroll().y,'height':$(window).getScrollSize().y+$(window).getScroll().y });
		this.navigation.setStyles({ 'top': $(window).getScroll().y});
		
		this.center.addClass('mbLoading');
		this.center.setStyle('top',$(window).getScroll().y+this.options.topPosition);

		this.currentGallery = gallery;
		this.currentIndex = index;
		this.overlay.tween('opacity',this.options.overlayOpacity);//onComplete: center.tween opacity
		
		if(gallery.length == 1){
			this.mode = 'singleImage';
			this.loadImages(gallery[index].getProperty(this.options.imageAttribute));
		} else {
			this.mode = 'imageGallery';
			
			var images = gallery.map(function(item){ return item.getProperty(this.options.imageAttribute); },this);
			
			$$(this.prev, this.next, this.play, this.count,this.music).setStyles({'display':'block'});
			this.navigation.setStyles({'visibility':'visible','display':'block'});
			var border = this.center.getStyle('border-right-width').toInt();//border-right is just ok for design purposes..
			var navWidth = this.prev.getSize().x+this.next.getSize().x+this.play.getSize().x+this.close.getSize().x+border;
			this.navigation.setStyle('width',navWidth);
			//this.description.setStyle('margin-right',navWidth);
			
			var next = (index != images.length-1) ? images[index+1] : images[0];
			var prev = (index != 0) ? images[index-1] : images[images.length-1];
			var preloads = (prev == next) ? [prev] : [prev,next]; //if gallery.length == 2, then prev == next

			this.loadImages(images[index],preloads);
		}
		
		this.closed = false;
	},
	
	//call with js
	showThisImage:function(image,description){
		
		//if init was not done because of the absence of galleries, do it now.
		if(!this.activated){ this.initMilkbox(); }
		
		this.mode = 'showThisImage';
		
		this.specialDescription = description;
		
		this.overlay.setStyles({ 'top': -$(window).getScroll().y,'height':$(window).getScrollSize().y+$(window).getScroll().y });
		
		this.center.addClass('mbLoading');
		this.center.setStyle('top',$(window).getScroll().y+this.options.topPosition);
		
		//this.overlay.tween('opacity',this.options.overlayOpacity);//onComplete: center.tween opacity
		this.loadImages(image);
		
		this.closed = false;
	},

 	//see loadImages()
 	showImage:function(image){
 		
 		if(this.closed){ return; };//if you close the Milkbox and an onload event is still running

 		var imageBoxSize = this.image.getSize();
 		
		if (this.options.imageTransition == 'crossfade'){
			// The first time through, when hidden_image is null, we'll assign 
			// image_alt to hidden_image
			if (this.hidden_image == null){
				this.visible_image = this.image;
				this.hidden_image = this.image_alt;
			}
			else{
				this.visible_image = this.hidden_image;
				this.hidden_image = this.image;
				this.image = this.visible_image;
			}
		}
		else{
 			this.image.setStyles({'opacity':0, 'width':'', 'height':''});
		}
 		
 		var imageSize = new Hash(image.getProperties('width','height')).map(function(item, index){
			return item.toInt();
		});
		
		if (this.options.constrainSizeToWindow){
			this.setMaxSize();  // In case they've resized the window
			if (imageSize.width > this.max_width){
				imageSize.height = (this.max_width / imageSize.width * imageSize.height).toInt();
				imageSize.width = this.max_width;
			}
			if (imageSize.height > this.max_height){
				imageSize.width = (this.max_height / imageSize.height * imageSize.width).toInt();
				imageSize.height = this.max_height;
			}
			image.width = imageSize.width;
			image.height = imageSize.height;
		}
 		
 		var centerSize = new Hash(this.center.getStyles('width','height')).map(function(item, index){
 			return item.toInt();
		});
 		
 		var targetSize = {};
 		
 		if(imageSize.width != centerSize.width){ 
 			targetSize.width = imageSize.width;
 			targetSize.marginLeft = -(imageSize.width/2).round();
 		};
 		
 		var gap = (imageBoxSize.y > 0) ? centerSize.height - imageBoxSize.y : 0; 

 		var targetHeight = imageSize.height + gap;

 	   targetSize.height = targetHeight;
 	   
		//so nav doesn't move when you click next/prev
		this.image.setStyles({'width':imageSize.width, 'height':imageSize.height, 'margin-left':'-'+(imageSize.width/2).toInt()});
		this.image.setStyle('margin-left',-1*(imageSize.width/2).toInt());

 		this.center.removeClass('mbLoading');

		if (this.options.imageTransition == 'fade'){
			this.image.setStyles({'width':imageSize.width, 'height':imageSize.height});
		}

 		this.center.morph(targetSize);//onComplete: show all items

 	},
 	
	loadImages:function(currentImage,preloads){
			var loadImage = new Asset.image(currentImage, { onload:function(img){
				this.currentImage = img;
				if(!this.loadedImages.contains(currentImage)){ this.loadedImages.push(currentImage); };//see next/prev events
				$$(this.next,this.prev,this.close).setStyle('backgroundPosition','0 0');
				this.showImage(this.currentImage);
			}.bindWithEvent(this)});
			
			if(preloads && !this.loadedImages.contains(preloads)){
				var preloadImages = new Asset.images(preloads, { onComplete:function(img){
					preloads.each(function(item){
						if(!(this.loadedImages.contains(item))){ this.loadedImages.push(item); }
					}.bind(this));
				}.bindWithEvent(this)});
			};
			
	},
	
	//all the main events
	prepareEvents:function(){
	
		//galleries
		if (this.options.openOnClick){
			this.galleries.each(function(gallery){
				$$(gallery).addEvent('click',function(e){
					var button=($(e.target).match('a')) ? $(e.target) : $(e.target).getParent('a');
					e.preventDefault();
				
					if(this.options.autoPlay){
						this.autoPlay({ gallery:gallery, index:gallery.indexOf(button) });
					}
				
					else{ this.openMilkbox(gallery, gallery.indexOf(button)); }
				
				}.bindWithEvent(this));
			},this);
		}
		
		//next, prev, see next_prev_aux()
		this.next.addEvent('click',this.next_prev_aux.bindWithEvent(this,'next'));
		this.prev.addEvent('click',this.next_prev_aux.bindWithEvent(this,'prev'));
		this.play.addEvent('click',this.pause_play_aux.bindWithEvent(this,'play'));
		this.pause.addEvent('click',this.pause_play_aux.bindWithEvent(this,'pause'));
		
		//keyboard next/prev/close
		$(window.document).addEvent('keydown',function(e){
			if(this.mode != 'imageGallery' || this.busy == true){ return; };
			if(e.key == 'right'){ this.next_prev_aux(e,'next'); }
			else if(e.key == 'space' && this.slideshow == 'playing'){ this.pause_play_aux(e,'pause'); e.preventDefault(); }
			else if(e.key == 'space' && this.slideshow != 'playing'){ this.pause_play_aux(e,'play'); e.preventDefault(); }
			else if(e.key == 'left'){ this.next_prev_aux(e,'prev'); }
			else if(e.key == 'esc'){ this.closeMilkbox(); }
		}.bindWithEvent(this));
		
		
		//css hover doesn't work in ie6, so I must do it via js...
		$$(this.next,this.prev,this.close).addEvents({
				'mouseover':function(e){ 
					var button=($(e.target).match('a')) ? $(e.target) : $(e.target).getParent('a');
					button.setStyle('backgroundPosition',this.options.hoverBackgroundPosition); 
				}.bindWithEvent(this),
				'mouseout':function(){ this.setStyle('backgroundPosition','0 0'); }
		});

		//overlay
		this.overlay.get('tween').addEvent('onComplete',function(){
			if(this.overlay.getStyle('opacity') == this.options.overlayOpacity){ 
				this.center.tween('opacity',1);
			} else if(this.overlay.getStyle('opacity') == 0) {
				this.overlay.setStyles({'height':0,'top':''});
			};
		}.bindWithEvent(this));
		
		//center
		this.center.get('morph').addEvent('onComplete',function(){
			
			 this.image.empty();
 			 this.image.grab(this.currentImage);
			 if (this.options.imageTransition == 'crossfade'){
			 	this.hidden_image.tween('opacity',0);
			 }
			 this.image.tween('opacity',1);
					 			 
			 var d = (!(this.mode == 'showThisImage')) ? this.currentGallery[this.currentIndex].retrieve('title') : this.specialDescription;
			 if($chk(d)){ this.description.innerHTML = d; };
			 this.bottom.setStyle('padding-top',this.description.getSize().y + 'px');
			 
			 if(this.mode == 'imageGallery'){ 
			 	this.count.innerHTML = (this.currentIndex+1)+' of '+this.currentGallery.length; 
			 }
			 
			 var currentCenterHeight = this.center.getStyle('height').toInt();
			 
			 //this.navigation.setStyle('height',this.bottom.getStyle('height').toInt());//to have the right-border height == total bottom height
			 var bottomSize = this.bottom.getSize().y;
			 
			 //after the 1st time, currentCenterHeight is always > this.image.getSize().y
			 var targetOffset = (currentCenterHeight > this.image.getSize().y) ? (this.bottom.getSize().y+this.image.getSize().y)-currentCenterHeight : bottomSize;
				
			 this.bottom.setStyle('display','none');//to avoid rendering problems during setFinalHeight

			 this.center.retrieve('setFinalHeight').start(currentCenterHeight,currentCenterHeight+targetOffset);
			/* */
		}.bindWithEvent(this));
		
		this.center.retrieve('setFinalHeight').addEvent('onComplete',function(){
			
			this.bottom.setStyles({'visibility':'visible','display':'block'});
			$$(this.description,this.navigation).setStyle('visibility','visible');
			//reset overlay height based on position and height
			var scrollSize = $(window).getScrollSize().y;
			var scrollTop = $(window).getScroll().y;
			
			this.overlay.setStyles({'height':scrollSize+scrollTop, 'top':-scrollTop });
			this.busy = false;
			
		}.bindWithEvent(this));
		
		//reset overlay height and position onResize
		window.addEvent('resize',function(){
			if(this.overlay.getStyle('opacity') == 0){ return; };//resize only if visible
			var scrollSize = $(window).getScrollSize().y;
			var scrollTop = $(window).getScroll().y;
			this.overlay.setStyles({ 'height':scrollSize+scrollTop,'top':-scrollTop });
		}.bindWithEvent(this));

		//close
		$$(this.overlay,this.image,this.close).addEvent('click',function(){ this.closeMilkbox(); }.bindWithEvent(this));
		
	},
	
	next_prev_aux:function(e,direction){
		if(e){ 
			e.preventDefault();
			if(this.intObj){ $clear(this.intObj); this.intObj = null; this.slideshow = 'paused'; this.updatePausePlay(); };
		} //if there's no event obj, than this is called by autoPlay()
		
		else{ if(this.busy){ return; } }//stop autoplay()
		
		this.busy = true; //for keyboard and autoplay
		
		var backupIndex = this.currentIndex;
		
		if(direction == "next"){
			var i= (this.currentIndex != this.currentGallery.length-1) ? this.currentIndex += 1 : this.currentIndex = 0;
			var _i= (this.currentIndex != this.currentGallery.length-1) ? this.currentIndex + 1 : 0;
		} else {
			var i= (this.currentIndex != 0) ? this.currentIndex -= 1 : this.currentIndex = this.currentGallery.length-1;
			var _i= (this.currentIndex != 0) ? this.currentIndex - 1 : this.currentGallery.length-1;		
		};
		
		//this.image.empty();
		this.description.empty();
		//this.count.innerHTML = "&nbsp;";
		
		if(!this.loadedImages.contains(this.currentGallery[i].getProperty(this.options.imageAttribute))){ 
			this.center.addClass('mbLoading');
		};
		this.loadImages(this.currentGallery[i].getProperty(this.options.imageAttribute),[this.currentGallery[_i].getProperty(this.options.imageAttribute)]);
	},
	
	pause_play_aux:function(e,action){
		if (action == 'play'){
			var d = this.options.autoPlayDelay*1000;
			this.slideshow = 'playing';
			this.next_prev_aux(e,'next');
			if(this.mode != 'imageGallery'){ return; };
			this.intObj = this.next_prev_aux.periodical(d,this,[null,'next']);
		}
		else{
			this.slideshow = 'paused';
			if(this.intObj){ $clear(this.intObj); this.intObj = null; }
		}
		this.updatePausePlay();
	},
	
	updatePausePlay:function(){
		if (this.slideshow == 'playing'){
			$$(this.play).setStyle('display','none');
			$$(this.pause).setStyle('display','block');
			this.speed_slider.setStyle('display','block');
		}
		else{
			$$(this.pause).setStyle('display','none');
			$$(this.play).setStyle('display','block');
			this.speed_slider.setStyle('display','none');
		}
	},
	
	autoPlay:function(obj){//obj: gallery, index, delay (in seconds)
		
		var g = (obj && obj.gallery && ($type(obj.gallery) == 'array')) ? obj.gallery : Milkbox.galleries[0];
		var i = (obj && obj.index && ($type(obj.index) == 'number')) ? obj.index : 0;
		var d = (obj && obj.delay && ($type(obj.delay) == 'number')) ? obj.delay*1000 : this.options.autoPlayDelay*1000;
		if(d < this.options.resizeDuration*2){ d = this.options.resizeDuration*2 };
		
		Milkbox.openMilkbox(g,i);
		if(this.mode != 'imageGallery'){ return; };
		this.intObj = this.next_prev_aux.periodical(d,this,[null,'next']);
		
		this.slideshow = 'playing';
		this.updatePausePlay();
	},
	
	closeMilkbox:function(){
		this.cancelAllEffects();
		if(this.intObj){ $clear(this.intObj); };
		
		this.currentImage = null;
		this.currentIndex = null;
		this.currentGallery = null;
 		
		var border = this.center.getStyle('border-right-width').toInt();
		var navWidth = this.close.getSize().x+border;
		this.navigation.setStyles({'visibility':'hidden','display':'none'});
		this.description.setStyle('margin-right',navWidth);
		this.description.empty();
		this.bottom.setStyles({'visibility':'hidden','display':''});
		
   	this.image.setStyles({'opacity':0, 'width':'', 'height':''});
 		this.image.empty();
 		
 		this.count.empty();
		
		this.center.setStyles({'opacity':0,'width':this.options.initialWidth,'height':this.options.initialHeight,'marginLeft':-(this.options.initialWidth/2)});
		this.overlay.tween('opacity',0);//see onComplete in prepareEvents() 
		
		if(this.formtags.length != 0){ this.formtags.setStyle('display','') };
		
		this.mode = null;
		this.closed = true;
	},
	
	cancelAllEffects:function(){
		this.overlay.get('tween').cancel();
		this.center.get('morph').cancel();
		this.center.get('tween').cancel();
		this.center.retrieve('setFinalHeight').cancel();
		this.image.get('tween').cancel();
	},
	
	prepareEffects:function(){
		this.overlay.set('tween',{ duration:'short',link:'cancel' });
		this.center.set('tween',{ duration:'short',link:'chain' });
		this.center.set('morph',{ duration:this.options.resizeDuration,link:'chain',transition:this.options.resizeTransition });
		this.center.store('setFinalHeight',new Fx.Tween(this.center,{property:'height',duration:'short'}));
		this.image.set('tween',{ link:'chain' });
	},
	
	prepareGalleries:function(){
		var families = [];
		var milkbox_a = [];
		
		$$('a').each(function(a){
			//test 'milkbox' and link extension, and collect all milkbox links
			var href = a.getProperty(this.options.imageAttribute);
			if(a.rel && a.rel.test(/^milkbox/i) && href.split('?')[0].test(/\.(gif|jpg|png|jpeg)$/i)){
				if(a.rel.length>7 && !families.contains(a.rel)){ families.push(a.rel); };
				milkbox_a.push(a);
			}
		},this);

		//console.log(milkbox_a)
		
		//create an array of arrays with all galleries
		milkbox_a.each(function(a){
			$ = document.id;
			$(a).store('href',a.getProperty(this.options.imageAttribute));
			$(a).store('rel',a.rel);
			$(a).store('title',a.title);
			if(this.options.removeTitle){ $(a).removeProperty('title'); }
			if(a.rel.length > 7){
				families.each(function(f,i){
					if(a.rel == f){
						if(!this.galleries[i]){ this.galleries[i] = [] };
						this.galleries[i].push($(a));
					};
				},this);
			} else { this.galleries.push([$(a)]); };
		},this);
		
		//console.log(this.galleries)
	},
		
	prepareHTML:function(){		
		
		this.overlay = new Element('div', { 'id':'mbOverlay','styles':{ 'opacity':0,'visibility':'visible','height':0,'overflow':'hidden' }}).inject($(document.body));
		
		this.center = new Element('div', {'id':'mbCenter', 'styles':{'width':this.options.initialWidth,'height':this.options.initialHeight,'marginLeft':-(this.options.initialWidth/2),'opacity':0/**/}}).inject($(document.body));
		this.image = new Element('div', {'id':'mbImage'}).inject(this.center);
		if (this.options.imageTransition == 'crossfade'){
			this.image_alt = new Element('div', {'id':'mbImageAlt'}).inject(this.center);
		}
		
		this.bottom = new Element('div',{'id':'mbBottom'}).inject(this.center).setStyle('visibility','hidden');
		this.navigation = new Element('div',{'id':'mbNavigation'}).setStyle('visibility','hidden');
		this.description = new Element('div',{'id':'mbDescription'}).setStyle('visibility','hidden');
		
		this.bottom.adopt(this.description, new Element('div',{'class':'clear'}));
		
		this.close = new Element('a',{'id':'mbCloseLink'});
		this.next = new Element('a',{'id':'mbNextLink'});
		this.prev = new Element('a',{'id':'mbPrevLink'});
		this.play = new Element('a',{'id':'mbPlayLink'});
		this.pause = new Element('a',{'id':'mbPauseLink'});
		this.count = new Element('span',{'id':'mbCount'});
		
		$$(this.next, this.prev, this.pause, this.play, this.count).setStyle('display','none');
		
		this.speed_slider = new Element('div',{'id':'mbSpeedSlider'});
		this.speed_slider_track = new Element('div',{'id':'mbSpeedSliderTrack'});
		this.speed_slider_track.addClass('slider');
		this.speed_slider_knob = new Element('div',{'id':'mbSpeedSliderKnob'});
		this.speed_slider_knob.addClass('knob');
		this.speed_slider_track.adopt(this.speed_slider_knob);
		this.speed_slider.adopt(this.speed_slider_track);
		this.speed_slider.adopt(new Element('p',{'id':'mbSpeedSliderLabel'}).appendText('Speed'));
		
		this.music = new Element('div',{'id' : 'mbMusic'}).setStyle('display','none');
		if (this.options.useMusicFile){
			this.music_link = new Element('a',{'id' : 'mbMusicLink'});
			this.music_link.href = this.options.musicFile;
			this.music_link.innerHTML = "sound";
			this.music.adopt(this.music_link);
		}
		
		this.navigation.inject($(document.body));
		
		this.navigation.adopt(this.close, this.next, this.pause, this.play, this.prev, this.count,this.speed_slider,this.music,new Element('div',{'class':'clear'}));

		// Create the new slider instance
		this.speed_slider_slider = new Slider($('mbSpeedSliderTrack'), $('mbSpeedSliderKnob'), {
			steps: 20,	// There are 20 steps
			wheel: true,
			range: [1]
		}).set(20 - (this.options.autoPlayDelay*2) + 1);
		
		this.speed_slider_slider.addEvent('change',this.setSpeed.bindWithEvent(this));
			
		this.speed_slider.setStyle('display','none');
		
		// Integrated 1bit to make the music file play
		if (this.options.useMusicFile){
			this.oneBit = new OneBit('lib/js/1bit/1bit.swf');
			this.oneBit.specify('background', 'transparent');
			this.oneBit.specify('playerSize', '11');
			this.oneBit.specify('removeLink', true);
			this.oneBit.apply('a');
		}
	},
	
	setSpeed:function(e){
		this.options.autoPlayDelay = (20 - this.speed_slider_slider.step + 1)/2;
		var d = this.options.autoPlayDelay*1000;
		$clear(this.intObj); this.intObj = null;
		this.intObj = this.next_prev_aux.periodical(d,this,[null,'next']);
	}
		
});//END MILKBOX;

