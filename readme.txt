=== SWFPut - SWFlash Put ===
Contributors: EdHynan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4Q2Y8ZUG8HXLC
Tags: video, video player, movies, tube, flash, flash video, html5, html5 video, graphics, movie, video content, a/v content
Requires at least: 3.0.2
Tested up to: 4.2
Stable tag: 2.9
Text Domain: swfput_l10n
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

SWFPut provides video players for posts and pages and widget areas, as both HTML5 and flash video.

== Description ==

SWFPut provides 'responsive' video for posts and pages.
SWFPut makes the presentation of video reliable for your
visitors: several conditions are handled well, such as
the uneven support for HTML5 video formats in the major
browsers, the possible lack of support for either HTML5
video or flash video in the visitor's browser, and even
the possiblilty that JavaScript might be disabled in your
visitor's browser.

In addition to video for posts and pages, SWFPut provides
a video widget for use in widget areas, such as a sidebar.

SWFPut video is 'responsive': it should display at a
suitable size on your visitor's device, whether large
or small (a responsive WordPress theme is necessary).

SWFPut makes video setup easy and flexible by providing
a full featured form with sensible defaults.
For posts and pages, the form appears in a new "metabox"
on the editor page. For widgets, the form appears with
the usual drag and drop widget interface. After adding
video objects, the form will continue to be useful for
making changes (or, if you wish, to delete the video).

In WordPress versions 3.3 and greater, video added by
SWFPut will be visible in the post/page visual editor.

As many video objects as you wish can be placed in posts
pages, and of course the widget supports as many instances
as you wish.
You may specify HTML5 or flash video, or both with one
being primary content and the other as fallback.

Here are some features of SWFPut to consider:

*	SWFPut works directly with media file
	URL's; that is, SWFPut does *not* embed
	the video players of providers such as YouTube or Vimeo.
	SWFPut is for video files which are accessible by URL,
	whether hosted at your site or off-site.
	The setup form provides two media selection lists.
	The first is a selection of files found (recursively)
	under your wp-content/uploads directory. This list
	has the advantage that it does *not* use the
	WordPress media library -- it will find files that
	you upload 'by hand' (with ftp, ssh, etc.). This feature
	will work around upload size limits that might prevent
	you from uploading large video files to the media library.
	The second is a selection of files found in the
	WordPress media library and is presented with the
	file name and the 'attachment id'. This refers to files
	by ID, so it might be helpful if you manipulate media
	and expect ID associations to be valid. Files selections
	are filtered by name extension: FLV and MP4 for flash,
	and MP4, OGG and OGV, and WEBM for HTML5 video.
	
*	Video resources do not need to be on your site:
	any URL can be specified, so you may present players
	for off-site of 3rd party resources.

*	SWFPut does not interfere with the appearance of
	a site: a video is presented jsut as an image
	(such as .png or .jpg) is, with the same sort of
	style, and optional caption. The appearance of the
	video control interface, or control bar, is simple
	and quiet so it should not clash with site design.

*	SWFPut allows you to set the display aspect ratio
	for the video. Some video is 'anamorphic' in that
	the pixel width and height do not match the intended
	proportion of display width and height. You might
	film your child's school play as 16:9 'widescreen'
	but use a space saving feature of your recorder that
	saves the video at 480x360 (which is not 16:9). You can
	set SWFPut to display the video at the intended 16:9
	aspect ratio. You may set any aspect ratio (make it
	distorted if you wish).

*	The core features of the flash video player program
	included with SWFPut have been verified to work with
	the Gnash free-software browser plugin, which is good
	if you care about free/libre software users. (At the
	time of this writing, Gnash does not handle the MP4
	video container format, so it is preferable that you
	prepare flash video in the FLV container, even using the
	h.264 and AAC codecs. Of course, you may use MP4 if
	you must.)

*	The flash video player program included with SWFPut
	is written and compiled with the *Ming* PHP extension,
	and the code is included, so you may modify the player.
	The HTML5 player is written JavaScript, and the original,
	un-minified version is included, so you may modify it.
	In fact, the zip archive available at the WordPress
	repository includes all sources, although a POSIX/Unix
	environment with certain tools is required to build.

*	Localization sources are included; hopefully, polyglot
	users will help with translations.

== Installation ==

There are no special installation requirements.

Preferably, install SWFPut from the WordPress Plugin
Repository through the WordPress administrative interface.

