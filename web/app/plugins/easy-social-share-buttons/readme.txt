=== Easy Social Share Buttons ===
Contributors: brianbrey
Tags: wordpress, plugin, social sharing
Requires at least: 3.9
Tested up to: 4.5.2
Stable tag: 1.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add social sharing buttons to your posts and images without slowing down your site with unnecessary javascript and image files.

== Description ==

Add social sharing buttons to your posts without slowing your website down.

Default sharing buttons provided by Facebook and other sites require that your users download many additional javascript, css, and image files. Every additional download increases your sites page loading time. A slow website can lose visitors and lower your Google page rank.

This plugin's sharing buttons require no images or font icon files to download, and only an optional 4kb CSS file.

Add buttons to share your posts for Facebook, Twitter, Google+, Pinterest, and Email. Automatically display buttons in the header or footer of your posts, or use shortcodes for including them within your content or template files.

Select between two styles: either minimal icon buttons, or share buttons with added share count. Show the number of times the post has been shared on each service. The share count option requires javascript.

If you have image heavy content, add share buttons directly on your posts' media, as seen on popular sites such as Buzzfeed.

For advanced theme developers, default plugin styles and scripts can be disabled. Include the plugin's CSS or Sass in your theme's styles to reduce additional HTTP Requests. Or completely customize the look and feel of the buttons with easily style-able HMTL and class names.

== Installation ==

Installing "Easy Social Share Buttons" can be done either by searching for "Easy Social Share Buttons" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Sharing buttons with added share count placed in header of post content.
2. Use shortcodes to display buttons in content.
3. Post media share buttons
4. Easy to use settings in dashboard


== Changelog ==

= 1.4.2 =
* 2017-08-11
* Add filter 'update_easy_social_share_post_types' to add share buttons to custom post types

= 1.4.1 =
* 2017-06-05
* Throw error if php environment missing DomDocument class
* Added additional filters for customizing sharing text

= 1.4 =
* 2017-03-03
* Updated social icons
* Fixed Facebook popup not automatically closing bug
* Disabled auto adding share buttons to all post types' content besides 'post'
* Updated plugin to request Facebook Graph API data using app access token
* Fixed share counts not working in certain https environments

= 1.3.1 =
* 2016-08-24
* Updated Facebook share count API endpoint

= 1.3 =
* 2016-05-25
* Download share counts via ajax to reduce page load speed
* Removed twitter share count. Twitter no longer has API for that information
* Added filters for greater customization
* Style updates to make plugin compatible with Twenty Sixteen theme

= 1.2 =
* 2015-06-30
* Fixed styles to make plugin compatible with Twenty Twelve, Twenty Thirteen, and Twenty Fourteen themes
* Added option to show button text without share count
* Fixed Facebook bug, when trying to share posts with featured images
* Updated documentation on how to correctly display shorcode within theme files
* Fixed debug notices when unselecting all multiple checkbox options

= 1.1 =
* 2015-05-11
* Fixed bug to prevent buttons from showing up in excerpts

= 1.0 =
* 2015-05-07
* Initial release

== Upgrade Notice ==

= 1.2 =
* 2015-06-30
* Added additional options and fixed several bugs to remove debug notices and to fix erros when sharing posts to facebook.

= 1.0 =
* 2015-05-07
* Initial release
