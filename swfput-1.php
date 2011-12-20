<?php
/*
Plugin Name: fortune-2.php
Plugin URI: http://wpblog.example.org/dl/wp-plugin-fortune-2/
Description: print `fortune` at footer (Unix hosts only)
Version: 0.0.2
Author: Ed Hynan
Author URI: http://wpblog.example.org/
License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/*
 *      fortune-2.php
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


// No, cannot define __autoload in a plugin; too rude (pity).
if ( false ) {
	function __autoload($class_name) {
		// only try to load certain classes
		$re = '/^Opt(Field|Section|Page|ions)_[0-9_]*$/';
		if ( preg_match($re, $class_name) ) {
			$f = plugin_dir_path(__FILE__).'/'.$class_name;
			include_once $f;
		}
	}
} else {
	$d = plugin_dir_path(__FILE__);
	// check if classes are declared; if they are it had better
	// be because another plugin is using the same classes!
	// these tests fail in a loop, or they'd be in one
	$c1 = 'OptField_0_0_2';
	$c2 = 'OptSection_0_0_2';
	$c3 = 'OptPage_0_0_2';
	$c4 = 'Options_0_0_2';
	$evh_opt_id = 0xED00AA33;
	if ( class_exists($c1) ) {
		$f = $c1 . '::id_token';
		if ( $f() !== $evh_opt_id ) {
			wp_die('class name conflict: ' . $c1);
		}
	} else {
		include_once $d.'/'.$c1.'.inc.php';
	}
	if ( class_exists($c2) ) {
		$f = $c2 . '::id_token';
		if ( $f() !== $evh_opt_id ) {
			wp_die('class name conflict: ' . $c2);
		}
	} else {
		include_once $d.'/'.$c2.'.inc.php';
	}
	if ( class_exists($c3) ) {
		$f = $c3 . '::id_token';
		if ( $f() !== $evh_opt_id ) {
			wp_die('class name conflict: ' . $c3);
		}
	} else {
		include_once $d.'/'.$c3.'.inc.php';
	}
	if ( class_exists($c4) ) {
		$f = $c4 . '::id_token';
		if ( $f() !== $evh_opt_id ) {
			wp_die('class name conflict: ' . $c4);
		}
	} else {
		include_once $d.'/'.$c4.'.inc.php';
	}
}


/**********************************************************************\
 *  missing functions that must be visible for definitions            *
\**********************************************************************/

/**
 * for translations; stub example
 */
if ( ! function_exists( '__' ) ) :
function __ ( $text )
{
	return $text;
}
endif;


/**********************************************************************\
 *  Class defs                                                        *
\**********************************************************************/


/**
 * class handling 'fortunes'
 */
if ( ! class_exists('Fortune2_evh') ) :
class Fortune2_evh {
	// most of these statics could be const in php 5.3
	// the widget class name
	public static $fortune_widget = 'Fortune2_widget_evh';
	// constant defaults for fortune program
	//public static $deffortune = '/usr/games/fortune';
	public static $deffortune = 'fortune';
	public static $deffortarg = ' -s ';
	
	// display opts, widget, inline or both
	public static $defdisplay = 3; // 1==inline | 2==widget
	public static $disp_inline = 1;
	public static $disp_widget = 2;
	// fortune program output text position
	public static $deftextpos = 'bot';  // either 'top' || 'bot'
	// delete options on uninstall
	public static $defdelopts = 'true';
	
	// object of class to handle options under WordPress
	protected $opt;

	// WP option group -- note prefix '_evh_'
	// to guard against name clashes
	public static $opt_group  = '_evh_fortune2_opt_grp';
	// WP option names/keys -- note prefix '_evh_'
	public static $optfortune = '_evh_fortune2_path';
	public static $optfortarg = '_evh_fortune2_args';
	public static $optdisplay = '_evh_fortune2_disp';
	public static $opttextpos = '_evh_fortune2_tpos';
	// delete options on uninstall
	public static $optdelopts = '_evh_fortune2_delopts';
	// autoload class version suffix
	public static $aclv = '0_0_2';

	// current received fortune text
	protected $cur;
	// fortune program path from option if available
	protected $fortune;
	// fortune program args from option if available
	protected $fortarg;
	// fortune program output text position
	protected $textpos;
	
	// hold an instance
	private static $instance;