To install from a zip archive:

1. Log in at your WordPress site

1. Select 'Plugins -> Add New'

1. Select 'Upload'

1. Select 'Browse'

1. In your system's file selector, select the SWFPut zip file;
  select 'OK' or equivalent

1. Select 'Install Now'

1. Select 'Activate Plugin'

At this point "SWFPut" should be an entry on the plugins page.
The Settings menu should have an item "SWFPut Plugin".

If the above is not so, there is probably a problem at your site's
host; for example if the host is Unix system there is very likely
a problem with incorrect permissions metadata (mode) on a directory
such as wp-content/uploads, or an unsuitable user or group ownership
of (probably several) files and directories. This can be a frequent
problem if the host has PHP configured in "safe mode".

If the host is not a Unix system, I'm sorry to say I cannot help;
maybe your hosting provider can.

If the installation was successful, you should see a "SWFPut Video Player"
widget under 'Appearance -> Widgets' and a meta-box entitled
"SWFPut Video" on the posts and pages editing pages.

For additional help, you will find README* files (differing in format,
and excluding 'readme.txt', which is this file) that discuss the
flash video player in more detail.

== Frequently Asked Questions ==

= I installed SWFPut, now what? =

First, note that in the editor page for posts/pages,
there is a new 'metabox' named "SWFPut Video." It
might be near the bottom of the page, but metaboxes
can be moved by dragging with the mouse. (When using
SWFPut it might be useful to drag its metabox to
just below the editor.) If necessary click the metabox
title to show the form.

The SWFPut form might look complicated, but don't
worry! There is help. At the top right of the WordPress
editor pages there is a tab named (in english)
"Help." Click that, and editor help material will drop
down. SWFPut adds a new tab here called "SWFPut Video"
(what else?). Click that tab and . . .

. . . another drop-down, this time specifically
for this plugin. Near the top there are two hyperlinks
which open in a new tab/page, one for PDF documentation
and one for (old-time style) HTML documentation. These
are versions of the same detailed document. The remainder
of the in-place help text addresses just a few items
that the author (I) thought would need more explanation
for a quick start. Please peruse these for use.

Quickest of all, see the FAQ below entitled
"Do I really need video in 3 or 4 formats?" which
in spite of the name addresses quick and easy use
as well as video formats.

= Do I really need to understand "aspect ratio" and things? =

