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
	<strong>Add SWFPut Video</strong> - Just above the
	the editor toolbars, <em>WordPress</em> places the
	"Add Media" button. Next to that, you will find the
	"Add SWFPut Video" button. First, place the cursor in
	the editor at the place for your video, then click
	the button. A placeholder video will appear, ready to
	setup. It should be selected by default; click it
	if necessary.
	</p>
	<p>
	<strong>Editing</strong> - when a <em>SWFPut</em>
	video is selected two buttons should appear -- one button
	looks like a pencil. and this opens a graphical editor 
	dialog box, similar to the one used for <em>WordPress</em>
	core media editing but with some different features.
	</p>
	<p>
	This dialog provides basic setup suitable for most video.
	<em>SWFPut</em> provides a few features not found in
	the setup dialog (which is meant to be simple and easy).
	Advanced setup is done with a form in a "metabox" on
	the editor page. You will probably not need this, but
	if you think you might, read on. The remainder of this
	help section concerns the metabox advanced form, but if
	<em>SWFPut</em> is new to you, please start with the
	steps described above.
	</p>
	<p>
	There is more detailed documentation as HTML
	<a href="%s" target="_blank">here (in a new tab)</a>,
	or as a PDF file
	<a href="%s" target="_blank">here (in a new tab)</a>.
	</p>
	<h3>The Metabox Form</h3>
	<p>
	There is one important restriction on the form\'s
	text entry fields. The values may not have any
	ASCII \'&quot;\' (double quote) characters. Hopefully
	that will not be a problem.
	</p><p>
	The following items probably need explanation:
	</p><p>
	<h4>Flash or HTML5 video URLs or media library IDs</h4>
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
	</p><p>
	By default, HTML5 is made primary content with
	flash as fallback. You may make flash be primary
	and HTML5 be fallback with the "HTML5 video primary"
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
	Some online examples, and even HTML specifaction pages,
	show a space after the comma,
	but browsers might reject that
	usage, so SWFPut will normalize the codecs argument.
	</p><p>
	<h4>Use initial image as no-video alternate</h4>
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
	<h4>Mobile width</h4>
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
	</p><p>
	<h4>Video preload</h4>
	This "radio" type option is in the <b>Behavior</b> section
	of the form. HTML5 video allows a "preload" attribute
	with a value of "none" or "metadata" or "auto." This
	option provides those three values and one special selection:
	"per initial image." This special selection will use
	"none" if an "initial image file" is set (in the <b>Media</b>
	section of the form), or "metadata" if an initial
	image, or <i>poster</i>, is not set.
	</p><p>
	The "metadata" selection tells the browser that it
	may fetch a small part of the video file that
	includes information such as dimensions, duration,
	codec types.
	This can be useful because with
	it a browser might also receive some of the video
	frames, and so it may display one frame as a \'poster.\'
	(Whether a
	frame displayed this way is suitable is not certain.)
	</p><p>
	If "none" is selected the browser will not fetch
	any of the video until it is played, and so without
	an initial image, the video region on the page will
	be solid black until played.
	</p><p>
	The "auto" selection should be avoided unless you
	know what it does and that you need it. This is
	because with "auto" the browser may choose to
	fetch the entire video even before the visitor
	actively plays the video. Video files can be
	quite large, and a large unsolicited download
	might be unkind to your site\'s visitors; it
	might even cause a visitor additional charges
	depending on their connection service. Also
	consider your server and network load.
	</p><p>
	The flash player does not have similar attributes,
	but will behave similarly with regard to an
	initial image: if one was not set, and the
	preload option is not "none," then the player
	will start playback and let it advance for a
	small random period, and then pause playback,
	leaving a visible frame to act as a \'poster.\'
	</p>', 'swfput_l10n');

	return sprintf($fmt, $htmllink, $pdflink);
}
?>
