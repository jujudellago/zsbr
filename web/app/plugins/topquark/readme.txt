=== Top Quark Architecture ===
Contributors: topquarky
Tags: framework, multisite, gallery, architecture, permissions
Requires at least: 3.0
Tested up to: 3.3.1
Donate link: http://topquark.com
Stable tag: 2.1.3

Though ostensibly this can be used as yet another gallery plugin, under the hood it provides a framework for rapid plugin development.  

== Description ==

THE FRAMEWORK
-------------
The real purpose of this plugin, besides yet another gallery tool, is to provide a framework for rapid, database-driven, plugin development.  At this point, the framework is only used by a half dozen or so plugins that I've written (see www.topquark.com).  I'm releasing it to the community to invite and encourage feedback and development using the framework.  A start to the requisite documentation is forthcoming.  

The framework provides the following services:

*	The above mentioned Gallery package to handle images
*	Generic Import/Export services (multibyte friendly and scalable)
*	Grant/revoke permissions to WordPress users to access framework plugins 
*	A database object container class with a handy dandy and powerful whereClass class
*	The Smarty templating engine
*	Enough hooks (actions & filters) to enable third-plugin add-ons to framework plugins

THE GALLERY
-----------
This plugin provides yet another gallery tool for use within WordPress.  It has some handy features such as being able to create image sets from images taken from multiple galleries.  It's been tested up to hundreds of galleries and has a handy export feature that allows administrators to quickly dump images for download.  While you are writing a `post` or a `page`, you can browse the Top Quark galleries and insert images.  

== Installation ==

1. Install and activate the plugin

== Screenshots ==

1. Main menu admin page for Top Quark packages
2. Manage Galleries page in admin area
3. Update Gallery page (thumbs can be reordered by drag-n-drop)
4. Gallery rendered on Hello World post in Twenty-Ten theme

== Frequently Asked Questions ==

= How do I include a gallery in my post =

1. Create your Top Quark gallery and upload images
2. Add the shortcode `[topquark action=paint package=Gallery gid=#]` (replacing # with the ID of the Gallery)
3. (Note, the above shortcode can be copied from the main Gallery admin page)

= How do I include an image set in my post =

1. Create your Top Quark Image Set and add some images from your (already created) Top Quark Galleries to the Images Set
2. Add the shortcode `[topquark action=paint package=Gallery sid=#]` (replacing # with the ID of the Image Set) 
3. (Note, the above shortcode can be copied from the main Gallery admin page)

= How do I create an index page for all my galleries or image sets =

1. Create your galleries/image sets
2. Add the shortcode `[topquark action=paint package=Gallery]` to your page/post
3. To display Image Sets, use `[topquark action=paint package=Gallery what_to_show=ImageSets]`

= Sure, great, but tell me more about this framework =

Interested in learning more?  Browse to [TopQuark](http://topquark.com/).  Sure, there's nothing there now, but I aim to get some documentation and a forum up there soonly.  

== Changelog ==
= 2.1.3 =
* Fix: a workaround if the function session_register does not exist (as is the case in PHP > 5.4
* Fix: long standing quote issue on forms.  It was possible to send a form to the browser with unescaped quotes as values in input fields.

= 2.1.2 =
* Fix: an issue that could have Top Quark Architecture fail silently on some Windows servers

= 2.1.1 = 
* Fix: Broken batch_resize code
* CRITICAL FIX: Removed Fancy arbitrary file upload vulnerability - http://secunia.com/advisories/49465/ 

= 2.1.0 = 
* Fix: Big code cleanup.  Fixed a lot of code that would have cause E_NOTICE notifications to be thrown.  Nothing fatal, but would be ugly if you have WP_DEBUG turned on
* New feature: You can now customize the thumbnail for images uploaded to the gallery.  Double click the image to get the Edit Image popup and then make your thumb right there.

= 2.0.15 = 
* Fix: Stricter type and null checking when adding/updating tables.  Affected files are ObjectContainer.php, FancyObjectContainer.php and DB_Object.php, all in lib/packages/Common.  Thanks to Simon Jamieson for working on these issues with me.  

= 2.0.14 = 
* Change: path to wp-config.php in env.php is now passed through a filter.  See the thread at http://wordpress.org/support/topic/broken-admin-pages
* Fix: the GalleryName field in the GalleryImage table now is DEFAULT NULL as opposed to NOT NULL

= 2.0.13 = 
* Fix: tables are now created with `ENGINE MyISAM` as opposed to `TYPE=MyISAM` (deprecated)
* Addition: Containers now have a working batchUpdate method which allows batch updates on a particular column in the table

= 2.0.12 = 
* Fix: Milkbox now respects .jpeg extension (thanks Shir Madness for breaking things and making me fix them)

= 2.0.11 = 

* Fix: Errant code in ObjectContainer.php (thanks Sheldon Bradshaw for pointing it out)

= 2.0.10 = 

* Fix: User Permissions now works on Multisite

= 2.0.9 = 

* Replaced short <? with <?php (thanks Martin Høegh Mortensen)

= 2.0.8 = 

* ImageLibrarian problem on windows servers with posix_guid

= 2.0.7 = 

* Improvement: Bootstrap::packageExists no longer instantiates package - just checks for existence
* Fix: Importer will run postImport on object now even if object didn't change

= 2.0.6 = 

* Regarding PEAR.php, now checking to see if it exists on the include path, and if it does, use that one

= 2.0.5 = 

* Added minimized PEAR installation of lib/packages/Common/PEAR.php to allow plugin to still work on hosts within PEAR installed

= 2.0.4 = 

* Updated Gallery slideshow to use more current SWFObject.  Also, displays fallback of primary thumbnail on non-flash devices
* Removed unlicensed code (Monoslideshow)

= 2.0.3 = 

Fixed ObjectContainer::addObject bug if DB::Field doesn't exist

== Upgrade Notice ==

= 2.1.2 = 
(Not released yet) - changed a function name from return_bytes to resolve a conflict with BuddyPress.

= 2.1.1 = 
CRITICAL UPDATE - This update fixes a security hole.  Please update immediately, or at the very least remove the directory topquark/lib/js/fancyupload/showcase (no functionality is affected by removing that directory).  Thank you.

= 2.0.4 =

If you were using a Gallery flash slideshow (by the shortcode `[topquark action=paint package=Gallery subject=Slideshow gid=1]`), this will no longer work.  You need to purchase [Monoslideshow](http://www.monoslideshow.com) and upload it to your site.  Once that's done, somewhere in your functions.php file, add:

`add_filter('monoslideshow_url',create_function('$path','return "http://mysite.com/path-to/monoslideshow.swf";'));`
