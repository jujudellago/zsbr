=== SpeakOut! Email Petitions ===
Contributors: 123host, kreg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4PPYZ8K2KLXUJ
Tags: petition, activism, community, email, social media
Requires at least: 3.4
Tested up to: 4.9.6
Stable tag: trunk

SpeakOut! Email Petitions makes it easy to add petitions to your website and rally your community to Speak Out about a cause by using direct action.

== Description ==

SpeakOut! Email Petitions allows you to easily create petition forms on your site.

When visitors to your site submit the petition form, a copy of your message will be sent to the email address you specified e.g. your mayor. They can also choose to have the email BCC'd to themselves (default).  The petition message will be signed with the contact information provided by the form submitter. After signing the petition, visitors will have the option of sharing your petition page with their followers on Facebook or Twitter. 

Signatures are stored in the database and can be easily exported to CSV format for further analysis. You may set a goal for the number of signatures you hope to collect and then watch as a progress bar tracks your petition's advance toward it's goal. Petitions may also be configured to stop accepting new signatures on a specified date.

This plugin is a fork of the SpeakUp plugin by Kreg Wallace that was quite good but needed updates and appears to have been abandoned.  Current versions of SpeakOut! include an importer to migrate from SpeakUp.

= Localizations =

* Albanian **sq_AL** Incomplete
* Arabic **ar_AR** (Asem Alzoubi)
* Czech **cs_CZ** (Petr Štepán)
* Danish **da_DK** (A. L.)
* Dutch **nl_NL** (Kris Zanders)
* Finnish **fi_FI** 
* French **fr_FR**
* German **de_DE** (Hannes Heller, Armin Vasilico, Andreas Kumlehn, Frank Jermann)
* Hebrew **he_IL** (Oren L)
* Korean **ko_KO** Incomplete
* Italian **it_IT** ([MacItaly](http://wordpress.org/support/profile/macitaly))
* Norwegian **nb_NO**
* Polish **pl_PL** (Damian Dzieduch)
* Romanian **ro_RO** ([Web Hosting Geeks](http://webhostinggeeks.com))
* Russian **ru_RU** ([Teplitsa](te-st.ru))
* Slovenian **sl_SI** ([MA-SEO](http://ma-seo.com))
* Spanish **es_ES**
* Swedish **sv_SE** (Susanne Nyman Furugård @sunyfu)

If you would like to request or contribute a specific translation not listed above, visit the [SpeakOut! Email Petitions website](http://speakout.123host.com.au/) and use the contact form.

== Changelog ==

== 1.11.0 ==

* Bug fix: added test to make sure jquery isn't already loaded in admin settings page.  Some themes do load jquery and this seems to have been the source of several reports of content missing from settings page and only tabs visible.  Yay!  Thanks Patrick S, footballmaestro25 and Alberto C who worked with me on this.

== 1.10.5 ==

* Bug fix: petition wouldn't submit if Privacy Policy checkbox wasn't being displayed.  Thanks Dominik!

== 1.10.4 ==

* Bug fix: widget was showing an admin checkbox instead of the honorific field.  It has been like that for a while, surprised no one reported this.

== 1.10.3 ==

* Bug fix: Fix the bug fix done in too much of a hurry :o(

== 1.10.2 ==

* Bug fix: something I *know* I edited earlier had reverted itself somehow

== 1.10.1 ==

* Bug fix: Privacy Policy display was enabled by default.  Enable via dashboard > SpeakOut! > settings

== 1.10.0 ==

* Improvement: added Privacy Policy checkbox option (under settings) to comply with the EU GDPR.  Works on both petition and widget.
* Update: languages


== 1.9.1 ==

* Bug fix: I left a debug line in the installer script which prevented the signature table being created for new installs...sigh... 

== 1.9.0 ==

* Bug fix: missing value in function saving signature when confirmation not required  Another of those "how did it ever work?" errors :P
* Improvement: moved the FAQ to https://SpeakOut.123host.net.au/FAQ
* Improvement: IP address now being saved with signature and exported with CSV
* Improvement: updated languages - some are incomplete, if yours is, I would love your contribution.

== 1.8.4 ==

* Bug fix: BCC wasn't working if people had to confirm email address


== 1.8.3 ==

* Improvement: updated some functions to ensure compatability with future PHP versions
* Improvement: Nicer HTML in confirmation emails - I was sure I had done this in the past :P

== 1.8.2 ==

* Bug fix: typo must have caused problems with honorific :P
* Bug fix: fine tuned HTML in confirmation email message
* Improvement: allowed some formatting tags in confirmation email message
* Improvement: update all translations including correction to German translation (Thanks @MKJ2)
* Improvement: if the full name is displayed in the signature list (not anonymised surname) it also displays the honorific.  I am sure someone is going to ask for this to become an option ;o)
* Improvement: the _[your signature]_ in the displayed petition text looks like shortcode to site owners.  I have changed it to _\*\*your signature\*\*_ to lessen confusion 
* Improvement: Danish translation added.  Thanks to A.L. who didn't want to be acknowledged but will be anyway :o)

== 1.8.1 ==

* Improvement: confirmation emails now HTML instead of plain text
* Improvement: added signaturegoal shortcode to display signaure goal.  Useful in conjuction with the signaturecount shortcode e.g. We have [signaturecount id="1"] signatures towards our goal of [signaturegoal id="1"]. 
* Improvement: updated petitions list to show all the shortcodes possible  

== 1.8 ==
* Bug fix: moved the code for the response message outside the form - this only affected anyone who had the form hide on submission
* Bug fix: hadn't updated one CSS colour
* Bug fix: optin label bumping to new line if long.
* Improvement: addition of non-gender specific honorific "Mx"
* Improvement: updated German translation didn't get included

== 1.7.2 --
* Bug fix: removed recaptcha code - it wasn't implemented anyway, but was ready to be activated.  The recaptcha process is tied to each website requiring a site ID and a secret code.  If it is included in a plugin then you would be required to open a google developer account and get your authentication codes to submit via the plugin.  This is too onerous a request of people so I am looking at alternatives so that spam can be stopped.

== 1.7.1 ==

* Bug fix: recaptcha shouldn't be displaying.  I am working on integrating it and this was preliminary code
* Improvement: Box displaying confirmation of signing and any errors moved from top of form to bottom.  Makes more sense that it is underneath where person just clicked
* Big fix: incorrect color value in CSS for progress bar when more than 50% to goal

== 1.7.0 == 

* Bug fix: incorrect code uploaded in 1.6.9.1

== 1.6.9.1 ==

* Bug fix: Typo in widget code caused closing </div> to not be included.

== 1.6.9 ==
* Improvement: Added percentage thingy to widget as well
* Improvement: CSS files updated to fix some errors
* Improvement: Added "thanks for signing"/error notification box at bottom as well as at top (Thanks Sherry)
 
== 1.6.8 ==
* Bug fix: the percentage of goal worked fine in testing with low goals, but high goals didn't calculate % properly
* Improvement: added "of goals" to all language files
* Bug fix: hopefully :P  the word signature wasn't pluralising properly, at least in French.

== 1.6.7 ==
* Improvement:  When you have set a goal, it how shows the percentage of the goal reached so far beside the signature count, plus the goal value on the right of the progress bar. (Thanks for pointing this out @anderenbenutzer)
* Improvement: reversed the colours of the progress bar.  It is now red when hardly signed and green when 100% of goal.  Makes more sense I reckon.
* Improvement: Added blank index.php file in each directory to prevent directory listing.  This is more for housekeeping, since like all like all WP plugins, SpeakOut! is open source so anyone can download it and view the file structure.
* Improvement: reduced the time for confirmation redirect from 10 seconds to 2 seconds.
* Improvement: fixes to German translation (Thanks Frank J)
* Update: minor tweaks to the file that generates this text
* Bug fix: If optin text is long, it wrapped onto a new line leaving the checkbox orphaned on a new line.
* Bug fix: The phrase "Your Signature" wasn't being translated when viewing petition text
* Bug fix: Custom field label wasn't being saved when petition was created

== 1.6.6 ==
* Feature: It is now possible to have the petition form hide after it is signed, leaving the success message and social media icons.  Look at /wp-content/plugins/speakout/js/public.js on line 108 (Thanks for the suggestion @kayleyathomas)
* Update: Added honorific _Dr._  Can add others on request ;o) (Thanksf for the suggestion @jimkcombs3)
* Update: All language files (Thanks Frank J. for German fixes)
* Update: changed text in signature field to black (from dark grey on grey).  Remember that you can have your own .CSS file if you want to modify styles.
* Bug fix: Honorific was completely broken on widget
* Bug fix: Some phrases weren't translating in confirmation email (Thanks Frank J.)

== 1.6.5 ==

* NEW FEATURE:  If you have multiple petitions and don't want to have a separate page for each one, you can load petitions dynamically by using the url example.com/?petition=(petitionID)  e.g. https://speakout.123host.net.au/?petition=2  Thanks Rick for this cool idea plus the code to make it work.

== 1.6.4.1 ==
* Added Arabic translation - thanks Asem Alzoubi
* Updated German translation to include Fraulein for Miss
* Changed the term Post Code to the more generic Postal Code and updated all translations

= 1.6.4 =
* Updated countries to match the current situation in the former Yugoslavia - thanks @cello78ss
* Another tweak to try to solve broken confirmation links in some email clients - thanks @rotegras

= 1.6.3.1 =
* Tweak of CSS to suit honorific fix

= 1.6.3 =
* Made honorific optional - you can't please everyone :o)

= 1.6.2 =
* Some edited files not included in 1.6.1

= 1.6.1 =

* forgot about the translation of honorifics (Ms, Mr etc)

= 1.6.0 =

* rearranged fields on dashboard > speakout > settings > signature list, moving the Display above Columns as choosing _long list_ disables the Columns fields.  This makes it more clear why they might be disabled
* petition admin display options, _Display custom message_ slider wasn't opening
* added honorific (Ms, Mr etc) field on petition.  This is displayed in target email.

= 1.5.6 =
* fixed bug where comma separated signature list only showed abbreviated surnames. Thanks Stefan

= 1.5.5 =
* for new installs removed extra space character after the petition title in confirmation message.  Existing installs can edit this in dashboard > speakout > settings > confirmation emails
* changed the email content type from text/html to text/plain following reports it messed with Outlook emails.

= 1.5.4 =

* changed the email for confirmation emails to utf-8 to fix the link only being half clickable in some email apps (GMX in particular).  It will now be a non clickable URL in some software, but it is still fine in gmail.  Not quite sure what is going on here, but this solves the problem of the link breaking.
* to match the previous fix, default text for confirmation emails in new installs is now "Please confirm your email address by clicking _or copying and pasting_ the link below:".  In existing installs you can change the text by going to dashboard > speakout > settings > confirmation emails > email message

= 1.5.3 =

* set default value for 'custom field value' (new installs only)
* set default value 0 for 'signature confirmation' field (new installs only)
* tweaked default email confirmation message to include petition title (new installs only)
* changed function type to fix errors when landing on email confirmation page
* added text on Admin Display tab of settings to remind about default values
* added missing translation for the word 'signatures'
* updated all translations - I am sure some of them are a bit dodgy

= 1.5.2 =

* typo in database update - not sure why I can see this on the development site before I commit update :o(

= 1.5.1 =

* typo in database update

= 1.5.0 =

* Fixed mislabelling of petition version so that it updates properly automatically.
* Trying to get languages more efficient a few functions were broken e.g. deleting petitions  They should be fixed.
* When choosing to display signatures in a row rather than a table, column checkboxes are disabled.
* Only display 1st 100 characters of custom message column in signature list
* Added nowrap to signer's name in signature list

= 1.4.5 =

* changed the name of the translation text domain from dk_speakout to speakout as specified here https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/

= 1.4.4 =

* fixed a typo that caused radio buttons to not fill on the petition tab of the setup page.

= 1.4.3 =

* added ability to display in signature list a custom message signer has sent. _dashboard > SpeakOut! > Signature List > check Custom Message
* added requested option (dashboard > speakout > settings > signatures) to display signatures as long comma separated list rather than table e.g. Judy R., Robert F., Steve D., etc
* remembered to update language files to match :o)
* changed width of count column to 12% when displaying signatures in table
* extended maximum execution time of CSV exporter from the default 30 seconds to 3 minutes

= 1.4.2 =

* fixed settings page not displaying in 1.4.1 - ouch! 

= 1.4.1 =
* Forgot to update translations to include new "Display BCC field" phrase

= 1.4.0 =
* Added option to globally display or hide BCC field
* Added code to theme_basic.css to fix size of modal popup on mobile devices - thanks @pattihis

= 1.3.11 =
* Add Czech translation.  Thanks Petr Štepán

= 1.3.10 =
* updating some phrases
* language file updates to match

= 1.3.9 =
* Fixed warning that '$this->WP_Widget' is deprecated, replaced with 'parent::__construct' - had already done this in 1.3.0.  Not sure what happened there.
* fixed typo in German translation

= 1.3.8 =
* Fixed bug BCC not working (again).  Discovered that I hadn't uploaded an edited file from my development site, then when I did the upgrade locally it over-wrote my changes with the original code that should have been changed...sigh...
* Housekeeping cleanups
* Fixed Norwegian translation

= 1.3.7 =
* Added Norwegian translation (requested by Robert)
* Added Estonian translation (requested by Janno)
* Updated all translation files with missing strings
* Replaced some hard coded strings with translatable strings

= 1.3.6 =
* Fixed bug BCC not working
* Fixed bug class.petitions.php
* Updated all translation files - I have bought poedit, so translation requests are welcome
* Fixed misnamed German translation file

= 1.3.5 =
* Removed non-breaking-space '&nbsp' from the shortcode example so that if people copy and past the example it doesn't break the petition.

= 1.3.4 =

* Tidied up uninstall to be better behaved and remove options from database
* Made label text for BCC field auto translatable

= 1.3.3 =

* Importer from speakup had bug.  Only change in this version, upgrade not needed if not using importer.

= 1.3.2 =

* Oops!  When copying code from test site to code store, accidentally copied contents of one file into another similarly named.  FIXED!
* Typo in CSS caused new BCC field to be out of alignment
* Fixed typo and reworded import page for clarity

= 1.3.1 =

* Added option for person signing to BCC self
* Added option to import global settings from speakup over-writing settings in SpeakOut!
* Added some fancies to disable the import button if there are no legacy speakup petitions to import, but enable if settings import is checked
* Added new column with Petition ID to petition list.  ID was in display of short codes, but wasn't intuitive.
* Added petition ID number to edit success message
* Changed settings page title and header from Email Petitions Settings to SpeakOut! Petition Settings to further distinguish from old plugin
* Changed option from "Allow custom messages" to "Allow message to be edited" for clarity
* If option "Allow message to be edited" is checked, on petition top section text will change from "READ THE PETITION" to "READ OR EDIT THE PETITION"

= 1.3.0 =

* Added importer from speakup to SpeakOut!
* Version jump to clean up some confusion after roll back due to broken widget
* Changed dashboard menu titles including main title SpeakOut! to make distinct from "email petition" used in SpeakUp
* Add clarification that multiple email target email addresses can be comma separated
* Removed list of US states from drop-down.  This is an international plugin :P
* Added how to make postal code required to FAQ - thanks @altinkaya
* Fixed warning that $this->WP_Widget is deprecated, replaced with parent::__construct - thanks @mannweb
* Various typo fixes

= 1.2.0 =

Abandon this version - widget broken, version control messed up.

= 1.1.3 =

Wrong icon being displayed

= 1.1.2 =

File missed in commit

= 1.1.1 =

Getting dashboard icons right

= 1.1.0 =

Fix broken directory references missed as part of fork.

= 1.0.0 =

Initial fork of SpeakUp! with additional privacy option in setting so only display first letter of surname

== Installation ==

Use the automatic installer. Or...

1. Download and unzip the the plugin zip file.
2. Upload the 'speakout' folder to your '/wp-content/plugins/' directory
3. Activate SpeakOut! Email Petitions through the "Plugins" menu in the WordPress admin.

== Frequently Asked Questions ==

= Where is the FAQ? =

(https://SpeakOut.123host.net.au/FAQ)


== Screenshots ==

1. Public-facing petition form
2. Form for creating and editing email petitions
3. Table view of existing petitions
4. Table view of collected signatures
5. Plugin settings screen
6. Sidebar widget
7. Pop-up Petition form (widget)
8. Email confirmation screen

== Upgrade Notice ==

= 1.6.1 =

Translations only update

== Emailpetition Shortcode Attributes ==

The following attributes may be applied when using the '[emailpetition]' shortcode

= id =
The ID number of your petition (required). To display a basic petition, use this format:
'[emailpetition id="1"]'

= width =
This sets the width of the wrapper '<div>' that surrounds the petition form. Format as you would a width rule for any standard CSS selector. Values can be denominated in px, pt, em, % etc. The units marker (px, %) must be included.

To set the petition from to display at 100% of it's container, use:
'[emailpetition id="1" width="100%"]'

A petition set to display at 500 pixels wide can be achieved using:
'[emailpetition id="1" width="500px"]'

= height =
This sets the height of the petition message box (rather than the height of the entire form). Format as you would a height rule for any standard CSS selector. Values can be denominated in px, pt, em, % etc. The units marker (px, %) must be included.

A few notes on using percentages:
Using a % value only works when the "Allow messages to be edited" feature is turned off—because the petition message will be displayed in a '<div>'. When "Allow  messages to be edited" is turned on, the petition message is displayed in a '<textarea>', which cannot be styled with % heights. Use px to set the height on petitions that allow message customization.

To set the message box to scale to 100% of the height of the message it contains, use any % value (setting this to 100%, 0%, 200% or any other % value has the same result). Use px if you want the box to scale to a specific height.

Examples:
'[emailpetition id="1" height="500px"]'
'[emailpetition id="1" height="100%"]'

= progresswidth =
Sets the width of the outer progress bar. The filled area of the progress bar will automatically scale proportionally with the width of the outer prgress bar. Provide a numeric value in pixels only. Do not include the px unit marker.

To display the progress bar at 300 pixels wide, use:
'[emailpetition id="1" progresswidth="300"]'

= class =
Adds an arbitrary class name to the wrapper '<div>' that surrounds the petition form. Typically used to assign the alignright, alignleft or aligncenter classes to the petition in order to float the petition form to one side of its container. To assign multiple classes, separate the class names with spaces.

Examples:
'[emailpetition id="1" class="alignright"]'
'[emailpetition id="1" class="style1 style2"]'

== Signaturelist Shortcode Attributes ==

= id =
The ID number of your petition (required). To display a basic signature list, use this format:
'[signaturelist id="1"]'

= rows =
The number of signature rows to display in the table. This will override the default value provided on the Settings page. To display 10 rows, use:
'[signaturelist id="1" rows="10"]'

= dateformat =
Format of values in the date column. Use any of the standard [PHP date formating characters](http://php.net/manual/en/function.date.php). Default is 'M d, Y'. A date such as "Sunday October 14, 2012 @ 9:42 am" can be displayed using:
'[signaturelist id="1" dateformat="l F d, Y @ g:i a"]'

= prevbuttontext =
The text that displays in the previous signatures pagination button. Default is &lt;.

= nextbuttontext =
The text that displays in the next signatures pagination button. Default is &gt;.

== signaturecount Shortcode ==
Display the number (as text) of signatures collected for a given petition:

= id =
The ID number of your petition (required).
'[signaturecount id="3"]'

== signaturegoal Shortcode ==
Display the number (as text) of goal for a given petition:

= id =
The ID number of your petition (required).
'[signaturegoal id="3"]'