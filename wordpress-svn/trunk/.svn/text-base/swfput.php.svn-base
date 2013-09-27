<?php
/*
Plugin Name: SWFPut
Plugin URI: http://agalena.nfshost.com/b1/?page_id=46
Description: Add Shockwave Flash video to WordPress posts and widgets, from arbitrary URI's or media library ID's or files in your media upload directory tree (even if not added by WordPress and assigned an ID).
Version: 1.0.4
Author: Ed Hynan
Author URI: http://agalena.nfshost.com/b1/
License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
Text Domain: swfput_l10n
*/

/*
 *      swfput.php
 *      
 *      Copyright 2011 Ed Hynan <edhynan@gmail.com>
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


/**********************************************************************\
 *  requirements                                                      *
\**********************************************************************/


// supporting classes found in files named "${cl}.inc.php"
// each class must define static method id_token() which returns
// the correct int, to help avoid name clashes
if ( ! function_exists( 'swfput_paranoid_require_class' ) ) :
function swfput_paranoid_require_class ($cl)
{
	$id = 0xED00AA33;
	$meth = 'id_token';
	if ( ! class_exists($cl) ) {
		$d = plugin_dir_path(__FILE__).'/'.$cl.'.inc.php';
		require_once $d;
	}
	if ( method_exists($cl, $meth) ) {
		$t = call_user_func(array($cl, $meth));
		if ( $t !== $id ) {
			wp_die('class name conflict: ' . $cl . ' !== ' . $id);
		}
	} else {
		wp_die('class name conflict: ' . $cl);
	}
}
endif;

// these support classes are in separate files as they are
// not specific to this plugin, and may be used in others
swfput_paranoid_require_class('OptField_0_0_2b');
swfput_paranoid_require_class('OptSection_0_0_2b');
swfput_paranoid_require_class('OptPage_0_0_2b');
swfput_paranoid_require_class('Options_0_0_2b');


/**********************************************************************\
 *  development, workaround functions                                 *
\**********************************************************************/

/**
 * Only until PHP 5.2 compat is abandoned:
 * a non-class method that can be aliased (by string)
 * to a $var; 5.2 *cannot* call class methods, static or
 * not, through any alias
 */
if ( ! function_exists( 'swfput_php52_htmlent' ) ) :
function swfput_php52_htmlent ($text, $cset = null)
{
	// try to use get_option('blog_charset') only once;
	// it's not cheap enough even with WP's cache for
	// the number of times this might be called
	static $_blog_charset = null;
	if ( $_blog_charset === null ) {
		$_blog_charset = get_option('blog_charset');
		if ( ! $_blog_charset ) {
			$_blog_charset = 'UTF-8';
		}
	}

	if ( $cset === null ) {
		$cset = $_blog_charset;
	}

	return htmlentities($text, ENT_QUOTES, $cset);
}
endif;


/**********************************************************************\
 *  Class defs: main plugin. widget, and support classes              *
\**********************************************************************/


/**
 * class providing flash video for WP pages
 */
if ( ! class_exists('SWF_put_evh') ) :
class SWF_put_evh {
	// web page as of release
	const plugin_webpage = 'http://agalena.nfshost.com/b1/?page_id=46';
	
	// the widget class name
	const swfput_widget = 'SWF_put_widget_evh';
	// parameter helper class name
	const swfput_params = 'SWF_params_evh';
	
	// identifier for settings page
	const settings_page_id = 'swfput1_settings_page';
	
	// option group name in the WP opt db
	const opt_group  = '_evh_swfput1_opt_grp';
	// verbose (helpful?) section descriptions?
	const optverbose = '_evh_swfput1_verbose';
	// WP option names/keys
	// optdisp... -- display areas
	const optdispmsg = '_evh_swfput1_dmsg'; // posts
	const optdispwdg = '_evh_swfput1_dwdg'; // widgets no-admin
	const optdisphdr = '_evh_swfput1_dhdr'; // header area
	// optcode... -- shortcode processing
	const optcodemsg = '_evh_swfput1_scms'; // posts
	const optcodewdg = '_evh_swfput1_scwi'; // widgets no-admin
	// optpreg... -- grepping for attachment id to resolve
	const optpregmsg = '_evh_swfput1_sedm'; // posts sed
	// optplugwdg -- use plugin's widget
	const optplugwdg = '_evh_swfput1_pwdg'; // plugin widget
	// delete options on uninstall
	const optdelopts = '_evh_swfput1_delopts';
	// use php+ming script if available?
	const optuseming = '_evh_swfput1_useming';

	// verbose (helpful?) section descriptions?
	const defverbose = 'true';
	// display opts, widget, inline or both
	 // 1==message | 2==widget | 4==header
	const defdisplay  = 7;
	const disp_msg    = 1;
	const disp_widget = 2;
	const disp_hdr    = 4;
	// more
	const defcodemsg = 'true';  // posts
	const defcodewdg = 'false'; // widgets no-admin
	const defpregmsg = 'false'; // posts sed
	// optplugwdg -- use plugin's widget
	const defplugwdg = 'true';  // plugin widget
	// delete options on uninstall
	const defdelopts = 'true';
	// use php+ming script if available?
	const defuseming = 'false';
	
	// autoload class version suffix
	const aclv = '0_0_2b';

	// string used for (default) shortcode tag
	const shortcode = 'putswf_video';

	// object of class to handle options under WordPress
	protected $opt = null;

	// swfput program directory
	const swfputdir = 'mingput';
	// swfput program binary name
	const swfputbinname = 'mingput.swf';
	// swfput program php+ming script name
	const swfputphpname = 'mingput.php';
	// swfput program css name
	const swfputcssname = 'obj.css';
	// swfput default video name
	const swfputdefvid = 'default.flv';
	// swfput program binary path
	protected $swfputbin;
	// swfput program php+ming script path
	protected $swfputphp;
	// swfput program css path
	protected $swfputcss;
	// swfput program default video path
	protected $swfputvid;

	// settings js subdirectory
	const settings_jsdir = 'js';
	// settings js shortcode editor helper name
	const settings_jsname = 'screens.js';
	// settings program js path
	protected $settings_js;
	// JS: name of class to control textare/button pairs
	const js_textpair_ctl = 'evhplg_ctl_textpair';

	// for a link to an html help doc
	const helphtmlname = 'README.html';
	const helphtml_ref = '#3.1. Form Buttons';
	protected static $helphtml = null;
	// for a link to an pdf help doc
	const helppdfname = 'README.pdf';
	protected static $helppdf = null;

	// swfput js shortcode editor helper name
	const swfxedjsname = 'formxed.js';
	
	// hold an instance
	private static $instance;

	// int, incr while wrapping WP do_shortcode(), decr when done
	private $in_wdg_do_shortcode;

	// this instance is fully initialized? (__construct($init == true))
	private $full_init;

	// correct file path (possibly needed due to symlinks)
	public static $pluginfile = null;

	public function __construct($init = true) {
		// admin or public invocation?
		$adm = is_admin();

		// if arg $init is false then this instance is just
		// meant to provide options and such
		$pf = self::mk_pluginfile();
		// URL setup
		$t = self::swfputdir . '/' . self::swfputbinname;
		$this->swfputbin = plugins_url($t, $pf);
		$t = self::swfputdir . '/' . self::swfputphpname;
		$this->swfputphp = plugins_url($t, $pf);
		$t = self::swfputdir . '/' . self::swfputcssname;
		$this->swfputcss = plugins_url($t, $pf);
		$t = self::swfputdir . '/' . self::swfputdefvid;
		$this->swfputvid = plugins_url($t, $pf);
		$t = self::settings_jsdir . '/' . self::settings_jsname;
		$this->settings_js = plugins_url($t, $pf);
		// these are used in static methods, so are static members
		if ( self::$helphtml === null ) {
			$t = self::helphtmlname . self::helphtml_ref;
			self::$helphtml = plugins_url($t, $pf);
		}
		if ( self::$helppdf === null ) {
			$t = self::helppdfname;
			self::$helppdf = plugins_url($t, $pf);
		}

		$this->in_wdg_do_shortcode = 0;
		
		if ( ($this->full_init = $init) !== true ) {
			// must do this
			$this->init_opts();
			return;
		}
		
		$cl = __CLASS__;

		if ( $adm ) {
			// add 'Settings' link on the plugins page entry
			// cannot be in activate hook
			$name = plugin_basename($pf);
			add_filter("plugin_action_links_$name",
				array($cl, 'plugin_page_addlink'));
		}

		// some things are to be done in init hook: add
		// hooks for shortcode and widget, and optionally
		// posts processing to scan attachments, etc...
		add_action('init', array($this, 'init_hook_func'));

		// it's not enough to add this action in the activation hook;
		// that alone does not work.  IAC administrative
		// {de,}activate also controls the widget
		add_action('widgets_init', array($cl, 'regi_widget'));//, 1);
	}

	public function __destruct() {
		$this->opt = null;
	}
	
	// get array of defaults for the plugin options; if '$chkonly'
	// is true include only those options associated with a checkbox
	// on the settings page -- useful for the validate function
	protected static function get_opts_defaults($chkonly = false) {
		$items = array(
			self::optverbose => self::defverbose,
			self::optdispmsg =>
				(self::defdisplay & self::disp_msg) ? 'true' : 'false',
			self::optdispwdg =>
				(self::defdisplay & self::disp_widget) ? 'true' : 'false',
			self::optdisphdr =>
				(self::defdisplay & self::disp_hdr) ? 'true' : 'false',
			self::optcodemsg => self::defcodemsg,
			self::optcodewdg => self::defcodewdg,
			self::optpregmsg => self::defpregmsg,
			self::optplugwdg => self::defplugwdg,
			self::optdelopts => self::defdelopts,
			self::optuseming => self::defuseming
		);
		
		if ( $chkonly !== true ) {
			// TODO: so far there are only checkboxes
		}
		
		return $items;
	}
	
	// initialize plugin options from defaults or WPDB
	protected function init_opts() {
		$items = self::get_opts_defaults();
		$opts = self::get_opt_group();
		// note values converted to string
		if ( $opts ) {
			$mod = false;
			foreach ($items as $k => $v) {
				if ( ! array_key_exists($k, $opts) ) {
					$opts[$k] = '' . $v;
					$mod = true;
				}
				if ( $opts[$k] == '' && $v !== '' ) {
					$opts[$k] = '' . $v;
					$mod = true;
				}
			}
			if ( $mod === true ) {
				update_option(self::opt_group, $opts);
			}
		} else {
			$opts = array();
			foreach ($items as $k => $v) {
				$opts[$k] = '' . $v;
			}
			add_option(self::opt_group, $opts);
		}
		return $opts;
	}