	public function __construct($init = true) {
		// if arg $init is false then this instance is just
		// meant to provide a fortune; don't do the whole-
		// shebang
		if ( ! $init ) {
			$this->get_options();
			return;
		}
		
		$this->init();
		$this->get_options();
		$this->mk_new();

		// paranoid redundancy
		$pf = __FILE__;
		if ( defined('WP_PLUGIN_DIR') ) {
			// using WP_PLUGIN_DIR due to symlink problems in
			// some installations; after much grief found fix at
			// https://wordpress.org/support/topic/register_activation_hook-does-not-work
			// in a post by member 'silviapfeiffer1' -- she nailed
			// it, and noone even replied to her!
			$ad = explode('/', rtrim(plugin_dir_path($pf), '/'));
			$pd = $ad[count($ad) - 1];
			$pf = WP_PLUGIN_DIR . '/' . $pd . '/' . basename($pf);
		} else {
			// this is similar to common methods w/  __FILE__; but
			// can cause regi* failures due to symlinks in path
			$pf = rtrim(plugin_dir_path($pf), '/').'/' . basename($pf);
		}

		// keep it clean: {de,}activation
		$cl = __CLASS__;
		register_deactivation_hook($pf, array($cl, 'on_deactivate'));
		register_activation_hook($pf,   array($cl,   'on_activate'));
		register_uninstall_hook($pf,    array($cl,  'on_uninstall'));

		// it's not enough to add this action in the activation hook;
		// that alone does not work.  IAC administrative
		// {de,}activate also controls the widget
		add_action('widgets_init', array($cl, 'regi_widget'));//, 1);

		add_action('admin_head', array($this, 'mk_css'));
		//add_action('wp_head', array($this, 'mk_css'));
		add_action('wp_head', array($this, 'mk_scripts_css'));
		add_action('admin_footer', array($this, 'put_text_html'));
		//add_action('admin_footer', array($this, 'put_text_html'));
		add_action('wp_footer', array($this, 'put_text_js'));

		// testing one two three
		//add_action('the_content', array($this, 'test_func'), 20);//low
		add_action('the_editor_content', array($this, 'test_func'), 20);//low
	}

	public function test_func ($dat) {
		global $post, $wp_locale;
		// interesting $post properties (->)
		// post_author, post_content, post_title,
		// post_status(==publish), post_content_filtered
		// post_parent, post_type(==post), post_mime_type, filter
		// custom meta e.g. $v = get_post_meta($post-ID, 'key_1', true);
		$out = '';
		//if ( current_user_can('upload_files')
		//if ( user_can_richedit() )
		// e.g. https://wpblog.example.org/?attachment_id=28
		//if ( true && is_single() ) {
		$pat = '/(\r\n|\r|\n)/';
		$la = preg_split($pat, $dat, null, PREG_SPLIT_DELIM_CAPTURE);
		$pat = '/^(.*)([\?\&])(attachment_id=)([0-9]+)\b(.*)$/';
		for ( $n = 0; $n < count($la); ) {
			$line = $la[$n]; $n++;
			$sep = isset($la[$n]) ? $la[$n] : ''; $n++;
			$m = null;
			if ( preg_match($pat, $line, $m, PREG_OFFSET_CAPTURE) ) {
				// want 2, 4
				$tok = $m[2][0];
				$id = $m[4][0];
				$meta = wp_get_attachment_metadata($id);
				if ( !is_wp_error($meta) ) {
					$out .= '<br />'.self::ht('attach id: ') . $id;
					$out .= '<br />'.self::ht('post id: ') . $post->ID;
					foreach($meta as $k => $v) {
						$s = 'attachmeta key == '.$k.' -- value == '.$v;
						$out .= '<br />'.self::ht($s);
					}
				} else {
					$out .= '<br />'.self::ht('FAILED metadata');
				}
				$url = wp_get_attachment_url($id);
				if ( !is_wp_error($url) ) {
					$s = 'attachment url == '.$url;
					$out .= '<br />'.self::ht($s);
				} else {
					$out .= '<br />'.self::ht('FAILED url');
				}
 				$out .= '<h3>TEST H3 EVH</h3>';
				$out .= $line . $sep;
			} else {
				$out .= $line . $sep;
			}
		}
		return $out;
	}
	
	public function __destruct() {
		$this->opt = null;
	}
	
