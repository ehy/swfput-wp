<?php
/*
Plugin Name: SWFlash Put
Plugin URI: http://lucy.example.org/dl/wp-plugin-swfput-1/
Description: add Flash player to Wordpress pages
Version: 0.1.0
Author: Ed Hynan
Author URI: http://wpblog.example.org/
License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/*
 *      swfput-1.php
 *      
 *      Copyright 2011 Ed Hynan <freecode@verizon.net>
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


// supporting classes found in files named __CLASS__.inc.php
// each class must define static method id_token() which returns
// the correct int, to help avoid name clashes
function swfput_paranoid_require_class ($cl, $rfunc = 'require_once') {
	$id = 0xED00AA33;
	$meth = 'id_token';
	if ( ! class_exists($cl) ) {
		$d = plugin_dir_path(__FILE__).'/'.$cl.'.inc.php';
		switch ( $rfunc ) {
			case 'require_once':
				require_once $d;
				break;
			case 'require':
				require $d;
				break;
			case 'include_once':
				include_once $d;
				break;
			case 'include':
				include $d;
				break;
			default:
				$s = '' . $rfunc;
				$s = sprintf('%s: what is %s?', __FUNCTION__, $s);
				wp_die($s);
				break;
		}
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

// these support classes are in separate files as they are
// not specific to this plugin, and may be used in others
swfput_paranoid_require_class('OptField_0_0_2');
swfput_paranoid_require_class('OptSection_0_0_2');
swfput_paranoid_require_class('OptPage_0_0_2');
swfput_paranoid_require_class('Options_0_0_2');


/**********************************************************************\
 *  missing functions that must be visible for definitions            *
\**********************************************************************/

/**
 * for translations; stub
 */