	// initialize options/settings page
	protected function init_settings_page() {
		if ( $this->opt ) {
			return;
		}
		$items = self::get_opt_group();

		// use Opt* classes for page, sections, and fields
		
		// mk_aclv adds a suffix to class names
		$Cf = self::mk_aclv('OptField');
		$Cs = self::mk_aclv('OptSection');
		// prepare fields to appear under various sections
		// of admin page
		$ns = 0;
		$sections = array();

		// General options section
		$nf = 0;
		$fields = array();
		$fields[$nf++] = new $Cf(self::optverbose,
				self::wt(__('Show verbose introductions:', 'swfput_l10n')),
				self::optverbose,
				$items[self::optverbose],
				array($this, 'put_verbose_opt'));
		// this field is not printed if ming is n.a.
		if ( self::can_use_ming() ) {
			$fields[$nf++] = new $Cf(self::optuseming,
					self::wt(__('Dynamic SWF generation:', 'swfput_l10n')),
					self::optuseming,
					$items[self::optuseming],
					array($this, 'put_useming_opt'));
		}

		// section object includes description callback
		$sections[$ns++] = new $Cs($fields,
				'swfput1_general_section',
				'<a name="general">' .
					self::wt(__('General Options', 'swfput_l10n'))
					. '</a>',
				array($this, 'put_general_desc'));

		// placement section: (posts, sidebar, header)
		$nf = 0;
		$fields = array();
		$fields[$nf++] = new $Cf(self::optdispmsg,
				self::wt(__('Place in posts:', 'swfput_l10n')),
				self::optdispmsg,
				$items[self::optdispmsg],
				array($this, 'put_inposts_opt'));
		$fields[$nf++] = new $Cf(self::optdispwdg,
				self::wt(__('Place in widget areas:', 'swfput_l10n')),
				self::optdispwdg,
				$items[self::optdispwdg],
				array($this, 'put_widget_opt'));
		// commented: from early false assumption that header
		// could be easily hooked:
		//$fields[$nf++] = new $Cf(self::optdisphdr,
				//self::wt(__('Place in header area:', 'swfput_l10n')),
				//self::optdisphdr,
				//$items[self::optdisphdr],
				//array($this, 'put_inhead_opt'));

		// section object includes description callback
		$sections[$ns++] = new $Cs($fields,
				'swfput1_placement_section',
				'<a name="placement">' .
					self::wt(__('Video Placement Options', 'swfput_l10n'))
					. '</a>',
				array($this, 'put_place_desc'));
		
		// options for posts
		$nf = 0;
		$fields = array();
		$fields[$nf++] = new $Cf(self::optcodemsg,
				self::wt(__('Use shortcodes in posts:', 'swfput_l10n')),
				self::optcodemsg,
				$items[self::optcodemsg],
				array($this, 'put_scposts_opt'));
		$fields[$nf++] = new $Cf(self::optpregmsg,
				self::wt(__('Search attachment links in posts:', 'swfput_l10n')),
				self::optpregmsg,
				$items[self::optpregmsg],
				array($this, 'put_rxposts_opt'));

		// section object includes description callback
		$sections[$ns++] = new $Cs($fields,
				'swfput1_postsopts_section',
				'<a name="postopts">' .
					self::wt(__('Video In Posts', 'swfput_l10n'))
					. '</a>',
				array($this, 'put_postopts_desc'));
		
		// options for widget areas
		$nf = 0;
		$fields = array();
		$fields[$nf++] = new $Cf(self::optplugwdg,
				self::wt(__('Use the included widget:', 'swfput_l10n')),
				self::optplugwdg,
				$items[self::optplugwdg],
				array($this, 'put_plwdg_opt'));
		$fields[$nf++] = new $Cf(self::optcodewdg,
				self::wt(__('Use shortcodes in widgets:', 'swfput_l10n')),
				self::optcodewdg,
				$items[self::optcodewdg],
				array($this, 'put_scwdg_opt'));

		// section object includes description callback
		$sections[$ns++] = new $Cs($fields,
				'swfput1_wdgsopts_section',
				'<a name="wdgsopts">' .
					self::wt(__('Video In Widget Areas', 'swfput_l10n'))
					. '</a>',
				array($this, 'put_wdgsopts_desc'));
					
		// install opts section:
		// field: delete opts on uninstall?
		$nf = 0;
		$fields = array();
		$fields[$nf++] = new $Cf(self::optdelopts,
				self::wt(__('When the plugin is uninstalled:', 'swfput_l10n')),
				self::optdelopts,
				$items[self::optdelopts],
				array($this, 'put_del_opts'));

		// prepare sections to appear under admin page
		$sections[$ns++] = new $Cs($fields,
				'swfput1_inst_section',
				'<a name="install">' .
					self::wt(__('Plugin Install Settings', 'swfput_l10n'))
					. '</a>',
				array($this, 'put_inst_desc'));

		// prepare admin page specific hooks 
		if ( false ) {
			$suffix_hooks = array(
				'admin_head' => array($this, 'settings_head'),
				'admin_print_scripts' => array($this, 'settings_js'),
				'load' => array($this, 'admin_load')
			);
		} else {
			$suffix_hooks = array(
				'admin_head' => array($this, 'settings_head'),
				'admin_print_scripts' => array($this, 'settings_js'),
			);
		}
		
		// prepare admin page
		// Note that validator applies to all options,
		// necessitating a big switch on option keys
		$Cp = self::mk_aclv('OptPage');
		$page = new $Cp(self::opt_group, $sections,
			self::settings_page_id,
			self::wt(__('SWFPut Plugin', 'swfput_l10n')),
			self::wt(__('SWFPut Configuration', 'swfput_l10n')),
			array(__CLASS__, 'validate_opts'),
			/* pagetype = 'options' */ '',
			/* capability = 'manage_options' */ '',
			array($this, 'setting_page_output_callback')/* callback */,
			/* 'hook_suffix' callback array */ $suffix_hooks,
			self::wt(__('SWFPut Plugin Configuration', 'swfput_l10n')),
			self::wt(__('Display and Runtime Settings.', 'swfput_l10n')),
			self::wt(__('Save Settings', 'swfput_l10n')));
		
		$Co = self::mk_aclv('Options');
		$this->opt = new $Co($page);
	}
	
	// filter for wp-admin/includes/screen.php get_column_headers()
	// to set text for Screen Options column
	public function screen_options_columns($a) {
		if ( ! is_array($a) ) {
			$a = array();
		}
		// checkbox id will 'verbose_show-hide'
		$a['verbose_show'] = __('Section introductions', 'swfput_l10n');
		return $a;
	}

	// filter for wp-admin/includes/screen.php show_screen_options()
	// to return true and enable the menu, or not
	public function screen_options_show($a) {
		if ( self::get_verbose_option() == 'true' ) {
			return true;
		}
		return false;
	}

