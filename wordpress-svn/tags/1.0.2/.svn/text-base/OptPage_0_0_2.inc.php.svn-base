<?php
/*
 *  OptPage_0_0_2.inc.php
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
* Description: class used by class for settings page
* Version: 0.0.2
* Author: Ed Hynan
* License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/* text editor: use real tabs of 4 column width, LF line ends */

/**********************************************************************\
 *  Class defs                                                        *
\**********************************************************************/

/**
 * class for an settings/option page
 */
class OptPage_0_0_2 {
	// help detect class name conflicts; called by using code
	// const evh_opt_id = 0xED00AA33; // N.G. < 5.3
	private static $evh_opt_id = 0xED00AA33;
	public static function id_token () {
		return self::$evh_opt_id;
	}

	public $group;        // option group unique key (string)
	public $sections;     // array of OptSection_0_0_2 instances
	public $id;           // page+menu unique id; string
	public $label;        // not label, menu item text
	public $title;        // page title; e.g. shown in browser titlebar
	public $cbvalidate;   // callback to validate option values -
	                      // if not given Options_0_0_2 class default
	                      // is used; the default is certainly not
	                      // suitable, but stands as an example
	public $pagetype;     // add page of this type: see page_type()
	                      // default 'settings'
	public $capability;   // required for page access -
	                      // default 'manage_options'
	public $callback;     // to put page body html -
	                      // if not given Options_0_0_2::settings_page
	                      // is used; see that as an example
	public $cbsuffixs;    // the WP 'add_FOO_page()' functions return a
						  // '$hook_suffix" that can be used for
						  // page specific hooks that WP generates, e.g.
						  // load-$cbsuffixs,
						  // admin_print_scripts-$cbsuffixs,
						  // admin_head-$cbsuffixs; see WP docs
						  // arg is array with key as hook prefix
						  // *without* dash (e.g. 'load'), value as
						  // callback.

	// if $callback is left to the default then all the following
	// should be given for use by the default callback
	public $pagehead;     // main <h2> for page
	public $pageintro;    // introductory text
	public $savelabel;    // label for 'Save' button

	// options not assigned from ctor arg:
	public $pagehead_id;  // main <h2> for page 'id=""' selects
						  // font and icon -- this is set according
						  // to $pagetype

	public function __construct($ogroup, $psections, $pid, $plabel,
		$ptitle,
		$pcbvalidate = '',
		$ptype = '',
		$pcapability = '', //'manage_options',
		$pcallback = '',
		$pcbsuffixs = '',
		$ppagehead = '',
		$ppageintro = '',
		$psavelabel = '')
	{
		$this->group = $ogroup;
		$this->sections = $psections;
		$this->id = $pid;
		$this->label = $plabel;
		$this->title = $ptitle;

		$this->cbvalidate = $pcbvalidate;
		$this->pagetype = $ptype ? $ptype : 'options';
		$this->capability =
			$pcapability ? $pcapability : 'manage_options';

		$this->callback = $pcallback;
		$this->cbsuffixs = $pcbsuffixs;
		// the following defaults are not useful . . .
		$this->pagehead = $ppagehead ? $ppagehead : __('Settings');
		$this->pageintro =
			$ppageintro ? $ppageintro : __('Options here:');
		$this->savelabel = $psavelabel ? $psavelabel : __('Save');
		
		$this->page_type();
	}

	protected function page_type () {
		// this switch does 1) check $pagetype arg, and
		// 2) for any $pagetype assign suitable 'id=' tag
		// for the pages introductory <h2> header
		// As of 22.10.2011 item 2) above is unlikely to be
		// entirely correct. Users of class can change $pagehead_id
		// of an instance as it is public.
		switch ( $this->pagetype ) {
			case 'comments':
				$this->pagehead_id = 'icon-edit-comments'; break;
			case 'dashboard':
				$this->pagehead_id = 'icon-index'; break;
			case 'links':
				$this->pagehead_id = 'icon-link-manager'; break;
			case 'management': // tools menu
				$this->pagehead_id = 'icon-admin'; break;
			case 'media':
				$this->pagehead_id = 'icon-upload'; break;
			case 'menu':
				// ???
				//$this->pagehead_id = 'icon-options-general'; break;
				$this->pagehead_id = 'icon-themes'; break;
			case 'object':
				// ???
				$this->pagehead_id = 'icon-options-general'; break;
			case 'pages':
				$this->pagehead_id = 'icon-edit-pages'; break;
			case 'posts':
				$this->pagehead_id = 'icon-edit'; break;
			/* TODO:
			 * submenu support ?
			case 'submenu':
				// ???
				//$this->pagehead_id = 'icon-options-general'; break;
				$this->pagehead_id = 'icon-themes'; break;
			 */
			case 'theme':
				$this->pagehead_id = 'icon-themes'; break;
			case 'users':
				$this->pagehead_id = 'icon-users'; break;
			case 'utility':
				$this->pagehead_id = 'icon-tools'; break;
			case 'plugins':
				$this->pagehead_id = 'icon-plugins'; break;
			case 'options': // options/settings synonyms
				/* fall through */
			default:
				$this->pagetype = 'options';
				$this->pagehead_id = 'icon-options-general'; break;
		
				/* other similar -- ??
				$this->pagehead_id = 'icon-post'; break;
				$this->pagehead_id = 'icon-link'; break;
				$this->pagehead_id = 'icon-link-category'; break;
				$this->pagehead_id = 'icon-page'; break;
				$this->pagehead_id = 'icon-profile'; break;
				$this->pagehead_id = 'icon-user-edit'; break;
				$this->pagehead_id = 'icon-ms-admin'; break;
				*/
		}
	}

}

?>
