<?php
/*
 *  Options_0_0_2b.inc.php
 *  
 *  Copyright 2011 Ed Hynan <edhynan@gmail.com>
 *  
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; specifically version 3 of the License.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *  MA 02110-1301, USA.
 */

/*
* Description: class for settings page
* Version: 1.0
* Author: Ed Hynan
* License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/* text editor: use real tabs of 4 column width, LF line ends */


/**********************************************************************\
 *  Class defs                                                        *
\**********************************************************************/

/**
 * class to handle options under WordPress
 * note[1] the example for WP options/settings provided by Otto:
 *     http://ottopress.com/2009/wordpress-settings-api-tutorial/
 * note[2] the pertinent WP function's names include the words
 * 'setting' and 'option' as if synonymous; in honor of that
 * inconsistency the class is named Options[...] and its methods
 * names use the word 'settings'
 */
class Options_0_0_2b {
	// help detect class name conflicts; called by using code
	// const evh_opt_id = 0xED00AA33; // N.G. < 5.3
	private static $evh_opt_id = 0xED00AA33;
	public static function id_token () {
		return self::$evh_opt_id;
	}

	// OptPage_0_0_2b instance
	protected $pg;
	// this holds return from WP 'add_FOO_page()',
	// sometimes called '$hook_suffix' in WP docs -- get value
	// with method get_page_suffix()
	protected $page_suffix;
	
	public function __construct($page_obj /* OptPage_0_0_2b instance */)
	{
		// assign our page object
		$this->pg = $page_obj;
		
		// FPO
		$this->page_suffix = null;
		
		// do the WP opt mechanism:
		$this->setup_admin_hooks();
	}
	
	public function __destruct() {
		$this->pg = null;
	}

	// After creating an instance of this class get options here
	public function get_option($opt_name) {
		// use WP get_option()
		$opts = get_option($this->pg->group);
		if ( $opts && array_key_exists($opt_name, $opts) ) {
			return $opts[$opt_name];
		}
		return '';
	}

	// return '$hook_suffix' returned from WP:: add_*_page()
	public function get_page_suffix() {
		return $this->page_suffix;
	}
	
	/**
	 * members and methods for WP admin page mechanism:
	 * NOTE! *none* of the following methods should be called by
	 * plugin code. Those marked "public" are *only* public to be
	 * used by WordPress code as callbacks!
	 */

	// methods
	protected function setup_admin_hooks() {
		// each of the callbacks passed passed in the two calls will
		// call more WP functions that take another callback; so, they
		// lead into a callback set
		add_action('admin_menu', array($this, 'add_admin_page'));
		// add the admin settings
		add_action('admin_init', array($this, 'init_admin_page'));
	}

	// begin 'admin_menu' callback set
	public function add_admin_page() {
		$fpage = 'add_' . $this->pg->pagetype . '_page';

		$this->page_suffix = $fpage(
			$this->pg->title, // page title,
			$this->pg->label, // menu text, appears under "Plugins"
			$this->pg->capability, // 'capability' for page access
			$this->pg->id, // menu+page unique id,
			$this->pg->callback ? $this->pg->callback :
			array($this, 'admin_page'));
		
		if ( $this->page_suffix && $this->pg->cbsuffixs ) {
			// if provided callbacks to page hooks, hook them
			foreach ( $this->pg->cbsuffixs as $hook => $cb ) {
				$h = $hook . "-" . $this->page_suffix;
				add_action($h, $cb);
			}
		}

	}

	// useful example/default for a settings/options page; this
	// will work from other page menus, but that might not be sane
	// IAC, note the sequence of data and function calls
	public function admin_page() {
		// check caps in page object
		// Note: the error strings are taken from WP core code (v3.6)
		// exactly, so that it will use default translations if any.
		// This depends, of course, on the string not changing in
		// WP core; these strings have many translated uses
		// (found in xgettext pot file for __ and _e), so perhaps
		// they will be stable.
		if ( ! current_user_can($this->pg->capability) )  {
			// this has sixteen (16) uses:
			//wp_die(__('You do not have permission to access this page.'));
			// this has seven (7) uses:
			wp_die(__('You do not have sufficient permissions to manage options for this site.'));
		}
		
		// put html
		?>
		<div class="wrap">
		<div id="<?php
			echo $this->pg->pagehead_id;
			?>" class="icon32"><br/></div>
		<h2><?php
			echo $this->pg->pagehead;
			?></h2>
		<?php 
			// NOTE 2013/09/20: *do not* call settings_errors();
			// it was never needed (was here due to examples on web)
			// at least from 3.0.2 -> 3.3.1 -- but the call was OK
			// because apparently WP had some guard against dup call,
			// but somewhere between 3.3.1 and 3.5.? the guard or
			// whatever was dropped (or broken? . . .) and duplicate
			// messages are shown! commented call left in place for info
			//settings_errors();
			echo $this->pg->pageintro;
			?>
		<form action="options.php" method="post">
		<?php
			settings_fields($this->pg->group);
			do_settings_sections($this->pg->id);
			?>
		
		<input name="Submit" type="submit" class="button-primary" value="<?php
			esc_attr_e($this->pg->savelabel);
			?>" />
		</form></div>
		
		<?php
		return true;
	}

	// begin 'admin_init' callback set

	// add the admin settings
	public function init_admin_page() {
		register_setting($this->pg->group, // option group
			$this->pg->group, // opt name; using group passes all to cb
			$this->pg->cbvalidate ? $this->pg->cbvalidate :
			array($this, 'options_validate'));
		
		$sects = $this->pg->sections;
		foreach ( $sects as $k => $v ) {
			add_settings_section($v->id, // section 'id'
				$v->label, // section head text
				$v->callback ? $v->callback :
				array($this, 'settings_section'), // section text
				$this->pg->id); // page unique id
	
			$fields = $v->fields;
			foreach ( $fields as $l => $u ) {
				$ov = self::get_option($u->key);
				if ( ! $ov ) {
					$ov = $u->defval;
				}
				
				add_settings_field($u->id, // 'id' of field
					$u->label, // label for the field
					$u->callback ? $u->callback :
					array($this, 'settings_field'), // put field html
					$this->pg->id, // page unique id
					$v->id, // section id
					array($u->key => $ov) // passed to callback
					);
			}
		}
	}

	// default callback: put html for settings section description
	public function settings_section() {
		// stub
		echo '<p>Set settings and options:</p>';
	}

	// default callback: put html for form field
	public function settings_field($a) {
		// loop over opts; put input field for each
		foreach ( $a as $k => $v ) {
			echo "<input id='{$k}'\n
				name='" . $this->pg->group . "[$k]'\n
				size='40' type='text'\n
				value='{$v}' />\n";
		}
	}

	// default callback: validate option arg from form
	// example only (obviously)
	public function options_validate($opts) {
		$a_out = array();

		foreach ( $this->pg->sections as $ksect => $sect ) {
			foreach ( $sect->fields as $kfield => $field ) {
				$k = $field->key;
				$ts = trim($opts[$k]);
				// allow limited chars for filesys path or program opts
				if ( preg_match('/^[A-Za-z0-9\/\. _-]*$/', $ts) ) {
					$a_out[$k] = $ts;
				} else {
					$a_out[$k] = $field->defval;
				}
			}
		}
		
		return $a_out;
	}
	// end callback sets

	// end members and methods for WP option page mechanism:
} // end Options_0_0_2b

?>
