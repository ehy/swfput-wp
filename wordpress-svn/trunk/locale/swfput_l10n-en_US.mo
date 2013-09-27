��    c      4  �   L      p     q     �     �  �  �  |   �     /  �  C     �  "        )     2     ;     W  
   p     {  *   �     �     �  %   �          9     T     o  D   �  h   �     3     I  !   Y     {     �     �     �     �     �     �     
       !   )  "   K  
   n     y          �  3   �     �  &   �       .        N     ^     u  *   �  "   �     �     �                /     D     W     t     �     �     �  !   �     �     �          *     3     Q     V     q  �  �  !   S!  �   u!  �  g"  �  %  u  �)    -     9/  =   >/     |/  %   �/     �/  '   �/  )   �/     )0     B0     ]0     v0     �0     �0  
   �0     �0     �0     �0      1  �  1     �2     �2     �2  �  �2  |   �?     I@  �  ]@     C  "    C     CC     LC     UC     qC  
   �C     �C  *   �C     �C     �C  %   D     6D     SD     nD     �D  D   �D  h   �D     ME     cE  !   sE     �E     �E     �E     �E     �E     �E     F     $F     5F  !   CF  "   eF  
   �F     �F     �F     �F  3   �F     �F  &   G     ,G  .   9G     hG     xG     �G  *   �G  "   �G     �G     H     H     4H     IH     ^H     qH     �H     �H     �H     �H  !   �H     �H     I     &I     DI     MI     kI     pI     �I  �  �I  !   mK  �   �K  �  �L  �  6O  o  �S    3W     MY  =   RY     �Y  %   �Y     �Y  '   �Y  )   Z     =Z     VZ     qZ     �Z     �Z     �Z  
   �Z     �Z     �Z      [     [         c   ^           [       ,   A   M                    &   F           ]   2         N      Y   1   .   C   W   0   S       K      
   Q      P              O          5           L              8   9           ?   B   b   (   %   6                      )          J   G      R             `   $             <   *   I   7   '                     \   H          @       ;             :   !   D   +   =   #   /   Z   4              >       "          _   -   T   U                 X   V   E           	   3   a        [A/V content "%s" disabled]  /^?y(e((s|ah)!?)?)?$/i /^n(o!?)?)?$/i <p>
				If the SWFPut shortcode form. or "metabox,"
				is not self-explanatory
				(hopefully, much of it will be), there is more
				explanation
				<a href="%s" target="_blank">here (in a new tab)</a>,
				or as a PDF
				<a href="%s" target="_blank">here (in a new tab)</a>.
				</p><p>
				There is one important restriction on the form's
				text entry fields. The values may not have any
				ASCII '&quot;' (double quote) characters. Hopefully
				that will not be a problem.
				</p><p>
				Two form items (added in version 1.0.4) are probably
				not self-explanatory:
				</p><p>
				<h6>URLs for alternate HTML5 video</h6>
				This text field accepts alternatives for non-flash
				browsers, if recent enough to provide HTML5 video.
				The current state of affairs with HTML5 video will
				require three transcodings of the material if you
				want broad browser support; moreover, the supported
				"container" formats -- .webm, .ogg, and .mp4 --
				might contain different audio and video types ("codecs")
				and only some of these will be supported by various
				browsers.
				Users not already familiar with this topic will need
				to do enough research to make the preceding statements
				clear.
				</p><p>
				The text field will accept any number of URLs, which
				must be separated by '|'. Each URL <em>may</em>
				be appended with a mime-type + codecs argument,
				separated from the URL by '?'. Whitespace around
				the separators is accepted and stripped-off. Please
				note that the argument given should <em>not</em>
				include "type=" or the quotes: give only the
				statement that should appear within the quotes.
				For example:</p>
				<blockquote><code>
				vids/gato.mp4?video/mp4 | vids/gato.webm ? video/webm; codecs=vp8,vorbis|vids/gato.ogv?video/ogg; codecs='theora, vorbis'
				</code></blockquote>
				<p>
				In the example, where two codecs are specified there is
				no space after the comma, or the two codecs are
				enclosed in <em>single</em> quotes.
				Many online examples
				show a space after the comma without the quotes,
				but some older
				versions of <em>Firefox</em> will reject that
				usage, so the space after the comma is best left out.
				</p><p>
				<h6>Use initial image as non-flash alternate</h6>
				This checkbox, if enabled (it is, by default) will
				use the "initial image file" that may be specified
				for the flash player in an 'img' element
				that the visitor's browser should display if flash
				is not available.
				</p><p>
				If alternate HTML5 video was specified, that will
				remain the first alternate display, and the initial
				image should display if neither flash or HTML5 video
				are available.
				</p><p>
				There is one important consideration for this image:
				the 'img' element is given the width and height
				specified in the form for the flash player, and the
				visitor's browser will scale the image in both
				dimensions, possibly causing the image to be
				'stretched' or 'squeezed'. (That is not a problem
				in the flash player, as it is coded to display the
				initial image proportionally.) Therefore, it is a
				good idea to prepare images to have the expected
				<em>pixel</em> aspect ratio
				(top/bottom or left/right tranparent
				areas might be one solution).
				</p>
				 <p><strong>%s</strong></p><p>
			Tips and examples can be found on the
			<a href="%s" target="_blank">web page</a>.
			</p> <p>TODO: %s
			</p> <p>The sections of this page each have an
			introduction which will, hopefully, be helpful.
			These introductions may
			be hidden or shown with a checkbox under the
			"Screen Options" tab (next to "Help") or with
			the "%1$s"
			option, which is the first option on this page.
			If "Screen Options" is absent, the verbose option
			is off: it must be on to enable that tab.
			</p><p>
			<em>SWFPut</em> will work well with
			the installed defaults, so it's not necessary
			to worry over the options on this page. 
			</p><p>
			Remember, when any change is made, the new settings must
			be submitted with the "%2$s" button, near the end
			of this page, to take effect.
			</p> Allow full screen: Auto aspect (e.g. 360x240 to 4:3): Behavior Caption: Control bar Height (20-50): Delete current in editor Dimensions Display and Runtime Settings. Display aspect (e.g. 4:3, precludes Auto): Dynamic SWF generation: Enable shortcode in posts Enable shortcode or attachment search Enable shortcodes in widgets Enable the included widget Enable widget or shortcode Fill form from editor Flash video (with HTML5 video fallback option) for your widget areas Flash video is not available, and the alternate <code>video</code> sources were rejected by your browser For more information: General Options Go back to top (General section). Go forward to save button. Height (default %u): Height: Hide Hide and disable control bar: Hide control bar initially: Initial volume (0-100): Install options: Introduction: Load image ID from media library: Load image from uploads directory: Loop play: Media Medium is audio (e.g. *.mp3): Medium is audio: One (%d) setting updated Some settings (%d) updated Overview Permanently delete settings (clean db) Pixel Width: Pixel aspect (e.g. 8:9, precluded by Display): Place in posts: Place in widget areas: Place new in editor Play on load (else waits for play button): Playpath (rtmp) or co-video (mp3): Playpath (rtmp): Plugin Install Settings Replace current in editor Reset default values SWFPut Configuration SWFPut Flash Video SWFPut Flash Video Shortcode SWFPut Form SWFPut Plugin SWFPut Plugin Configuration Save Settings Search attachment links in posts: Search attachments in posts Section introductions Select ID from media library: Settings Settings updated successfully Show Show verbose introductions Show verbose introductions: The PHP+Ming option selects whether
				the Flash player program is generated with PHP
				and the Ming extension for each request.
				When this option is not selected, then
				a compiled binary player is used.
				This option is only displayed if the Ming
				PHP extension is installed and loaded; if you
				are reading this then Ming has been found to
				be loaded.
				Note that this option will increase the load on the
				server of your site. The flash plugin is not available The verbose option selects whether
			verbose descriptions
			should be displayed with the various settings
			sections. The long descriptions, of which 
			this paragraph is an example,
			will not be shown if the option is not
			selected. These options enable or completely disable
			placing video in posts or widgets. If the placement
			of video must be switched on or off, for either
			posts (and pages) or widgets
			or both, these are the options to use.
			</p><p>
			When the plugin shortcode is disabled the flash
			video player that would have been displayed is
			replaced by a notice with the form
			"[A/V content &lt;caption&gt; disabled],"
			where "&lt;caption&gt;"
			is any caption that was included with the shortcode,
			or empty if there was no caption.
			</p><p>
			Note that in the two following sections,
			"Video In Posts" and "Video In Widget Areas,"
			the options are effective only if enabled here. These options select 
			how flash video (or audio) may be placed in posts or pages.
			Use shortcodes for any new posts (and preferably
			for existing posts) that should include
			the flash media player of this plugin.
			Shortcodes are an efficient method provided by the
			<em>WordPress</em> API. When shortcodes are enabled,
			a form for parameters will appear in the post (and page)
			editing pages (probably near the bottom of the page,
			but it can be dragged nearer to the editor).
			</p><p>
			The "Search attachment"
			option might help with some existing posts if
			you already have attached media (i.e., the posts contain
			attachment_id=<em>N</em> links).
			The attachment number is used to find the associated
			URL, and if the filename extension suggests that the
			medium is a suitable type, the flash player code
			is put in line with the URL; the original attachment_id
			URL is placed after the flash player.
			Use of this option is discouraged
			because it requires additional processing of each
			line of each post (or page) displayed,
			and so it increases server load. User parameters
			are not available for this method. These options select 
			how flash video (or audio) may be placed in widget areas.
			The first option selects use of the included multi-widget.
			This widget is configured in the
			Appearance-&gt;Widgets page, just
			like the widgets included with <em>WordPress</em>, and
			the widget setup interface
			includes a form to set parameters.
			</p><p>
			The second option "shortcodes in widgets"
			selects shortcode processing in other widget output, as for
			posts. This is probably only useful with the
			<em>WordPress</em> Text widget or a similar widget. These
			shortcodes must be entered by hand, and therefore this
			option requires a knowledge of the shortcode and
			parameters used by this plugin.
			(If necessary, a temporary shortcode
			can be made within a post using the provided form, and
			then cut and
			pasted into the widget text, on a line of its own.) This section includes optional
			features for plugin install or uninstall. Presently
			the only option is whether to remove the plugin's
			set of options from the database when
			the plugin is deleted.
			There is probably no reason to leave the options in
			place if you intend to delete the plugin permanently;
			you may simply deactivate the plugin if
			you want it off temporarily.
			If you intend to delete and then reinstall the plugin,
			possibly for a new version or update, then keeping the
			options might be helpful. Tips URLs for alternate HTML5 video (optional: .mp4, .webm, .ogv): Url from uploads directory: Url of initial image file (optional): Url or media library ID: Use SWF script if PHP+Ming is available Use initial image as non-flash alternate: Use shortcodes in posts: Use shortcodes in widgets: Use the included widget: Video In Posts Video In Widget Areas Video Placement Options WRITE TIPS When the plugin is uninstalled: Widget title: Width (default %u): none Project-Id-Version: swfput 1.0.4
Report-Msgid-Bugs-To: edhynan@gmail.com
POT-Creation-Date: 2013-09-27 17:26-0400
PO-Revision-Date: 2013-09-27 17:26 EDT
Last-Translator: FULL NAME <EMAIL@ADDRESS>
Language-Team: LANGUAGE <LL@li.org>
Language: en_US
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;
  [A/V content "%s" disabled]  /^?y(e((s|ah)!?)?)?$/i /^n(o!?)?)?$/i <p>
				If the SWFPut shortcode form. or "metabox,"
				is not self-explanatory
				(hopefully, much of it will be), there is more
				explanation
				<a href="%s" target="_blank">here (in a new tab)</a>,
				or as a PDF
				<a href="%s" target="_blank">here (in a new tab)</a>.
				</p><p>
				There is one important restriction on the form's
				text entry fields. The values may not have any
				ASCII '&quot;' (double quote) characters. Hopefully
				that will not be a problem.
				</p><p>
				Two form items (added in version 1.0.4) are probably
				not self-explanatory:
				</p><p>
				<h6>URLs for alternate HTML5 video</h6>
				This text field accepts alternatives for non-flash
				browsers, if recent enough to provide HTML5 video.
				The current state of affairs with HTML5 video will
				require three transcodings of the material if you
				want broad browser support; moreover, the supported
				"container" formats -- .webm, .ogg, and .mp4 --
				might contain different audio and video types ("codecs")
				and only some of these will be supported by various
				browsers.
				Users not already familiar with this topic will need
				to do enough research to make the preceding statements
				clear.
				</p><p>
				The text field will accept any number of URLs, which
				must be separated by '|'. Each URL <em>may</em>
				be appended with a mime-type + codecs argument,
				separated from the URL by '?'. Whitespace around
				the separators is accepted and stripped-off. Please
				note that the argument given should <em>not</em>
				include "type=" or the quotes: give only the
				statement that should appear within the quotes.
				For example:</p>
				<blockquote><code>
				vids/gato.mp4?video/mp4 | vids/gato.webm ? video/webm; codecs=vp8,vorbis|vids/gato.ogv?video/ogg; codecs='theora, vorbis'
				</code></blockquote>
				<p>
				In the example, where two codecs are specified there is
				no space after the comma, or the two codecs are
				enclosed in <em>single</em> quotes.
				Many online examples
				show a space after the comma without the quotes,
				but some older
				versions of <em>Firefox</em> will reject that
				usage, so the space after the comma is best left out.
				</p><p>
				<h6>Use initial image as non-flash alternate</h6>
				This checkbox, if enabled (it is, by default) will
				use the "initial image file" that may be specified
				for the flash player in an 'img' element
				that the visitor's browser should display if flash
				is not available.
				</p><p>
				If alternate HTML5 video was specified, that will
				remain the first alternate display, and the initial
				image should display if neither flash or HTML5 video
				are available.
				</p><p>
				There is one important consideration for this image:
				the 'img' element is given the width and height
				specified in the form for the flash player, and the
				visitor's browser will scale the image in both
				dimensions, possibly causing the image to be
				'stretched' or 'squeezed'. (That is not a problem
				in the flash player, as it is coded to display the
				initial image proportionally.) Therefore, it is a
				good idea to prepare images to have the expected
				<em>pixel</em> aspect ratio
				(top/bottom or left/right tranparent
				areas might be one solution).
				</p>
				 <p><strong>%s</strong></p><p>
			Tips and examples can be found on the
			<a href="%s" target="_blank">web page</a>.
			</p> <p>TODO: %s
			</p> <p>The sections of this page each have an
			introduction which will, hopefully, be helpful.
			These introductions may
			be hidden or shown with a checkbox under the
			"Screen Options" tab (next to "Help") or with
			the "%1$s"
			option, which is the first option on this page.
			If "Screen Options" is absent, the verbose option
			is off: it must be on to enable that tab.
			</p><p>
			<em>SWFPut</em> will work well with
			the installed defaults, so it's not necessary
			to worry over the options on this page. 
			</p><p>
			Remember, when any change is made, the new settings must
			be submitted with the "%2$s" button, near the end
			of this page, to take effect.
			</p> Allow full screen: Auto aspect (e.g. 360x240 to 4:3): Behavior Caption: Control bar Height (20-50): Delete current in editor Dimensions Display and Runtime Settings. Display aspect (e.g. 4:3, precludes Auto): Dynamic SWF generation: Enable shortcode in posts Enable shortcode or attachment search Enable shortcodes in widgets Enable the included widget Enable widget or shortcode Fill form from editor Flash video (with HTML5 video fallback option) for your widget areas Flash video is not available, and the alternate <code>video</code> sources were rejected by your browser For more information: General Options Go back to top (General section). Go forward to save button. Height (default %u): Height: Hide Hide and disable control bar: Hide control bar initially: Initial volume (0-100): Install options: Introduction: Load image ID from media library: Load image from uploads directory: Loop play: Media Medium is audio (e.g. *.mp3): Medium is audio: One (%d) setting updated Some settings (%d) updated Overview Permanently delete settings (clean db) Pixel Width: Pixel aspect (e.g. 8:9, precluded by Display): Place in posts: Place in widget areas: Place new in editor Play on load (else waits for play button): Playpath (rtmp) or co-video (mp3): Playpath (rtmp): Plugin Install Settings Replace current in editor Reset default values SWFPut Configuration SWFPut Flash Video SWFPut Flash Video Shortcode SWFPut Form SWFPut Plugin SWFPut Plugin Configuration Save Settings Search attachment links in posts: Search attachments in posts Section introductions Select ID from media library: Settings Settings updated successfully Show Show verbose introductions Show verbose introductions: The PHP+Ming option selects whether
				the Flash player program is generated with PHP
				and the Ming extension for each request.
				When this option is not selected, then
				a compiled binary player is used.
				This option is only displayed if the Ming
				PHP extension is installed and loaded; if you
				are reading this then Ming has been found to
				be loaded.
				Note that this option will increase the load on the
				server of your site. The flash plugin is not available The verbose option selects whether
			verbose descriptions
			should be displayed with the various settings
			sections. The long descriptions, of which 
			this paragraph is an example,
			will not be shown if the option is not
			selected. These options enable or completely disable
			placing video in posts or widgets. If the placement
			of video must be switched on or off, for either
			posts (and pages) or widgets
			or both, these are the options to use.
			</p><p>
			When the plugin shortcode is disabled the flash
			video player that would have been displayed is
			replaced by a notice with the form
			"[A/V content &lt;caption&gt; disabled],"
			where "&lt;caption&gt;"
			is any caption that was included with the shortcode,
			or empty if there was no caption.
			</p><p>
			Note that in the two following sections,
			"Video In Posts" and "Video In Widget Areas,"
			the options are effective only if enabled here. These options select 
			how flash video (or audio) may be placed in posts or pages.
			Use shortcodes for any new posts (and preferably
			for existing posts) that should include
			the flash media player of this plugin.
			Shortcodes are an efficient method provided by the
			<em>WordPress</em> API. When shortcodes are enabled,
			a form for parameters will appear in the post (and page)
			editing pages (probably near the bottom of the page,
			but it can be dragged nearer to the editor).
			</p><p>
			The "Search attachment"
			option might help with some existing posts if
			you already have attached media (i.e., the posts contain
			attachment_id=<em>N</em> links).
			The attachment number is used to find the associated
			URL, and if the filename extension suggests that the
			medium is a suitable type, the flash player code
			is put in line with the URL; the original attachment_id
			URL is placed after the flash player.
			Use of this option is discouraged
			because it requires additional processing of each
			line of each post (or page) displayed,
			and so it increases server load. User parameters
			are not available for this method. These options select 
			how flash video (or audio) may be placed in widget areas.
			The first option selects use of the included widget.
			This widget is configured in the
			Appearance-&gt;Widgets page, just
			like the widgets included with <em>WordPress</em>, and
			the widget setup interface
			includes a form to set parameters.
			</p><p>
			The second option "shortcodes in widgets"
			selects shortcode processing in other widget output, as for
			posts. This is probably only useful with the
			<em>WordPress</em> Text widget or a similar widget. These
			shortcodes must be entered by hand, and therefore this
			option requires a knowledge of the shortcode and
			parameters used by this plugin.
			(If necessary, a temporary shortcode
			can be made within a post using the provided form, and
			then cut and
			pasted into the widget text, on a line of its own.) This section includes optional
			features for plugin install or uninstall. Presently
			the only option is whether to remove the plugin's
			set of options from the database when
			the plugin is deleted.
			There is probably no reason to leave the options in
			place if you intend to delete the plugin permanently;
			you may simply deactivate the plugin if
			you want it off temporarily.
			If you intend to delete and then reinstall the plugin,
			possibly for a new version or update, then keeping the
			options might be helpful. Tips URLs for alternate HTML5 video (optional: .mp4, .webm, .ogv): Url from uploads directory: Url of initial image file (optional): Url or media library ID: Use SWF script if PHP+Ming is available Use initial image as non-flash alternate: Use shortcodes in posts: Use shortcodes in widgets: Use the included widget: Video In Posts Video In Widget Areas Video Placement Options WRITE TIPS When the plugin is uninstalled: Widget title: Width (default %u): none 