	public static function on_deactivate() {
		self::unregi_widget();
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

	public static function regi_widget ($fargs = array()) {
		global $wp_widget_factory;
		if ( ! isset($wp_widget_factory) ) {
			return;
		}
		if ( function_exists(register_widget) ) {
			$cl = self::$fortune_widget;
			register_widget($cl);
		}
	}

	public static function unregi_widget () {
		global $wp_widget_factory;
		if ( ! isset($wp_widget_factory) ) {
			return;
		}
		if ( function_exists(unregister_widget) ) {
			$cl = self::$fortune_widget;
			unregister_widget($cl);
		}
	}

	protected function init_opts() {
		$items = array();
		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts ) {
			if ( array_key_exists(self::$optfortune, $opts) &&
				 $opts[self::$optfortune] != '') {
				$items[self::$optfortune] = $opts[self::$optfortune];
			} else {
				$items[self::$optfortune] = self::$deffortune;
			}
			if ( array_key_exists(self::$optfortarg, $opts) &&
				 $opts[self::$optfortarg] != '') {
				$items[self::$optfortarg] = $opts[self::$optfortarg];
			} else {
				$items[self::$optfortarg] = self::$deffortarg;
			}
			if ( array_key_exists(self::$optdisplay, $opts) &&
				 $opts[self::$optdisplay] != '') {
				$items[self::$optdisplay] = $opts[self::$optdisplay];
			} else {
				$items[self::$optdisplay] = self::$defdisplay;
			}
			if ( array_key_exists(self::$opttextpos, $opts) &&
				 $opts[self::$opttextpos] != '') {
				$items[self::$opttextpos] = $opts[self::$opttextpos];
			} else {
				$items[self::$opttextpos] = self::$deftextpos;
			}
			if ( array_key_exists(self::$optdelopts, $opts) &&
				 $opts[self::$optdelopts] != '') {
				$items[self::$optdelopts] = $opts[self::$optdelopts];
			} else {
				$items[self::$optdelopts] = self::$defdelopts;
			}
		} else {
			$items[self::$optfortune] = self::$deffortune;
			$items[self::$optfortarg] = self::$deffortarg;
			$items[self::$optdisplay] = self::$defdisplay;
			$items[self::$opttextpos] = self::$deftextpos;
			$items[self::$optdelopts] = self::$defdelopts;
			add_option(self::$opt_group, $items);
		}
		return $items;
	}

