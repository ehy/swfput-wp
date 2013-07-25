=== Plugin Name ===
Contributors: EdHynan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4Q2Y8ZUG8HXLC
Tags: video, audio, movies, tube, flash, graphics, webcam, movie, cat videos, audio-visual, a/v content
Requires at least: 3.0.2
Tested up to: 3.5.2
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

SWFPut provides a flash video player for posts and a widget and the forms to configure instances with a video source and playback attributes.

== Description ==

     SWFPut  is  a  plugin  for the popular WordPress weblog
software. It provides a video player program for  the  flash
plugin  and  the means to configure an instance with a video
source and playback attributes.  There are two separate com-
ponents:  the  flash  video player, and the WordPress plugin
proper.  The video player is delivered to site  visitors  by
the  plugin  in  the traditional <object ...> block with the
necessary arguments. Flash video objects may  be  placed  in
posts  and  pages,  or in the widget areas supported by your
theme (i.e., the plugin includes a widget).  Video is placed
in posts and pages with a shortcode; if you do not know what
a shortcode is, or do not want to deal with them, that's  no
problem.  (In fact, it is preferable that the shortcodes not
be hand-edited, and they will not  be  discussed  in  detail
here.)  The  plugin  adds  to the administrative interface a
full featured form to setup and  add,  or  edit,  or  delete
video objects, so the user does not need to be troubled with
shortcodes (they will be visible in the editor; you will get
used  to  them).   The flash video widget has a similar full
featured form.

     The plugin does not add any  JavaScript  to  the  pages
generated  for  your visitors, which might be helpful if you
try  to  keep  your  pages  useful  to  those  who   disable
JavaScript  in  their browsers. (Such visitors might need to
explicitly enable the flash web browser plugin, but that  is
another,  unavoidable,  issue.)   JavaScript  is used in the
administrative interface for the forms and  manipulation  of
shortcodes  in  the  editor;  but  of  course  you must have
JavaScript enabled when you log in to your WordPress site --
this does not affect your visitors.

     (Note  that  the  SWFPut video player has been coded to
work well with the free Gnash web browser plugin, as well as
the  closed  binary-only  proprietary version in common use.
As of this writing, Gnash does not handle  MP4  files  well,
even though it handles H.264 video and AAC audio if they are
in an FLV container file.)

== Installation ==

SWFPut is installed through the WordPress administrative interface.

1 Fetch the SWFPut zip file; save it on your computer

1 Log in at your WordPress site

1 Select 'Plugins -> Add New'

1 Select 'Upload'

1 Select 'Browse'

1 In your system's file selector, select the SWFPut zip file;
  select 'OK' or equivalent

1 Select 'Install Now'

1 Select 'Activate Plugin'

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

= How many questions have been asked frequently =

See below.

= How many questions have been asked in all =

At the time of this writing, 0 (zero). The time of this writing
is the time of preparation for initial release, so that amount
should not seem too small. Of course, this answer might be present
here until the next release, and it might have happened that questions
have been asked in the interim, and so 0 might not really be an
accurate figure.

= How many questions have been answered in all =

Three more than have been asked. At the time of this writing.

== Screenshots ==

TBA

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
None as yet.

== Arbitrary section ==

Coming: media preparation resources.

