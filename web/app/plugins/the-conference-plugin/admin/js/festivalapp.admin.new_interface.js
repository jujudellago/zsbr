var ManageDrawer,ManagePane,ManageScheduleName;
jQuery(function(){
	jQuery('.schedule-options').hide();
	ManageDrawer = jQuery('#schedule-manage-drawer')
	ManagePane = jQuery('#schedule-manage-pane')
	ManageScheduleName = ManagePane.find('h1#schedule-manage-name');
	jQuery('.schedule-name').live('click',function(){
		loadSchedule(jQuery(this));
	});
	
	jQuery('.festival-day').live('click',function(){
		loadDay(jQuery(this));
	});
	
	jQuery('.this-schedule-settings').live('click',function(){
		var scheduleID = jQuery(this).parents('.schedule-list-li').attr('id').match(/schedule-(.*)/)[1];
		location.href = 'admin.php?page=topquark&noheader=true&package=FestivalApp&toppage=schedule_settings&_year='+jQuery('#schedule-year').html()+'&schedule='+scheduleID;
		return;
	});
	
	jQuery('.delete-this-schedule').live('click',function(){
		var scheduleID = jQuery(this).attr('id').match(/delete-schedule-(.*)/)[1];
	    var str = "Are you sure you wish to delete this schedule?  This cannot be undone.  \n\nAll events on the schedule will be deleted."; 
	    if (confirm(str)){
			jQuery.post(getWPAjaxURL(),
				{
					'action':'admin_schedule_action',
					'do':'delete',
					'year':jQuery('#schedule-year').html(),
					'schedule':scheduleID
				}
				,function(data){
					jQuery('li#schedule-'+scheduleID).hide('slow',function(){
						jQuery('li#schedule-'+scheduleID).remove();
					});
					ManagePane.find('.schedule-'+scheduleID+'-day').each(function(){
						jQuery(this).remove();
					});
					ManageScheduleName.empty();
				}
			);
		}
	});
	
	jQuery('.schedule-orphans').live('click',function(){
		var scheduleID = jQuery(this).parents('.schedule-list-li').attr('id').match(/schedule-(.*)/)[1];
		location.href = 'admin.php?page=topquark&noheader=true&package=FestivalApp&toppage=schedule_orphans&_year='+jQuery('#schedule-year').html()+'&schedule='+scheduleID;
		return;
	});
	
	jQuery('#schedule-refresh').click(function(){
		jQuery(this).addClass('off');
		var thisDay = jQuery('.festival-day.on');
		var scheduleID = thisDay.parents('.schedule-list-li').attr('id').match(/schedule-(.*)/)[1];
		var scheduleDay = thisDay.attr('class').match(/day-([^ ]*)/)[1];
		var contentID = 'schedule-'+scheduleID+'-day-'+scheduleDay+'-content';
		jQuery('#'+contentID).remove();
		loadDay(thisDay);
	});
	
	var QueryType = getParameterByName('type');
	var QueryDay = getParameterByName('day');
	if (QueryType != ''){
		loadSchedule(jQuery('#schedule-'+QueryType).find('.schedule-name'));
		if (QueryDay != ''){
			loadDay(jQuery('#schedule-'+QueryType+'-day-'+QueryDay));
		}
	}
});

loadStatistics = function(){
	alert('Statistics feature coming soon....');
}