if ( ! function_exists( '__' ) ) :
function __ ( $text )
{
	return $text;
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
	// most of these static properties would be const in php 5.3, but
	// this is written for 5.2 -- the earliest ver. available here

	// the widget class name
	public static $swfput_widget = 'SWF_put_widget_evh';
	// parameter helper class name
	public static $swfput_params = 'SWF_params_evh';
	
	// identifier for settings page
	public static $settings_page_id = 'swfput1_settings_page';
	
	// option group name in the WP opt db
	public static $opt_group  = '_evh_swfput1_opt_grp';
	// verbose (helpful?) section descriptions?
	public static $optverbose = '_evh_swfput1_verbose';
	// WP option names/keys -- note prefix '_evh_'
	public static $optdispmsg = '_evh_swfput1_dmsg';
	public static $optdispwdg = '_evh_swfput1_dwdg';
	public static $optdisphdr = '_evh_swfput1_dhdr';
	// delete options on uninstall
	public static $optdelopts = '_evh_swfput1_delopts';
	// use php+ming script if available?
	public static $optuseming = '_evh_swfput1_useming';

	// verbose (helpful?) section descriptions?
	public static $defverbose = 'true';
	// display opts, widget, inline or both
	 // 1==message | 2==widget | 4==header
	public static $defdisplay  = 7;
	public static $disp_msg    = 1;
	public static $disp_widget = 2;
	public static $disp_hdr    = 4;
	// delete options on uninstall
	public static $defdelopts = 'true';
	// use php+ming script if available?
	public static $defuseming = 'false';
	
	// object of class to handle options under WordPress
	protected $opt;

	// autoload class version suffix
	public static $aclv = '0_0_2';

	// swfput program directory
	protected static $swfputdir = 'mingput';
	// swfput program binary name
	protected static $swfputbinname = 'mingput.swf';
	// swfput program php+ming script name
	protected static $swfputphpname = 'mingput.php';
	// swfput program css name
	protected static $swfputcssname = 'obj.css';
	// swfput program binary path
	protected $swfputbin;
	// swfput program php+ming script path
	protected $swfputphp;
	// swfput program css path
	protected $swfputcss;
	
	// hold an instance
	private static $instance;

	// correct file path (possibly needed due to symlinks)
	public static $pluginfile = null;

	public function __construct($init = true) {
		// if arg $init is false then this instance is just
		// meant to provide options and such
		$pf = self::mk_pluginfile();
		// URL setup
		$t = self::$swfputdir . '/' . self::$swfputbinname;
		$this->swfputbin = plugins_url($t, $pf);
		$t = self::$swfputdir . '/' . self::$swfputphpname;
		$this->swfputphp = plugins_url($t, $pf);
		$t = self::$swfputdir . '/' . self::$swfputcssname;
		$this->swfputcss = plugins_url($t, $pf);

		if ( ! $init ) {
			// must do this
			$this->init_opts();
			return;
		}
		
		$this->init();

		// keep it clean: {de,}activation
		$cl = __CLASS__;
		register_deactivation_hook($pf, array($cl, 'on_deactivate'));
		register_activation_hook($pf,   array($cl,   'on_activate'));
		register_uninstall_hook($pf,    array($cl,  'on_uninstall'));

		// some things are to be done in init hook
		add_action('init', array($this, 'init_hook_func'));

		// add 'Settings' link on the plugins page entry
		// cannot be in activate hook
		$name = plugin_basename($pf);
		add_filter("plugin_action_links_$name",
			array($cl, 'plugin_page_addlink'));

		// it's not enough to add this action in the activation hook;
		// that alone does not work.  IAC administrative
		// {de,}activate also controls the widget
		add_action('widgets_init', array($cl, 'regi_widget'));//, 1);

	}

	public function __destruct() {
		$this->opt = null;
	}
	
	public static function on_deactivate() {
		$wreg = __CLASS__;
		$name = plugin_basename(self::mk_pluginfile());
		$arf = array($wreg, 'plugin_page_addlink');
		remove_filter("plugin_action_links_$name", $arf);
		self::unregi_widget();
		unregister_setting(self::$opt_group, // option group
			self::$opt_group, // opt name; using group passes all to cb
			array($wreg, 'validate_opts'));
	}

	public static function on_activate() {
		$wreg = __CLASS__;
		add_action('widgets_init', array($wreg, 'regi_widget'), 1);
	}

	public static function on_uninstall() {
		self::unregi_widget();
		$opts = get_option(self::$opt_group); // WP get_option()
		
		if ( $opts && $opts[self::$optdelopts] != 'false' ) {
			delete_option(self::$opt_group);
		}
	}

	public static function plugin_page_addlink($links) {
		$opturl = '<a href="' . get_option('siteurl');
		$opturl .= '/wp-admin/options-general.php?page=';
		$opturl .= self::$settings_page_id;
		$opturl .= '">' . __('Settings') . '</a>';
		// Add a link to this plugin's settings page
		array_unshift($links, $opturl); 
		return $links; 
	}

	public static function regi_widget ($fargs = array()) {
		global $wp_widget_factory;
		if ( ! isset($wp_widget_factory) ) {
			return;
		}
		if ( function_exists(register_widget) ) {
			$cl = self::$swfput_widget;
			register_widget($cl);
		}
	}

	public static function unregi_widget () {
		global $wp_widget_factory;
		if ( ! isset($wp_widget_factory) ) {
			return;
		}
		if ( function_exists(unregister_widget) ) {
			$cl = self::$swfput_widget;
			unregister_widget($cl);
		}
	}

	protected function init_opts() {
		$items = array(
			self::$optverbose => self::$defverbose,
			self::$optdispmsg =>
				(self::$defdisplay & self::$disp_msg) ? 'true' : 'false',
			self::$optdispwdg =>
				(self::$defdisplay & self::$disp_widget) ? 'true' : 'false',
			self::$optdisphdr =>
				(self::$defdisplay & self::$disp_hdr) ? 'true' : 'false',
			self::$optdelopts => self::$defdelopts,
			self::$optuseming => self::$defuseming
		);
		$opts = get_option(self::$opt_group); // WP get_option()
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
				update_option(self::$opt_group, $opts);
			}
		} else {
			$opts = array();
			foreach ($items as $k => $v) {
				$opts[$k] = '' . $v;
			}
			add_option(self::$opt_group, $opts);
		}
		return $opts;
	}

	protected function init() {
		if ( ! $this->opt ) {
			$items = $this->init_opts();
			
			// set up Options_evh with its page, sections, and fields
			
			// mk_aclv adds a suffix to class names
			$Cf = self::mk_aclv('OptField');
			$Cs = self::mk_aclv('OptSection');
			// prepare fields to appear under various sections
			// of admin page
			$ns = 0;
			$sections = array();

			// placement section: (posts, sidebar, header)
			$nf = 0;
			$fields = array();
			$fields[$nf++] = new $Cf(self::$optverbose,
					self::ht(__('Show verbose descriptions:')),
					self::$optverbose,
					$items[self::$optverbose],
					array($this, 'put_verbose_opt'));
			// this field is not printed if ming is n.a.
			if ( self::can_use_ming() )
			$fields[$nf++] = new $Cf(self::$optuseming,
					self::ht(__('Dynamic SWF generation:')),
					self::$optuseming,
					$items[self::$optuseming],
					array($this, 'put_useming_opt'));
			// section object includes description callback
			$sections[$ns++] = new $Cs($fields,
					'swfput1_general_section',
					'<a name="general">' .
						self::ht(__('SWF General Option Settings'))
						. '</a>',
					array($this, 'put_general_desc'));

			// placement section: (posts, sidebar, header)
			$nf = 0;
			$fields = array();
			$fields[$nf++] = new $Cf(self::$optdispmsg,
					self::ht(__('Place in posts:')),
					self::$optdispmsg,
					$items[self::$optdispmsg],
					array($this, 'put_inposts_opt'));
			$fields[$nf++] = new $Cf(self::$optdispwdg,
					self::ht(__('Place in sidebar:')),
					self::$optdispwdg,
					$items[self::$optdispwdg],
					array($this, 'put_widget_opt'));
			$fields[$nf++] = new $Cf(self::$optdisphdr,
					self::ht(__('Place in head:')),
					self::$optdisphdr,
					$items[self::$optdisphdr],
					array($this, 'put_inhead_opt'));
			// section object includes description callback
			$sections[$ns++] = new $Cs($fields,
					'swfput1_placement_section',
					'<a name="placement">' .
						self::ht(__('SWF Video Placement Settings'))
						. '</a>',
					array($this, 'put_place_desc'));
			
/*
			// prepare fields to appear under various sections
			// of admin page
			$nf = 0;
			$fields = array();
			$fields[$nf++] =	new $Cf(self::$optdisplay,
					self::ht(__('Fortune display type:'));
					self::$optdisplay,
					$items[self::$optdisplay],
					array($this, 'put_disp_opt')),
			$fields[$nf++] = new $Cf(self::$opttextpos,
					self::ht(__('Fortune inline placement:'));
					self::$opttextpos,
					$items[self::$opttextpos],
					array($this, 'put_text_pos'));
			// prepare sections to appear under admin page
			$sections[$ns++] = new $Cs($fields,
					'swfput1_text_section',
					self::ht(__('Fortune Display Settings')),
					array($this, 'put_text_desc'));
*/
			
			// install opts section:
			// field: delete opts on uninstall?
			$nf = 0;
			$fields = array();
			$fields[$nf++] = new $Cf(self::$optdelopts,
					self::ht(__('When the plugin is uninstalled:')),
					self::$optdelopts,
					$items[self::$optdelopts],
					array($this, 'put_del_opts'));
			// prepare sections to appear under admin page
			$sections[$ns++] = new $Cs($fields,
					'swfput1_inst_section',
					'<a name="install">' .
						self::ht(__('Plugin Install Settings'))
						. '</a>',
					array($this, 'put_inst_desc'));

			// prepare admin page specific hooks per page. e.g.:
			if ( false ) {
				$suffix_hooks = array(
					'admin_head' => array($this, 'admin_head'),
					'admin_print_scripts' => array($this, 'admin_js'),
					'load' => array($this, 'admin_load')
					);
			} else {
				$suffix_hooks = '';
			}
			
			// prepare admin page
			// Note that validator applies to all options,
			// necessitating a big switch on option keys
			$Cp = self::mk_aclv('OptPage');
			$page = new $Cp(self::$opt_group, $sections,
				self::$settings_page_id,
				self::ht(__('SWFPut Plugin')),
				self::ht(__('SWFPut Configuration')),
				array(__CLASS__, 'validate_opts'),
				/* pagetype = 'options' */ '',
				/* capability = 'manage_options' */ '',
				/* callback */ '',
				/* 'hook_suffix' callback array */ $suffix_hooks,
				self::ht(__('Configuration of SWFPut Plugin')),
				self::ht(__('Display and Runtime Settings.')),
				self::ht(__('Save Settings')));
			
			$Co = self::mk_aclv('Options');
			$this->opt = new $Co($page);
		}
	}
	
	public function init_hook_func () {
		// add here to be sure option is ready
		if ( $this->get_message_option() === 'true' ) {
			$scf = array($this, 'post_shortcode');
			add_shortcode('putswf_video', $scf);
			$scf = array($this, 'post_sed');
			add_action('the_content', $scf, 20);
		} else {
			remove_shortcode('putswf_video');
			remove_action('the_content', array($this, 'post_sed'));
		}
	}
	
	/**
	 * Settings page callback functions:
	 * validators, sections, fields, and page
	 */

	// static callback: validate options main
	public static function validate_opts($opts) {	
		$a_out = array();
		$a_orig = get_option(self::$opt_group);
		$nerr = 0;
		$nupd = 0;

		// empty happens if all fields are checkboxes and none checked
		if ( empty($opts) ) {
			$opts = array();
		}
		// checkboxes need value set - nonexistant means false
		$ta = array(self::$optverbose,
			self::$optdispmsg, self::$optdispwdg,
			self::$optdisphdr, self::$optdelopts,
			self::$optuseming);
		foreach ( $ta as $k ) {
			if ( array_key_exists($k, $opts) ) {
				continue;
			}
			$opts[$k] = 'false';
		}
	
		foreach ( $opts as $k => $v ) {
			$ot = trim($v);
			$oo = trim($a_orig[$k]);

			switch ( $k ) {
				case self::$optverbose:
				case self::$optdispmsg:
				case self::$optdispwdg:
				case self::$optdisphdr:
				case self::$optdelopts:
				case self::$optuseming:
					if ( $ot != 'true' && $ot != 'false' ) {
						$e = sprintf('bad option: %s[%s]', $k, $v);
						self::errlog($e);
						add_settings_error('SWFPut checkbox option',
							sprintf('%s[%s]', self::$opt_group, $k),
							self::ht($e),
							'error');
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
					add_settings_error('internal error, WP broken?',
						sprintf('%s[%s]', self::$opt_group, ''),
						self::ht($e),
						'error');
					$nerr++;
			}
		}

		// now register updates
		if ( $nupd > 0 ) {
			$str = $nerr == 0 ? __('Settings updated correctly') :
				sprintf(__('Some settings (%d) updated'), $nupd);
			add_settings_error(self::$opt_group, self::$opt_group,
				self::ht($str), 'updated');
		}
		
		return $a_out;
	}

	//
	// section callbacks
	//
	
	// callback: put html for placement field description
	public function put_general_desc() {
		$t = self::ht(__('General SWF plugin options:'));
		printf('<p>%s</p>%s', $t, "\n");
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$t = self::ht(__('The verbose option selects whether
			long and hopefully helpful descriptions
			should be displayed with the various settings
			sections. This paragraph is an example, and
			will not be shown if the option is not
			selected.'));
		printf('<p>%s</p>%s', $t, "\n");

		if ( self::can_use_ming() ) {
			$t = self::ht(__('The PHP+Ming option selects whether
				the Flash player program is generated with PHP
				and the Ming extension for each request, or
				precompiled binary Flash players are used.
				This option is only displayed if the Ming
				PHP extension is installed and loaded; if you
				are reading this then Ming has been found to
				be loaded. Generation the player on the fly
				has the advantage of making options available
				even if they require compilation changes. The binaries
				might save a (very small) bit of server load, but
				they are less flexible (that is the reason that
				there is more than one binary player: each has
				small differences selected when compiled).'));
			printf('<p>%s</p>%s', $t, "\n");
		}
	}

	// callback: put html for placement field description
	public function put_place_desc() {
		$t = self::ht(__('SWF placement options:'));
		printf('<p>%s</p>%s', $t, "\n");
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$t = self::ht(__('This section includes options to select 
			where the Flash player may be embedded. By various
			means video may be placed near the head of the page,
			in sidebar widgets, or in certain posts.'));
		printf('<p>%s</p>%s', $t, "\n");
		$t = self::ht(__('Go back to top (General section).'));
		printf('<p><a href="#general">%s</a></p>%s', $t, "\n");
	}

	// callback: put html install field description
	public function put_inst_desc() {
		$t = self::ht(__('Install options:'));
		printf('<p>%s</p>%s', $t, "\n");
		if ( self::get_verbose_option() !== 'true' ) {
			return;
		}

		$t = self::ht(__('This section includes optional
			features for plugin install or uninstall. Presently
			the only option is whether to remove the plugin\'s
			set of options from the database when it is deleted.
			There is probably no reason to leave the options in
			place; you may simply deactivate the plugin if
			you want it off temporarily. This option is useful
			for development, but you might think of another reason
			to use it.'));
		printf('<p>%s</p>%s', $t, "\n");
		$t = self::ht(__('Go back to top (General section).'));
		printf('<p><a href="#general">%s</a></p>%s', $t, "\n");
	}
	
	//
	// fields callbacks
	//
	
	// callback helper, put single checkbox
	public function put_single_checkbox($a, $opt, $label) {
		$group = self::$opt_group;
		$c = $a[$opt] == 'true' ? "checked='CHECKED' " : "";

		//echo "\n		<!-- {$opt} checkbox-->\n";

		echo "		<label><input type='checkbox' id='{$opt}' ";
		echo "name='{$group}[{$opt}]' value='true' {$c}/> ";
		echo "{$label}</label><br />\n";
	}

	// callback, put verbose section descriptions?
	public function put_verbose_opt($a) {
		$tt = self::ht(__('Show verbose descriptions'));
		$k = self::$optverbose;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, put SWF in head?
	public function put_useming_opt($a) {
		$tt = self::ht(__('Use SWF script if PHP+Ming is available'));
		$k = self::$optuseming;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, put SWF in head?
	public function put_inhead_opt($a) {
		$tt = self::ht(__('Enable SWF in head'));
		$k = self::$optdisphdr;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, put SWF in sidebar (widget)?
	public function put_widget_opt($a) {
		$tt = self::ht(__('Enable SWF in sidebar'));
		$k = self::$optdispwdg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, put SWF in posts?
	public function put_inposts_opt($a) {
		$tt = self::ht(__('Enable SWF in posts'));
		$k = self::$optdispmsg;
		$this->put_single_checkbox($a, $k, $tt);
	}

	// callback, install section field: opt delete
	public function put_del_opts($a) {
		$tt = self::ht(__('Permanently delete settings (clean db)'));
		$k = self::$optdelopts;
		$this->put_single_checkbox($a, $k, $tt);
	}

	/**
	 * procedures to place and/or edit pages and content
	 */

	// handler for 'shortcode' tags that will be
	// replaced with SWF video
	// subject to option $optdispmsg
	public function post_shortcode($atts, $content = null, $code = "") {
		$pr = self::$swfput_params;
		$pr = new $pr();
		$pa = shortcode_atts($pr->getparams(), $atts);
		$w = $pa['width']; $h = $pa['height'];
		
		if ( $code === "" ) {
			$code = $atts[0];
		}
		$swf = $this->get_swf_url('post', $w, $h);
		$dw = $w + 0;
		$dv = '<div id="'.$code.'" class="wp-caption aligncenter"';
		$dv .= ' style="width: '.$dw.'px">';
		$em = $this->get_swf_tags($swf, $pr->setarray($pa));
		$c = '';
		if ( $content !== null ) {
			$c = '<p class="wp-caption-text">' .
				do_shortcode($content) . '</p>';
		}
		return sprintf('%s%s%s</div>', $dv, $em, $c);
	}

	// filter the blogger's posts for attachments that can be
	// replaced with SWF video
	// subject to option $optdispmsg
	public function post_sed($dat) {
		global $post, $wp_locale;
		$w = 400; $h = 300; // TODO: from option
		$pr = self::$swfput_params;
		$pr = new $pr();
		$pr->setvalue('width', $w);
		$pr->setvalue('height', $h);
		
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
				} else {
					$pr->setvalue('url', $url);
					$sw = $this->get_swf_url('post_sed', $w, $h);
					$s = $this->get_swf_tags($sw, $pr);
					$out .= '<br />' . $s . '<br />' . $sep;
					//$out .= $s;
					if ( false /*elide attach url -- needs work*/ ) {
						$s = '' . $m[1][0] . $m[6][0];
						if ( strlen($s) > 0	) {
							$out .= $s . $sep;
						}
					} else {
						$out .= $line . $sep;
					}
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
		$s = $pfx . '_' . self::$aclv;
		return $s;
	}
	
	// help for plugin file path/name; __FILE__ alone
	// is not good enough -- see comment in body
	public static function mk_pluginfile() {
		if ( self::$pluginfile !== null ) {
			return self::$pluginfile;
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
			$pf = WP_PLUGIN_DIR . '/' . $pd . '/' . basename($pf);
		} else {
			// this is similar to common methods w/  __FILE__; but
			// can cause regi* failures due to symlinks in path
			$pf = rtrim(plugin_dir_path($pf), '/').'/' . basename($pf);
		}
		
		// store and return corrected file path
		return self::$pluginfile = $pf;
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
			if ( self::opt_by_name(self::$optuseming) === 'true' ) {
				return true;
			}
		}
		return false;
	}

	// 'html-ize' a text string
	public static function ht($text) {
		if ( function_exists('wptexturize') ) {
			return wptexturize($text);
		}
		//return htmlspecialchars($text);
		return htmlentities($text, ENT_QUOTES, 'UTF-8');
	}
	
	// error messages; where {wp_}die is not suitable
	public static function errlog($err) {
		$e = sprintf('SWF Put WP plugin: %s', $err);
		error_log($e, 0);
	}
	
	// helper to make self
	public static function instantiate($init = true) {
		$cl = __CLASS__;
		self::$instance = new $cl($init);
		return self::$instance;
	}
	
	// get an option value by name/key
	public static function opt_by_name($name) {
		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts && array_key_exists($name, $opts) ) {
			return $opts[$name];
		}
		return null;
	}

	// for settings section descriptions
	public static function get_verbose_option() {
		return self::opt_by_name(self::$optverbose);
	}

	// for the sidebar widget, to get its option
	public static function get_widget_option() {
		return self::opt_by_name(self::$optdispwdg);
	}

	// get the do messages (place in posts) option
	public static function get_message_option() {
		return self::opt_by_name(self::$optdispmsg);
	}

	// get the place at head option
	public static function get_head_option() {
		return self::opt_by_name(self::$optdisphdr);
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
		// gnash does not encode URLs, and so fails on
		// chars that need encoding: do it for gnash
		// (note gnash doesn't do https)
		// the Adobe plugin handles encoded or not
		$p = '/';
		$ua = parse_url(urldecode($url));
		$vurl = '';
		if ( array_key_exists('path', $ua) ) {
			$t = ltrim($ua['path'], '/');
			if ( isset($rxpath) ) {
				if ( ! preg_match($rxpath, $t) ) {
					return false;
				}
			}
			$p .= $fesc === '' ? $t : $fesc($t);
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
				$vurl = $t . '://';
			} else if ( isset($requirehost) && $requirehost ) {
				return false;
			}
			if ( array_key_exists('user', $ua) ) {
				if ( isset($rejuser) && $rejuser ) {
					return false;
				}
				$vurl .= $ua['user'];
				// user not rejected; pass OK
				if ( array_key_exists('pass', $ua) ) {
					$vurl .= ':' . $ua['pass'];
				}
				$vurl .= '@';
			}
			$vurl .= $ua['host'];
			if ( array_key_exists('port', $ua) ) {
				if ( isset($rejport) && $rejport ) {
					return false;
				}
				$vurl .= ':' . $ua['port'];
			}
		} else if ( isset($requirehost) && $requirehost ) {
			return false;
		}
	
		$vurl .= $p;
		// A query with the media URL? It can happen
		// for stream servers.
		// this works, e.g. w/ ffserver ?date=...
		if ( array_key_exists('query', $ua) ) {
			if ( isset($rejquery) && $rejquery ) {
				return false;
			}
			$vurl .= '?' . $ua['query'];
		}
		if ( array_key_exists('fragment', $ua) ) {
			if ( isset($rejfrag) && $rejfrag ) {
				return false;
			}
			$vurl .= '#' . $ua['fragment'];
		}
		
		return $vurl;
	}

	// helper for selecting swf type (bin||script)) url
	// arg $sel should be caller tag: 'widget',
	// 'post' (shortcodes in posts), 'post_sed' (attachment_id filter),
	// 'head' -- it might be used in future
	public function get_swf_url($sel, $wi = 640, $hi = 480) {
		$useming = false;
		if ( self::should_use_ming() ) {
			$useming = true;
		}

		if ( $useming === true ) {
			$t = $this->swfputphp;
		} else {
			$t = $this->swfputbin;
		}

		return $t;
	}

	// helper for getting swf css (internal use)) url
	// arg $sel should be caller tag: 'widget',
	// 'post' (shortcodes in posts), 'post_sed' (attachment_id filter),
	// 'head' -- it might be used in future
	public function get_swf_css_url($sel = '') {
		return $this->swfputcss;
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

		$fesc = 'rawurlencode';
		if ( isset($esc_t) && $esc_t === 'plus' ) {
			$fesc = 'urlencode';
		}

		if ( $url === '' ) {
			$url = $defaulturl;
		}

		$achk = array(
			'requirehost' => true,
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
		$w = $width; $h = $height;
		if ( $cssurl === '' )
			$cssurl = $this->get_swf_css_url();
		$achk = array(
			'requirehost' => true,
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
		$playpath = ($esc == true) ? $fesc($playpath) : $playpath;
		
		// query vars
		$qv = sprintf('ST=%s&WI=%u&HI=%u&IDV=%s&FN=%s',
			$cssurl, $w, $h, $playpath, $url);
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

		return ''
		. sprintf('
		<object classid="%s" codebase="%s" width="%u" height="%u">
		<param name="data" value="%s?%s">
		', $classid, $codebase, $w, $h, $uswf, $pv)
		. sprintf('<param name="play" value="%s">
		<param name="loop" value="%s">
		<param name="quality" value="%s">
		<param name="allowFullScreen" value="%s">
		<param name="allowScriptAccess" value="sameDomain">
		<param name="flashvars" value="%s">
		<param name="src" value="%s?%s">
		<param name="name" value="mingput">
		<param name="bgcolor" value="#000000">
		<param name="align" value="middle">
		', $play, $loop, $quality, $allowfull, $fv, $uswf, $pv)
		. sprintf('<embed type="%s" src="%s?%s" bgcolor="#000000" '
		. 'name="mingput" flashvars="%s" allowscriptaccess="sameDomain"'
		. ' quality="%s" loop="%s" play="%s" ',
		$mtype, $uswf, $pv, $fv, $quality, $loop, $play)
		. sprintf('data="%s?%s" allowfullscreen="%s" align="middle"'
		. ' width="%u" height="%u" />
		</object>
		', $uswf, $pv, $allowfull, $w, $h);
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
		'url' => '',
		'defaulturl' => 'rtmp://cp82347.live.edgefcs.net/live', //akamai
		'cssurl' => '',
		'width' => '240',
		'height' => '180',
		'audio' => 'false',        // source is audio; (mp3 is detected)
		'aspectautoadj' => 'true', // adj. common sizes, e.g. 720x480
		'displayaspect' => '0',    // needed if pixels are not square
		'pixelaspect' => '0',      // use if display aspect unknown
		'volume' => '50',          // if host has no saved setting
		'play' => 'false',         // play (or pause) on load
		'hidebar' => 'false',      // initially hide control bar
		'disablebar' => 'false',   // disable and hide control bar
		'barheight' => 'default',
		'quality' => 'high',
		'allowfull' => 'true',
		'allowxdom' => 'false',
		'loop' => 'false',
		'mtype' => 'application/x-shockwave-flash',
		// rtmp
		'playpath' => 'CSPAN2@14846',
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
			$this->inst[$k] = $v;
		}
		return $this;
	}
	public function cmpval($key) {
		return (self::$defs[$key] === $this->inst[$key]);
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
	protected static $swfput_plugin = 'SWF_put_evh';
	// params helper class name
	protected static $swfput_params = 'SWF_params_evh';
	// an instance of the main plugun class
	protected $plinst;
	
	// defaults == width should not be wider than sidebar, but
	// widgets may be placed elsewhere, e.g. near bottom
	protected static $defwidth  = 216; // 216x162 is
	protected static $defheight = 162; // 4:3 aspect

	public function __construct() {
		global $swfput1_evh_instance_1;
		if ( ! isset($swfput1_evh_instance_1) ) {
			$cl = self::$swfput_plugin;
			$this->plinst = new $cl(false);
		} else {
			$this->plinst = &$swfput1_evh_instance_1;
		}
	
		$cl = __CLASS__;
		$desc = __('Flash video for your widget areas');
		$opts = array('classname' => $cl, 'description' => $desc);
		$copts = array('width' => self::$defwidth,
					'height' => self::$defheight);
		parent::__construct($cl, __('Flash Video'), $opts, $copts);
	}

	// surely this code cannot run under PHP4, but anyway . . .
	public function SWF_put_widget_evh() {
		$this->__construct();
	}

	public function widget($args, $instance) {
		$opt = $this->plinst->get_widget_option();
		if ( $opt != 'true' ) {
			return;
		}
		
		$w = self::$defwidth;
		$h = self::$defheight;
		$uswf = $this->plinst->get_swf_url('widget', $w, $h);

		$url = $instance['vurl'];
		$pr = self::$swfput_params;
		$pr = new $pr();
		$pr->setvalue('width', $w);
		$pr->setvalue('height', $h);
		$pr->setvalue('url', $url);
		if ( false && empty($url) ) { // allow default url
			return;
		}

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

		$this->plinst->put_swf_tags($uswf, $pr);

		echo $after_widget;
	}

	public function update($new_instance, $old_instance) {
		$instance = $new_instance;
		
		foreach ( $old_instance as $k => $v ) {
			if ( ! array_key_exists($k, $instance) ) {
				$instance[$k] = $v;
			}
		}
		if ( ! array_key_exists('title', $instance) ) {
			$instance['title'] = '';
		}
		if ( ! array_key_exists('vurl', $instance) ) {
			$instance['vurl'] = '';
		}

		return $instance;
	}

	public function form($instance) {
		$ht = 'wptexturize';
		$instance = wp_parse_args((array)$instance,
			array('title' => '', 'vurl' => ''));
		$title = $ht($instance['title']);
		$vurl = $ht($instance['vurl']);

		$id = $this->get_field_id('title');
		$nm = $this->get_field_name('title');
		$tl = $ht(__('Instance Title:'));
		?>

		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $title; ?>" /></p>

		<?php
		$id = $this->get_field_id('vurl');
		$nm = $this->get_field_name('vurl');
		$tl = $ht(__('URL (.flv|.mp4|.m4v):'));
		?>
		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $vurl; ?>" /></p>

		<?php
	}
} // End class SWF_put_widget_evh
else :
	wp_die('class name conflict: SWF_put_widget_evh in ' . __FILE__);
endif; // if ( ! class_exists('SWF_put_widget_evh') ) :


/**********************************************************************\
 *  plugin 'main()' level code                                        *
\**********************************************************************/

/**
 * 'main()' here
 */
if (!defined('WP_UNINSTALL_PLUGIN')&&$swfput1_evh_instance_1 === null) {
	$swfput1_evh_instance_1 = SWF_put_evh::instantiate();
}

// End PHP script:
?>
