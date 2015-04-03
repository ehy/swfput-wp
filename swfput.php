<?php
/*
Plugin Name: SWFPut
Plugin URI: http://agalena.nfshost.com/b1/swfput-html5-flash-wordpress-plugin
Description: Add Flash and HTML5 video to WordPress posts, pages, and widgets, from arbitrary URI's or media library ID's or files in your media upload directory tree (including uploads not in the WordPress media library).
Version: 2.9
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


// check for naughty direct invocation; w/o this we'd soon die
// from undefined WP functions anyway, but let's check anyway
if ( basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']) ) {
	die("No, do not invoke this directly.\n");
}

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
	// try to use get_option('blog_charset') only once
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
 * main class flash/HTML5 video for WP pages
 */
if ( ! class_exists('SWF_put_evh') ) :
class SWF_put_evh {
	// web page as of release
	const plugin_webpage = 'http://agalena.nfshost.com/b1/swfput-html5-flash-wordpress-plugin';
	
	// this version
	const plugin_version = '2.9';
	
	// the widget class name
	const swfput_widget = 'SWF_put_widget_evh';
	// parameter helper class name
	const swfput_params = 'SWF_params_evh';
	
	// identifier for settings page
	const settings_page_id = 'swfput1_settings_page';
	
	// option for mce plugin -> iframe video content script
	const optmceplg  = 'swfput_mceifm';
	// TTL for ticket, re. mce plugin -> iframe video content script
	const ttlmceplg  = 86400;
	
	// option group name in the WP opt db
	const opt_group  = '_evh_swfput1_opt_grp';
	// verbose (helpful?) section descriptions?
	const optverbose = '_evh_swfput1_verbose';
	// this is hidden in settings page; used w/ JS for 'screen options'
	const optscreen1 = 'screen_opts_1';
	// WP option names/keys
	// optdisp... -- display areas
	const opth5vprim = '_evh_swfput1_h5pr'; // posts
	const optdispmsg = '_evh_swfput1_dmsg'; // posts
	const optdispwdg = '_evh_swfput1_dwdg'; // widgets no-admin
	const optdisphdr = '_evh_swfput1_dhdr'; // header area
	const opttinymce = '_evh_swfput1_dmce'; // tinymce editor
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
	// this is hidden in settings page; used w/ JS for 'screen options'
	const defscreen1 = 'true';
	// put html5 video alternate as primary content?
	const defh5vprim = 'true'; // changed to true in 2.1.0
	// display opts, widget, inline or both
	 // 1==message | 2==widget | 4==header
	const defdisplay  = 7;
	const disp_msg    = 1;
	const disp_widget = 2;
	const disp_hdr    = 4;
	// when to use video display in tinymce editor
	// changed from always to nonmobile in 2.1.0
	const deftinymce  = 'nonmobile'; // always, nonmobile, never
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

	// Settings page object
	protected $spg = null;

	// php include file sub-directory
	const includedir = 'php-inc';

	// swfput program directory
	const swfputdir = 'evhflv';
	// swfput program binary name
	const swfputbinname = 'evhflv.swf';
	// swfput program php+ming script name
	const swfputphpname = 'mingput.php';
	// swfput program css name
	const swfputcssname = 'obj.css';
	// swfput default video name
	const swfputdefvid = 'default.mp4';
	// swfput program binary path
	protected $swfputbin;
	// swfput program php+ming script path
	protected $swfputphp;
	// swfput program css path
	protected $swfputcss;
	// swfput program default video path
	protected $swfputvid;

	// for a link to an html help doc
	const helphtmlname = 'README.html';
	const helphtml_ref = '#3.1. Form Buttons';
	protected static $helphtml = null;
	// for a link to an pdf help doc
	const helppdfname = 'README.pdf';
	protected static $helppdf = null;

	// settings js subdirectory
	const settings_jsdir = 'js';
	// settings js shortcode editor helper name
	const settings_jsname = 'screens.min.js';
	// settings program js path
	protected $settings_js;
	// JS: name of class to control textare/button pairs
	const js_textpair_ctl = 'evhplg_ctl_textpair';

	// swfput js shortcode editor helper name
	const swfxedjsname = 'formxed.min.js';
	// swfput js shortcode editor helper name
	const swfxpljsname = 'editor_plugin.min.js';
	const swfxpljsname3x = 'editor_plugin3x.min.js';
	
	// html5 video front-end js
	const evhv5vjsdir  = 'evhh5v';
	const evhv5vjsname = 'front.min.js';
	//const evhv5vjsname = 'front.js';
	// swfput js front-end js name prefix:
	const evhv5vjsnpfx = 'evhh5v';
	// html5 video front-end css
	const evhv5vcssdir  = 'evhh5v';
	const evhv5vcssname = 'evhh5v.css';
	const evhv5vcssnpfx = 'evhh5v';
	// html5 video front-end svg files
	const evhv5vsvgdir  = 'evhh5v';
	const evhv5vsvg_bar = 'ctlbar.svg';
	const evhv5vsvg_vol = 'ctlvol.svg';
	const evhv5vsvg_but = 'ctrbut.svg';
	// set to map of svg file names -> URL
	protected $evhv5v_svgs;
	
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
		$t = /* self::swfputdir . '/' . */ self::swfputdefvid;
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
			// Some things that must be *before* 'init'
			// NOTE cannot call current_user_can() because
			// its dependencies might not be ready at this point!
			// Use condition on current_user_can() in the callbacks
			$aa = array($cl, 'on_deactivate');
			register_deactivation_hook($pf, $aa);
			$aa = array($cl, 'on_activate');
			register_activation_hook($pf,   $aa);

			$aa = array($cl, 'on_uninstall');
			register_uninstall_hook($pf,    $aa);