loadSchedule = function(thisSchedule){
	var scheduleName = thisSchedule.html();
	var scheduleID = thisSchedule.parent('li').attr('id').match(/schedule-(.*)/)[1];
	
	if (scheduleID == 'new'){
		location.href = 'admin.php?page=topquark&noheader=true&package=FestivalApp&toppage=schedule_settings&_year='+jQuery('#schedule-year').html()+'&schedule=New';
		return;
	}
	else if(scheduleID == 'statistics'){
		loadStatistics();
		return;
	}
	
	jQuery('#schedule-refresh').addClass('off');
	if (thisSchedule.next('.schedule-options').hasClass('visible')){
		// If the schedule is visible, simply hide it
		thisSchedule.next('.schedule-options').hide('fast',function(){
			ManageScheduleName.html('');
			ManagePane.find('.schedule-day').each(function(){
				jQuery(this).hide();
			});
			jQuery(this).removeClass('visible');
		});
	}
	else{
		// Otherwise, hide the visible days for whatever schedule is currently showing
		jQuery('.schedule-options.visible').hide('fast',function(){
			jQuery(this).removeClass('visible');
		});
		// Hide visible schedules within the manage pane
		ManagePane.find('.schedule-day').each(function(){
			jQuery(this).hide();
		});
		
		thisSchedule.next('.schedule-options').show('fast',function(){
			ManageScheduleName.html(scheduleName);
			ManagePane.find('.schedule-'+scheduleID+'-day.visible').each(function(){
				jQuery('#schedule-refresh').removeClass('off');
				jQuery(this).show();
			});
			jQuery(this).addClass('visible');
		});
	}
}

loadDay = function(thisDay){
	//var thisDay = jQuery(this);
	if (thisDay.hasClass('loading')){
		// avoid double duties
		return;
	}
	thisDay.addClass('loading');
	var scheduleID = thisDay.parents('.schedule-list-li').attr('id').match(/schedule-(.*)/)[1];
	var scheduleDay = thisDay.attr('class').match(/day-([^ ]*)/)[1];
	var contentID = 'schedule-'+scheduleID+'-day-'+scheduleDay+'-content';
	var scheduleContent = jQuery('#'+contentID);
	var scheduleRefresh = jQuery('#schedule-refresh');
	if (scheduleContent.length){
		// it already exists, just show it
		if (scheduleContent.hasClass('visible')){
			scheduleContent.hide().removeClass('visible');
			thisDay.removeClass('on');
			scheduleRefresh.addClass('off');
		}
		else{
			jQuery('.schedule-day.visible').hide().removeClass('visible');
			jQuery('.festival-day.on').removeClass('on');
			scheduleContent.show().addClass('visible');
			thisDay.addClass('on');
			scheduleRefresh.removeClass('off');
		}
		thisDay.removeClass('loading');
	}
	else{
		scheduleContent = jQuery('<div></div>');
		scheduleContent.attr('id',contentID);
		scheduleContent.addClass('schedule-day schedule-'+scheduleID+'-day');
		jQuery.get(getWPAjaxURL(),
			{
				'action':'get_admin_schedule',
				'year':jQuery('#schedule-year').html(),
				'schedule':scheduleID,
				'day':scheduleDay
			}
			,function(data){
				scheduleContent.html(data);
				scheduleContent.find('div.ShowListingTableWrapper').resizable({
					handles: 'e',
					resize:function(){
						if (jQuery(this).width() < jQuery(this).children('.ShowListingTable').width()){
							jQuery(this).resizable('option','minWidth',jQuery(this).children('.ShowListingTable').width());
						}
					},
					stop:function(){
						if (jQuery(this).width() < jQuery(this).children('.ShowListingTable').width()){
							jQuery(this).resizable('option','minWidth',jQuery(this).children('.ShowListingTable').width());
						}
					}
				});
				ManagePane.append(scheduleContent);
				var tableElement = scheduleContent.find('.ShowListingTable:first');
				tableElement.find('tr:first td.ShowListingRowHeadingCell').each(function(){
					jQuery(this).attr('width','');
				});
				var columns = tableElement.find('.ShowListingColumnHeadingCell');
				var columnWidth = parseInt(100 / columns.length) + '%';
				tableElement.find('.ShowListingColumnHeadingCell').each(function(){
					jQuery(this).attr('width',columnWidth);
				});
				jQuery('.schedule-day.visible').hide().removeClass('visible');
				jQuery('.festival-day.on').removeClass('on');
				scheduleContent.show().addClass('visible');
				thisDay.addClass('on').removeClass('loading');
				scheduleRefresh.removeClass('off');
			}
		);
	}
}