Probably not. In most case the width and height of the
video will match the intended display proportion.
"Anamorphic" video is not rare, but probably not too common
either. The author has seen videos on e.g., YouTube,
that are distorted by wrong display aspect ratio
(which is not YouTube's fault), but only a few.
If you find that your video looks squeezed or stretched,
you can always use a little trial & error with the display
aspect setting until it looks good.

= Do I really need video in 3 or 4 formats? =

Possibly not. The help documention included with SWFPut
discusses file formats for HTML5 and flash with a level
of detail that -- it is hoped -- will help you make your video
available to the broadest range of browsers used by your
visitors. The cost of this detail is the loss of simplicity.

The simplest use with not-too-bad availability would be like this:
prepare your video in MP4 format; place the URL in *either*
the "Flash video URL..." or "HTML5 video URLs..." fields[*]
in the setup form, and set display size in the "Pixel Width:"
and "x Height:" fields; make sure that the cursor in the
editor is at the spot you want, and click the
"Place new in post" button. Publish the post/page with
the "Update" button. Not too difficult!

Currently many browsers support MP4 for HTML5 video,
but not all! The flash browser plugin has supported MP4
for a long time, but alternatives such as Gnash
(a free-software flash plugin) might not. Availability
will be increased significantly by adding FLV (flash)
and OGG (or 'ogv', a patent-free, free-licensed type) formats.
If you need video conversion software,
a quick web search should yield several options.
Change the procedure in the above paragraph like this:
place the URL for the FLV type in the "Flash video URL..."
field, place the URL for the MP4 type *and*
the URL for the OGG type separated by a '|'
in the "HTML5 video URLs..." field (and don't worry about
'codec' types discussed in the help docs). Easy, and fun!

Finally, to cover all the bases, amend the the above paragraph
like this: place the URL for the FLV type in the "Flash video URL..."
field, place the URL for the MP4 type *and*
the URL for the OGG type *and*
the URL for the WEBM type separated by a '|'
between each in the "HTML5 video URLs..." field . . . .

[*] MP4 can be placed in either the flash or HTML5
URL fields and it will be effective for both, as long
as the visitor's browser has JavaScript enabled.

= What if a visitor has JavaScript disabled? =

SWFPut is designed with that in mind. If scripting
is disabled, but either flash or HTML5 video is
available, then your video should be viewable.
HTML5 video will have the browser's native interface
and basic features.

= How do contact to the developer? =

Post a comment at
http://agalena.nfshost.com/b1/swfput-html5-flash-wordpress-plugin/
or email edhynan at the email provider 'gmail' with the
top level domain 'com', or of course go the the SWFPut
WordPress page at https://wordpress.org/support/plugin/swfput
and select the "Support" tab.

= Does the developer want to know I use SWFPut? =

Yes, the developer would consider it kind of you
to leave a comment with the URLs of your using pages.

== Screenshots ==

1. The SWFPut widget setup form (bottom).

2. The SWFPut posts/page setup form ('meta box') with the first
	section hidden.

3. The appearance of video placed by SWFPut (Twentyeleven theme
	with dark custom colors, sidebar on left), not yet playing.

== Changelog ==

= 2.9 =
* 

= 2.2.2.1 =
* Sigh. Just added a forgotten file (from 2.2.2). The default
	video (mentioned in the 2.2.2 entry, below) would be
	missing, because I neglected to 'svn add' it.  Simple
	mistake updating the repository.

	This should not affect any but those who might try
	using a URL that SWFPut rejects. Upgrade at your
	convenience.

= 2.2.2 =
* Bug fix release in response to a kind user query.
	URLs provided for video files are checked before they
	are used (good), but elsewhere in code an array key
	would be missing if the URL was rejected (bad), possibly
	leading to PHP error messages appearing on page.

	Now, if a faulty URL is rejected, a default video URL
	is used, linking to a small video that says "OOPS [...]"
	and which should get the site owners attention (site
	visitors should find the default video inoffensive, even
	if unexpected).

= 2.2.1 =
* Confirmed working with WP 4.1.

= 2.2 =
* Added long overdue alignment options.
* Added video preload option. Before now, the video element was always
	given preload="none". Now, the specification values "none",
	"metadata", and "auto" may be selected, or one special option
	named (in english) "per initial image", which will use "none"
	if if an initial image (poster) is set, or else "metadata" so
	that the browser may display a frame of video as a poster.
	NOTE: the new default is "per initial image" which will
	change the behavior of existing videos that do not have
	an initial image (or 'poster'): they will now have the
	preload="metadata" attribute, so the browser will make a
	small unsolicited fetch from the server, and might display
	a frame. If that is unwanted, the preload option should
	be set to "none."
* Some code cleanups and reorganization.
* Check with WordPress 4.0: OK. (Actually, WordPress 4.0-beta3,
	and in the unlikely event that a problem arises with the
	real WP 4.0 release, it will be addressed in a minor revision.)

= 2.1.1 =
* This release is called "Sigh" and its only change is a
	workaround for a chromium 3.4 bug -- not on the front end, but
	in the Visual editor plugin. You want details, you say? OK:
	this plugin's video in the tinymce visual editor is housed
	in an iframe element. The iframe was given, *for principle only*,
	a sandbox attribute (with the "allow-scripts" argument), even
	thought the content is generated by a plugin script and is a
	known quantity. This worked in the major browsers including the
	Chromium 22 and 3.[123] tested with, but Chromium 3.4 would
	no longer run scripts in the iframe. Persons-of-curiosity may
	web search 'chromium iframe scripts' and see at a glance that
	Chromium has dithered on this subject. Bottom line: the sandbox
	attribute is removed. Apologies for this release so soon after
	2.1 a few days ago.

= 2.1 =
* Several small bug fixes and improvements.
* Now, by default HTML5 video will be placed as primary
	content with flash as fallback (see settings page).
* Now, if the stop button is clicked the initial poster
	image, if provided, should reappear.
* Better handling of unsupported HTML5 video types: if
	an MP4 was given it will passed to the flash player
	when necessary.

= 2.0 =
* Video will now display in the TinyMCE "Visual" editor. This
	requires HTML5 compatible video files, and a recent and
	not-too-buggy browser (Chromium is fine on GNU/Linux, but
	as on MS it might not run the script in the iframe,
	but the display is still useful; MSIE has some oddities but
	overall works). The settings page has a new option to control
	SWFPut video in the editor: always, only non-mobile, or
	never. If the video display feature is disabled, the
	shortcode will simply appear in the editor.
* Overdue improvement to the video control bar: if the display
	is too narrow for all buttons, then the non-essential
	fullscreen and natural-scale buttons are hidden.
* Miscellaneous small fixes.
* Checked with new WordPress 3.9.

= 1.0.9 =
* Vacated in quantum leap to ring 2 resulting from increased
	energy state induced by scale of recent changes.

= 1.0.8 =
* HTML5 video support now equals the original flash video support, and
	a new HTML5 video player provides an interface with the same
	design as the flash player, and as much of the same behavior
	as can be implemented with the HTML5 video specification.
* A new option (on the settings page) to make HTML5 video be primary
	content, with flash video as fallback. The default is to place
	flash video as primary content with HTML5 video as fallback due
	to the burden HTML5 video puts on users to provide several
	video file formats, but users who are confident in the use
	of HTML5 video will find this new option preferable.
* It is not necessary to specify both flash and and html video
	resources; either can be left out (i.e., SWFPut is no longer
	a flash video player first with html video as an afterthought).
* Incompatible change: a checkbox on the setup form to specify that
	the medium is audio, not video, has been removed. That feature
	really had no place in this plugin, and audio-only support in
	the flash player was bare-bones minimal.
* Interface: when a mobile browser is detected, the control bar
	removes the natural-scale and full-scale buttons, which do
	not make sense on mobile. The simpler control bar is more
	appropriate and usable.
* Improved help under the "Help" button the editor and widgets pages.
* Interface: volume control slider now presents vertically on
	non-mobile, and horizontally on mobile. It now scales down
	at small display sizes (previously it was clipped).
* The original design goal that JavaScript will not be necessary so
	that your site remains useful to visitors with scripting
	disabled has been retained, albeit with necessary qualification:
	the html video player requires JavaScript, but where scripting
	is not available, the default interface and behavior for the
	HTML5 video element provided by the browser will be present,
	so all is not lost.
* The several .swf binaries for control bar sizes are gone, now
	a single binary simply scales the control bar (which of course
	was the original intent and meant to be among the first
	updates, but time flies like a banana).
* Directory and file file name changes.
* Bug fixes, of course.

= 1.0.7 =
* Presentation improvements. Display should be well scaled now,
	at least for themes that handle scaling; e.g., 'viewport'
	meta element. This improvement should be particularly
	appreciable with regard to mobile platforms (on which the
	display was very poor in previous versions), but desktop/notebook
	machines benefit too when the window is made small. Video
	widgets place on sidebar should now be resized to sidebar
	width regardless of user-set dimensions, but on mobile if
	secondary content is placed below primary content (i.e.
	sidebar appears below main area) video object will use
	available space up up to the dimensions set.
* The original description through version 1.0.6 stated that
	"SWFPut does not add any JavaScript." That is no longer
	the case. Video object size adjustments depend on JavaScript,
	but on non-mobile platforms the display does not depend on
	script, and if scripts are disabled the video objects will
	behave as they have through version 1.0.6. On mobile platforms
	JavaScript is necessary because on those platforms the plugin now 
	builds the elements by script rather than putting out HTML
	directly. (It is probably uncommon and impractical for
	scripting to be disabled in mobile browsers.)
* There is a new input field on the setup forms, just below the
	dimensions fields. This is to provide a width to use only
	if a mobile browser is detected; the height is automatically
	proportional, according to the regular dimensions. This might
	be useful for widgets placed on the sidebar, because the
	sidebar might be placed below, rather than beside, the main
	content. In this case more space might be available, and
	larger display might be suitable. This feature is disabled
	with a value of '0' which is the default. Experiment.

= 1.0.6 =
* Added do-nothing index.php to prevent directory listing, as WP does.
* Made the "Screen Options" tab -> "Section Introductions" checkbox
	value persistent, if the "Save Settings" button is clicked.
* Style tweaks and size tweaks (admin) in response to WP 3.8 changes.
* Checked with WP 3.8: OK.

= 1.0.5 =
* BUG[unimportant]: tested a defined(FOO) (rather than 'FOO'),
	but PHP handles that common mistake anyway, and
	it could only matter in the very exceedingly extremely unlikely
	case that a .mo translation binary for this plugin's
	text domain has been installed under the WP's WP_LANG_DIR.
* Added check for naughty direct invocation.
* Checked (Oct 25 2013) with just-released WP 3.7: OK.

= 1.0.4 =
* Fixed duplicated message on settings page update resulting from
	unneeded settings_errors() call: this call did not cause a dup
	from 3.0.1 to 3.3.1 (but was not needed either), but between
	WP 3.3.1 and 3.5.? some core guard against the duplicate was
	removed (or broken?).
* Updated swf object element and added optional alternative
	img and video (html5) nested elements. Removed classid from
	object, except when MSIE is in user agent string. (inspired
	by suggestion from aileenf).
* Added help tabs.
* Some code cleanups.

= 1.0.3 =
* Maintenance.
* Put i18n final code (__() was already present), added make rules to
	build *.mo using (added) script in new locale dir, added FPO/test
	en_US.mo, confirmed working with dummy string replacement.
* Changed Opt* support classes to use display strings borrowed
	exactly from WP (3.6) core; these classes are not tied to this
	plugin and should not use its text-domain. Using core strings,
	they might still get translated (might get translated when the
	plugin does not -- that is deemed OK).
* Added is_admin() check in init code to avoid setting admin-only
	hooks when not needed (and executing associated code); plus a
	few more specific current_user_can() checks.
* Increased maximum "attachment" queried when finding suitable
	media files to present in posts/pages shortcode form.

= 1.0.2 =
* Corrections in (vaguely distinguished)
	add_(action|filter) calls, according to tag used, checked against
	WP source (whether do_action() or apply_filters() is invoked
	for the tag in question).
* Changed JS unescape() to decodeURIComponent().
* Removed compiled README.{tty,tt8} from distribution.
* Changed 'wptexturize' to 'htmlentities' for paths and things that
	should not be pretty-pretty'd.
* Changed 'Tags:' in readme.txt (and stable, etc.).

= 1.0.1 =
* Maintenance.
* Editing and corrections in readme.txt.
* Behavior change: without initial image ('poster'), medium is no
	longer fetched automatically (without visitor play); was a
	misfeature that would simulate an initial image by pausing
	at a random point within first few seconds of the video, but
	the unsolicited download is a bad idea. (Might be an option
	in future.)

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.9 =
* 

= 2.2.2.1 =
* Sigh. Just added a forgotten file (from 2.2.2).

= 2.2.2 =
* Bug fix release.

= 2.2.1 =
* Confirmed working with WP 4.1.

= 2.2 =
* Confirmed working with WP 4.0.
* Added alignment option.
* Added video preload option.

= 2.1.1 =
* Chromium 3,4 broken iframe handling will not run scripts
	with attribute sandbox="allowScripts", so sandbox is removed.
	Do web search 'Chromium iframe scripts' to see Chromium dither.

= 2.1 =
* Several small bug fixes and improvements.

= 2.0 =
* New video display in editor, minor bug fixes.

= 1.0.8 =
* HTML5 video support now equals the original flash video support, and
	a new HTML5 video player provides an interface with the same
	design as the flash player, and as much of the same behavior
	as can be implemented with the HTML5 video specification.

= 1.0.7 =
* Presentation improvements, especially for small mobile platforms.

= 1.0.6 =
* Confirmed working with WP 3.8.

= 1.0.5 =
* Confirmed working with WP 3.7.

= 1.0.4 =
* Now has option to specify fallback HTML5 video sources, and/or use
	the initial (poster) image as display when flash video is
	not supported.

= 1.0.3 =
* Internationalized (i18n) string handling should now be usable for
	anyone interested in making (and contributing) translations;
	distribution includes a .POT file. Remaining changes are cleanups
	which should not have a noticeable effect.

= 1.0.2 =
* BUG FIX: URLs with non-8-bit characters would be corrupted in form
	fields, causing not-found errors in the player: changed
	JS unescape() to decodeURIComponent(). (Feedback on non-UTF-8
	charsets would be welcome!)

= 1.0.1 =
* This revision has one important change: a misfeature that would
	simulate an initial image (if one was not set) by pausing
	at a random point within first few seconds of the video, but
	causing an unsolicited download of the medium in order to do so,
	has been disabled.
	
