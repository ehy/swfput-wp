<?php
/*
 *      help_txt.php
 *      
 *      Copyright 2014 Ed Hynan <edhynan@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; specifically version 3 of the License.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

/* text editor: use real tabs of 4 column width, LF line ends */
/* human coder: keep line length <= 72 columns; break at params */

/*
 * Get text and with markup for the "Help" tab on WordPress
 * post and page editing pages, where the SWFPut Video form
 * also appears in a meta-box.
 *
 * Move to this file in v. 1.0.9 so that PHP needn't parse it
 * when not used.
 */


// Get the help to be displayed with a tab under the "Help" button.
// for posts/pages, where there is an editor and the SWFPut form.
function swfput_get_helptext($htmllink, $pdflink)
{
	$fmt =
	__('<p>
	Hopefully, much of the SWFPut setup form
	is self-explanatory.
	There is more detailed documentation as HTML
	<a href="%s" target="_blank">here (in a new tab)</a>,
	or as a PDF file
	<a href="%s" target="_blank">here (in a new tab)</a>.
	</p><p>
	There is one important restriction on the form\'s
	text entry fields. The values may not have any
	ASCII \'&quot;\' (double quote) characters. Hopefully
	that will not be a problem.
	</p><p>
	The following items probably need explanation:
	</p><p>
	<h3>Flash or HTML5 video URLs or media library IDs</h3>
	Near the top of the form, after the "Caption" field,
	a text entry field named
	"Flash video URL or media library ID" appears.
	This is for the video file that the flash player
	will use. You may enter a URL by hand (which may
	be off-site), or make a selection from the next
	two items,
	"Select flash video URL from uploads directory" and
	"Select ID for flash video from media library."
	The first of these two holds a selection of files
	found under your <code>wp-content/uploads</code>
	directory with a FLV or MP4 extension. Files
	are placed under this directory when you use the
	<em>WordPress</em> media library, but you may also
	place files there \'by hand\' using, for example,
	ftp or ssh or any suitable utility (placing files
	in a subdirectory is a good idea).
	In fact, uploading video files \'by hand\' might
	be the easiest way to bypass size limits that
	reject large video file uploads through the
	media library interface. The next field
	has a selection of media files with a
	<em>WordPress</em> \'attachment id\' and so it
	provides only those files uploaded to the media
	library (with a FLV or MP4 extension).
	</p><p>
	After those three fields for flash video, there is
	"HTML5 video URLs or media library IDs" which,
	like the flash text entry, is followed by selections
	of files and \'attachment id\'s. These show files
	with MP4 or OGG or OGV or WEBM extensions. As the
	field names suggest, these are for the HTML5 video
	player. An important difference is that when you
	make a selection, the entry field is appended,
	rather than replaced, with a \'|\' separator.
	The HTML5 video entry field can take more than one
	value, as explained below.
	</p><p>
	It is not necessary to fill both the flash and HTML5
	video URL fields, but it is a good idea to do so
	if you can prepare the video in the needed formats.
	If you specify only one type, the other type of
	video player is not produced in the page code.
	If you do specify URLs for both flash and HTML5 video,
	then the page code will have one as primary content,
	and the other as "fallback" content. Fallback content
	is shown by the web-browser only when the primary
	content cannot be shown. For example, if flash is
	primary content, but you have specified HTML5 content
	too, then a visitor to your site who does not
	have a flash plugin would see the HTML5 video player
	if the browser supports it.
	(Mobile browsers are less likely to have a flash
	plugin than desktop-type browsers.)
	</p><p>
	By default, flash is made primary content with
	HTML5 as fallback. You may make HTML5 be primary
	and flash be fallback with the "HTML5 video primary"
	option on the settings page. (Go to the "Settings"
	menu and select "SWFPut Plugin" for the settings page.)
	</p><p>
	The current state of affairs with HTML5 video will
	require three transcodings of the video if you
	want broad browser support; moreover, the supported
	"container" formats -- .webm, .ogg/.ogv, and .mp4 --
	might contain different audio and video types ("codecs")
	and only some of these will be supported by various
	browsers.
	Users not already familiar with this topic should
	do enough research to make the preceding statements
	clear.
	</p><p>
	The "HTML5 video URLs" field
	will accept any number of URLs, which
	must be separated by \'|\'. Each URL <em>may</em>
	be appended with a mime-type + codecs argument,
	separated from the URL by \'?\'. Whitespace around
	the separators is accepted and stripped-off. Please
	note that the argument given should <em>not</em>
	include "type=" or quotes: give only the
	statement that should appear within the quotes.
	For example:</p>
	<blockquote><code>
	vids/gato.mp4 ? video/mp4 | vids/gato.webm ? video/webm; codecs=vp8,vorbis | vids/gato.ogv?video/ogg; codecs=theora,vorbis
	</code></blockquote>
	<p>
	In the example, where two codecs are specified there is
	no space after the comma.
	Some online examples
	show a space after the comma,
	but some older
	versions of <em>Firefox</em> will reject that
	usage, so the space after the comma is best left out.
	</p><p>
	<h3>Use initial image as no-video alternate</h3>
	This checkbox, if enabled (it is, by default) will
	use the "initial image file" that may be specified
	for the video player in an \'img\' element
	that the visitor\'s browser might display if video
	is not available.
	</p><p>
	There is one additional consideration for this image:
	the \'img\' element is given the width and height
	specified in the form for the flash player, and the
	visitor\'s browser will scale the image in both
	dimensions, possibly causing the image to be
	\'stretched\' or \'squeezed\'.
	The image proportions are restored with
	<em>JavaScript</em>, but only if scripts are
	not disabled in the visitor\'s browser.
	Therefore, it is a
	good idea to prepare images to have the expected
	<em>pixel</em> aspect ratio
	(top/bottom or left/right tranparent
	areas might be one solution).
	</p><p>
	<h3>Mobile width</h3>
	This input field appears just below the
	pixel dimensions fields. If this value is
	greater than zero, and a mobile browser is
	detected, then this width will be used with
	a proportional height according to the
	regular pixel dimensions. This might be
	useful when, for example, sidebar content
	actually appears below main content due to
	the mobile browser\'s small size (theme support
	may be necessary to see this behavior). This
	is probably most useful for video widgets placed
	on a sidebar, but please experiment.
	The default value for this field, 0,
	disables this feature, and it has no effect if
	a mobile browser is not detected.
	</p>', 'swfput_l10n');

	return sprintf($fmt, $htmllink, $pdflink);
}
?>