	protected function init() {
		if ( ! $this->opt ) {
			$items = $this->init_opts();
			
			// set up Options_evh with its page, sections, and fields
			
			// prepare fields to appear under various sections
			// of admin page
			$Cf = self::mk_aclv('OptField');
			$fields0 = array(
				0 => new $Cf(self::$optfortune,
					self::ht(__('Fortune program path:')),
					self::$optfortune,
					$items[self::$optfortune] /*, $fcallback = '' */),
				1 => new $Cf(self::$optfortarg,
					self::ht(__('Fortune program arguments:')),
					self::$optfortarg,
					$items[self::$optfortarg] /*, $fcallback = '' */)
				);
			
			$fields1 = array(
				0 => new $Cf(self::$optdisplay,
					self::ht(__('Fortune display type:')),
					self::$optdisplay,
					$items[self::$optdisplay],
					array($this, 'put_disp_opt')),
				1 => new $Cf(self::$opttextpos,
					self::ht(__('Fortune inline placement:')),
					self::$opttextpos,
					$items[self::$opttextpos],
					array($this, 'put_text_pos'))
				);
			
			$fields2 = array(
				0 => new $Cf(self::$optdelopts,
					self::ht(__('When Fortune is uninstalled:')),
					self::$optdelopts,
					$items[self::$optdelopts],
					array($this, 'put_del_opts'))
				);
			
			// prepare sections to appear under admin page
			$Cs = self::mk_aclv('OptSection');
			$sections = array(
				0 => new $Cs($fields0,
					'fortune2_program_section',
					self::ht(__('Fortune Path Settings'))
					/*, $callback = '' */),
				1 => new $Cs($fields1,
					'fortune2_text_section',
					self::ht(__('Fortune Display Settings')),
					array($this, 'put_text_desc')),
				2 => new $Cs($fields2,
					'fortune2_inst_section',
					self::ht(__('Plugin Install Settings')),
					array($this, 'put_inst_desc'))
				);

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
				'Fortune2_settings',
				self::ht(__('Fortune Plugin')),
				self::ht(__('Fortune Plugin Configuration')),
				array($this, 'validate_opts'),
				/* pagetype = 'options' */ '',
				/* capability = 'manage_options' */ '',
				/* callback */ '',
				/* 'hook_suffix' callback array */ $suffix_hooks,
				self::ht(__('Configuration of Fortune Plugin')),
				self::ht(__('Path, options, and text output.')),
				self::ht(__('Save Settings'))
				);
			
			$Co = self::mk_aclv('Options');
			$this->opt = new $Co($page);
		}
	}
	
	protected function get_options () {
		$items = $this->init_opts();
		$this->fortune = $items[self::$optfortune];
		$this->fortarg = $items[self::$optfortarg];
		$this->textpos = $items[self::$opttextpos];
/*
		$this->fortune = $this->opt->get_option(self::$optfortune);
		if ( ! $this->fortune ) {
			$this->fortune = self::$deffortune;
		}
		$this->fortarg = $this->opt->get_option(self::$optfortarg);
		if ( $this->fortarg !== '' && ! $this->fortarg ) {
			$this->fortarg = self::$deffortarg;
		}
		$this->textpos = $this->opt->get_option(self::$opttextpos);
		if ( ! $this->textpos ) {
			$this->textpos = self::$deftextpos;
		}
*/
	}
	
	protected function mk_new () {
		$cmd = $this->fortune . ' ' . $this->fortarg;
		$ret = 0;
		$out = array();
		$this->cur = exec($cmd, $out, $ret);
		if ( $ret == 0 ) {
			$this->cur = implode("\n", $out);
		} else {
			$this->cur .= "\n" . $ret . " status from " . $cmd;
			$this->cur .= ': ' . implode("\n", $out);
		}
	}
	
	public function get_fortune () {
		if ( !$this->cur ) {
			$this->mk_new();
		}
		return $this->cur;
	}
	
	public function get_fortune_once () {
		$t = $this->get_fortune();
		$this->cur = '';
		return $t;
	}
	
	public function get_fortune_html () {
		$t = $this->get_fortune();
		return self::ht($t);
	}
	
	public function get_fortune_once_html () {
		$t = $this->get_fortune_html();
		$this->cur = '';
		return $t;
	}
	
	public static function static_get_fortune_html () {
		$cl = __CLASS__;
		$fc = new $cl(false);
		$ft = $fc->get_fortune_html();
		$fc = null;
		return $ft;
	}

	// callback: validate options
	public function validate_opts($opts) {	
		$a_out = array();
		$nerr = 0;
		$nupd = 0;
		
		// checkboxes |= ugly hack
		$a_out[self::$optdisplay] = 0;
		$odisp = 0 + $this->opt->get_option(self::$optdisplay);
		// another checkbox
		$odelopt = $this->opt->get_option(self::$optdelopts);
		$a_out[self::$optdelopts] = 'false';

		foreach ( $opts as $k => $v ) {
			$ot = trim($v);
			$oo = trim($this->opt->get_option($k));

			switch ( $k ) {
				case self::$optfortune:
					// limited chars for filesys path
					$re =
					'/^(\/[a-z0-9\/_-]*\/)?fortune[a-z0-9\._-]*$/i';
					if ( preg_match($re, $ot) == 1 ) {
						$a_out[$k] = $ot;
						$nupd += ($ot === $oo) ? 0 : 1;
					} else {
						$e = __('bad fortune program path:');
						$e .= ' "' . $ot . '"';
						self::errlog($e);
						add_settings_error('fortune program path',
							sprintf('%s[%s]', self::$opt_group, $k),
							self::ht($e),
							'error');
						$a_out[$k] = $oo;
						$nerr++;
					}
					break;
				case self::$optfortarg:
					// limited chars for program options
					$re = '/^[A-Za-z0-9\/\. _-]*$/';
					if ( preg_match($re, $ot) ) {
						$a_out[$k] = $ot;
						$nupd += ($ot === $oo) ? 0 : 1;
					} else {
						$e = __('bad fortune program options:');
						$e .= ' "' . $ot . '"';
						self::errlog($e);
						add_settings_error('fortune program options',
							sprintf('%s[%s]', self::$opt_group, $k),
							self::ht($e),
							'error');
						$a_out[$k] = $oo;
						$nerr++;
					}
					break;
				case self::$optdisplay.'1':
				case self::$optdisplay.'2':
				// the integer suffix is a checkbox hack
				// this case is not called if all boxes are unchecked
					$t = 0 + $ot;
					if ( $t < 0 || $t > 3 ) {
						$e = 'bad check option: "' . $ot . '", crack?';
						self::errlog($e);
						add_settings_error('fortune text display',
							sprintf('%s[%s]', self::$opt_group,
								self::$optdisplay),
							self::ht($e),
							'error');
						$a_out[self::$optdisplay] = $odisp;
						$nerr++;
					} else {
						$a_out[self::$optdisplay] |= $t;
						$nupd += ($t & (0+$odisp)) ? 0 : 1;
					}
					break;
				case self::$opttextpos:
					if ( $ot !== 'top' && $ot !== 'bot' ) {
						$e = 'bad radio option: "' . $ot . '", crack?';
						self::errlog($e);
						add_settings_error('fortune text placement',
							sprintf('%s[%s]', self::$opt_group, $k),
							self::ht($e),
							'error');
						$a_out[$k] = $oo;
						$nerr++;
					} else {
						$a_out[$k] = $ot;
						$nupd += ($ot === $oo) ? 0 : 1;
					}
					break;
				case self::$optdelopts:
					if ( $ot != 'true' ) {
						$e = 'bad check option: "' . $ot . '", crack?';
						self::errlog($e);
						add_settings_error('fortune delete opts',
							sprintf('%s[%s]', self::$opt_group,
								self::$optdelopts),
							self::ht($e),
							'error');
						$a_out[self::$optdelopts] = $odelopt;
						$nerr++;
					} else {
						$a_out[self::$optdelopts] = $ot;
					}
					break;
				case 'activestate':
					// this is a debugging key; do nothing
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

		// checkboxes
		if ( $a_out[self::$optdisplay] == 0 && $odisp > 0 ) {
			$nupd++;
		}
		// must coerce integer > text; int 0 makes trouble in if($foo)
		$a_out[self::$optdisplay] = "" . $a_out[self::$optdisplay];
		// more checkboxes
		if ( $a_out[self::$optdelopts] != $odelopt ) {
			$nupd++;
		}

		if ( $nerr == 0 && $nupd > 0 ) {
			add_settings_error(self::$opt_group,
				sprintf('%s[%s]', self::$opt_group, $k),
				self::ht(__('Settings updated correctly')),
				'updated');
		} else  if ( $nerr > 0 && $nupd > 0 ) {
			add_settings_error(self::$opt_group,
				sprintf('%s[%s]', self::$opt_group, $k),
				self::ht(
					sprintf(__('Some settings (%d) updated'), $nupd)
					),
				'updated');
		}
		
		return $a_out;
	}

	// callback: put html for text position field description
	public function put_text_desc() {
		$t = self::ht(__('Set display options:'));
		printf('<p>%s</p>%s', $t, "\n");
	}

	// callback: put html for text position field description
	public function put_inst_desc() {
		$t = self::ht(__('Set install options'));
		printf('<p>%s', $t);

		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts && array_key_exists('activestate', $opts) ) {
			$as = $opts['activestate'];
			$t = self::ht(__('active state: '));
			printf(' (%s%s):</p>%s', $t, $as, "\n");
		} else {
			printf(':</p>%s', "\n");
		}
	}

	// callback: put html for form field for text position
	public function put_disp_opt($a) {
		$k = self::$optdisplay;
		if ( ! array_key_exists($k, $a) ) {
			self::errlog('display option key not found');
			return;
		}
		$group = self::$opt_group;
		$ti = self::ht(__('Display inline (near top or bottom)'));
		$tw = self::ht(__('Display sidebar widget'));

		$di = self::$disp_inline;
		$dw = self::$disp_widget;
		$v = $a[$k];
		$cs0 = $v & $di ? "checked='CHECKED'" : "";
		$cs1 = $v & $dw ? "checked='CHECKED'" : "";

		$s = "		<!-- Fortune plugin: display checkboxes -->\n";
		$s = "\n" . $s;
		echo $s;

		$s = "		<label><input type='checkbox' id='{$k}' ";
		$s = $s . "name='" . $group . "[".$k.'1'."]' ";
		$s = $s . "value='".$di."' " . $cs0 . " /> ";
		$s = $s . $ti . "</label><br/>\n";
		echo $s;
		$s = "		<label><input type='checkbox' id='{$k}' ";
		$s = $s . "name='" . $group . "[".$k.'2'."]' ";
		$s = $s . "value='".$dw."' " . $cs1 . " /> ";
		$s = $s . $tw . "</label><br/>\n";
		echo $s;
	}

	// callback: put html for form field for text position
	public function put_text_pos($a) {
		$group = self::$opt_group;
		$tt = self::ht(__('Place Near Top'));
		$bt = self::ht(__('Place Near Bottom'));

		// loop over opts; put input field for each
		// this loop is wrong; don't use it in new code
		// it is left in place as a bad example
		foreach ( $a as $k => $v ) {
			$cs0 = $v == "top" ? "checked='CHECKED'" : "";
			$cs1 = $v == "bot" ? "checked='CHECKED'" : "";

			$s = "		<!-- Fortune plugin: radios for text pos -->\n";
			$s = "\n" . $s;
			echo $s;

			$s = "		<label><input type='radio' id='{$k}' ";
			$s = $s . "name='" . $group . "[$k]' ";
			$s = $s . "value='top' " . $cs0 . " /> ";
			$s = $s . $tt . "</label><br/>\n";
			echo $s;
			$s = "		<label><input type='radio' id='{$k}' ";
			$s = $s . "name='" . $group . "[$k]' ";
			$s = $s . "value='bot' " . $cs1 . " /> ";
			$s = $s . $bt . "</label><br/>\n";
			echo $s;
		}
	}

	// callback: put html for form field for opt delete
	public function put_del_opts($a) {
		$group = self::$opt_group;
		$tt = self::ht(__('Permanently delete settings (clean db)'));

		$k = self::$optdelopts;
		$v = $a[$k];
		$c = $v == 'true' ? "checked='CHECKED'" : "";

		$s = "		<!-- Fortune plugin: del opts checkbox-->\n";
		$s = "\n" . $s;
		echo $s;

		$s = "		<label><input type='checkbox' id='{$k}' ";
		$s = $s . "name='" . $group . "[".$k."]' ";
		$s = $s . "value='true' " . $c . " /> ";
		$s = $s . $tt . "</label><br/>\n";
		echo $s;
	}

	/**
	 *	print a fortune using Fortune2_evh; the string is processed
	 *  for HTML special chars, and is placed in <p/> with 'id' to match
	 *  a CSS block
	 */
	public function put_text_html() {
		$bits = 0;
		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts && array_key_exists(self::$optdisplay, $opts) ) {
			$bits |= $opts[self::$optdisplay];
		}
		if ( ! ($bits & self::$disp_inline) ) {
			return;
		}
		$ts = $this->get_fortune_html();
		printf('<p id="fortune2_message">%s</p>%s', $ts, "\n");
	}
	
	public function put_text_js() {
		$bits = 0;
		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts && array_key_exists(self::$optdisplay, $opts) ) {
			$bits |= $opts[self::$optdisplay];
		}
		if ( ! ($bits & self::$disp_inline) ) {
			return;
		}
		?>

		<p id="fortune2_message">
		<script type='text/javascript'>
		/* <![CDATA[ */
			document.writeln(""+fortune2_get_fortune());
		/* ]]> */
		</script>
		</p>

		<?php
	}
	
	/**
	 *	print a CSS block for our output
	 */
	public function mk_css() {
		global $text_direction;
		// right-to-left language?
		if ( function_exists('is_rtl') ) {
			$x = is_rtl() ? 'left' : 'right';
		} else if ( isset($text_direction) ) {
			$x = ($text_direction  == 'ltr' ) ? 'right' : 'left';
		} else {
			$x = 'right';
		}
		
		// this static called before init, so get option
		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts && array_key_exists(self::$opttextpos, $opts) ) {
			$this->textpos = $opts[self::$opttextpos];
		}
		$pos = $this->textpos == 'top' ? 'top: 4.5em;' : '';
		$off = $this->textpos == 'top' ? '220px;' : '12px;';
	
		echo <<<OMM
	<style type='text/css'>
	/* <![CDATA[ */
	#fortune2_message {
		position: absolute;
		$pos
		margin: 0;
		padding: 4px;
		$x: $off
		font-size: 11px;
		color: #0C5273;
	}
	/* ]]> */
	</style>
	