			// add 'Settings' link on the plugins page entry
			$name = plugin_basename($pf);
			$aa = array($cl, 'plugin_page_addlink');
			add_filter('plugin_action_links_' . $name, $aa);
		}

		add_action('init', array($this, 'init_hook_func'));

		add_action('widgets_init', array($cl, 'regi_widget'));//, 1);
	}

	public function __destruct() {
		$this->spg = null;
	}
	
	// get array of defaults for the plugin options; if '$chkonly'
	// is true include only those options associated with a checkbox
	// on the settings page -- useful for the validate function
	protected static function get_opts_defaults($chkonly = false) {
		$items = array(
			self::optverbose => self::defverbose,
			self::optscreen1 => self::defscreen1,
			self::opth5vprim => self::defh5vprim,
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
		
		if ( $chkonly !== true && self::use_tinymce_plugin_ok() ) {
			$items[self::opttinymce] = self::deftinymce;
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
		if ( $this->spg ) {
			return;
		}
		$items = self::get_opt_group();

		// use Opt* classes for page, sections, and fields;
		// these support classes are in separate files as they are
		// not specific to this plugin, and may be used in others
		swfput_paranoid_require_class(self::mk_aclv('OptField'));
		swfput_paranoid_require_class(self::mk_aclv('OptSection'));
		swfput_paranoid_require_class(self::mk_aclv('OptPage'));
		swfput_paranoid_require_class(self::mk_aclv('Options'));
		
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
		$fields[$nf++] = new $Cf(self::opth5vprim,
				self::wt(__('HTML5 video primary:', 'swfput_l10n')),
				self::opth5vprim,
				$items[self::opth5vprim],
				array($this, 'put_h5vprim_opt'));
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
		if ( self::use_tinymce_plugin_ok() )
			$fields[$nf++] = new $Cf(self::opttinymce,
				self::wt(__('Video in post editor:', 'swfput_l10n')),
				self::opttinymce,
				$items[self::opttinymce],
				array($this, 'put_tinymce_opt'));
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
		$this->spg = new $Co($page);
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
			)) /*,
			self::wt(sprintf(
			__('<p>TODO: %s
			</p>', 'swfput_l10n'),
			__('WRITE TIPS', 'swfput_l10n')
			)) */
		);

		// TRANSLATORS: first '%s' is the the phrase
		// 'For more information:'; using translation
		// from default textdomain (WP core)
		$tt = self::wt(sprintf(
			__('<p><strong>%s</strong></p><p>
			More information can be found on the
			<a href="%s" target="_blank">web page</a>.
			Please submit feedback or questions as comments
			on that page.
			</p>', 'spambl_l10n'),
			__('For more information:'),
			self::plugin_webpage
		));
	
		// finagle the "Screen Options" tab
		$h = 'manage_' . $this->spg->get_page_suffix() . '_columns';
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
				'<h3>' . __('Overview') . '</h3>' . $t[0] .
				'<h3>' . __('Tips', 'swfput_l10n') . '</h3>' . $t[1] .
				$tt);
		}
	}

	public function settings_js() {
		$jsfn = self::settings_jsname;
		$j = $this->settings_js;
		$v = self::plugin_version;
        wp_enqueue_script($jsfn, $j, false, $v);
	}

	// This function is placed here below the function that sets-up
	// the options page so that it is easy to see from that function.
	// It exists only for the echo "<a name='aSubmit'/>\n";
	// line which mindbogglingly cannot be printed from
	// Options::admin_page() -- it is somehow *always* stripped out!
	// After hours I cannot figure this out; but, having added this
	// function as the page callback, I can add the anchor after
	// calling $this->spg->admin_page() (which is Options::admin_page())
	// BUT it still does not show in the page if the echo is moved
	// into Options::admin_page() and placed just before return!
	// Baffled.
	public function setting_page_output_callback() {
		$r = $this->spg->admin_page();
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
		$tl = __('SWFPut Video', 'swfput_l10n');
		$tl = self::wt($tl);
		$fn = 'put_xed_form';
		if ( current_user_can('edit_posts') ) {
			add_meta_box($id, $tl, array($cl, $fn), 'post', 'normal');
		}
		if ( current_user_can('edit_pages') ) {
			add_meta_box($id, $tl, array($cl, $fn), 'page', 'normal');
		}
	}

	// on init, e.g. mce plugin in the post-related pages
	public static function hook_admin_init() {
		if ( self::use_tinymce_plugin() ) {
			// tinymce version in 3.0.3 n.g. and next testing
			// version of WP I have is 3.3.1
			$v = (3 << 24) | (3 << 16) | (0 << 8) | 0;
			$ok = self::wpv_min($v);
	
			if ( $ok && current_user_can( 'edit_posts' )
				&& current_user_can( 'edit_pages' ) ) {
				$aa = array(__CLASS__, 'add_mceplugin_js');
				add_filter('mce_external_plugins', $aa);
				$aa = array(__CLASS__, 'filter_mce_init');
				add_filter('tiny_mce_before_init', $aa);
			}
		}
	}

	// filter for any mce plugin init settings needs (and testing)
	public static function filter_mce_init($init_array) {
		// FPO, presently
		if ( false ) {
			//$init_array['wpeditimage_disable_captions'] = true;
			$init_array['keep_styles'] = true;
		}
		return $init_array;
	}

	// filter to add mce plugin javascript
	public static function add_mceplugin_js($plugin_array) {
		// tinymce major version 4 begins in WP 3.9
		$v = (3 << 24) | (9 << 16) | (0 << 8) | 0;
		$shiny = self::wpv_min($v);
		
		$jsfile = $shiny ? self::swfxpljsname : self::swfxpljsname3x;

		$pf = self::mk_pluginfile();
		$pname = 'swfput_mceplugin';
		$t = self::settings_jsdir . '/' . $jsfile;
		$jsfile = plugins_url($t, $pf);
		$plugin_array[$pname] = $jsfile;
		return $plugin_array;
	}

	// add a help tab in the post-related pages
	public static function hook_admin_head() {
		$scr = function_exists('get_current_screen') ?
			get_current_screen() : false;

		// for pages w/ TinyMCE editor, put info
		// needed by the tinymce plugin added in 1.0.9
		// -- use something like wp nonces -- the nonce will
		// not work, the checks fail when made from the
		// iframe content script, also nonces are intended
		// for single use. So, use a prng number; it will be
		// reused any number of times while post with swfput
		// video is being edited, so it is not a nonce.
		// Note this is not rigorous security*, just deflecting
		// mischievous poking at the script w/ odd query string
		// values. Other check values may be used as well.
		// [*] The script does not write to the DB, it only uses
		// get_option and wp_get_attachment_url; but, prefer not
		// to produce arbitrary URLs or attachment info that would
		// result from crafted query --
		// this prng-ticket should do the trick. self::ttlmceplg
		// is an expiration value (TTL) tested in the script against
		// the stored (int)time(). The script fails if its client
		// differs from $_SERVER['REMOTE_ADDR'].
		if ( self::use_tinymce_plugin() ) {
			if ( ! $scr || $scr->base === 'post' ) {
				$u = '' . (function_exists('get_current_user_id') ?
					get_current_user_id() : 0);
				$rn = '' . self::uniq_rand();
				$ticket = array($rn, (int)time(),
					self::ttlmceplg, $_SERVER['REMOTE_ADDR']);
				if ( isset($_SERVER['REMOTE_HOST']) ) {
					$ticket[] = $_SERVER['REMOTE_HOST'];
				}
		
				$opt = get_option(self::optmceplg);
				if ( $opt ) {
					$opt[$u] = $ticket;
					update_option(self::optmceplg, $opt);			
				} else {
					$opt = array($u => $ticket);
					add_option(self::optmceplg, $opt, '', false);
				}
		
				$info = array('a' => ABSPATH, 'i' => $rn, 'u' => $u);
				printf('
					<script type="text/javascript">
						var swfput_mceplug_inf = %s;
					</script>%s',
					json_encode($info), "\n");
			}
		}

		// get_current_screen() introduced in WP 3.1
		// (thus spake codex)
		// I have 3.0.2 to test with, and 3.3.1, nothing in between,
		// so 3.3 will be used as minimum
		$v = (3 << 24) | (3 << 16) | (0 << 8) | 0;
		$ok = self::wpv_min($v);

		include self::mk_pluginincpath('help_txt.php');
		$hlptxt = swfput_get_helptext(self::$helphtml, self::$helppdf);

		// put help tab content, for 3.3.0 or greater . . .
		if ( $ok && $scr ) {
			// nothing specific to widgets; only guessing that
			// edit_theme* are suitable
			if ( $scr->base === 'widgets'
				&& (current_user_can('edit_theme_options')
				||  current_user_can('edit_themes')) ) {
				$scr->add_help_tab(array(
					'id'      => 'help_tab_widgets_swfput',
					'title'   => __('SWFPut Video Player', 'swfput_l10n'),
					'content' => self::wt($hlptxt)
					// content may be a callback
					)
				);
			} else if ( $scr->base === 'post'
				&& (current_user_can('edit_posts')
				||  current_user_can('edit_pages')) ) {
				$scr->add_help_tab(array(
					'id'      => 'help_tab_posts_swfput',
					'title'   => __('SWFPut Video', 'swfput_l10n'),
					'content' => self::wt($hlptxt)
					// content may be a callback
					)
				);
			}

		// . . . or, lesser
		} else {
			global $current_screen;
			add_contextual_help($current_screen,
				'<h6>'.__('SWFPut Video Player', 'swfput_l10n').'</h6>'
				. self::wt($hlptxt)
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
			$v = self::plugin_version;
	        wp_enqueue_script($jsfn, $jsfile, array('jquery'), $v);
	    }
	}

	// deactivate cleanup
	public static function on_deactivate() {
		if ( ! current_user_can('activate_plugins') ) {
			return;
		}

		$name = plugin_basename(self::mk_pluginfile());
		$aa = array(__CLASS__, 'plugin_page_addlink');
		remove_filter('plugin_action_links_' . $name, $aa);

		self::unregi_widget();

		$aa = array(__CLASS__, 'validate_opts');
		unregister_setting(self::opt_group, // option group
			self::opt_group, // opt name; using group passes all to cb
			$aa);
	}

	// activate setup
	public static function on_activate() {
		if ( ! current_user_can('activate_plugins') ) {
			return;
		}

		add_action('widgets_init', array(__CLASS__, 'regi_widget'), 1);

		// try to write ABSPATH to a php var in an included php file,
		// for secure use by the script that produces iframe content
		// for the tinymce plugin.
		//
		// Whether this can be done is uncertain, and we are silent
		// about failure, the consequence of which is that the
		// aforementioned script will do something else that requires
		// WP_PLUGIN_DIR to be descended from the WP root dir, and
		// will fail if it is not.
		if ( ! defined('ABSPATH') ) {
			return;
		}
		
		$fn = self::mk_pluginsubpath('wpabspath.php');
		$fh = fopen($fn, 'r+b');
		if ( ! $fh ) {
			return;
		}
		$cont = fread($fh, filesize($fn));
		$pat =
			'/([ \t]*\\$wpabspath[ \t]*=[ \t]*\').+(\'[ \t]*;[ \t]*)/m';

		if ( $cont && preg_match($pat, $cont) ) {
			$cont = explode("\n", $cont);
			$cont = preg_replace($pat, '\1' . ABSPATH . '\2', $cont);
			if ( $cont && ! fseek($fh, 0, SEEK_SET) && ftruncate($fh, 0) ) {
				$cont = implode("\n", $cont);
				// no need to check return: failure allowed
				fwrite($fh, $cont);
			}
		}
		
		fclose($fh);
	}

	// uninstall cleanup
	public static function on_uninstall() {
		if ( ! current_user_can('install_plugins') ) {
			return;
		}

		self::unregi_widget();
		
		$opts = self::get_opt_group();
		if ( $opts && $opts[self::optdelopts] != 'false' ) {
			delete_option(self::opt_group);
		}
		// This does not need to be conditional
		delete_option(self::optmceplg);
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

		// for video in tinymce plugin: remove stale tickets;
		// the option is a map user_id -> ticket-data to allow
		// multiple users editing (wonder if this code will ever
		// get excersized); update if any are stale, or delete
		// if none are fresh
		$pf = get_option(self::optmceplg);
		if ( $pf ) {
			$upd = array();
			$doupd = false;
			$t = (int)time();
			foreach ( $pf as $k => $v ) {
				if ( (int)$v[2] > ($t - (int)$v[1]) ) {
					$upd[$k] = $v;
				} else {
					$doupd = true;
				}
			}
			
			if ( empty($upd) ) {
				delete_option(self::optmceplg);
			} else if ( $doupd ) {
				update_option(self::optmceplg, $upd);
			}
		}

		$pf = self::mk_pluginfile();
		// admin or public invocation?
		$adm = is_admin();

		$cl = __CLASS__;

		if ( $adm ) {
			// hook&filter to make shortcode form for editor
			if ( self::get_posts_code_option() === 'true' ) {
				$aa = array($cl, 'hook_admin_init');
				add_action('admin_init', $aa);
				$aa = array($cl, 'hook_admin_head');
				add_action('admin_head-post.php', $aa);
				add_action('admin_head-post-new.php', $aa);
				add_action('admin_head-widgets.php', $aa);
				$aa = array($cl, 'hook_admin_menu');
				add_action('admin_menu', $aa);
				$aa = array($cl, 'filter_admin_print_scripts');
				add_action('admin_print_scripts', $aa);
			}

			// Settings/Options page setup
			if ( current_user_can('manage_options') ) {
				$this->init_settings_page();
			}
		} else { // if ( $adm )
			$stfn = self::evhv5vcssnpfx . '_css';
			$t = self::evhv5vcssdir . '/' . self::evhv5vcssname;
			$stfile = plugins_url($t, $pf);
			$t = self::plugin_version;
			wp_enqueue_style($stfn, $stfile, false, $t);

			$jsfn = self::evhv5vjsnpfx . '_js';
			$t = self::evhv5vjsdir . '/' . self::evhv5vjsname;
			$jsfile = plugins_url($t, $pf);
			$t = self::plugin_version;
	        wp_enqueue_script($jsfn, $jsfile, false, $t);

			$t = self::evhv5vsvgdir;
			$this->evhv5v_svgs = array(
				self::evhv5vsvg_bar =>
					plugins_url($t . '/' . self::evhv5vsvg_bar, $pf),
				self::evhv5vsvg_vol =>
					plugins_url($t . '/' . self::evhv5vsvg_vol, $pf),
				self::evhv5vsvg_but =>
					plugins_url($t . '/' . self::evhv5vsvg_but, $pf)
			);
		}

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

		if ( ! isset($WP_textdomain_done) && defined('WP_LANG_DIR') ) {
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
		$ta = self::get_opts_defaults(true);
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
				// 'radio' multi-choice
				case self::opttinymce:
					switch ( $ot ) {
						case 'always':
						case 'nonmobile':
						case 'never':
							$a_out[$k] = $ot;
							$nupd += ($ot === $oo) ? 0 : 1;
							break;
						default:
							$e = __('bad choice: "%s"', 'swfput_l10n');
							$e = sprintf($e, $ot);
							self::errlog($e);
							add_settings_error(self::ht($k),
								sprintf('%s[%s]', self::opt_group, $k),
								self::ht($e), 'error');
							$a_out[$k] = $oo;
							$nerr++;
							break;
					}
					break;
				// hidden opts for 'screen options' -- boolean
				case self::optscreen1:
					$a_out[$k] = ($ot == 'false') ? 'false' : 'true';
					break;
				case self::optverbose:
				case self::opth5vprim:
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
						add_settings_error(self::ht($k),
							sprintf('%s[%s]', self::opt_group, $k),
							self::ht($e), 'error');
						$a_out[$k] = $oo;
						$nerr++;
					} else {
						$a_out[$k] = $ot;
						$nupd += ($oo === $ot) ? 0 : 1;
					}
					break;
				default:
					$e = sprintf(
						__('bad key in option validation: "%s"', 'swfput_l10n')
						, $k);
					self::errlog($e);
					add_settings_error(self::ht($k),
						sprintf('%s[%s]',
							self::opt_group, self::ht($k)),
						self::ht($e), 'error');
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

		// coopt this proc to put 'screen options' hidden opt:
		$eid = self::optscreen1 . '_ini';
		$val = self::get_screen1_option() == 'true' ? "true" : "false";

		printf('<input id="%s" value="%s" type="hidden">%s',
			$eid, $val, "\n"
		);

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
		$t = self::wt(__('These options control video placement.
			</p><p>
			The first option, "HTML5 video primary,"
			controls whether HTML5 video will be placed as
			primary or fallback (alternate) content. If this
			is set on then flash video
			will be placed as fallback content when both
			types have been specified.
			Note that if the web browser supports HTML5 video
			but cannot play any of the video
			types specified, the HTML5 video controller script
			will try to swap fallback flash video to the
			active role. This is useful
			if flash video sources had been provided in
			the setup form (or if .MP4 had been given
			for HTML5 video, will be passed to the flash player
			by the player script).
			</p><p>
			By default HTML5 video is primary and flash video
			is fallback content. (Prior to SWFPut 2.1 flash
			would be placed as primary content by default.)
			</p><p>
			Note that at present the major graphical browsers
			do <em>not</em> all support the same set of video
			types for their HTML5 video players.
			To reliably use HTML5 video as primary content,
			it is best to prepare the video in .MP4, .OGG (.OGV),
			and .WEBM container formats with suitable codecs.
			(The posts/pages editor page has a help button which
			should have a "SWFPut Video Form" tab
			with more explanation.)
			</p><p>
			The next two options allow the video content
			to be completely disabled.
			If the placement
			of video must be switched on or off, for either
			posts (and pages) or widgets
			or both, these are the options to use.
			When the plugin shortcode is disabled the
			video elements that would have been placed are
			replaced by a notice with the form
			"[A/V content &lt;caption&gt; disabled],"
			where "&lt;caption&gt;"
			is any caption that was included with the shortcode,
			or empty if there was no caption.
			'
			, 'swfput_l10n'));
		printf('<p>%s</p>%s', $t, "\n");

		if ( self::use_tinymce_plugin_ok() ) {
			$t = self::wt(__('
			The "Video in post editor" multiple choice option
			controls the display of video in the post/page
			editor. This is only effective if the "TinyMCE"
			editor included with WordPress is in use, and only
			when the "Visual" tab is selected.
			'
				, 'swfput_l10n'));
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

		$t = self::wt(__('
			<strong>These options are deprecated and will be
			removed in a future release. Do not use these.</strong>
			</p><p>
			These options select 
			how video may be placed in posts or pages.
			Use shortcodes for any new posts (and preferably
			for existing posts) that should include
			the video players of this plugin.
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

		$t = self::wt(__('
			<strong>These options are deprecated and will be
			removed in a future release. Do not use these.</strong>
			</p><p>
			These options select 
			how video may be placed in widget areas.
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

		// coopt this proc to put 'screen options' hidden opt:
		$group = self::opt_group;
		$eid = self::optscreen1;
		$enm = "{$group}[{$eid}]";
		$val = self::get_screen1_option() == 'true' ? "true" : "false";

		printf('<input id="%s" name="%s" value="%s" type="hidden">%s',
			$eid, $enm, $val, "\n"
		);
	}

	// callback, dynamic use of php+ming?
	public function put_useming_opt($a) {
		$tt = self::wt(__('Use SWF script if PHP+Ming is available', 'swfput_l10n'));
		$k = self::optuseming;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, put SWF in head?
	public function put_tinymce_opt($a) {
		if ( ! self::use_tinymce_plugin_ok() ) {
			printf('<strong>%s</strong>%s',
				self::wt(__('The SWFPut editor plugin is not supported in this installation', 'swfput_l10n')),
				"\n");
			return;
		}

		$tt = self::wt(__('When to display video in post editor', 'swfput_l10n'));
		$k = self::opttinymce;
		$group = self::opt_group;
		$va = array(
			array(__('Always display video in the post editor', 'spambl_l10n'), 'always'),
			array(__('Only when the browser platform is not mobile', 'spambl_l10n'), 'nonmobile'),
			array(__('Never display video in the post editor', 'spambl_l10n'), 'never')
		);

		$v = trim('' . $a[$k]);

		foreach ( $va as $oa ) {
			$txt = self::wt($oa[0]);
			$val = $oa[1];
			$chk = '';
			$chk = $v === $val ? 'checked="checked" ' : '';

			printf(
				'%s<label><input type="radio" id="%s" ', "\n", $k
			);
			printf(
				'name="%s[%s]" value="%s" %s/>', $group, $k, $val, $chk
			);
			printf(
				'&nbsp;%s</label><br />%s', $txt, "\n"
			);
		}
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

	// callback, place "alternate" HTML <video> as primary content?
	public function put_h5vprim_opt($a) {
		$tt = self::wt(__('Place HTML5 video as primary content', 'swfput_l10n'));
		$k = self::opth5vprim;
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
	// the WP post editor
	public static function put_xed_form() {
		// EH: 20.07.2014
		// Form markup and code moved to file xed_form.php
		require self::mk_pluginincpath('xed_form.php');
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
			// because this text takes the place of the video program
			// when disabled by a plugin option or not supported.
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
		if ( $code === "" ) {
			$code = 'swfput_div';
		}
		
		$swf = $this->get_swf_url();

		$dw = $w + 3;

		// EH: 2.2
		// use class widget, do not use deprecated align
		$dv = 'class="widget align' 
			. $pr->getvalue('align'). '" style="width: '
			. $dw . 'px; max-width: 100%"';
		$c = '';
		// Note '!=' -- not '!=='
		if ( $content != null ) {
			$c = do_shortcode($content);
			$c = '</p><p><span class="caption">'
				. $c . '</span></p><p>';
		}

		$ids = $this->get_div_ids($code);
		$em  = $this->get_player_elements($swf, $pr, $ids);
		return $this->get_div($ids, $dv, $c, $em);
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
			// because this text takes the place of a video program
			// when disabled by a plugin option or not supported.
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
		if ( $code === "" ) {
			$code = 'swfput_div';
		}
		
		$swf = $this->get_swf_url();

		$dw = $w + 0;

		// use class that WP uses for e.g. images
		$dv = ' class="wp-caption align'
			. $pr->getvalue('align') . '"';
		$dv .= ' style="width: '.$dw.'px; max-width: 100%"';
		$c = '';
		// Note '!=' -- not '!=='
		if ( $content != null ) {
			$c = do_shortcode($content);
			$pr->setvalue('caption', $c);
			$c = '<p class="wp-caption-text">' . $c . '</p>';
		}

		$ids = $this->get_div_ids($code);
		$em  = $this->get_player_elements($swf, $pr, $ids);
		return $this->get_div($ids, $dv, $c, $em);
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
		
		$swf = $this->get_swf_url();
		
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
				$url = wp_get_attachment_url($id);
				if ( is_wp_error($url) ) {
					$out .= $line . $sep;
					self::errlog('failed URL of attachment ' . $id);
				} else if ( ! preg_match($mpat['av'], $url) ) {
					$out .= $line . $sep;
				} else {
					$pr->setvalue('url', $url);
					$ids = $this->get_div_ids('swfput_sed');
					$em  = $this->get_player_elements($swf, $pr, $ids);

					$dv = 'style="width: '.($w+0).'px; max-width: 100%"'
						. ' class="wp-caption align'
						. $pr->setvalue('align') . '"';
					$code = 'swfput_div';
					$s = $this->get_div($ids, $dv, '', $em) . $sep;
					$out .= $s . $line . $sep;
				}
			} else {
				$out .= $line . $sep;
			}
		}
		return $out;
	}
	
	// get video <div>+<script> id strings
	// $base is base od <div> id,
	public function get_div_ids($base) {
		$rndnum = self::uniq_rand();
		$divid = sprintf('d_%s_%06u', $base, $rndnum);
		$objid = sprintf('o_%s_%06u', $base, $rndnum);
		return array(
			$divid, $objid, 'va_' . $objid, 'ia_' . $objid, $rndnum
		);
	}
	
	// get video <div>+<script> strings
	// $divids are returned from get_div_ids($base),
	// $divatts are appended to <div> after id,
	// $cap is caption within <div> below video, and
	// $vidtags are the array returned by get_player_elements(),
	// with key 'el'; caller may also add 'enter' and 'leave'
	// to place markup after the opening and before the closing
	// of the div
	public function get_div($divids, $divatts, $cap, $vidtags) {
		$opfx = self::evhv5vjsnpfx;

		$dv = sprintf('id="%s" %s', $divids[0], $divatts);

		return sprintf('
			<div %s>
			%s%s%s
			%s</div><!-- %s -->
			<script type="text/javascript">
				new %s_sizer("%s", "%s", "%s", "%s", false);
			</script>',
			$dv,
			isset($vidtags['enter']) ? $vidtags['enter'] : '',
			$vidtags['el'], $cap,
			isset($vidtags['leave']) ? $vidtags['leave'] : '',
			$divids[0],
			$opfx, $divids[0], $divids[1], $divids[2], $divids[3]);
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
		
		return $pf;
	}
	
	
	// use mk_plugindir() and append separator and argument
	// $addlpath which is expected to name a subdir, a file,
	// both, or empty string (default) to get the dir w/ sep.
	public static function mk_pluginsubpath($addlpath = '') {
		return self::mk_plugindir() . '/' . ltrim($addlpath, '/');
	}

	// use mk_pluginsubpath() to get plugin's include dir,
	// append $addlpath which is expected to name a file,
	// or empty string (default) to get the dir w/ sep.
	public static function mk_pluginincpath($addlpath = '') {
		$d = self::includedir;
		return self::mk_pluginsubpath($d) . '/' . ltrim($addlpath, '/');
	}

	// help for plugin file path/name; __FILE__ alone
	// is not good enough -- see comment in body of mk_plugindir()
	public static function mk_pluginfile() {
		if ( self::$pluginfile !== null ) {
			return self::$pluginfile;
		}
	
		$pf = self::mk_pluginsubpath(basename(__FILE__));
		
		// store and return corrected file path
		return self::$pluginfile = $pf;
	}
	
	// help for swf player file path/name; it is
	// contained in the plugin directory
	public static function mk_playerdir() {
		return self::mk_pluginsubpath(self::swfputdir);
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
		// 1.0.8 -- remove mp3: 'av' =>'/.*\.(flv|f4v|m4v|mp4|mp3)$/i'
		// 'm' => '/.*\.(flv|f4v|m4v|mp4|mp3|swf|png|jpg|jpeg|gif)$/i'
		// TODO: build from extensions option
		return array('av' =>'/.*\.(flv|f4v|m4v|mp4)$/i',
			'h5av' =>'/.*\.(m4v|mp4|og[gv]|webm)$/i',
			'i' => '/.*\.(png|jpg|jpeg|gif)$/i',
			'm' => '/.*\.(flv|f4v|m4v|mp4|og[gv]|webm|png|jpg|jpeg|gif)$/i'
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
	
	// WP provides a MSIE test, but use this for more control; also,
	// recent MSIE no longer have "MSIE" in the agent string[*] which
	// must be an assertion that it is now compatible and no longer
	// has special needs, therefore this test need not be comprehensive.
	// [*] ``Trident'' is being used, and some MS dev page I found while
	// web-searching explained it as being indeed to defeat MSIE
	// identification, so let 'em have it their way.
	public static function is_msie() {
		static $is_so = null;
		if ( $is_so === null ) {
			// Occasionally 'HTTP_USER_AGENT' is not set in $_SERVER,
			// and if that is the case, just say yes.
			if ( ! isset($_SERVER['HTTP_USER_AGENT']) ) {
				$is_so = true;
			} else {
				$r = preg_match('/\bMSIE\b/', $_SERVER['HTTP_USER_AGENT']);
				$is_so = $r ? true : false;
			}
		}
		return $is_so;
	}
	
	// V. 1.0.9 added video display in the TinyMCE post/page editor,
	// and this checks whether it is OK to use it, e.g.
	// the current WP version is suitable.
	public static function use_tinymce_plugin_ok() {
		// tinymce version in 3.0.3 n.g. and next testing
		// version of WP I have is 3.3.1
		$v = (3 << 24) | (3 << 16) | (0 << 8) | 0;
		return self::wpv_min($v);
	}
	
	// V. 1.0.9 added video display in the TinyMCE post/page editor,
	// and this checks the settings page option controlling when
	// it is used, and returns boolean.
	public static function use_tinymce_plugin() {
		if ( ! self::use_tinymce_plugin_ok() ) {
			return false;
		}

		$opt = self::get_tinymce_option();
		
		switch ( $opt ) {
			case 'always':
				return true;
			case 'nonmobile':
				if ( function_exists('wp_is_mobile') ) {
					return ! wp_is_mobile();
				}
				return true;
			case 'never':
			default:
				return false;
		}
		
		return false;
	}

	// Get a (almost certainly) unique random number:
	// **NOT** for security, only probable uniqueness
	// for e.g., element id attributes.
	// Note PHP has uniqid(), but docs example says:
	// ``[...] the more_entropy parameter, which is 
	// required on some systems[...]'' which makes me
	// just uncertain enough to want to use this instead.
	public static function uniq_rand($maxtries = 2048) {
		static $rndmap = null;
		if ( $rndmap === null ) {
			$rndmap = array();
		}

		if ( ! is_int($maxtries) || $maxtries < 1 ) {
			$maxtries = 2048;
		}

		$rndnum = rand();
		for ( $i = 0; $i < $maxtries; $i++ ) {
			if ( $rndnum && ! array_key_exists($rndnum, $rndmap) ) {
				$rndmap[$rndnum] = 1;
				break;
			}
			$rndnum = rand();
		}

		return $rndnum;
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
		$cdir = getcwd();
		if ( $au && chdir($au['basedir']) ) {
			$ao = self::r_find_files('.', $pat, $follow);
			chdir($cdir);
		}

		return array(
			'uploadfiles' => $ao,
			'uploadsdir' => $au,
			'medialib' => $aa
		);
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

	// for 'screen options' tab: hidden, no form item
	public static function get_screen1_option() {
		return self::opt_by_name(self::optscreen1);
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

	// get the HTML video as primary option
	public static function get_h5vprim_option() {
		return self::opt_by_name(self::opth5vprim);
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
	public static function get_tinymce_option() {
		if ( self::use_tinymce_plugin_ok() )
			return self::opt_by_name(self::opttinymce);
		return 'never';
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

	// return ming+php script address for server w/ ming module
	public function get_swf_srcurl() {
		return $this->swfputphp;
	}

	// return compiled .swf program address
	public function get_swf_binurl() {
		return $this->swfputbin;
	}

	// return one or the other of the two above
	public function get_swf_url() {
		if ( self::should_use_ming() ) {
			return $this->get_swf_srcurl();
		}

		return $this->get_swf_binurl();
	}

	// the swf player use a small bit of css for e.g. net error
	// reporting; get its address
	public function get_swf_css_url() {
		return $this->swfputcss;
	}

	// the swf player directory should have a small default video file;
	// get its address
	public function get_swf_default_video_url() {
		return $this->swfputvid;
	}

	// expand and check user input URL arg from setup form, return
	// false if n.g.
	public function check_expand_video_url($url, $defaulturl = 'default') {
		if ( preg_match('/^0*[1-9][0-9]*$/', $url) ) {
			// expand WP media lib ID
			$url = wp_get_attachment_url(ltrim($url, '0'));
			if ( ! $url ) {
				$url = '';
				self::errlog('rejected video url media ID');
			}
		}
		if ( $url === '' ) {
			$url = $defaulturl;
			if ( $url === 'default' ) {
				$url = $this->get_swf_default_video_url();
			} else if ( $url === '' ) {
				// allow caller to permit empty url by placing
				// empty string in default
				return $url;
			}
		}
		
		$achk = array(
			'requirehost' => false, // can use orig host
			'requirepath' => true,
			'rejfrag' => true,
			// no, don't try to match extension; who knows?
			//'rxpath' => '/.*\.(flv|f4v|mp4|m4v|mp3)$/i',
			'rxproto' => '/^(https?|rtmp[a-z]{0,2})$/'
			);
		return self::check_url($url, $achk);
	}

	// return array with media elements as string in ['el']
	public function get_player_elements($uswf, $par, $ids = null) {
		extract($par->getparams());
		$ming = self::should_use_ming();
		$esc = true;
		
		if ( $ids === null ) {
			$ids  = $this->get_div_ids('SWFPut_vid');
		}
		$id   = $ids[1]; // object
		$idav = $ids[2]; // alternate video
		$idai = $ids[3]; // alternate img

		if ( ! $uswf ) {
			$uswf = $this->get_swf_url();
		}

		$fesc = 'rawurlencode';
		if ( isset($esc_t) && $esc_t === 'plus' ) {
			$fesc = 'urlencode';
		}

		//$ut = $this->check_expand_video_url($url, $defaulturl);
		$flurl = $ut = $this->check_expand_video_url($url);
		if ( $ut === false ) {
			self::errlog('rejected URL: "' . $url . '"');
			$flurl = $ut = $this->check_expand_video_url('');
			//return '<!-- SWF embedding declined:  URL displeasing -->';
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
		$achk = array(
			'requirehost' => false, // can use orig host
			'requirepath' => true,
			'rejfrag' => true,
			// no, don't try to match extension; who knows?
			//'rxpath' => '/.*\.(flv|f4v|mp4|m4v|mp3)$/i',
			'rxproto' => '/^(https?|rtmp[a-z]{0,2})$/'
			);
		$e2url = self::check_url($ut, $achk, 'rawurlencode');
		if ( $esc == true ) {
			$e2url = $fesc($e2url);
		}

		$w = $width; $h = $height;
		// Added v. 1.0.7; 2014/01/24:
		// if wp_is_mobile is a defined function (wp 3.4?), then
		// honor new param 'mobiwidth' to set the dimensions
		// (proportionally to regular WxH) for mobile devices
		// user can set $mobiwidth 0 to disable this
		$mob = 'false';
		if ( function_exists('wp_is_mobile') && wp_is_mobile() ) {
			$mob = 'true';
			if ( $mobiwidth > 0 ) {
				$h = (int)($h * $mobiwidth / $w);
				$w = $mobiwidth;
			}
		}

		if ( $cssurl === '' )
			$cssurl = $this->get_swf_css_url();
		/* $achk = array(
			'requirehost' => false, // can use orig host
			'requirepath' => true,
			'rejuser' => true,
			'rejquery' => true,
			'rejfrag' => true,
			'rxpath' => '/.*\.css$/i',
			'rxproto' => '/^https?$/'
			);
		$ut = self::check_url($cssurl, $achk); */
		$ut = $this->check_expand_video_url($cssurl, false);
		if ( ! $ut ) {
			self::errlog('rejected URL: "' . $url . '"');
			$ut = $this->get_swf_css_url();
			//return '<!-- SWF embedding declined:  URL displeasing -->';
		}
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

		// prepare the preload attr: one special value 'image'
		if ( ! isset($preload) ) {
			$preload = $par->getdefault('preload');
		}
		switch ( $preload ) {
			case 'none':
			case 'metadata':
			case 'auto':
				break;
			case 'image':
			default:
				$preload = ($iimgunesc == '') ? 'metadata' : 'none';
				break;
		}
		
		$playpath = ($esc == true) ? $fesc($playpath) : $playpath;
		
		// query vars
		$qv = sprintf('ST=%s&amp;WI=%u&amp;HI=%u&amp;IDV=%s&amp;FN=%s&amp;II=%s&amp;F2=%s',
			$cssurl, $w, $h, $playpath, $url, $iimage, $e2url);
		$qv .= sprintf('&amp;PL=%s&amp;HB=%s&amp;VL=%u&amp;LP=%s&amp;DB=%s',
			$play, $hidebar, $volume, $loop, $disablebar);
		$qv .= sprintf('&amp;AU=%s&amp;AA=%s&amp;DA=%s&amp;PA=%s',
			$audio, $aspectautoadj, $displayaspect, $pixelaspect);
		$qv .= sprintf('&amp;BH=%s&amp;PLD=%s',
			$barheight, $preload);

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

		// 1.0.7, 2014/01/27 -- alongside the elements, also
		// build an array for JS (to be json encoded)
		$jatt = array();

		// alternates
		$altimg = '';
		if ( $iimgbg == 'true' && $iimgunesc != '' ) {
			$viid = '';
			$jatt['a_img'] = array(
				'width' => $w, 'height' => $h,
				'id'    => $idai,
				'src'   => self::ht($iimgunesc),
				'alt'   => self::wt(
				  __('Video playback is not available', 'swfput_l10n')
				)
			);
			if ( $idai != '' ) {
				$viid = sprintf(' id="%s"', $idai);
			}
			$fmt = '%s<img%s src="%s" alt="%s" width="%u" height="%u" '
				. 'style="margin-left: auto; margin-right: auto;">';
			$altimg = sprintf($fmt, "\n\t\t\t", $viid,
				$jatt['a_img']['src'],
				$jatt['a_img']['alt'],
				$w, $h
			);
		} else {
			$jatt['a_img'] = '';
		}

		// v. 1.0.8, H5V as primary content is an option:
		$h5v_fallback = self::get_h5vprim_option() == 'false'
			? true : false;
		$h5v = $h5vclose = '';

		// altvideo is a misleading name from when H5 video was
		// first added as fallback only. As of 2.1 it is primary
		// content by default.
		// IAC, if URLs were not specified, try flash URL which
		// may be mp4 or flv; if it's flv then H5 will simply fail
		if ( $altvideo == '' ) {
			$altvideo = $flurl;
		}
		if ( $altvideo != '' ) {
			// vars for alternate h5 video
			$aspect = $displayaspect;
			$barwidth = $w;
			$vstd = compact(
				"play", "loop", "volume", "preload",
				"hidebar", "disablebar",
				"aspectautoadj", "aspect",
				"displayaspect", "pixelaspect",
				"barheight", "barwidth",
				"allowfull", "mob"
			);

			$viid = '';
			$vdid = '';
			$jatt['a_vid'] = array(
				'width'     => $w, 'height' => $h,
				'id'        => $idav,
				'poster'    => self::ht($iimgunesc),
				'controls'  => 'true',
				'preload'   => $preload,
				'autoplay'  => $play, // CHECK for h5v player
				'loop'      => $loop,
				'srcs'      => array(),
				// added in 1.0.8 for new h5 video program
				'fallback'	=> $h5v_fallback ? 'true' : 'false',
				'uniq'		=> ''.$ids[4],
				'parentdiv'	=> $ids[0],
				'barheight'	=> $barheight,
				'barwidth'	=> $barwidth,
				'aspect'	=> $displayaspect,
				'altmsg'	=> 'Control bar load failed.',
				'std'		=> $vstd
			);
			if ( $idav != '' ) {
				$viid = sprintf(' id="%s"', $idav);
				$vdid = sprintf(' id="aux_%s"', $idav);
			}
			// div added in 1.0.8 for new h5 video program
			// TODO: move css classname to class-constant
			$vd = "\n\t\t\t".'<div'.$vdid.' class="evhh5v_vidobjdiv">';
			$vd .= "\n\t\t\t".'<video'.$viid.' controls';
			$vd .= ' preload="' . $preload . '"';
			// cannot use autoplay attr.: video will be played, even
			// when <video> is placed as fallback content and flash is
			// loaded in the primary <object>! In fact, fallback content
			// is not disabled at all, it merely is not shown; so,
			// with autoplay the audio is heard even though the video
			// is not shown; Auto play behavior will be implemented
			// in the JS video controller added in 1.0.8, with the loss
			// that where JS is disabled and flash is not available
			// the <video>, in native mode, will not autoplay even
			// if user set that option.
			if ( false && $play == 'true' ) {
				$vd .= ' autoplay';
			}
			if ( $loop == 'true' ) {
				$vd .= ' loop';
			}
			if ( $iimgunesc != '' ) {
				$vd .= sprintf(' poster="%s"', $jatt['a_vid']['poster']);
			}
			$vd .= sprintf(' width="%u" height="%u">', $w, $h);

			// allow multiple video src, separated by pipe
			$altvideo = trim($altvideo, " \t|");
			$av = explode('|', $altvideo);

			// format for source elements
			$fmt = "\n\t\t\t" . '<source src="%s"%s>';
			// place sources
			foreach ( $av as $src ) {
				// allow '?' separated type string
				if ( ($src = trim($src, " \t?")) === '' ) {
					continue;
				}

				$typ = ''; // might remain empty (or not)
				$jsa = array('type' => '');
				$tv = explode('?', $src);

				// leave off src
				$src = trim($tv[0]);
				$ut = $this->check_expand_video_url($src);
				if ( ! $ut ) {
					self::errlog('rejected HTML video URL: "' . $src . '"');
					continue;
				}

				// check user-supplied type
				if ( ! isset($tv[1]) ) {
					// not given: infer from suffix,
					// patterns always subject to revision
					$pats = array(
						'/.*\.(mp4|m4v|mv4)$/i',
						'/.*\.(og[gv]|vorbis)$/i',
						'/.*\.(webm|wbm|vp[89])$/i'
					);
					if ( preg_match($pats[0], $ut) ) {
						$tv[1] = 'video/mp4';
					} else if ( preg_match($pats[1], $ut) ) {
						$tv[1] = 'video/ogg';
					} else if ( preg_match($pats[2], $ut) ) {
						$tv[1] = 'video/webm';
					}
					// not fatal if not found
					if ( isset($tv[1]) ) {
						$jsa['type'] = $tv[1];
						$typ = sprintf(' type="%s"', $jsa['type']);
					}
				} else {
					if ( ($tv[1] = trim($tv[1])) !== '' ) {
						$tv[1] = self::clean_h5vid_type($tv[1]);
						// clean_h5vid_type may return '' on error,
						// but playback most often works w/o type
						if ( $tv[1] !== '' ) {
							$jsa['type'] = $tv[1];
							$typ = sprintf(' type="%s"', $jsa['type']);
						}
					}
				}

				$jsa['src'] = self::ht($ut);
				$vd .= sprintf($fmt, $jsa['src'], $typ);
				$jatt['a_vid']['srcs'][] = $jsa;
			}
			
			$h5v = $vd;

			// place as alt the altimg, or message string
			$jatt['a_vid']['altmsg'] = self::wt("\n\t\t" .
				__('Video playback is not available.', 'swfput_l10n')
			);
			
			if ( $altimg == '' ) {
				$altimg = $jatt['a_vid']['altmsg'];
			}

			$h5vclose = sprintf("\n\t\t\t</video>\t%s\n\t\t\t</div>%s",
				$this->get_h5vjs_tags($jatt['a_vid'], $id),
				"<!-- aux -->\n\t\t\t"
			);
		} else {
			$jatt['a_vid'] = '';
		}

		// build in $obj the flash related elements
		$obj = '';
		
		if ( $id != '' ) {
			$id = sprintf(' id="%s"', $id);
		}
		if ( self::is_msie() ) { 
			$obj = sprintf('
			<object%s classid="%s" codebase="%s" width="%u" height="%u">
			<param name="data" value="%s?%s">
			', $id, $classid, $codebase, $w, $h, $uswf, $pv);
		} else {
			$typ = $mtype ? $mtype :'application/x-shockwave-flash';

			$obj = sprintf('
			<object%s width="%u" height="%u" type="%s" data="%s?%s"%s>
			', $id, $w, $h, $typ, $uswf, $pv, ''); // ' typemustmatch');
		}

		$obj .= sprintf('<param name="play" value="%s">
			<param name="quality" value="%s">
			<param name="allowFullScreen" value="%s">
			<param name="allowScriptAccess" value="sameDomain">
			<param name="flashvars" value="%s">
			<param name="src" value="%s?%s">
			<param name="name" value="' . self::swfputdir . '">
			<param name="bgcolor" value="#000000">
			<param name="align" value="middle">',
			$play, $quality, $allowfull, $fv, $uswf, $pv);

		$aret = array('js' => $jatt);

		if ( $h5v_fallback ) {
			$aret['el'] =  sprintf('%s%s%s%s
			</object>',
				$obj, $h5v, $altimg, $h5vclose);
		} else {
			$aret['el'] =  sprintf('%s%s%s
			</object>%s',
				$h5v, $obj, $altimg, $h5vclose);
		}
		
		return $aret;
	}

	// normalize H5V type string from user input -- NOT using
	// W3.org form, which does not work in existing browsers
	// such as Opera (at least GNU/Linux), or older FFox (v 10+?)
	// W3 experimental H5 validator says form made here is in error,
	// but we assume that if approved and useful are a 
	// one-or-the-other choice then the latter is preferred
	public static function clean_h5vid_type($str) {
		// help text instructs user NOT to give 'type='
		// but just in case . . .
		$t = explode('=', $str);
		if ( ! strcasecmp(trim($t[0]), 'type') ) {
			array_shift($t);
			$str = trim(implode('=', $t), "'\\\" ");
		}
		
		// separate mime type and any codecs arg
		$t = explode(';', $str);
		
		// type
		$ty = explode('/', $t[0]);
		// if incorrect type form, no value will have to work
		if ( count($ty) < 2 ) {
			return '';
		}
		// no further check on type parts: beyond our purview
		$ty = trim($ty[0], "'\\\" ") . '/' . trim($ty[1], "'\\\" ");
	
		// got type only
		if ( count($t) < 2 ) {
			return $ty;
		}
		
		// codecs
		$t = explode('=', trim($t[1], "'\\\" "));
		// if incorrect codecs arg, no value will probably work
		if ( count($t) < 2 ) {
			return $ty;
		}
		if ( strcasecmp($t[0] = trim($t[0], "'\\\" "), 'codecs') ) {
			// allow mistake in plural form
			if ( strcasecmp($t[0], 'codec') ) {
				return $ty;
			}
		}
	
		// codecs args
		$t = trim($t[1], "'\\\" ");
		$t = explode(',', $t);
	
		// rebuild codecs args as comma sep'd value in the form
		// that has been found to work in existing browsers;
		// reuse $str for new value
		$str = trim($t[0], "'\\\" ");
		for ( $i = 1; $i < count($t); $i++ ) {
			// NO SPACE after comma: browsers might reject source!
			$str .= ',' . trim($t[$i], "'\\\" ");
		}
		
		// NO QUOTES on codecs arg: browsers might reject source!
		// This contradicts examples at W3, but ultimately the
		// the browsers dictate what works
		return sprintf("%s; codecs=%s", $ty, $str);
	}

	// get a script element string for H5 video JS setup
	public function get_h5vjs_tags($atts, $flashid = null) {
		$bar = $this->evhv5v_svgs[self::evhv5vsvg_bar];
		$vol = $this->evhv5v_svgs[self::evhv5vsvg_vol];
		$but = $this->evhv5v_svgs[self::evhv5vsvg_but];

		/*
		 * assemble parameters for control bar builder:
		 * "iparm" are generally needed parameters
		 * "oparm" are for <param> children of <object>
		 * some items may be repeated to keep the JS simple and orderly
		 * * OPTIONAL fields do not appear here;
		 * * see JS evhh5v_controlbar_elements
		 */
		$iparm = array("uniq" => $atts['uniq'],
			"barurl" => $bar, "buturl" => $but, "volurl" => $vol,
			"divclass" => "evhh5v_cbardiv", "vidid" => $atts['id'],
			"parentdiv" => $atts['parentdiv'], "auxdiv" => 'aux_' . $atts['id'],
			"width" => $atts['width'], "barheight" => $atts['barheight'],
			"altmsg" => "<span id=\"span_objerr_".$atts['uniq']."\" class=\"evhh5v_cbardiv\">".$atts['altmsg']."</span>"
		);
		$oparm = array(
			// these must be appended with uniq id
			"uniq" => array("id" => "evhh5v_ctlbar_" . $atts['uniq']),
			// these must *not* be appended with uniq id
			"std" => $atts["std"]
		);
		
		$parms = array("iparm" => $iparm, "oparm" => $oparm);
		if ( $flashid !== null && $flashid !== '' ) {
			$parms['flashid'] = $flashid;
		}

		return sprintf('
			<script type="text/javascript">
				evhh5v_controlbar_elements(%s, true);
			</script>', json_encode($parms));
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
 require_once SWF_put_evh::mk_pluginincpath('class-SWF-params-evh.php');
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
	// an instance of the main plugin class
	protected $plinst;
	
	// default width should not be wider than sidebar, but
	// widgets may be placed elsewhere, e.g. near bottom
	// 216x138 is suggested minimum size in Adobe docs,
	// because flash user settings dialog is clipped if 
	// window is smaller; AFAIK recent plugins refuse to map
	// the context menu rather than let it be clipped.
	// 216 is a bit wide for a sidebar (in some themes),
	// consider 200x150
	// Update: included JS now sizes the display if enclosing
	// div is sized smaller than its style.width; this is a
	// good thing.
	const defwidth  = 200; // is 4:3 aspect
	const defheight = 150; //


	public function __construct() {
		$this->plinst = SWF_put_evh::get_instance(false);
	
		$cl = __CLASS__;
		// Label shown on widgets page
		$lb =  __('SWFPut Video Player', 'swfput_l10n');
		// Description shown under label shown on widgets page
		$desc = __('Flash and HTML5 video for your widget areas', 'swfput_l10n');
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

		// Added v. 1.0.7; 2014/01/24:
		// if wp_is_mobile is a defined function (wp 3.4?), then
		// honor new param 'mobiwidth' to set the dimensions
		// (proportionally to regular WxH) for mobile devices
		// user can set $mobiwidth 0 to disable this
		$mobiwidth = 0 + $pr->getvalue('mobiwidth');
		if ( $mobiwidth > 0 && function_exists('wp_is_mobile') ) {
			if ( wp_is_mobile() ) {
				$h = (int)($h * $mobiwidth / $w);
				$w = $mobiwidth;
			}
		}

		$cap = $this->plinst->wt($pr->getvalue('caption'));
		$uswf = $this->plinst->get_swf_url();
		// added 2.2: $pr->getvalue('align')
		$aln = 'align' . $pr->getvalue('align');

		// overdue: 2.1 removed deprecated align
		$dv = sprintf(
			'class="widget %s" style="width: %upx; max-width: 100%%"',
			$aln, $w);

		extract($args);

		// note *no default* for title; allow empty title so that
		// user may place this below another widget with
		// apparent continuity (subject to filters)
		$title = apply_filters('widget_title',
			empty($instance['title']) ? '' : $instance['title'],
			$instance, $this->id_base);

		echo $before_widget;

		// 2.2: title is now assigned to $elem['enter'] (below) which
		// places it just after opening of enclosing div in get_div()
		// so, no more "echo $before_title . $title . $after_title;"
		// but, before and after are still needed
		if ( $title ) {
			$title = $before_title . $title . $after_title;
		}

		if ( $cap ) {
			$cap = '<p class="caption"><span class="caption-span">'
				. $cap . '</span></p>';
		}

		// setup and print inner video div
		$ids  = $this->plinst->get_div_ids('widget-div');
		$elem = $this->plinst->get_player_elements($uswf, $pr, $ids);
		if ( $title ) {
			$elem['enter'] = $title;
		}
		printf('%s', $this->plinst->get_div($ids, $dv, $cap, $elem));

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
		if ( ! $i['align'] ) {
			$i['align'] = $pr->getdefault('align');
		}

		return $i;
	}

	public function form($instance) {
		// EH: 20.07.2014
		// code and markup moved into file xed_widget_form.php
		// method is static, but use '->' to avoid NAME::
		require $this->plinst->mk_pluginincpath('xed_widget_form.php');
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
