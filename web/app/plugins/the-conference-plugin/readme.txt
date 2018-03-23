=== The Conference Plugin ===
Contributors: topquarky
Tags: conference, festival, schedule, speakers, app
Requires at least: 3.0
Tested up to: 3.5.1
Donate link: http://topquark.com
Stable tag: 1.1.3.2

Manage a multi-day, multi-scheduled event right within WordPress

== Description ==

Use this plugin to manage the speaker and session information for your conference.  You can input the data either in the easy-to-use admin interface or by uploading an CSV (Excel) file with all of the data contained therein.  The plugin keeps all of the data from year to year so as you use it, you build a rich archive of your event's history.  

This plugin was born when I was running a [small folk festival](http://www.eaglewoodfolk.com) in Southern Ontario.  I needed a way to manage all of the artists and schedules for the festival.  It has since grown in into a highly-customizable, scalable and powerful tool for managing any multi-day, multi-talented (i.e. lots of speakers or artists), multi-scheduled (i.e. several venues, lots of scheduling within the event) event.  It has been used for conferences and festivals.  

Taking into consideration that different events have different vocabularies, you can define your event's vocabulary right on the settings page.  For example, a Festival has Artists and Shows, but a Conference has Speakers and Sessions.  No problem.

You can define multiple schedules within your event.  Perfect for conferences with different streams or festivals with a daytime schedule and an evening schedule.

For larger events with lots of data, I recommend enabling the cache (also on the settings page).  This will greatly speed up the rendering and reduce load on your server (crucial for shared hosting environments).

Adding your event's lineup to a page is as simple as inserting the shortcode `[the_conference_lineup]`.  This will take the lineup from the current year's event and output on the page.  When you drill-down to a speaker's page, you see immediately where they're appearing over the course of your event. 

Adding your event's schedule to a page is as simple as inserting the shortcode `[the_conference_schedule]`.  This will output a grid view of your event's schedule, with all of the names clickable (takes you to the speaker's page) and the session titles clickable (takes you to a description of the session).

The plugin is built on the Top Quark Architecture, and includes a wealth of hooks available to allow you to change all sorts of things.  By writing an add-on plugin, or hiring me to do it for you ;) you can add extra fields for your speakers, change the templates for any of the views and lots more.  I've used the hooks to add multiple language options, streams within a festival, twitter handles for performers and many other things.  The possibilities are limited only by imagination (and, I suppose, budget & time).

== Installation ==

1. The Conference Plugin is available for purchase from [topquark.com](http://topquark.com)
1. Purchase/Download The Conference Plugin from [topquark.com/extend/plugins/the-conference-plugin](http://topquark.com/extend/plugins/the-conference-plugin)
1. Install the ZIP file to your server and activate the plugin

== Screenshots ==

1. The Festivals page (if your vocabulary uses Conference, then this would be the Conferences page)
2. The admin interface to define your schedule
3. The schedule on the front-end.  Everything is customizable through CSS

== Frequently Asked Questions ==

= How do I insert my event's lineup into a page or post =

Very simply.  Add the shortcode `[the_conference_lineup]` (or if your using different vocabulary, like Festival, you can also use `[the_festival_lineup]`).  There are a few options you can include in this shortcode
* `year` - the year you want to display (use the year you defined for your event)
* `style` - choices are `list` (just a list of names with a link), `expanded` (includes photo and brief bio) or `floated` (floated thumbnails)
* `order` - choices are `default` (use the order you've set for your event), `alphabetic` or `random`

So, if you wanted to display the speakers from your 2011 event in alphabetic order, showing floated thumbnails, use the shortcode `[the_conference_lineup year=2011 style=expanded order=alphabetic]`

= Can I exclude certain speakers from the displayed lineup? =

Yes.  There are now two ways you can do this.  When you edit a speaker, if you click on the tab for a specific conference, there is a checkbox that says "Check to prevent this Speaker from being listed in the published lineup".  Any speakers with that checkbox checked will not appear in the lineup as included by the `[the_conference_lineup]` shortcode.  

If you want to put a list of speakers onto a specific page, but only want to include certain speakers (for example, your featured speakers), you can add the attribute `include` to the `[the_conference_lineup]` shortcode.  The value should be a comma separated list of speaker IDs.  You can find the id for a speaker going to the Edit Speaker page.  On the General Info tab, at the top, will be the Speaker ID.  So, for example, you could include the following shortcode in one of your pages (or a sidebar widget, if you want to get creative) - `[the_conference_lineup year="2013" style="floated" include="2,43,21"]` to only show the speakers with the IDs 2, 43 & 21.  The order that they appear will be the same order they are saved in the main lineup as.  

Similarly, there is an `exclude` attribute that will specifically *exclude* certain speakers from the lineup.  `[the_conference_lineup year="2013" style="floated" exclude="2,43,21"]` will show all speakers except those with IDs 2, 43 & 21.

= How do I insert my event's schedule into a page or post =

Very simply.  Add the shortcode `[the_conference_schedule]` (or if your using different vocabulary, like Festival, you can also use `[the_festival_schedule]`).  There are a few options you can include in this shortcode
* `year` - the year you want to display (use the year you defined for your event)
* `include_times` - choices are `true` (default - show times within the table) or `false` (don't show the times)
* `schedule` - the ID of schedule to show by default (defaults to the first schedule alphabetically)  

So, if you wanted to display the schedule from your 2011 event without showing the times and defaulting to Schedule ID #2, use the shortcode `[the_conference_schedule year=2011 include_times=false schedule=2]`

= The shortcodes aren't working - nothing appears, or my changes don't appear = 

In order for the plugin to publish data on the front end, you must publish the data in the back end.  Navigate to the Conferences page (under the Top Quark menu in the left-nav of your dashboard), find your event and then click either `publish lineup` (to publish the speakers or artists at your event) or `publish schedules` (to publish the schedules).  

This is done so that you can publish part of your schedule while still working on other parts.  

If you've made changes to your schedule, you must click the `update published schedules` link on the Conferences page.  

Note: if you've used a different vocabulary for your event, like Festival instead of Conference, then the page you want is called Festivals (still under the Top Quark menu in the left-nav)

= Does this plugin handle event registration? =

No. There are other plugins for that.  This plugin is for showing your event's information.  

= All of the links on my schedule page are leading back to my home page =

This is a known issue for sites that do not have pretty permalinks turned on.  We are working to fix it for a future release, but for now, the problem can be solved by using pretty permalinks.

= Can I print Name Badges for my speakers? =

Yes.  This plugin comes with a powerful Printables module that you can use to design and print name badges, accreditation letters, tech sheets or bio sheets.  The module uses the Smarty templating engine and there are many variables available.  The plugin can also be setup to hook into other databases, making it possible to print all delegate badges without having to add them to the lineup.  This requires some development, but it is possible.  Using these hooks, I've hooked the plugin up with the contact management plugin [http://wordpress.org/extend/plugins/pommo](poMMo for WordPress).  

**Bonus** - using the printables module, you can actually print the speaker's schedule right on the front or back of their name badge.  To do that, simply insert the Smarty code `{$Artist.ArtistShows}` into the Printable.  Heck you can even include a venue legend with the code `{$StageNamesLegend}`.  If you want to put this on the back of the badges, just click the "Collate for reverse-side printing" checkbox.  

= How do I change the template for one of the views? = 

All of the views are generated using Smarty templates.  To hijack a view and make customizations, you'll need to add your own plugin.  If you're familiar with writing plugins and have experience with the Smarty templating engine, this is quite simple.  If not, it might be best to get a programmer on board to help you out.  [I'm available for hire](http://topquark.com/contact).  

The first step is figuring out the name of the template that you want to hijack.  Look at the files within The Conference Plugin.  The templates are located in a directory called `smarty`.  For example, if you wanted to hijack the floated lineup view, you would be looking for the template `smarty/festivalapp.floated_lineup.tpl`.  Once you figure out the name of the template you want to take over, you can begin writing your plugin.  

Within your new plugin, simply add the following:

`add_filter('Smarty_Instance_resource_name','my_Smarty_resource_name',10,2);
function my_Smarty_resource_name($resource_name,$args){
	$smarty = $args[0];
	switch($resource_name){
	case 'festivalapp.floated_lineup.tpl':
		$smarty->template_dir = dirname(__FILE__).'/smarty/';
		break;
	}
	return $resource_name;
}`

Then, put a file called `festivalapp.floated_lineup.tpl` into a subdirectory of your plugin called `smarty` (i.e. wp-content/plugins/my_plugin/smarty).  Start by copying the contents of the original template in The Conference Plugin and then start making your modifications.  

*Please Note*: It is wise to disable caching while you are working on your customizations.  Otherwise you may not see your changes as you make them.  To disable caching, visit the settings page for The Conference Plugin.

= Anything Else? = 

Please visit the forums on [http://topquark.com](TopQuark.com) and search for or ask your question.  

== Add-Ons ==

I've written the following add-ons and they are available for purchase from [TopQuark.com](http://topquark.com/plugins)

= The Conference App = 

Turns the data for your event into a SmartPhone, touch-driven, native-feeling web app that works on iPhone, Android and Blackberry Torch.  Mobile is the way of the future and this plugin will give your event a mobile app at a small fraction of the cost other people want to charge you.  The app can be enabled to work offline, meaning once someone has loaded the app onto their phone and saved it to their homescreen, they can access it even if their phone is in Airplane mode. 

= My Schedule = 

Allows registered users of your site to create their own schedule by bookmarking speakers or sessions that interest them.  They can then view their schedule and even print it.  

= Sponsors = 

Add sponsors to your event, upload logos, define sponsorship categories.  You can even attach sponsors to particular sessions within your event.  

== Changelog ==

= 1.1.3.2 = 
* Fix: If you're trying to view a schedule page for a schedule with no sessions added, the script no longer spits out warnings and errors. (Thanks Claude @ IMHOCorp.com)

= 1.1.3.1 = 
* Fix: when importing speakers, if the image is a URL, the import now works if you're running PHP in Safe Mode

= 1.1.3 = 
* Fix: when publishing speakers, if they were speaking in sessions that were part of an embedded schedule that doesn't get published, those sessions weren't showing up as sessions for that speaker
* New Feature: The ability to sort the order that the speakers appear in a given session
* New Feature: When using the `[the_conference_lineup]` shortcode, you can now add a parameter to exclude certain speakers, or only include.  See the Frequently Asked Questions for an example.

= 1.1.2 = 
* New Feature: added an agenda style to [the_conference_schedule] shortcode.  Useful for complicated schedules.  It displays the sessions in a simple agenda format, time in the left column, description & speakers in the right
* Fix: Earlier versions of WordPress did not register the datepicker jQuery plugin.  Added a fix to make the datepicker still work in those versions
* Fix: Invalid argument supplied for foreach() in wp-content/plugins/the-conference-plugin/user.functions.php on line 717 when publishing with no lineup

= 1.1.1 = 
* Fix: a bug introduced in 1.1.0 preventing the "Quick Add" feature from working properly
* Fix: an htmlentity encoding problem in the XML feeds
* New Feature: You can turn the feeds off on the Update Conference page if you don't want the feeds to be available

= 1.1.0 = 
* New Feature: A speaker and session XML feed is now included.  The feed URLs can be found on the edit conference page.  
* New Feature: Edit the speaker thumbnail right from Edit Speaker page
* Fix: Shortcode Preview no longer will convert double quotes to problematic curly quotes
* Fix: On Speaker page, fixed the logic for "appeared in" vs. "is appearing in" (it now properly knows if the Conference has passed)
* Fix: Fatal error when publishing schedules when only one speaker is in the conference
* Fix: Conference Date datepicker (broken since WordPress 3.3)

= 1.0.16 = 
* Fix: When adding a session with no speakers, it was adding a null row to the session table.  No longer. 

= 1.0.15 =
* New Feature: When you edit a conference, there is now a Shortcodes tab that lets you build a shortcode for your page/post
* Change: You are now able to change the Conference Year after you've already created the conference. If you're using any add-ons to The Conference Plugin, please update them as well.  Also, this change requires an upgrade to the Top Quark Architecture plugin
* Change: on the floated lineup (a grid of thumbnails) the names now appear below the image, as opposed to above it
* Fix: tables are now created with `ENGINE MyISAM` as opposed to `TYPE=MyISAM` (which is deprecated)

= 1.0.14 =
* Fix: import/export now respects Member Of for speakers

= 1.0.13 =
* Fix: a friendlier error message on activate if Top Quark Architecture plugin is not installed (thanks Chris Alexander)
* Licensing: Plugin is now 100% GPLv3 Compliant.  

= 1.0.12 =
* Feature: added the ability to "reset" a conference, choosing to erase the lineup, erase the schedules and/or remove orphaned speakers (speakers not assigned to any conference)

= 1.0.11 =
* Fix: sponsor display is now done more intelligently, allowing for upcoming Sponsors add-on

= 1.0.10 =
* Fix: stale smarty compiled templates now deleted on plugin deactivation
* Fix: session details now show the sponsor

= 1.0.9 =
* Tweak: in the Conference vocabulary, you can now specify singular & plural versions (thanks for the suggestion Martin Høegh Mortensen)

= 1.0.8 =
* Fix: shortening the event no longer results in shows being deleted
* Fix: changing the room name no longer results in shows becoming unscheduled
* Fix: date-picker now works properly

= 1.0.7 =
* Fix: now properly displays embedded schedules

= 1.0.6 =
* Fix: synchronize and detect duplicates on import
* Fix: minor import bug

= 1.0.5 =
* Added Session and Speaker exporters, and made sure the exports work with the relevant importers
* Added reporting of orphaned sessions (sessions without a day assigned)

= 1.0.4 =
* Added a filter to allow for plugins to more easily affect the layout of the table within SchedulePainter
* Fixed: Display of inner tables within SchedulePainter works better now (there are still a few outside use cases that need attention)

= 1.0.3 =
* Changed batch size on Artist Import to 5 to allow for time to download image.

= 1.0.2 = 
* Updated Top Quark Settings mechanism

= 1.0.1 = 
* New: Importer can now take image URL for speakers
* Fix: Importer synchronize works with speakers & sessions

= 1.0.0 = 

* Initial Checkin

== Upgrade Notice ==