OMM;
	}
	
	/**
	 *	print head scripts and a CSS block for our output
	 */
	public function mk_scripts_css() {
		// css 1st
		$this->mk_css();
		
		// now js at head level
		$bits = 0;
		$opts = get_option(self::$opt_group); // WP get_option()
		if ( $opts && array_key_exists(self::$optdisplay, $opts) ) {
			$bits |= $opts[self::$optdisplay];
		}
		if ( $bits & self::$disp_inline ) {
			// encode the fortune; js has unescape()
			$fesc = rawurlencode($this->get_fortune_html());
			?>

			<script type='text/javascript'>
			/* <![CDATA[ */
			function fortune2_get_fortune() {
				var fesc = '<?php echo $fesc; ?>';
				return unescape(fesc);
			}
			/* ]]> */
			</script>

			<?php
		}
	}

	// utility
	protected static function mk_aclv($pfx) {
		$s = $pfx . '_' . self::$aclv;
		return $s;
	}
	
	public static function ht($text) {
		if ( function_exists('wptexturize') ) {
			return wptexturize($text);
		}
		//return htmlspecialchars($text);
		return htmlentities($text, ENT_QUOTES, 'UTF-8');
	}
	
	public static function errlog($err) {
		$e = sprintf('Fortune2 WP plugin: %s', $err);
		error_log($e, 0);
	}
	
	// helper to make self
	public static function instantiate($init = true) {
		$cl = __CLASS__;
		self::$instance = new $cl($init);
		return self::$instance;
	}
} // End class Fortune2_evh
endif; // if ( ! class_exists('Fortune2_evh') ) :

