=== SWFPut - SWFlash Put ===
Contributors: EdHynan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4Q2Y8ZUG8HXLC
Tags: video, audio, movies, tube, flash, flash player, graphics, movie, audio-visual, a/v content
Requires at least: 3.0.2
Tested up to: 3.6.1
Stable tag: 1.0.4
Text Domain: swfput_l10n
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

SWFPut provides a flash video player for posts and pages, and a widget, and optional HTML5 video fallback.

== Description ==

SWFPut helps place flash video within posts, on pages,
and in the sidebar or other widget areas (by providing a
widget). Video objects are placed and configured with
forms, so the user doesn't need to learn a shortcode or
maintain one with hand-editing. A shortcode will be visible
in the editor for posts and pages; it can be considered a
visual indication that the video is in place. The widget
does not use a shortcode. If you don't know what a shortcode
is, that's okay, SWFPut does not require you to know.

Here are some features of SWFPut to consider if you wonder
whether it will suit your purpose:

*	SWFPut includes and uses its own
	video player program (that runs in the
	web browser's flash plugin).
	It is not designed to work
	with other flash video player programs.

*	SWFPut works directly with media file (.flv, .mp4)
	URLs; that is, SWFPut does *not* embed
	the video players of providers such as YouTube or Vimeo.
	SWFPut is for video files which are accessible by URL,
	whether hosted at your site or off-site.
	The setup form provides two media lists:
	one offers media files (.flv, .mp4) that you can
	upload with the WordPress 'Add Media' feature,
	and one offer media files (.flv, .mp4) that are
	found in a search under the "uploads" directory
	(this allows you upload media files without using
	the WordPress PHP upload, which might have a size
	limit too low for audio/visual material). Of course,
	a URL may be placed directly in a text input field.

*	An initial image (sometimes called a "poster") that
	will display until the play button is clicked can
	(and should) be provided. The setup form provides for
	this in the same way as described above.

*	SWFPut, as of version 1.0.4,
	allows for optional URLs (with optional
	mime and codec types) that will be placed in
	an HTML5 video element, as a fallback
	in case flash is not supported. A tab
	has been added to the editor screen help (WP 3.3 or greater)
	with a brief explanation of this text field, but the user
	will need to understand the state of HTML5 video
	regarding media types.

*	SWFPut should not interfere with the appearance of
	a site: a video is presented much like an image
	(such as .png or .jpg) is, with the same sort of
	border and optional caption.

*	SWFPut allows you to set the size of the
	video player window. Generally, you would want the
	aspect ratio of the window to match that of the video
	(but that is not required). The size of the player
	window does not need to match the display size of
	the video frames; the video will be scaled to fit
	the player window, maintaining the video aspect ratio
	as set by you or as implied by the width and height.
	Note that the widths of the page columns set by
	your theme's CSS limit the width of the player window.

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
	because non-free binary-only software is bad. (At the
	time of this writing, Gnash does not handle the MP4
	video container format, so it is preferable that you
	prepare video in the FLV container, even using the
	h.264 and AAC codecs. Of course, you may use MP4 if
	you must.)

*	The flash video player program included with SWFPut
	is written and compiled with the *Ming* PHP extension,
	and the code is included, so you may modify the player.

*	SWFPut does not add any JavaScript to the pages
	generated for your visitors, which might be helpful if
	you try to keep your pages useful to those who disable
	JavaScript in their browsers. (Such visitors might need to
	explicitly enable the flash web browser plugin, but that is
	another, unavoidable, issue.) JavaScript is only used in the
	administrative interface for the forms and manipulation of
	shortcodes in the editor.

== Installation ==

SWFPut is installed through the WordPress administrative interface.

1. Fetch the SWFPut zip file; save it on your computer

1. Log in at your WordPress site

1. Select 'Plugins -> Add New'

1. Select 'Upload'

1. Select 'Browse'

1. In your system's file selector, select the SWFPut zip file;
  select 'OK' or equivalent

1. Select 'Install Now'

1. Select 'Activate Plugin'

At this point "SWFlash Put" should be an entry on the plugins page.
The Settings menu should have an item "SWFPut Plugin".

If the above is not so, there is probably a problem at your site's
host; for example if the host is Unix system there is very likely
a problem with incorrect permissions metadata (mode) on a directory
such as wp-content/uploads, or an unsuitable user or group ownership
of (probably several) files and directories. This can be a frequent
problem if the host has PHP configured in "safe mode".

If the host is not a Unix system, I'm sorry to say I cannot help;
maybe your hosting provider can.

If the installation was successful, you should see a "SWFPut Flash Video"
widget under 'Appearance -> Widgets' and a form entitled
"SWFPut Flash Video Shortcode" on the posts and pages editing pages.

For additional help, you will find README* files (differing in format,
and excluding 'readme.txt', which is this file) that discuss the
flash video player in more detail.

== Frequently Asked Questions ==

= Is this really a FAQ? =

At the time of this writing, 0 (zero) questions have
been asked, which implies that few have been asked
frequently. Until this becomes a true FAQ, it will
be used to answer questions that are merely anticipated,
as is common practice.

= Do I really need to understand "aspect ratio" and such-like? =

Probably not. In most case the width and height of the
video will match the intended display proportion.
"Anamorphic" video is not rare, but probably not too common
either. The author has seen videos on e.g., YouTube,
that are distorted by wrong display aspect ratio
(which is not YouTube's fault), but only a few.
If you find that your video looks squeezed or stretched,
you can always use a little trial & error with the display
aspect setting until it looks good.

What you *must* understand is that you *must* convert
video to the format (the type) that the web-browser plugin
can handle; namely, FLV or MP4. If you use a converter
program designed for non-experts, you won't need to
understand too many details. A web search should turn up
some converter programs that might be worth a try.

= Why doesn't SWFPut support HTML5 video? =

Because the author has decided that that would be
done best in a separate (but similar) plugin. The
author might write one, particularly if SWFPut generates
some interest.

The problem with including HTML5 video in the same
package is that HTML5 video in its current specification
does not provide features that SWFPut provides; for
example, HTML5 video will not scale video disproportionate
to the pixel width and height and will *only* scale video
(proportionally) to the width or height of the html video
element. (An insane JavaScript hack can create a not-displayed
video object and use a timeout callback at at least the video
frame rate to paint the current frame on a canvas with scaling
suitably calculated for an anamorphic video, but using this
method squanders the visitor's CPU, increases dropped frames,
has no full-screen mode, and provides no built in controls,
and is a bad idea that the author has looked into and
rejected.) There are other reasons, such as different
supported file formats.

Update 1 August 2013: WordPress 3.6 is released, with HTML5
video and audio support. That's another reason.

Update for version 1.0.4: there is now a field in the video
setup forms that can be given URLs (separated by '|' if there is
more than one) which will appear as SOURCE elements within a
VIDEO element within the OBJECT element that specifies the
flash program; so, if flash support is absent, a browser might
make use of the HTML5 alternative. It remains up to the user
to understand the the current state of HTML5 regarding
video formats (uneven and differing support among common
browsers), how to prepare a set of these video files in
different formats and specify them in the best order to be
useful with the greatest number of browsers. Each URL may have
an optional argument for the type attribute (NOT a whole type
attribute statement -- only the argument that will appear
within quotes), separated from the URL by a '?' character.

= Does SWFPut retard hair loss, or increase gas mileage? =

Of course!

= Are you going to anticipate more questions? =

Maybe later. Honey, I burned the spaghetti.

== Screenshots ==

1. The SWFPut widget setup form (bottom).

2. The SWFPut posts/page setup form ('meta box') with the first
	section hidden.

3. The appearance of video placed by SWFPut (Twentyeleven theme
	with dark custom colors, sidebar on left), not yet playing.

== Changelog ==

= 1.0.4 =
* Fixed duplicated message on settings page update resulting from
	uneeded settings_errors() call: this call did not cause a dup
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

= 1.0.4 =
Now has option to specify fallback HTML5 video sources, and/or use
	the initial (poster) image as display when flash video is
	not supported.

= 1.0.3 =
Internationalized (i18n) string handling should now be usable for
	anyone interested in making (and contributing) translations;
	distribution includes a .POT file. Remaining changes are cleanups
	which should not have a noticeable effect.

= 1.0.2 =
BUG FIX: URLs with non-8-bit characters would be corrupted in form
	fields, causing not-found errors in the player: changed
	JS unescape() to decodeURIComponent(). (Feedback on non-UTF-8
	charsets would be welcome!)

= 1.0.1 =
This revision has one important change: a misfeature that would
	simulate an initial image (if one was not set) by pausing
	at a random point within first few seconds of the video, but
	causing an unsolicited download of the medium in order to do so,
	has been disabled.
	