	public function settings_head() {
		// get_current_screen() introduced in WP 3.1
		// (thus spake codex)
		// I have 3.0.2 to test with, and 3.3.1, nothing in between,
		// so 3.3 will be used as minimum
		$v = (3 << 24) | (3 << 16) | (0 << 8) | 0;
		$ok = self::wpv_min($v);

		$t = array(
			self::wt(sprintf(
		// TRANSLATORS: '%1$s' is the label of a checkbox option,
		// '%2$s' is the button label 'Save Settings';
		// The quoted string "Screen Options" should match an
		// interface label from the WP core, so if possible
		// use the WP core translation for that (likewise "Help").
			__('<p>The sections of this page each have an
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
			the installed defaults, so it\'s not necessary
			to worry over the options on this page. 
			</p><p>
			Remember, when any change is made, the new settings must
			be submitted with the "%2$s" button, near the end
			of this page, to take effect.
			</p>', 'swfput_l10n'),
			__('Show verbose introductions', 'swfput_l10n'),
			__('Save Settings', 'swfput_l10n')
			)),
			self::wt(sprintf(
			__('<p>TODO: %s
			</p>', 'swfput_l10n'),
			__('WRITE TIPS', 'swfput_l10n')
			))
		);

		// TRANSLATORS: first '%s' is the the phrase
		// 'For more information:'; using translation
		// from default textdomain (WP core)
		$tt = self::wt(sprintf(
			__('<p><strong>%s</strong></p><p>
			Tips and examples can be found on the
			<a href="%s" target="_blank">web page</a>.
			</p>', 'swfput_l10n'),
			__('For more information:'),
			self::plugin_webpage
		));
	
		// finagle the "Screen Options" tab
		$h = 'manage_' . $this->opt->get_page_suffix() . '_columns';
		add_filter($h, array($this, 'screen_options_columns'));
		$h = 'screen_options_show_screen';
		add_filter($h, array($this, 'screen_options_show'), 200);

		// put help tab content, for 3.3.1 or greater . . .
		if ( $ok ) {
			$scr = get_current_screen();
			$scr->add_help_tab(array(
				'id'      => 'overview',
				'title'   => __('Overview'), // use transl. from core
				'content' => $t[0]
				// content may be a callback
				)
			);
	
			// later . . .
			if ( false )
			$scr->add_help_tab(array(
				'id'      => 'help_tab_tips',
				'title'   => __('Tips', 'swfput_l10n'),
				'content' => $t[1]
				// content may be a callback
				)
			);
	
			$scr->set_help_sidebar($tt);
		
		// . . . or, lesser
		} else {
			global $current_screen;
			add_contextual_help($current_screen,
				'<h6>' . __('Overview') . '</h6>' . $t[0] .
				'<h6>' . __('Tips', 'swfput_l10n') . '</h6>' . $t[1] .
				$tt);
		}
	}

	public function settings_js() {
		$jsfn = self::settings_jsname;
		$j = $this->settings_js;
        wp_enqueue_script($jsfn, $j);
	}

	// This function is placed here below the function that sets-up
	// the options page so that it is easy to see from that function.
	// It exists only for the echo "<a name='aSubmit'/>\n";
	// line which mindbogglingly cannot be printed from
	// Options::admin_page() -- it is somehow *always* stripped out!
	// After hours I cannot figure this out; but, having added this
	// function as the page callback, I can add the anchor after
	// calling $this->opt->admin_page() (which is Options::admin_page())
	// BUT it still does not show in the page if the echo is moved
	// into Options::admin_page() and placed just before return!
	// Baffled.
	public function setting_page_output_callback() {
		$r = $this->opt->admin_page();
		echo "<a name='aSubmit'/>\n";
		return $r;
	}

	/**
	 * General hook/filter callbacks
	 */
	
	// register shortcode editor forms for posts & pages ("meta boxes")
	public static function hook_admin_menu() {
		$cl = __CLASS__;
		$id = 'SWFPut_putswf_video';
		$tl = __('SWFPut Flash Video Shortcode', 'swfput_l10n');
		$fn = 'put_xed_form';
		if ( current_user_can('edit_posts') )
			add_meta_box($id, $tl, array($cl, $fn), 'post', 'normal');
		if ( current_user_can('edit_pages') )
			add_meta_box($id, $tl, array($cl, $fn), 'page', 'normal');
	}

	// add a help tab in the post-related pages
	public static function hook_admin_head() {
		// get_current_screen() introduced in WP 3.1
		// (thus spake codex)
		// I have 3.0.2 to test with, and 3.3.1, nothing in between,
		// so 3.3 will be used as minimum
		$v = (3 << 24) | (3 << 16) | (0 << 8) | 0;
		$ok = self::wpv_min($v);
		// no compatible alternative for now
		if ( ! $ok ) {
			return;
		}

		$scr = get_current_screen();
		if ( $scr && $scr->base === 'post'
			&& (current_user_can('edit_posts')
			||  current_user_can('edit_pages')) ) {
			$scr->add_help_tab(array(
				'id'      => 'help_tab_posts_swfput',
				'title'   => __('SWFPut Form', 'swfput_l10n'),
				'content' => self::wt(sprintf(__('<p>
				If the SWFPut shortcode form. or "metabox,"
				is not self-explanatory
				(hopefully, much of it will be), there is more
				explanation
				<a href="%s" target="_blank">here (in a new tab)</a>,
				or as a PDF
				<a href="%s" target="_blank">here (in a new tab)</a>.
				</p><p>
				There is one important restriction on the form\'s
				text entry fields. The values may not have any
				ASCII \'&quot;\' (double quote) characters. Hopefully
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
				must be separated by \'|\'. Each URL <em>may</em>
				be appended with a mime-type + codecs argument,
				separated from the URL by \'?\'. Whitespace around
				the separators is accepted and stripped-off. Please
				note that the argument given should <em>not</em>
				include "type=" or the quotes: give only the
				statement that should appear within the quotes.
				For example:</p>
				<blockquote><code>
				vids/gato.mp4?video/mp4 | vids/gato.webm ? video/webm; codecs=vp8,vorbis|vids/gato.ogv?video/ogg; codecs=\'theora, vorbis\'
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
				for the flash player in an \'img\' element
				that the visitor\'s browser should display if flash
				is not available.
				</p><p>
				If alternate HTML5 video was specified, that will
				remain the first alternate display, and the initial
				image should display if neither flash or HTML5 video
				are available.
				</p><p>
				There is one important consideration for this image:
				the \'img\' element is given the width and height
				specified in the form for the flash player, and the
				visitor\'s browser will scale the image in both
				dimensions, possibly causing the image to be
				\'stretched\' or \'squeezed\'. (That is not a problem
				in the flash player, as it is coded to display the
				initial image proportionally.) Therefore, it is a
				good idea to prepare images to have the expected
				<em>pixel</em> aspect ratio
				(top/bottom or left/right tranparent
				areas might be one solution).
				</p>
				', 'swfput_l10n'), self::$helphtml, self::$helppdf))
				// content may be a callback
				)
			);
		}
	}

	// register shortcode editor forms javascript
	public static function filter_admin_print_scripts() {
		// cap check: not sure if this is necessary here,
		// hope it doesn't cause failures for legit users
	    if ( $GLOBALS['editing']
			&& (current_user_can('edit_posts')
			||  current_user_can('edit_pages')) ) {
			$jsfn = 'SWFPut_putswf_video_xed';
			$pf = self::mk_pluginfile();
			$t = self::settings_jsdir . '/' . self::swfxedjsname;
			$jsfile = plugins_url($t, $pf);
	        wp_enqueue_script($jsfn, $jsfile, array('jquery'), 'xed');
	    }
	}

	// deactivate cleanup
	public static function on_deactivate() {
		$wreg = __CLASS__;
		$name = plugin_basename(self::mk_pluginfile());
		$arf = array($wreg, 'plugin_page_addlink');
		remove_filter("plugin_action_links_$name", $arf);

		self::unregi_widget();

		unregister_setting(self::opt_group, // option group
			self::opt_group, // opt name; using group passes all to cb
			array($wreg, 'validate_opts'));
	}

	// activate setup
	public static function on_activate() {
		$wreg = __CLASS__;
		add_action('widgets_init', array($wreg, 'regi_widget'), 1);
	}

	// uninstall cleanup
	public static function on_uninstall() {
		self::unregi_widget();
		
		$opts = self::get_opt_group();
		if ( $opts && $opts[self::optdelopts] != 'false' ) {
			delete_option(self::opt_group);
		}
	}

	// add link at plugins page entry for the settings page
	public static function plugin_page_addlink($links) {
		$opturl = '<a href="' . get_option('siteurl');
		$opturl .= '/wp-admin/options-general.php?page=';
		$opturl .= self::settings_page_id;
		$opturl .= '">' . __('Settings', 'swfput_l10n') . '</a>';
		// Add a link to this plugin's settings page
		array_unshift($links, $opturl); 
		return $links; 
	}

	// register the SWFPut widget
	public static function regi_widget ($fargs = array()) {
		global $wp_widget_factory;
		if ( ! isset($wp_widget_factory) ) {
			return;
		}
		if ( self::get_widget_option() == 'false' ) {
			return;
		}
		if ( function_exists('register_widget') ) {
			$cl = self::swfput_widget;
			register_widget($cl);
		}
	}

	// unregister the SWFPut widget
	public static function unregi_widget () {
		global $wp_widget_factory;
		if ( ! isset($wp_widget_factory) ) {
			return;
		}
		if ( function_exists('unregister_widget') ) {
			$cl = self::swfput_widget;
			unregister_widget($cl);
		}
	}

	// the 'init' hook callback
	public function init_hook_func () {
		self::load_translations();
		$this->init_opts();

		$pf = self::mk_pluginfile();
		// admin or public invocation?
		$adm = is_admin();

		$cl = __CLASS__;

		if ( $adm ) {
		// keep it clean: {de,}activation
			if ( current_user_can('activate_plugins') ) {
				$aa = array($cl, 'on_deactivate');
				register_deactivation_hook($pf, $aa);
				$aa = array($cl, 'on_activate');
				register_activation_hook($pf,   $aa);
			}
			if ( current_user_can('install_plugins') ) {
				$aa = array($cl, 'on_uninstall');
				register_uninstall_hook($pf,    $aa);
			}

			// hook&filter to make shortcode form for editor
			if ( self::get_posts_code_option() === 'true' ) {
				$aa = array($cl, 'hook_admin_head');
				add_action('admin_head', $aa);
				$aa = array($cl, 'hook_admin_menu');
				add_action('admin_menu', $aa);
				$aa = array($cl, 'filter_admin_print_scripts');
				add_action('admin_print_scripts', $aa);
			}

			// Settings/Options page setup
			if ( current_user_can('manage_options') ) {
				$this->init_settings_page();
			}
		} // if ( $adm )

		$aa = array($this, 'post_shortcode');
		add_shortcode(self::shortcode, $aa);

		$aa = array($this, 'wdg_do_shortcode');
		add_filter('widget_text', $aa);

		$aa = array($this, 'post_sed');
		if ( self::get_posts_preg_option() === 'true' ) {
			add_filter('the_content', $aa, 20);
		} else {
			remove_filter('the_content', $aa);
		}
	}

	public static function load_translations () {
		// The several load*() calls here are inspired by this:
		//   http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
		// So, provide for custom *.mo installed in either
		// WP_LANG_DIR or WP_PLUGIN_DIR/languages or WP_PLUGIN_DIR,
		// and do translations in the plugin directory last.
		
		// hack test whether .mo load call has been done
		static $WP_textdomain_done;
		static $plugin_langdir_textdomain_done;
		static $plugin_dir_textdomain_done;
		static $plugin_textdomain_done;

		$dom = 'swfput_l10n';

		if ( ! isset($WP_textdomain_done)
			&& defined(WP_LANG_DIR) ) {
			$loc = apply_filters('plugin_locale', get_locale(), $dom);
			// this file path is built in the manner shown at the
			// URL above -- it does look strange
			$t = sprintf('%s/%s/%s-%s.mo',
				WP_LANG_DIR, $dom, $dom, $loc);
			$WP_textdomain_done = load_textdomain($dom, $t);
		}
		if ( ! isset($plugin_langdir_textdomain_done) ) {
			$t = 'languages/';
			$plugin_langdir_textdomain_done =
				load_plugin_textdomain($dom, false, $t);
		}
		if ( ! isset($plugin_dir_textdomain_done) ) {
			$plugin_dir_textdomain_done =
				load_plugin_textdomain($dom, false, false);
		}
		if ( ! isset($plugin_textdomain_done) ) {
			$t = basename(trim(self::mk_plugindir(), '/')) . '/locale/';
			$plugin_textdomain_done =
				load_plugin_textdomain($dom, false, $t);
		}
	}

	/**
	 * Settings page callback functions:
	 * validators, sections, fields, and page
	 */

	// static callback: validate options main
	public static function validate_opts($opts) {	
		$a_out = array();
		$a_orig = self::get_opt_group();
		$nerr = 0;
		$nupd = 0;

		// empty happens if all fields are checkboxes and none checked
		if ( empty($opts) ) {
			$opts = array();
		}
		// checkboxes need value set - nonexistant means false
		$ta = self::get_opts_defaults();
		foreach ( $ta as $k => $v ) {
			if ( array_key_exists($k, $opts) ) {
				continue;
			}
			$opts[$k] = 'false';
		}
		// remainder of controls
		$ta = self::get_opts_defaults(false); // gets all
		foreach ( $ta as $k => $v ) {
			if ( array_key_exists($k, $opts) ) {
				continue;
			}
			$opts[$k] = $v;
		}
	
		foreach ( $opts as $k => $v ) {
			if ( ! array_key_exists($k, $a_orig) ) {
				// this happens for the IDs of extra form items
				// in use, if not associated with an option
				continue;
			}
			$ot = trim($v);
			$oo = $a_orig[$k];

			switch ( $k ) {
				case self::optverbose:
				case self::optdispmsg:
				case self::optdispwdg:
				case self::optdisphdr:
				case self::optcodemsg:
				case self::optcodewdg:
				case self::optpregmsg:
				case self::optplugwdg:
				case self::optdelopts:
				case self::optuseming:
					if ( $ot != 'true' && $ot != 'false' ) {
						$e = sprintf('bad option: %s[%s]', $k, $v);
						self::errlog($e);
						add_settings_error(self::wt($k),
							sprintf('%s[%s]', self::opt_group, $k),
							self::wt($e), 'error');
						$a_out[$k] = $oo;
						$nerr++;
					} else {
						$a_out[$k] = $ot;
						$nupd += ($oo === $ot) ? 0 : 1;
					}
					break;
				default:
					$e = "funny key in validate opts: '" . $k . "'";
					self::errlog($e);
					add_settings_error(self::wt($k),
						sprintf('%s[%s]',
							self::opt_group, self::ht($k)),
						self::wt($e), 'error');
					$nerr++;
			}
		}

		// now register updates
		if ( $nupd > 0 ) {
			$str = $nerr == 0 ?
				__('Settings updated successfully', 'swfput_l10n') :
				sprintf(_n('One (%d) setting updated',
					'Some settings (%d) updated',
					$nupd, 'swfput_l10n'), $nupd);
			$type = $nerr == 0 ? 'updated' : 'updated error';
			add_settings_error(self::opt_group, self::opt_group,
				self::wt($str), $type);
		}
		
		return $a_out;
	}

	//
	// section callbacks
	//
	
	// callback: put html for placement field description
	public function put_general_desc() {
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$did = 'SWFPut_General_Desc';
		echo '<div id="' . $did . '">';

		$t = self::wt(__('Introduction:', 'swfput_l10n'));
		printf('<p><strong>%s</strong>%s</p>', $t, "\n");

		$t = self::wt(__('The verbose option selects whether
			verbose descriptions
			should be displayed with the various settings
			sections. The long descriptions, of which 
			this paragraph is an example,
			will not be shown if the option is not
			selected.', 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");

		if ( self::can_use_ming() ) {
			$t = self::wt(__('The PHP+Ming option selects whether
				the Flash player program is generated with PHP
				and the Ming extension for each request.
				When this option is not selected, then
				a compiled binary player is used.
				This option is only displayed if the Ming
				PHP extension is installed and loaded; if you
				are reading this then Ming has been found to
				be loaded.
				Note that this option will increase the load on the
				server of your site.', 'swfput_l10n'));
			printf('<p>%s</p>%s', $t, "\n");
		}

		echo '</div>';
		?>
		<script type="text/javascript">
		addto_evhplg_obj_screenopt("verbose_show-hide", "<?php echo $did ?>");
		</script>
		<?php

		$t = self::wt(__('Go forward to save button.', 'swfput_l10n'));
		printf('<p><a href="#aSubmit">%s</a></p>%s', $t, "\n");
	}

	// callback: put html for placement field description
	public function put_place_desc() {
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$did = 'SWFPut_Placement_Desc';
		echo '<div id="' . $did . '">';

		$t = self::wt(__('Introduction:', 'swfput_l10n'));
		printf('<p><strong>%s</strong>%s</p>', $t, "\n");
		$t = self::wt(__('These options enable or completely disable
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
			the options are effective only if enabled here.'
			, 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");

		echo '</div>';
		?>
		<script type="text/javascript">
		addto_evhplg_obj_screenopt("verbose_show-hide", "<?php echo $did ?>");
		</script>
		<?php

		$t = self::wt(__('Go forward to save button.', 'swfput_l10n'));
		printf('<p><a href="#aSubmit">%s</a></p>%s', $t, "\n");
		$t = self::wt(__('Go back to top (General section).', 'swfput_l10n'));
		printf('<p><a href="#general">%s</a></p>%s', $t, "\n");
	}

	// callback: put html for posts options
	public function put_postopts_desc() {
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$did = 'SWFPut_PostOpts_Desc';
		echo '<div id="' . $did . '">';

		$t = self::wt(__('Introduction:', 'swfput_l10n'));
		printf('<p><strong>%s</strong>%s</p>', $t, "\n");

		$t = self::wt(__('These options select 
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
			are not available for this method.', 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");

		echo '</div>';
		?>
		<script type="text/javascript">
		addto_evhplg_obj_screenopt("verbose_show-hide", "<?php echo $did ?>");
		</script>
		<?php

		$t = self::wt(__('Go forward to save button.', 'swfput_l10n'));
		printf('<p><a href="#aSubmit">%s</a></p>%s', $t, "\n");
		$t = self::wt(__('Go back to top (General section).', 'swfput_l10n'));
		printf('<p><a href="#general">%s</a></p>%s', $t, "\n");
	}

	// callback: put html for widget area options
	public function put_wdgsopts_desc() {
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$did = 'SWFPut_WidgetAreaOpts_Desc';
		echo '<div id="' . $did . '">';

		$t = self::wt(__('Introduction:', 'swfput_l10n'));
		printf('<p><strong>%s</strong>%s</p>', $t, "\n");

		$t = self::wt(__('These options select 
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
			pasted into the widget text, on a line of its own.)'
			, 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");

		echo '</div>';
		?>
		<script type="text/javascript">
		addto_evhplg_obj_screenopt("verbose_show-hide", "<?php echo $did ?>");
		</script>
		<?php

		$t = self::wt(__('Go forward to save button.', 'swfput_l10n'));
		printf('<p><a href="#aSubmit">%s</a></p>%s', $t, "\n");
		$t = self::wt(__('Go back to top (General section).', 'swfput_l10n'));
		printf('<p><a href="#general">%s</a></p>%s', $t, "\n");
	}

	// callback: put html install field description
	public function put_inst_desc() {
		$t = self::wt(__('Install options:', 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$did = 'SWFPut_InstallOpts_Desc';
		echo '<div id="' . $did . '">';

		$t = self::wt(__('Introduction:', 'swfput_l10n'));
		printf('<p><strong>%s</strong>%s</p>', $t, "\n");

		$t = self::wt(__('This section includes optional
			features for plugin install or uninstall. Presently
			the only option is whether to remove the plugin\'s
			set of options from the database when
			the plugin is deleted.
			There is probably no reason to leave the options in
			place if you intend to delete the plugin permanently;
			you may simply deactivate the plugin if
			you want it off temporarily.
			If you intend to delete and then reinstall the plugin,
			possibly for a new version or update, then keeping the
			options might be helpful.', 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");

		echo '</div>';
		?>
		<script type="text/javascript">
		addto_evhplg_obj_screenopt("verbose_show-hide", "<?php echo $did ?>");
		</script>
		<?php

		$t = self::wt(__('Go forward to save button.', 'swfput_l10n'));
		printf('<p><a href="#aSubmit">%s</a></p>%s', $t, "\n");
		$t = self::wt(__('Go back to top (General section).', 'swfput_l10n'));
		printf('<p><a href="#general">%s</a></p>%s', $t, "\n");
	}
	
	//
	// Options page fields callbacks
	//
	
	// callback helper, put single checkbox
	public function put_single_checkbox($a, $opt, $label) {
		$group = self::opt_group;
		$c = $a[$opt] == 'true' ? "checked='CHECKED' " : "";

		//echo "\n		<!-- {$opt} checkbox-->\n";

		echo "		<label><input type='checkbox' id='{$opt}' ";
		echo "name='{$group}[{$opt}]' value='true' {$c}/> ";
		echo "{$label}</label><br />\n";
	}

	// callback, put verbose section descriptions?
	public function put_verbose_opt($a) {
		$tt = self::wt(__('Show verbose introductions', 'swfput_l10n'));
		$k = self::optverbose;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, dynamic use of php+ming?
	public function put_useming_opt($a) {
		$tt = self::wt(__('Use SWF script if PHP+Ming is available', 'swfput_l10n'));
		$k = self::optuseming;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// commented: from early false assumption that header
	// could be easily hooked:
	// callback, put SWF in head?
	//public function put_inhead_opt($a) {
		//$tt = self::wt(__('Enable SWF in head', 'swfput_l10n'));
		//$k = self::optdisphdr;
		//$this->put_single_checkbox($a, $k, $tt);
	//}

	// callback, put SWF in sidebar (widget)?
	public function put_widget_opt($a) {
		$tt = self::wt(__('Enable widget or shortcode', 'swfput_l10n'));
		$k = self::optdispwdg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, put SWF in posts?
	public function put_inposts_opt($a) {
		$tt = self::wt(__('Enable shortcode or attachment search', 'swfput_l10n'));
		$k = self::optdispmsg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, use shortcodes in posts?
	public function put_rxposts_opt($a) {
		$tt = self::wt(__('Search attachments in posts', 'swfput_l10n'));
		$k = self::optpregmsg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, use plugin's widget?
	public function put_plwdg_opt($a) {
		$tt = self::wt(__('Enable the included widget', 'swfput_l10n'));
		$k = self::optplugwdg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, use plugin's widget?
	public function put_scwdg_opt($a) {
		$tt = self::wt(__('Enable shortcodes in widgets', 'swfput_l10n'));
		$k = self::optcodewdg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, sed attachments in posts?
	public function put_scposts_opt($a) {
		$tt = self::wt(__('Enable shortcode in posts', 'swfput_l10n'));
		$k = self::optcodemsg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, install section field: opt delete
	public function put_del_opts($a) {
		$tt = self::wt(__('Permanently delete settings (clean db)', 'swfput_l10n'));
		$k = self::optdelopts;
		$this->put_single_checkbox($a, $k, $tt);
	}

	/**
	 * procedures to place and/or edit pages and content
	 */

	// put form that with some js will help with shortcodes in
	// the WP post editor: following example at:
	// http://bluedogwebservices.com/wordpress-25-shortcodes/
	public static function put_xed_form() {
		// cap check is done at registration of this callback
		$pr = self::swfput_params;
		$pr = new $pr();
		extract($pr->getparams());

		$sc = 'putswf_video';
		// file select by ext pattern
		$mpat = self::get_mfilter_pat();
		// files array from uploads dirs (empty if none)
		$rhu = self::r_find_uploads($mpat['m'], true);
		$af = &$rhu['rf'];
		$au = &$rhu['wu'];
		$aa = &$rhu['at'];
		// url base for upload dirs files
		$ub = rtrim($au['baseurl'], '/') . '/';
		// directory base for upload dirs files
		$up = rtrim($au['basedir'], '/') . '/';
		// id base for form and js
		$id = 'SWFPut_putswf_video';
		// label format string
		$lbfmt = '<label for="%s_%s">%s</label>';
		// table <input type="text"> format string
		$infmt = '<input type="text" size="40" style="width:%u%%;" name="%sX%sX" id="%s_%s" value="%s" />';
		// table <input type="checkbox"> format string
		$ckfmt = '<input type="checkbox" name="%sX%sX" id="%s_%s" value="%s" %s/>';
		// js function object
		$job = $id . '_inst';
		// form buttons format string
		$bjfmt = '<input type="button" onclick="return %s.%s;" value="%s" />';
		// form <select > format string
		$slfmt = '<select name="%sX%sX" id="%s_%s" style="width:%u%%;" onchange="return %s.%s;">' . "\n";
		// form <select > <optgroup > format string
		$sgfmt = '<optgroup label="%s">' . "\n";
		// form <select > <option > format string
		$sofmt = '<option value="%s">%s</option>' . "\n";
		// js send form values to editor method
		$jfu = "send_xed(this.form,'{$id}','caption','{$sc}')";
		// js reset form to defaults method
		$jfur = "reset_fm(this.form,'{$id}')";
		// js fill form from editor if possible
		$jfuf = "from_xed(this.form,'{$id}','caption','{$sc}')";
		// js replace last found shortcode in editor
		$jfuc = "repl_xed(this.form,'{$id}','caption','{$sc}')";
		// js delete last found shortcode from editor
		$jfud = "rmsc_xed(this.form,'{$id}','caption','{$sc}')";
		// js to copy from select/dropdown to text input
		$jfsl = "form_cpval(this.form,'%s','%s','%s')";
		// input text widths, wide, narrow
		$iw = 100; $in = 16;
		// incr var for sliding divs
		$ndiv = 0;
		// button format for sliding divs
		$dbfmt = '<input type="button" id="%s" value="%s" onclick="%s.%s" />';
		// button values for sliding divs
		$dbvhi = self::wt(__('Hide', 'swfput_l10n'));
		$dbvsh = self::wt(__('Show', 'swfput_l10n'));
		// js to show/hide sliding divs
		$jdsh = "hideshow('%s', this.id, '{$dbvhi}', '{$dbvsh}')";
		// class and base of id for sliding divs
		$dvio = $id . '_odiv';
		$dvii = $id . '_idiv';
		
		// begin form
		?>
		<!-- form buttons, it seems these *must* be in a table? -->
		<table id="<?php echo $id . '_buttons'; ?>"><tr><td>
			<span  class="submit">
			<?php
				$l = self::wt(__('Fill form from editor', 'swfput_l10n'));
				printf($bjfmt, $job, $jfuf, $l);
				$l = self::wt(__('Replace current in editor', 'swfput_l10n'));
				printf($bjfmt, $job, $jfuc, $l);
				$l = self::wt(__('Delete current in editor', 'swfput_l10n'));
				printf($bjfmt, $job, $jfud, $l);
				$l = self::wt(__('Place new in editor', 'swfput_l10n'));
				printf($bjfmt, $job, $jfu, $l);
				$l = self::wt(__('Reset default values', 'swfput_l10n'));
				printf($bjfmt, $job, $jfur, $l);
			?>
			</span>
		</td></tr></table>

		<?php $ndiv++;
			$dvon = $dvio . $ndiv;
			$dvin = $dvii . $ndiv;
			$dvib = $dvin . '_btn';
			$jdft = sprintf($jdsh, $dvin);
		?>
		<div class="<?php echo $dvio; ?>" id="<?php echo $dvon; ?>">
		<span class="submit">
			<?php printf($dbfmt, $dvib, $dbvhi, $job, $jdft); ?>
		</span>
		<h3 class="hndle"><span><?php
			echo self::wt(__('Media', 'swfput_l10n')); ?></span></h3>
		<div class="<?php echo $dvii; ?>" id="<?php echo $dvin; ?>">

		<p>
		<?php $k = 'caption';
			$l = self::wt(__('Caption:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
		</p><p>
		<?php $k = 'url';
			$l = self::wt(__('Url or media library ID:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
		</p>
		<?php
			// if there are upload files, print <select >
			$kl = $k;
			if ( count($af) > 0 ) {
				echo "<p>\n";
				$k = 'files';
				$jfcp = sprintf($jfsl, $id, $k, $kl);
				$l = self::wt(__('Url from uploads directory:', 'swfput_l10n'));
				printf($lbfmt, $id, $k, $l);
				// <select>
				printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
				// <options>
				printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
				foreach ( $af as $d => $e ) {
					$hit = array();
					for ( $i = 0; $i < count($e); $i++ )
						if ( preg_match($mpat['av'], $e[$i]) )
							$hit[] = &$af[$d][$i];
					if ( empty($hit) )
						continue;
					printf($sgfmt, self::ht($d));
					foreach ( $hit as $fv ) {
						$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
						$fv = self::ht($fv);
						printf($sofmt, self::et($tu), $fv);
					}
					echo "</optgroup>\n";
				}
				// end select
				echo "</select><br />\n";
				echo "</p>\n";
			} // end if there are upload files
			if ( ! empty($aa) ) {
				echo "<p>\n";
				$k = 'atch';
				$jfcp = sprintf($jfsl, $id, $k, $kl);
				$l = self::wt(__('Select ID from media library:', 'swfput_l10n'));
				printf($lbfmt, $id, $k, $l);
				// <select>
				printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
				// <options>
				printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
				foreach ( $aa as $fn => $fi ) {
					$m = basename($fn);
					if ( ! preg_match($mpat['av'], $m) )
						continue;
					$ts = $m . " (" . $fi . ")";
					printf($sofmt, self::et($fi), self::ht($ts));
				}
				// end select
				echo "</select><br />\n";
				echo "</p>\n";
			} // end if there are upload files
		?>
		<p>
		<?php $k = 'audio';
			$l = self::wt(__('Medium is audio:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			$ck = $$k == 'true' ? 'checked="checked" ' : '';
			printf($ckfmt, $id, $k, $id, $k, $$k, $ck); ?>
		</p><p>
		<?php $k = 'altvideo'; 
			$l = self::wt(__('URLs for alternate HTML5 video (optional: .mp4, .webm, .ogv):', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
		</p><p>
		<?php $k = 'playpath'; 
			$l = self::wt(__('Playpath (rtmp):', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
		</p><p>
		<?php $k = 'iimage';
			$l = self::wt(__('Url of initial image file (optional):', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
		</p>
		<?php
			// if there are upload files, print <select >
			$kl = $k;
			if ( count($af) > 0 ) {
				echo "<p>\n";
				$k = 'ifiles';
				$jfcp = sprintf($jfsl, $id, $k, $kl);
				$l = self::wt(__('Load image from uploads directory:', 'swfput_l10n'));
				printf($lbfmt, $id, $k, $l);
				// <select>
				printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
				// <options>
				printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
				foreach ( $af as $d => $e ) {
					$hit = array();
					for ( $i = 0; $i < count($e); $i++ )
						if ( preg_match($mpat['i'], $e[$i]) )
							$hit[] = &$af[$d][$i];
					if ( empty($hit) )
						continue;
					printf($sgfmt, self::ht($d));
					foreach ( $hit as $fv ) {
						$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
						$fv = self::ht($fv);
						printf($sofmt, self::et($tu), $fv);
					}
					echo "</optgroup>\n";
				}
				// end select
				echo "</select><br />\n";
				echo "</p>\n";
			} // end if there are upload files
			if ( ! empty($aa) ) {
				echo "<p>\n";
				$k = 'iatch';
				$jfcp = sprintf($jfsl, $id, $k, $kl);
				$l = self::wt(__('Load image ID from media library:', 'swfput_l10n'));
				printf($lbfmt, $id, $k, $l);
				// <select>
				printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
				// <options>
				printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
				foreach ( $aa as $fn => $fi ) {
					$m = basename($fn);
					if ( ! preg_match($mpat['i'], $m) )
						continue;
					$ts = $m . " (" . $fi . ")";
					printf($sofmt, self::et($fi), self::ht($ts));
				}
				// end select
				echo "</select><br />\n";
				echo "</p>\n";
			} // end if there are upload files
		?>
		<p>
		<?php $k = 'iimgbg';
			$l = self::wt(__('Use initial image as non-flash alternate:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			$ck = $$k == 'true' ? 'checked="checked" ' : '';
			printf($ckfmt, $id, $k, $id, $k, $$k, $ck); ?>
		</p>

		</div></div>
		<?php $ndiv++;
			$dvon = $dvio . $ndiv;
			$dvin = $dvii . $ndiv;
			$dvib = $dvin . '_btn';
			$jdft = sprintf($jdsh, $dvin);
		?>
		<div class="<?php echo $dvio; ?>" id="<?php echo $dvon; ?>">
		<span class="submit">
			<?php printf($dbfmt, $dvib, $dbvhi, $job, $jdft); ?>
		</span>
		<h3 class="hndle"><span><?php
			echo self::wt(__('Dimensions', 'swfput_l10n')); ?></span></h3>
		<div class="<?php echo $dvii; ?>" id="<?php echo $dvin; ?>">

		<?php $els = array(
			array('width', '<p>', ' &#215; ', $in, 'inp',
				__('Pixel Width:', 'swfput_l10n')),
			array('height', '', '</p>', $in, 'inp',
				__('Height:', 'swfput_l10n')),
			array('aspectautoadj', '<p>', '</p>', $in, 'chk',
				__('Auto aspect (e.g. 360x240 to 4:3):', 'swfput_l10n')),
			array('displayaspect', '<p>', '</p>', $in, 'inp',
				__('Display aspect (e.g. 4:3, precludes Auto):', 'swfput_l10n')),
			array('pixelaspect', '<p>', '</p>', $in, 'inp',
				__('Pixel aspect (e.g. 8:9, precluded by Display):', 'swfput_l10n'))
			);
			foreach ( $els as $el ) {
				$k = $el[0];
				echo $el[1];
				$type = &$el[4];
				$l = self::wt($el[5]);
				printf($lbfmt, $id, $k, $l);
				if ( $type == 'inp' ) {
					printf($infmt, $el[3], $id, $k, $id, $k, $$k);
				} else if ( $type == 'chk' ) {
					$ck = $$k == 'true' ? 'checked="checked" ' : '';
					printf($ckfmt, $id, $k, $id, $k, $$k, $ck);
				}
				echo $el[2];
			}
		?>

		</div></div>
		<?php $ndiv++;
			$dvon = $dvio . $ndiv;
			$dvin = $dvii . $ndiv;
			$dvib = $dvin . '_btn';
			$jdft = sprintf($jdsh, $dvin);
		?>
		<div class="<?php echo $dvio; ?>" id="<?php echo $dvon; ?>">
		<span class="submit">
			<?php printf($dbfmt, $dvib, $dbvhi, $job, $jdft); ?>
		</span>
		<h3 class="hndle"><span><?php
			echo self::wt(__('Behavior', 'swfput_l10n')); ?></span></h3>
		<div class="<?php echo $dvii; ?>" id="<?php echo $dvin; ?>">
		
		<?php $els = array(
			array('volume', '<p>', '</p>', $in, 'inp',
				__('Initial volume (0-100):', 'swfput_l10n')),
			array('play', '<p>', '</p>', $in, 'chk',
				__('Play on load (else waits for play button):', 'swfput_l10n')),
			array('loop', '<p>', '</p>', $in, 'chk',
				__('Loop play:', 'swfput_l10n')),
			array('hidebar', '<p>', '</p>', $in, 'chk',
				__('Hide control bar initially:', 'swfput_l10n')),
			array('disablebar', '<p>', '</p>', $in, 'chk',
				__('Hide and disable control bar:', 'swfput_l10n')),
			array('allowfull', '<p>', '</p>', $in, 'chk',
				__('Allow full screen:', 'swfput_l10n')),
			array('barheight', '<p>', '</p>', $in, 'inp',
				__('Control bar Height (20-50):', 'swfput_l10n'))
			);
			foreach ( $els as $el ) {
				$k = $el[0];
				echo $el[1];
				$type = &$el[4];
				$l = self::wt($el[5]);
				printf($lbfmt, $id, $k, $l);
				if ( $type == 'inp' ) {
					printf($infmt, $el[3], $id, $k, $id, $k, $$k);
				} else if ( $type == 'chk' ) {
					$ck = $$k == 'true' ? 'checked="checked" ' : '';
					printf($ckfmt, $id, $k, $id, $k, $$k, $ck);
				}
				echo $el[2];
			}
		?>

		</div></div>

		<?php
	}

	// wrap do_shortcode() to set a flag for the callback
	public function wdg_do_shortcode($cont) {
		$this->in_wdg_do_shortcode++;
		$r = do_shortcode($cont);
		$this->in_wdg_do_shortcode--;
		return $r;
	}
	
	// handler for 'shortcode' tags in widget that will be
	// replaced with SWF video
	// subject to option $optcodewdg
	public function wdg_shortcode($atts, $content = null, $code = "") {
		if ( $this->in_wdg_do_shortcode < 1 ) {
			return $this->post_shortcode($atts, $content, $code);
		}
		if ( self::get_widget_code_option() === 'false' ) {
			$c = '';
			// TRANSLATORS the '[]' are meant to indicate strongly
			// that this is not normal, expected text display,
			// because this text takes the place of a Flash program
			// when disabled by a plugin option.
			// 'A/V' is understood in US (all English language???)
			// as 'Audio/Visual' e.g., film, sound.
			// '%s' is any caption provided for a/v, if any,
			// prepended with ASCII space ' '; '%s' is an empty string
			// if there is no caption
			$fmt = self::wt(__(' [A/V content "%s" disabled] ', 'swfput_l10n'));
			// Note '!=' -- not '!=='
			if ( $content != null ) {
				$c = ' ' . do_shortcode($content);
			}
			return sprintf($fmt, $c);
		}

		$pr = self::swfput_params;
		$pr = new $pr();
		$pr->setarray(shortcode_atts($pr->getparams(), $atts));
		$pr->sanitize();
		$w = $pr->getvalue('width');
		$h = $pr->getvalue('height');
		
		if ( $code === "" ) {
			$code = $atts[0];
		}
		if ( $this->should_use_ming() ) {
			$swf = $this->get_swf_url('widget', $w, $h);
		} else {
			$bh = $pr->getvalue('barheight');
			$swf = $this->get_swf_binurl($bh);
		}
		$dw = $w + 3;
		// use no class, but do use deprecated align
		$dv = '<p><div id="'.$code.'" align="center"';
		$dv .= ' style="width: '.$dw.'px">';
		$em = $this->get_swf_tags($swf, $pr);
		$c = '';
		// Note '!=' -- not '!=='
		if ( $content != null ) {
			$c = do_shortcode($content);
			$c = '</p><p><span align="center">' . $c . '</span></p><p>';
		}
		return sprintf('%s%s%s</div></p>', $dv, $em, $c);
	}

	// handler for 'shortcode' tags in posts that will be
	// replaced with SWF video
	// subject to option $optdispmsg && $optcodemsg
	public function post_shortcode($atts, $content = null, $code = "") {
		if ( $this->in_wdg_do_shortcode > 0 ) {
			return $this->wdg_shortcode($atts, $content, $code);
		}
		if ( self::get_posts_code_option() === 'false' ) {
			$c = '';
			// TRANSLATORS the '[]' are meant to indicate strongly
			// that this is not normal, expected text display,
			// because this text takes the place of a Flash program
			// when disabled by a plugin option.
			// 'A/V' is understood in US (all English language???)
			// as 'Audio/Visual' e.g., film, sound.
			// '%s' is any caption provided for a/v, if any,
			// prepended with ASCII space ' '; '%s' is an empty string
			// if there is no caption
			$fmt = self::wt(__(' [A/V content "%s" disabled] ', 'swfput_l10n'));
			// Note '!=' -- not '!=='
			if ( $content != null ) {
				$c = ' ' . do_shortcode($content);
			}
			return sprintf($fmt, $c);
		}

		$pr = self::swfput_params;
		$pr = new $pr();
		$pr->setarray(shortcode_atts($pr->getparams(), $atts));
		$pr->sanitize();
		$w = $pr->getvalue('width');
		$h = $pr->getvalue('height');
		
		if ( $code === "" ) {
			$code = $atts[0];
		}
		if ( $this->should_use_ming() ) {
			$swf = $this->get_swf_url('post', $w, $h);
		} else {
			$bh = $pr->getvalue('barheight');
			$swf = $this->get_swf_binurl($bh);
		}
		$dw = $w + 0;
		// use class that WP uses for e.g. images
		$dv = '<div id="'.$code.'" class="wp-caption aligncenter"';
		$dv .= ' style="width: '.$dw.'px">';
		$em = $this->get_swf_tags($swf, $pr);
		$c = '';
		// Note '!=' -- not '!=='
		if ( $content != null ) {
			$c = do_shortcode($content);
			$c = '<p class="wp-caption-text">' . $c . '</p>';
		}
		return sprintf('%s%s%s</div>', $dv, $em, $c);
	}

	// filter the posts for attachments that can be
	// replaced with SWF video
	// subject to option $optdispmsg
	public function post_sed($dat) {
		global $post, $wp_locale;
		$mpat = self::get_mfilter_pat();
		$w = 400; $h = 300; // TODO: from option
		$pr = self::swfput_params;
		$pr = new $pr();
		$pr->setvalue('width', $w);
		$pr->setvalue('height', $h);
		if ( $this->should_use_ming() ) {
			$swf = $this->get_swf_url('post_sed', $w, $h);
		} else {
			$bh = $pr->getvalue('barheight');
			$swf = $this->get_swf_binurl($bh);
		}
		
		// accumulate in $out
		$out = '';
		// split into lines, saving line end chars
		$pat = '/(\r\n|\r|\n)/';
		$la = preg_split($pat, $dat, null, PREG_SPLIT_DELIM_CAPTURE);
		// loop through lines checking for string to
		// replace with swf tags
		$pat = '/^(.*)\b(https?:\/\/[^\?\&]+)([\?\&])(attachment_id=)([0-9]+)\b(.*)$/';
		for ( $n = 0; $n < count($la); ) {
			$line = $la[$n]; $n++;
			$sep = isset($la[$n]) ? $la[$n] : ''; $n++;
			$m = null;
			if ( preg_match($pat, $line, $m, PREG_OFFSET_CAPTURE) ) {
				$tok = $m[3][0];
				$id = $m[5][0];
				//$meta = wp_get_attachment_metadata($id);
				$url = wp_get_attachment_url($id);
				if ( is_wp_error($url) ) {
					$out .= $line . $sep;
					self::errlog('failed URL of attachment ' . $id);
				} else if ( ! preg_match($mpat['av'], $url) ) {
					$out .= $line . $sep;
				} else {
					$pr->setvalue('url', $url);
					$s = '<div style="width: '.($w+0).'px" '
						. 'class="wp-caption aligncenter">'
						. $this->get_swf_tags($swf, $pr)
						// . '<p class="wp-caption-text"></p>'
						. '</div>'
						. $sep;
					$out .= $s . $line . $sep;
				}
			} else {
				$out .= $line . $sep;
			}
		}
		return $out;
	}
	
	/**
	 * Utility and misc. helper procs
	 */

	// append version suffix for Options classes names
	protected static function mk_aclv($pfx) {
		$s = $pfx . '_' . self::aclv;
		return $s;
	}
	
	// help for plugin file path/name; __FILE__ alone
	// is not good enough -- see comment in body
	public static function mk_plugindir() {
		if ( self::$pluginfile !== null ) {
			return dirname(self::$pluginfile);
		}
	
		$pf = __FILE__;
		// using WP_PLUGIN_DIR due to symlink problems in
		// some installations; after much grief found fix at
		// https://wordpress.org/support/topic/register_activation_hook-does-not-work
		// in a post by member 'silviapfeiffer1' -- she nailed
		// it, and noone even replied to her!
		if ( defined('WP_PLUGIN_DIR') ) {
			$ad = explode('/', rtrim(plugin_dir_path($pf), '/'));
			$pd = $ad[count($ad) - 1];
			$pf = WP_PLUGIN_DIR . '/' . $pd;
		} else {
			// this is similar to common methods w/  __FILE__; but
			// can cause regi* failures due to symlinks in path
			$pf = rtrim(plugin_dir_path($pf), '/');
		}
		
		// store and return corrected file path
		return $pf;
	}
	
	// help for plugin file path/name; __FILE__ alone
	// is not good enough -- see comment in body
	public static function mk_pluginfile() {
		if ( self::$pluginfile !== null ) {
			return self::$pluginfile;
		}
	
		$pf = self::mk_plugindir();
		$ff = basename(__FILE__);
		
		// store and return corrected file path
		return self::$pluginfile = $pf . '/' . $ff;
	}
	
	// help for swf player file path/name; it is
	// contained in the plugin directory
	public static function mk_playerdir() {
		$pd = self::mk_plugindir();
		return $pd . '/' . self::swfputdir;
	}

	// help for swf player file path/name; it is
	// contained in the plugin directory
	public static function mk_playerfile() {
		$pd = self::mk_playerdir();
		return $pd . '/' . self::swfputphpname;
	}

	// help for swf player file path/name; it is
	// contained in the plugin directory
	public static function mk_playerbinfile() {
		$pd = self::mk_playerdir();
		return $pd . '/' . self::swfputbinname;
	}

	// can php+ming be used?
	public static function can_use_ming() {
		if ( extension_loaded('ming') ) {
			return true;
		}
		return false;
	}

	// should php+ming be used?
	public static function should_use_ming() {
		if ( self::can_use_ming() === true ) {
			if ( self::opt_by_name(self::optuseming) === 'true' ) {
				return true;
			}
		}
		return false;
	}

	// return preg_match() pattern for media filter by file extension
	public static function get_mfilter_pat() {
		// TODO: build from extensions option
		return array('av' =>'/.*\.(flv|f4v|m4v|mp4|mp3)$/i',
			'i' => '/.*\.(swf|png|jpg|jpeg|gif)$/i',
			'm' => '/.*\.(flv|f4v|m4v|mp4|mp3|swf|png|jpg|jpeg|gif)$/i'
			);
	}

	// escape symbol for use in jQuery selector or similar; see
	//     http://api.jquery.com/category/selectors/
	public static function esc_jqsel($sym, $include_dash = false) {
		$chr = '!"#$%&\'()*+,.\/:;<=>?@\[\]\^`{|}~';
		if ( $include_dash === true )
			$chr .= '-';
		$pat = '/([' . $chr . '])/';
		$rep = '\\\\\\\$1';
		return preg_replace($pat, $rep, $sym);
	}

	// hex encode a text string
	public static function et($text) {
		return rawurlencode($text);
	}
	
	// 'html-ize' a text string
	public static function ht($text, $cset = null) {
		// try to use get_option('blog_charset') only once;
		// it's not cheap enough even with WP's cache for
		// the number of times this might be called
		static $_blog_charset = null;
		if ( $_blog_charset === null ) {
			$_blog_charset = get_option('blog_charset');
			if ( ! $_blog_charset ) {
				$_blog_charset = 'UTF-8';
			}
		}
	
		if ( $cset === null ) {
			$cset = $_blog_charset;
		}

		return htmlentities($text, ENT_QUOTES, 'UTF-8');
	}
	
	// 'html-ize' a text string; with WorPress char translations
	public static function wt($text) {
		if ( function_exists('wptexturize') ) {
			return wptexturize($text);
		}
		return self::ht($text);
	}
	
	// get WP software version as int (at least 32 bit, major < 128)
	public static function wpv_int() {
		static $wp_vi = null;
		if ( $wp_vi === null ) {
			global $wp_version;
			$v = 0;
			$va = explode('.', $wp_version);
			for ( $i = 0; $i < 4; $i++ ) {
				if ( ! isset($va[$i]) ) {
					break;
				}
				$v |= ((int)$va[$i] << ((3 - $i) * 8));
			}
			$wp_vi = $v;
		}
		return $wp_vi;
	}
	
	// compare WP software version -- 1 if wp > cmp val,
	// -1 if <, else 0
	public static function wpv_cmp($cv) {
		$wv = self::wpv_int();
		$cv = (int)$cv;
		if ( $cv < $wv ) return 1;
		if ( $cv > $wv ) return -1;
		return 0;
	}
	
	// compare WP software version
	public static function wpv_min($cv) {
		return (self::wpv_cmp($cv) >= 0) ? true : false;
	}
	
	protected static function is_msie() {
		static $is_so = null;
		if ( $is_so === null ) {
			$r = preg_match('/\bMSIE\b/', $_SERVER['HTTP_USER_AGENT']);
			$is_so = $r ? true : false;
		}
		return $is_so;
	}
	
	// error messages; where {wp_}die is not suitable
	public static function errlog($err) {
		$e = sprintf('SWFPut WP plugin: %s', $err);
		error_log($e, 0);
	}
	
	// helper to make self
	public static function instantiate($init = true) {
		if ( ! self::$instance ) {
			$cl = __CLASS__;
			self::$instance = new $cl($init);
		}
		return self::$instance;
	}

	// helper get instance of this class
	public static function get_instance($init = false) {
		global $swfput1_evh_instance_1;
		$pg = null;

		if ( ! isset($swfput1_evh_instance_1)
			|| $swfput1_evh_instance_1 == null ) {
			$pg = self::instantiate($init);
		} else {
			$pg = $swfput1_evh_instance_1;
		}

		return $pg;
	}

	// helper to recursively find files preg_matching $pat,
	// starting at directory $dir, ignoring symbolic links
	// if $follow is false -- returns array of array(filename, dirname)
	public static function r_find_files($dir, $pat, $follow = false) {
	        $ao = array();
	        $pr = $dir === '.' ? '' : $dir . '/';

	        // although this procedure should not be handling
	        // external input, take some security measures anyway
	        if ( preg_match('/^\.\.\//', $pr) ) {
				// found ../*
				return $ao; // empty
			} else if ( preg_match('/\/\.\.\//', $pr) ) {
				// found */../*
				return $ao; // empty
			} else if ( preg_match('/^\//', $pr) ) {
				// found /*
				return $ao; // empty
			}

	        foreach ( scandir($dir) as $e) {
	                if ( $e === '.' || $e === '..' )
	                        continue;
	                $t = $pr . $e;
	                if ( $follow === false && is_link($t) )
	                        continue;
	                if ( ! is_readable($t) )
	                        continue;
	                if ( is_dir($t) ) {
	                        // array_merge should *not* overwrite
	                        // numeric keys, but rather append
	                        $at = self::r_find_files($t, $pat, $follow);
	                        if ( count($at) > 0 ) {
	                                $ao = array_merge($ao, $at);
	                        }
	                        continue;
	                }
	                if ( is_file($t) && preg_match($pat, $e) ) {
	                        $ao[$dir][] = $e;
	                }
	        }
	        return $ao;
	}

	// helper to recursively find files preg_matching $pat,
	// starting at WP uploads base dir, ignoring symbolic links
	// if $follow is false -- returns as r_find_files() above,
	// with the dirnames rooted at (i.e. excluding) WP upload base dir
	// NOTE: not tested on MS, but should work if uploads are on
	// current drive; else forget it.
	public static function r_find_uploads($pat, $follow = false) {
		global $wpdb;
		$minq = 0; $maxq = 4096; $tq = 'attachment';

		$qs = "SELECT * FROM $wpdb->posts WHERE post_type = '%s'";
		$qs .= ' LIMIT %d, %d';
		$rat =
			$wpdb->get_results($wpdb->prepare($qs, $tq, $minq, $maxq));

		$aa = array();
		
		foreach ( $rat as $att ) {
			$id = $att->ID;
			$af = get_attached_file($id, true);
			if ( ! preg_match($pat, $af) )
				continue;
			$aa[$af] = '' . $id;
		}

		$ao = array();
		$au = wp_upload_dir();
		if ( ! $au )
			return array('rf' => $ao, 'wu' => $au, 'at' => $aa);
		$cdir = getcwd();
		if ( ! chdir($au['basedir']) ) {
			return array('rf' => $ao, 'wu' => $au, 'at' => $aa);
		}
		$ao = self::r_find_files('.', $pat, $follow);
		chdir($cdir);
		return array('rf' => $ao, 'wu' => $au, 'at' => $aa);
	}

	/**
	 * WP options specific helpers
	 */

	// get the plugins main option group
	public static function get_opt_group() {
		return get_option(self::opt_group); /* WP get_option() */
	}
	
	// get an option value by name/key
	public static function opt_by_name($name) {
		$opts = self::get_opt_group();
		if ( $opts && array_key_exists($name, $opts) ) {
			return $opts[$name];
		}
		return null;
	}

	// for settings section descriptions
	public static function get_verbose_option() {
		return self::opt_by_name(self::optverbose);
	}

	// option for widget areas
	public static function get_widget_option() {
		return self::opt_by_name(self::optdispwdg);
	}

	// for the sidebar widget
	public static function get_widget_plugin_option() {
		if ( self::get_widget_option() !== 'true' )
			return 'false';
		return self::opt_by_name(self::optplugwdg);
	}

	// for the widget shortcodes
	public static function get_widget_code_option() {
		if ( self::get_widget_option() !== 'true' )
			return 'false';
		return self::opt_by_name(self::optcodewdg);
	}

	// get the do messages (place in posts) option
	public static function get_message_option() {
		return self::opt_by_name(self::optdispmsg);
	}

	// do message shortcodes
	public static function get_posts_code_option() {
		if ( self::get_message_option() !== 'true' )
			return 'false';
		return self::opt_by_name(self::optcodemsg);
	}

	// do message attachment sed
	public static function get_posts_preg_option() {
		if ( self::get_message_option() !== 'true' )
			return 'false';
		return self::opt_by_name(self::optpregmsg);
	}

	// get the place at head option
	public static function get_head_option() {
		return self::opt_by_name(self::optdisphdr);
	}

	/**
	 * encode a path for a URL, e.g. from parse_url['path']
	 * leaving '/' un-encoded
	 * $func might also be urlencode(), or user defined
	 * inheritable
	 */
	public static function upathencode($p, $func = 'rawurlencode') {
		return implode('/',
			array_map($func,
				explode('/', $p) ) );
	}

	/**
	 * check that URL passed in query is OK; re{encode,escape}
	 * $args is array of booleans, plus two regex pats -- all optional
	 * requirehost, requirepath, rejuser, rejport, rejquery, rejfrag +
	 * rxproto, rxpath (regex search patterns); true requirehost
	 * implies proto is required
	 * $fesc is escaping function for path, if wanted; e.g. urlencode()
	 */
	public static function check_url($url, $args = array(), $fesc = '') {
		extract($args);
		$ourl = '';
		$p = '/';
		$ua = parse_url($url);
		if ( array_key_exists('path', $ua) ) {
			$t = ltrim($ua['path'], '/');
			if ( isset($rxpath) ) {
				if ( ! preg_match($rxpath, $t) ) {
					return false;
				}
			}
			// no '..' in path!
			if ( preg_match('/^(.*\/)?\.\.(\/.*)?$/', $t) ) {
				return false;
			}
			$p .= $fesc === '' ? $t : self::upathencode($t, $fesc);
		} else if ( isset($requirepath) && $requirepath ) {
			return false;
		}
		if ( array_key_exists('host', $ua) ) {
			if ( array_key_exists('scheme', $ua) ) {
				$t = $ua['scheme'];
				if ( isset($rxproto) ) {
					if ( ! preg_match($rxproto, $t) ) {
						return false;
					}
				}
				$ourl = $t . '://';
			} else if ( isset($requirehost) && $requirehost ) {
				return false;
			}
			if ( array_key_exists('user', $ua) ) {
				if ( isset($rejuser) && $rejuser ) {
					return false;
				}
				$ourl .= $ua['user'];
				// user not rejected; pass OK
				if ( array_key_exists('pass', $ua) ) {
					$ourl .= ':' . $ua['pass'];
				}
				$ourl .= '@';
			}
			$ourl .= $ua['host'];
			if ( array_key_exists('port', $ua) ) {
				if ( isset($rejport) && $rejport ) {
					return false;
				}
				$ourl .= ':' . $ua['port'];
			}
		} else if ( isset($requirehost) && $requirehost ) {
			return false;
		}
	
		$ourl .= $p;
		// A query with the media URL? It can happen
		// for stream servers.
		// this works, e.g. w/ ffserver ?date=...
		if ( array_key_exists('query', $ua) ) {
			if ( isset($rejquery) && $rejquery ) {
				return false;
			}
			$ourl .= '?' . $ua['query'];
		}
		if ( array_key_exists('fragment', $ua) ) {
			if ( isset($rejfrag) && $rejfrag ) {
				return false;
			}
			$ourl .= '#' . $ua['fragment'];
		}
		
		return $ourl;
	}

	// helper for selecting swf type (bin||script)) url
	// arg $sel should be caller tag: 'widget',
	// 'post' (shortcodes in posts), 'post_sed' (attachment_id filter),
	// 'head' -- it might be used in future
	public function get_swf_url($sel, $wi = 640, $hi = 480) {
		$useming = self::should_use_ming();

		if ( $useming === true ) {
			$t = $this->swfputphp;
		} else {
			$n = floor((int)$hi / 10);
			if ( $sel === 'widget' ) {
				$n = 24;
			}
			$t = $this->get_swf_binurl($n);
		}

		return $t;
	}

	// helper for selecting swf bin near desired bar height
	public function get_swf_binurl($bh = 48) {
		$d = self::mk_playerdir();
		$f = self::swfputbinname;
		$a = explode('.', $f);
		$p = sprintf('/^%s([0-9]+)\.%s$/i', $a[0], $a[1]);
		$vmin = 65535; $vmax = 0;

		$a = array();
		foreach ( scandir($d) as $e ) {
			$t = $d . '/' . $e;
			if ( ! is_file($t) )
				continue;
			if ( ! is_readable($t) )
				continue;
			if ( ! preg_match($p, $e, $m) )
				continue;
			$a[$m[1]] = $e;
			$n = (int)$m[1];
			$vmin = min($vmin, $n);
			$vmax = max($vmax, $n);
		}

		$bh = (int)$bh;
		$n = count($a);
		if ( $n === 0 ) {
			$f = self::swfputbinname;
		} else if ( $n === 1 ) {
			// $vmax will index the only entry in $a
			$f = $a['' . $vmax];
		} else if ( $bh >= $vmax ) {
			$f = $a['' . $vmax];
		} else if ( $bh <= $vmin ) {
			$f = $a['' . $vmin];
		} else {
			$ak = array_keys($a);
			sort($ak, SORT_NUMERIC);
			$lk = (int)$ak[0];
			for ( $n = 1; $n < count($ak); $n++ ) {
				$k = (int)$ak[$n];
				// $bh must be found < $k within this loop
				// due to above test 'if ( $bh >== $vmax )'
				if ( $bh > $k ) {
					$lk = (int)$ak[$n];
					continue;
				}
				if ( ($bh - $lk) < ($k - $bh) ) {
					$n--;
				}
				break;
			}
			if ( $n >= count($ak) ) {
				die('broken logic in ' . __FUNCTION__);
			}
			$f = $a[$ak[$n]];
		}

		$t = dirname($this->swfputbin);
		return $t . '/' . $f;
	}

	// helper for getting swf css (internal use)) url
	// arg $sel should be caller tag: 'widget',
	// 'post' (shortcodes in posts), 'post_sed' (attachment_id filter),
	// 'head' -- it might be used in future
	public function get_swf_css_url($sel = '') {
		return $this->swfputcss;
	}

	// The swf player directory should have a small default video file;
	// if it exists make a url for it.
	public function get_swf_default_url() {
		return $this->swfputvid;
	}

	// print suitable SWF object/embed tags
	public function put_swf_tags($uswf, $par, $esc = true) {
		$s = $this->get_swf_tags($uswf, $par, $esc);
		echo $s;
	}

	// return a string with suitable SWF object/embed tags
	public function get_swf_tags($uswf, $par, $esc = true) {
		extract($par->getparams());
		$ming = self::should_use_ming();

		if ( ! $uswf ) {
			if ( $ming ) {
				$uswf = $this->get_swf_url('post', $width, $height);
			} else {
				$uswf = $this->get_swf_binurl($barheight);
			}
		}

		$fesc = 'rawurlencode';
		if ( isset($esc_t) && $esc_t === 'plus' ) {
			$fesc = 'urlencode';
		}

		if ( preg_match('/^0*[1-9][0-9]*$/', $url) ) {
			$url = wp_get_attachment_url(ltrim($url, '0'));
			if ( ! $url ) {
				$url = '';
				self::errlog('rejected video url media ID');
			}
		}
		if ( $url === '' ) {
			$url = $defaulturl;
			if ( $url === 'default' ) {
				$url = $this->get_swf_default_url();
			}
		}
		if ( $url === '' ) {
			$url = $defrtmpurl;
			$playpath = $defaultplaypath;
		}
		
		$achk = array(
			'requirehost' => false, // can use orig host
			'requirepath' => true,
			'rejfrag' => true,
			// no, don't try to match extension; who knows?
			//'rxpath' => '/.*\.(flv|f4v|mp4|m4v|mp3)$/i',
			'rxproto' => '/^(https?|rtmp[a-z]{0,2})$/'
			);
		$ut = self::check_url($url, $achk);
		if ( ! $ut ) {
			self::errlog('rejected URL: "' . $url . '"');
			return '<!-- SWF embedding declined:  URL displeasing -->';
		}
		// escaping: note url used here is itself a query arg
		$url = ($esc == true) ? $fesc($ut) : $ut;
		// Hack: double escaped URL. This is to releive the swf
		// player of the need to escape URL with the simplistic
		// ActionScript escape(), the need for which arises from
		// attempting Gnash comaptibility because it differs
		// from the adobe binary in not internally encoding
		// URL args to a/v objects -- escaping is doubled because
		// flashvars are always unescaped by the plugin, making
		// a 2nd level necessary.
		$e2url = self::check_url($ut, $achk, 'rawurlencode');
		if ( $esc == true ) {
			$e2url = $fesc($e2url);
		}

		$w = $width; $h = $height;
		if ( $cssurl === '' )
			$cssurl = $this->get_swf_css_url();
		$achk = array(
			'requirehost' => false, // can use orig host
			'requirepath' => true,
			'rejuser' => true,
			'rejquery' => true,
			'rejfrag' => true,
			'rxpath' => '/.*\.css$/i',
			'rxproto' => '/^https?$/'
			);
		$ut = self::check_url($cssurl, $achk);
		if ( ! $ut ) {
			self::errlog('rejected css URL: "' . $cssurl . '"');
			$ut = '';
		}
		$cssurl = ($esc == true) ? $fesc($ut) : $ut;
		if ( $iimage !== '' ) {
			if ( preg_match('/^0*[1-9][0-9]*$/', $iimage) ) {
				$iimage = wp_get_attachment_url(ltrim($iimage, '0'));
				if ( ! $iimage ) {
					self::errlog('rejected i-image media ID');
					$iimage = '';
				}
			}
		}
		$iimgunesc = ''; // $iimage not escaped: see below
		if ( $iimage !== '' ) {
			$achk['rxpath'] = '/.*\.(swf|png|jpg|jpeg|gif)$/i';
			$ut = self::check_url($iimage, $achk);
			if ( ! $ut ) {
				self::errlog('rejected i-image URL: "' . $iimage . '"');
				$ut = '';
			}
			if ( ! preg_match('/.*\.swf$/i', $ut) ) {
				$iimgunesc = $ut;
			}
			$iimage = ($esc == true) ? $fesc($ut) : $ut;
		}
		$playpath = ($esc == true) ? $fesc($playpath) : $playpath;
		
		// query vars
		$qv = sprintf('ST=%s&WI=%u&HI=%u&IDV=%s&FN=%s&II=%s&F2=%s',
			$cssurl, $w, $h, $playpath, $url, $iimage, $e2url);
		$qv .= sprintf('&PL=%s&HB=%s&VL=%u&LP=%s&DB=%s',
			$play, $hidebar, $volume, $loop, $disablebar);
		$qv .= sprintf('&AU=%s&AA=%s&DA=%s&PA=%s',
			$audio, $aspectautoadj, $displayaspect, $pixelaspect);
		$qv .= sprintf('&BH=%s',
			$barheight);

		// if using the precompiled player the query vars should be
		// written to 'flashvars' so that the player can access them;
		// but if using the php+ming script generated player the vars
		// should be written to the script query, and they get better
		// processing there, and then initialize the player's vars
		// in actionscript; moreover, in this case they should not
		// be passed in 'flashvars' so that the player does not see
		// them and uses the initial values instead
		if ( $ming ) {
			$pv = &$qv;
			$fv = '';
		} else {
			$pv = 'fpo=php+ming';
			$fv = &$qv;
		}

		// alternates
		$altimg = '';
		if ( $iimgbg == 'true' && $iimgunesc != '' ) {
			$fmt = '%s<img src="%s" alt="%s" width="%u" height="%u">';
			$altimg = sprintf($fmt, "\n\t\t",
				self::ht($iimgunesc),
				self::wt(
				__('The flash plugin is not available', 'swfput_l10n')
				), $w, $h
			);
		}
		if ( $altvideo != '' ) {
			$vd = "\n\t\t" . '<video controls preload="none"';
			if ( $play == 'true' ) {
				$vd .= ' autoplay';
			}
			if ( $loop == 'true' ) {
				$vd .= ' loop';
			}
			if ( $iimgunesc != '' ) {
				$vd .= sprintf(' poster="%s"', self::ht($iimgunesc));
			}
			$vd .= sprintf(' width="%u" height="%u">', $w, $h);
			// format for source elements
			$fmt = "\n\t\t" . '<source src="%s"%s>';
			// allow multiple video src, separated by pipe
			$altvideo = trim($altvideo, " \t|");
			$av = explode('|', $altvideo);
			// place sources
			foreach ( $av as $src ) {
				$typ = '';
				// allow '?' separated type string
				if ( ($src = trim($src, " \t?")) === '' ) {
					continue;
				}
				$tv = explode('?', $src);
				if ( isset($tv[1]) ) {
					if ( ($tv[1] = trim($tv[1])) !== '' ) {
						$typ = sprintf(' type="%s"', self::ht($tv[1]));
					}
					// leave off src
					$src = trim($tv[0]);
				}
				$vd .= sprintf($fmt, self::ht($src), $typ);
			}

			// place as alt the altimg, or message string
			$vd .= sprintf("%s\n\t\t</video>",
				$altimg == '' ?
				self::wt("\n\t\t" .
				__('Flash video is not available, and the alternate <code>video</code> sources were rejected by your browser', 'swfput_l10n')
				) : $altimg
			);
			
			$altimg = $vd;
		}

		// Update 2013/09/23: update object element, separating
		// MSIE PITA, so that alternative elements can be added
		// for no-flash browsers: previously, with the classid attribute
		// within the object element, firefox (and others) would
		// always fall through to the now-removed embed element;
		// therefore, browser ID is attempted to find MSIE (in
		// self::is_msie()) on the assumption that classid will
		// be necessary to make that one work
		$obj = '';
		if ( self::is_msie() ) { 
			$obj = sprintf('
			<object classid="%s" codebase="%s" width="%u" height="%u">
			<param name="data" value="%s?%s">
			', $classid, $codebase, $w, $h, $uswf, $pv);
		} else {
			$typ = 'application/x-shockwave-flash';
			$obj = sprintf('
			<object data="%s?%s" type="%s" width="%u" height="%u">
			', $uswf, $pv, $typ, $w, $h);
		}
		return $obj
		. sprintf('<param name="play" value="%s">
		<param name="quality" value="%s">
		<param name="allowFullScreen" value="%s">
		<param name="allowScriptAccess" value="sameDomain">
		<param name="flashvars" value="%s">
		<param name="src" value="%s?%s">
		<param name="name" value="mingput">
		<param name="bgcolor" value="#000000">
		<param name="align" value="middle">%s
		</object>
		', $play, $quality, $allowfull, $fv, $uswf, $pv, $altimg);
	}
} // End class SWF_put_evh

// global instance of plugin class
global $swfput1_evh_instance_1;
if ( ! isset($swfput1_evh_instance_1) ) :
	$swfput1_evh_instance_1 = null;
endif; // global instance of plugin class

else :
	wp_die('class name conflict: SWF_put_evh in ' . __FILE__);
endif; // if ( ! class_exists('SWF_put_evh') ) :


/**
 * class providing embed and player parameters, built around array --
 * uncommented, but it's simple and obvious
 * values are all strings, even if empty or numeric etc.
 */
if ( ! class_exists('SWF_params_evh') ) :
class SWF_params_evh {
	protected static $defs = array(
		'caption' => '',		   // added for forms, not embed object
		'url' => '',
		'defaulturl' => 'default', // subs. distributed default file
		'defrtmpurl' => 'rtmp://cp82347.live.edgefcs.net/live', //akamai
		'cssurl' => '',
		'iimage' => '',
		'width' => '240',
		'height' => '180',
		'audio' => 'false',        // source is audio; (mp3 is detected)
		'aspectautoadj' => 'true', // adj. common sizes, e.g. 720x480
		'displayaspect' => '0',    // needed if pixels are not square
		'pixelaspect' => '0',      // use if display aspect unknown
		'volume' => '50',          // if host has no saved setting
		'play' => 'false',         // play (or pause) on load
		'hidebar' => 'true',       // initially hide control bar
		'disablebar' => 'false',   // disable and hide control bar
		'iimgbg' => 'true',        // use iimage arg as alt. img element
		'barheight' => '36',
		'quality' => 'high',
		'allowfull' => 'true',
		'allowxdom' => 'false',
		'loop' => 'false',
		'mtype' => 'application/x-shockwave-flash',
		// rtmp
		'playpath' => '',
		// alternative <video> within object
		'altvideo' => '',
		'defaultplaypath' => 'CSPAN2@14846',
		// <object>
		'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
		'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0'
	);

	protected $inst = null; // modifiable copy per instance
	
	public function __construct($copy = null) {
		$this->inst = self::$defs;
		if ( is_array($copy) )
			$this->setarray($copy);
	}
	
	public static function getdefs() { return self::$defs; }
	public function getparams() { return $this->inst; }
	public function getkeys() { return array_keys($this->inst); }
	public function getvalues() { return array_values($this->inst); }
	public function getvalue($key) {
		if ( array_key_exists($key, $this->inst) ) {
			return $this->inst[$key];
		}
		return null;
	}
	public function getdefault($key) {
		if ( array_key_exists($key, self::$defs) ) {
			return self::$defs[$key];
		}
		return null;
	}
	public function setvalue($key, $val) {
		if ( array_key_exists($key, $this->inst) ) {
			$t = $this->inst[$key];
			$this->inst[$key] = $val;
			return $t;
		}
		return null;
	}
	public function setnewvalue($key, $val) {
		if ( array_key_exists($key, $this->inst) ) {
			$t = $this->inst[$key];
		} else {
			$t = $val;
		}
		$this->inst[$key] = $val;
		return $t;
	}
	public function setdefault($key) {
		if ( array_key_exists($key, self::$defs) ) {
			$t = $this->inst[$key];
			$this->inst[$key] = self::$defs[$key];
			return $t;
		}
		return null;
	}
	public function setarray($ar) {
		// array_replace is new w/ 5.3; want 5.2 here
		//$this->inst = array_replace($this->inst, $ar);
		// so . . .
		foreach ( $ar as $k => $v ) {
			if ( array_key_exists($k, self::$defs) ) {
				$this->inst[$k] = $v;
			}
		}
		return $this;
	}
	public function setnewarray($ar) {
		// array_replace is new w/ 5.3; want 5.2 here
		//$this->inst = array_replace($this->inst, $ar);
		// so . . .
		foreach ( $ar as $k => $v ) {
			$this->inst[$k] = $v;
		}
		return $this;
	}
	public function cmpval($key) {
		return (self::$defs[$key] === $this->inst[$key]);
	}
	public function sanitize($fuzz = true) {
		$i = &$this->inst;
		// check against default keys; if instance map has
		// other keys, leave them
		foreach ( self::$defs as $k => $v ) {
			if ( ! array_key_exists($k, $i) ) {
				$i[$k] = $v;
				continue;
			}
			$t = trim(' ' . $i[$k]);
			switch ( $k ) {
			// strings that must present positive integers
			case 'width':
			case 'height':
			case 'volume':
			case 'barheight':
				if ( $k === 'barheight' && $t === 'default' ) {
					continue;
				}
				if ( $fuzz === true && preg_match('/^\+?[0-9]+/',$t) ) {
					$t = sprintf('%u', absint($t));
				}
				if ( ! preg_match('/^[0-9]+$/', $t) ) {
					$t = $v;
				}
				$i[$k] = $t;
				break;
			// strings that must present booleans
			case 'audio':
			case 'aspectautoadj':
			case 'play':
			case 'hidebar':
			case 'disablebar':
			case 'iimgbg':
			case 'allowfull':
			case 'allowxdom':
			case 'loop':
				$t = strtolower($t);
				if ( $t !== 'true' && $t !== 'false' ) {
					if ( $fuzz === true ) {
						// TRANSLATORS perl-type regular expression
						// that matches a 'yes'
						$xt = __('/^?y(e((s|ah)!?)?)?$/i', 'swfput_l10n');
						// TRANSLATORS perl-type regular expression
						// that matches a 'no'
						$xf = __('/^n(o!?)?)?$/i', 'swfput_l10n');
						if ( is_numeric($t) ) {
							$t = $t == 0 ? 'false' : 'true';
						} else if ( preg_match($xf, $t) ) {
							$t = 'false';
						} else if ( preg_match($xt, $t) ) {
							$t = 'true';
						} else {
							$t = $v;
						}
					} else {
						$t = $v;
					}
				}
				$i[$k] = $t;
				break;
			// special format: ratio strings
			case 'displayaspect':
			case 'pixelaspect':
				// exception: these allow one alpha as special flag,
				// or 0 to disable
				if ( preg_match('/^[A-Z0]$/i', $t) ) {
					$i[$k] = $t;
					break;
				}
				if ( $fuzz === true ) {
					$sep = '[Xx[:punct:][:space:]]+';
				} else {
					$sep = '[Xx:]';
				}
				// exception: allow FLOAT or FLOATsep1
				$px = '/^\+?([0-9]+(\.[0-9]+)?)(' . $sep . '([0-9]+(\.[0-9]+)?))?$/';
				// wanted: INTsepINT;
				$pw  = '/^([0-9]+)' . $sep . '([0-9]+)$/';
				$m = array();
				if ( preg_match($px, $t, $m) ) {
					$i[$k] = $m[1] . ($m[4] ? (':' . $m[4]) : ':1');
				} else if ( preg_match($pw, $t, $m) ) {
					$i[$k] = $m[1] . ':' . $m[2];
				} else {
					$i[$k] = $v;
				}
				break;
			// varied complex strings; not sanitized here
			case 'caption':
			case 'url':
			case 'cssurl':
			case 'iimage':
			case 'mtype':
			case 'playpath':
			case 'altvideo':
			case 'classid':
			case 'codebase':
				break;
			// for reference defaults discard any changes,
			// e.g. 'defaulturl'
			default:
				$i[$k] = $v;
				break;
			}
		}
	}
} // End class SWF_params_evh
else :
	wp_die('class name conflict: SWF_params_evh in ' . __FILE__);
endif; // if ( ! class_exists('SWF_params_evh') ) :

/**
 * class handling swf video as widget; uses SWF_put_evh
 */
if ( ! class_exists('SWF_put_widget_evh') ) :
class SWF_put_widget_evh extends WP_Widget {
	// main plugin class name
	const swfput_plugin = 'SWF_put_evh';
	// params helper class name
	const swfput_params = 'SWF_params_evh';
	// an instance of the main plugun class
	protected $plinst;
	
	// default width should not be wider than sidebar, but
	// widgets may be placed elsewhere, e.g. near bottom
	// 216x138 is suggest minimum size in Adobe docs,
	// because flash user settings dialog is clipped if 
	// window is smaller; AFAIK recent plugins refuse to map
	// the context menu rather than let it be clipped.
	// 216 is a bit wide for a sidebar (in some themes),
	// consider 200x150
	const defwidth  = 200; // is 4:3 aspect
	const defheight = 150; //


	public function __construct() {
		$this->plinst = SWF_put_evh::get_instance(false);
	
		$cl = __CLASS__;
		// Label shown on widgets page
		$lb =  __('SWFPut Flash Video', 'swfput_l10n');
		// Description shown under label shown on widgets page
		$desc = __('Flash video (with HTML5 video fallback option) for your widget areas', 'swfput_l10n');
		$opts = array('classname' => $cl, 'description' => $desc);

		// control opts width affects the parameters form,
		// height is ignored.  Width 400 allows long text fields
		// (not as log as most URL's), and informative (long) labels
		$copts = array('width' => 400, 'height' => 500);

		parent::__construct($cl, $lb, $opts, $copts);
	}

	// surely this code cannot run under PHP4, but anyway . . .
	public function SWF_put_widget_evh() {
		$this->__construct();
	}

	public function widget($args, $instance) {
		$opt = $this->plinst->get_widget_plugin_option();
		if ( $opt != 'true' ) {
			return;
		}
		
		$pr = self::swfput_params;
		$pr = new $pr();
		$pr->setnewarray($instance);

		if ( ! $pr->getvalue('width') ) {
			$pr->setvalue('width', self::defwidth);
		}
		if ( ! $pr->getvalue('height') ) {
			$pr->setvalue('height', self::defheight);
		}
		$pr->sanitize();
		$w = $pr->getvalue('width');
		$h = $pr->getvalue('height');
		$bh = $pr->getvalue('barheight');

		$cap = $this->plinst->wt($pr->getvalue('caption'));
		if ( $this->plinst->should_use_ming() ) {
			$uswf = $this->plinst->get_swf_url('widget', $w, $h);
		} else {
			$uswf = $this->plinst->get_swf_binurl($bh);
		}

		$code = 'widget-div';
		$dw = $w + 3;
		// use no class, but do use deprecated align
		$dv = '<div id="'.$code.'" align="center"';
		$dv .= ' style="width: '.$dw.'px">';

		extract($args);

		// note *no default* for title; allow empty title so that
		// user may place this below another widget with
		// apparent continuity (subject to filters)
		$title = apply_filters('widget_title',
			empty($instance['title']) ? '' : $instance['title'],
			$instance, $this->id_base);

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo $dv;
		$this->plinst->put_swf_tags($uswf, $pr);
		if ( $cap ) {
			echo '<p><span align="center">' .$cap. '</span></p>';
		}
		echo '</div>';

		echo $after_widget;
	}

	public function update($new_instance, $old_instance) {
		$pr = self::swfput_params;
		$pr = new $pr();
		
		if ( is_array($old_instance) ) {
			$pr->setnewarray($old_instance);
		}
		if ( is_array($new_instance) ) {
			$pr->setnewarray($new_instance);
		}
		
		$pr->sanitize();
		$i = $pr->getparams();
		if ( is_array($new_instance) ) {
			// for pesky checkboxes; not present if unchecked, but
			// present 'false' is wanted
			foreach ( $i as $k => $v ) {
				if ( ! array_key_exists($k, $new_instance) ) {
					$t = $pr->getdefault($k);
					// booleans == checkboxes
					if ( $t == 'true' || $t == 'false' ) {
						$i[$k] = 'false';
					}
				}
			}
		}

		if ( ! array_key_exists('caption', $i) ) {
			$i['caption'] = '';
		}
		if ( ! array_key_exists('title', $i) ) {
			$i['title'] = '';
		}
		if ( ! $i['width'] ) {
			$i['width'] = self::defwidth;
		}
		if ( ! $i['height'] ) {
			$i['height'] = self::defheight;
		}

		return $i;
	}

	public function form($instance) {
		$wt = 'wptexturize';  // display with char translations
		// still being 5.2 compatible; anon funcs appeared in 5.3
		//$ht = function($v) { return htmlentities($v, ENT_QUOTES, 'UTF-8'); };
		$ht = 'swfput_php52_htmlent'; // just escape without char translations
		// NOTE on encoding: do *not* use JS::unescape()!
		// decodeURIComponent() should use the page charset (which
		// still leaves room for error; this code assumes UTF-8 presently)
		$et = 'rawurlencode'; // %XX -- for transfer

		$pr = self::swfput_params;
		$pr = new $pr(array('width' => self::defwidth,
			'height' => self::defheight));
		$instance = wp_parse_args((array)$instance, $pr->getparams());

		$val = '';
		if ( array_key_exists('title', $instance) ) {
			$val = $wt($instance['title']);
		}
		$id = $this->get_field_id('title');
		$nm = $this->get_field_name('title');
		$tl = $wt(__('Widget title:', 'swfput_l10n'));

		// file select by ext pattern
		$mpat = $this->plinst->get_mfilter_pat();
		// files array from uploads dirs (empty if none)
		$rhu = $this->plinst->r_find_uploads($mpat['m'], true);
		$af = &$rhu['rf'];
		$au = &$rhu['wu'];
		$aa = &$rhu['at'];
		// url base for upload dirs files
		$ub = rtrim($au['baseurl'], '/') . '/';
		// directory base for upload dirs files
		$up = rtrim($au['basedir'], '/') . '/';
		$slfmt =
			'<select class="widefat" name="%s" id="%s" onchange="%s">';
		$sgfmt = '<optgroup label="%s">' . "\n";
		$sofmt = '<option value="%s">%s</option>' . "\n";
		// expect jQuery to be loaded by WP (tried $() invocation
		// but N.G. w/ MSIE. Sheesh.)
		$jsfmt = "jQuery('[id=%s]').val";
		// BAD
		//$jsfmt .= '(unescape(this.options[selectedIndex].value))';
		// better
		$jsfmt .= '(decodeURIComponent(this.options[selectedIndex].value))';
		$jsfmt .= '; return false;';

		?>

		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $wt($instance['caption']);
		$id = $this->get_field_id('caption');
		$nm = $this->get_field_name('caption');
		$tl = $wt(__('Caption:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $instance['url'];
		$id = $this->get_field_id('url');
		$nm = $this->get_field_name('url');
		$tl = $wt(__('Url or media library ID:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php // selects for URLs and attachment id's
		// escape url field id for jQuery selector
		$id = $this->plinst->esc_jqsel($id);
		$js = sprintf($jsfmt, $id);
		// optional print <select >
		if ( count($af) > 0 ) {
			$id = $this->get_field_id('files');
			$k = $this->get_field_name('files');
			$tl = $wt(__('Url from uploads directory:', 'swfput_l10n'));
			printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
			// <select>
			printf($slfmt . "\n", $k, $id, $js);
			// <options>
			printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
			foreach ( $af as $d => $e ) {
				$hit = array();
				for ( $i = 0; $i < count($e); $i++ )
					if ( preg_match($mpat['av'], $e[$i]) )
						$hit[] = &$af[$d][$i];
				if ( empty($hit) )
					continue;
				printf($sgfmt, $ht($d));
				foreach ( $hit as $fv ) {
					$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
					$fv = $ht($fv);
					printf($sofmt, $et($tu), $fv);
				}
				echo "</optgroup>\n";
			}
			// end select
			echo "</select></td></tr>\n";
		} // end if there are upload files
		if ( ! empty($aa) ) {
			$id = $this->get_field_id('atch');
			$k = $this->get_field_name('atch');
			$tl = $wt(__('Select ID from media library:', 'swfput_l10n'));
			printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
			// <select>
			printf($slfmt . "\n", $k, $id, $js);
			// <options>
			printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
			foreach ( $aa as $fn => $fi ) {
				$m = basename($fn);
				if ( ! preg_match($mpat['av'], $m) )
					continue;
				$ts = $m . " (" . $fi . ")";
				printf($sofmt, $et($fi), $ht($ts));
			}
			// end select
			echo "</select></td></tr>\n";
		} // end if there are upload files
		?>

		<?php
		// audio checkbox
		$val = $instance['audio'];
		$id = $this->get_field_id('audio');
		$nm = $this->get_field_name('audio');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Medium is audio (e.g. *.mp3):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $instance['altvideo'];
		$id = $this->get_field_id('altvideo');
		$nm = $this->get_field_name('altvideo');
		$tl = $wt(__('URLs for alternate HTML5 video (optional: .mp4, .webm, .ogv):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $instance['playpath'];
		$id = $this->get_field_id('playpath');
		$nm = $this->get_field_name('playpath');
		$tl = $wt(__('Playpath (rtmp) or co-video (mp3):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $instance['iimage'];
		$id = $this->get_field_id('iimage');
		$nm = $this->get_field_name('iimage');
		$tl = $wt(__('Url of initial image file (optional):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php // selects for URLs and attachment id's
		// escape url field id for jQuery selector
		$id = $this->plinst->esc_jqsel($id);
		$js = sprintf($jsfmt, $id);
		// optional print <select >
		if ( count($af) > 0 ) {
			$id = $this->get_field_id('ifiles');
			$k = $this->get_field_name('ifiles');
			$tl = $wt(__('Load image from uploads directory:', 'swfput_l10n'));
			printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
			// <select>
			printf($slfmt . "\n", $k, $id, $js);
			// <options>
			printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
			foreach ( $af as $d => $e ) {
				$hit = array();
				for ( $i = 0; $i < count($e); $i++ )
					if ( preg_match($mpat['i'], $e[$i]) )
						$hit[] = &$af[$d][$i];
				if ( empty($hit) )
					continue;
				printf($sgfmt, $ht($d));
				foreach ( $hit as $fv ) {
					$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
					$fv = $ht($fv);
					printf($sofmt, $et($tu), $fv);
				}
				echo "</optgroup>\n";
			}
			// end select
			echo "</select></td></tr>\n";
		} // end if there are upload files
		if ( ! empty($aa) ) {
			$id = $this->get_field_id('iatch');
			$k = $this->get_field_name('iatch');
			$tl = $wt(__('Load image ID from media library:', 'swfput_l10n'));
			printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
			// <select>
			printf($slfmt . "\n", $k, $id, $js);
			// <options>
			printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
			foreach ( $aa as $fn => $fi ) {
				$m = basename($fn);
				if ( ! preg_match($mpat['i'], $m) )
					continue;
				$ts = $m . " (" . $fi . ")";
				printf($sofmt, $et($fi), $ht($ts));
			}
			// end select
			echo "</select></td></tr>\n";
		} // end if there are upload files
		?>

		<?php
		// initial as 'bg', alternate checkbox
		$val = $instance['iimgbg'];
		$id = $this->get_field_id('iimgbg');
		$nm = $this->get_field_name('iimgbg');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Use initial image as non-flash alternate:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $wt($instance['width']);
		$id = $this->get_field_id('width');
		$nm = $this->get_field_name('width');
		$tl = sprintf(__('Width (default %u):', 'swfput_l10n'), self::defwidth);
		$tl = $wt($tl);
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $wt($instance['height']);
		$id = $this->get_field_id('height');
		$nm = $this->get_field_name('height');
		$tl = sprintf(__('Height (default %u):', 'swfput_l10n'), self::defheight);
		$tl = $wt($tl);
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $instance['aspectautoadj'];
		$id = $this->get_field_id('aspectautoadj');
		$nm = $this->get_field_name('aspectautoadj');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Auto aspect (e.g. 360x240 to 4:3):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $instance['displayaspect'];
		$id = $this->get_field_id('displayaspect');
		$nm = $this->get_field_name('displayaspect');
		$tl = $wt(__('Display aspect (e.g. 4:3, precludes Auto):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $instance['pixelaspect'];
		$id = $this->get_field_id('pixelaspect');
		$nm = $this->get_field_name('pixelaspect');
		$tl = $wt(__('Pixel aspect (e.g. 8:9, precluded by Display):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $wt($instance['volume']);
		$id = $this->get_field_id('volume');
		$nm = $this->get_field_name('volume');
		$tl = $wt(__('Initial volume (0-100):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
		$val = $instance['play'];
		$id = $this->get_field_id('play');
		$nm = $this->get_field_name('play');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Play on load (else waits for play button):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $instance['loop'];
		$id = $this->get_field_id('loop');
		$nm = $this->get_field_name('loop');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Loop play:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $instance['hidebar'];
		$id = $this->get_field_id('hidebar');
		$nm = $this->get_field_name('hidebar');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Hide control bar initially:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $instance['disablebar'];
		$id = $this->get_field_id('disablebar');
		$nm = $this->get_field_name('disablebar');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Hide and disable control bar:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $instance['allowfull'];
		$id = $this->get_field_id('allowfull');
		$nm = $this->get_field_name('allowfull');
		$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
		$tl = $wt(__('Allow full screen:', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
			value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

		<?php
		$val = $ht($instance['barheight']);
		$id = $this->get_field_id('barheight');
		$nm = $this->get_field_name('barheight');
		$tl = $wt(__('Control bar Height (20-50):', 'swfput_l10n'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>" style="width:16%;"
			type="text" value="<?php echo $val; ?>" /></p>

		<?php
	}
} // End class SWF_put_widget_evh
else :
	wp_die('class name conflict: SWF_put_widget_evh in ' . __FILE__);
endif; // if ( ! class_exists('SWF_put_widget_evh') ) :


/**********************************************************************\
 *  plugin 'main()' level code                                        *
\**********************************************************************/

// Instance not needed (or wanted) if uninstalling; the registered
// uninstall hook is saved by WP in an option so it is presistent,
// and the plugin's static uninstall method will be called.
// Else, make an instance, which triggers running.
if ( ! defined('WP_UNINSTALL_PLUGIN')
	&& $swfput1_evh_instance_1 === null ) {
	$swfput1_evh_instance_1 = SWF_put_evh::instantiate();
}

// End PHP script:
?>