/**
 * class handling 'fortunes' as widget; uses Fortune2_evh
 */
if ( ! class_exists('Fortune2_widget_evh') ) :
class Fortune2_widget_evh extends WP_Widget {
	public static $fortune_plugin = 'Fortune2_evh';

	public function Fortune2_widget_evh () {
		$desc = __( 'Unix fortunes for your visitors (and you)');
		$ops = array('classname' => '' . __CLASS__,
			'description' => $desc);
		$this->WP_Widget('fortune2', __('Fortune'), $ops);
	}

	public function widget ($args, $instance) {
		$ftn = ':-(';
		$cl = self::$fortune_plugin;
		eval("\$d = ${cl}::\$disp_widget;");
		eval("\$g = ${cl}::\$opt_group;");
		eval("\$o = ${cl}::\$optdisplay;");
		$opt = null;
		$g = get_option($g);
		if ( $g && array_key_exists($o, $g) ) {
			$opt = $g[$o];
		}
		$bits = 0;
		if ( $opt ) {
			$bits |= $opt;
		}
		if ( $bits & $d ) {
			$f = new $cl(false);
			$ftn = $f->get_fortune_html();
			$f = null;
		} else {
			return;
		}

		extract($args);

		$title = apply_filters('widget_title',
			empty($instance['title']) ?
				__('Your Fortune:') :
				$instance['title'],
			$instance, $this->id_base);

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		printf("<p>%s</p>\n", $ftn);

		echo $after_widget;
	}

	public function update ($new_instance, $old_instance) {
		$instance = $new_instance;
		
		// sample/test diddle w/ array
		foreach ( $old_instance as $k => $v ) {
			if ( ! array_key_exists($k, $instance) ) {
				$instance[$k] = $v;
			}
		}
		if ( ! array_key_exists('title', $instance) ) {
			$instance['title'] = '';
		} else {
			// just testing . . .
			$instance['title'] = str_replace('Mizz', 'Miss',
				$instance['title']);
		}

		return $instance;
	}

	public function form ($instance) {
		$ht = 'wptexturize';
		$instance = wp_parse_args((array)$instance,
			array('title' => ''));
		$title = $ht($instance['title']);
		$id = $this->get_field_id('title');
		$nm = $this->get_field_name('title');
		$tl = $ht(__('Fortune Title:'));
		?>

		<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
		<input class="widefat" id="<?php echo $id; ?>"
			name="<?php echo $nm; ?>"
			type="text" value="<?php echo $title; ?>" /></p>

		<?php
	}
} // End class Fortune2_widget_evh
endif; // if ( ! class_exists('Fortune2_widget_evh') ) :


/**********************************************************************\
 *  standalone functions                                              *
\**********************************************************************/

// not just now


/**********************************************************************\
 *  plugin 'main()' level code                                        *
\**********************************************************************/

/**
 * 'main()' here
 */
$fortune2_evh_instance_1 = null;
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	$fortune2_evh_instance_1 = Fortune2_evh::instantiate();
}

// End PHP script:
?>
