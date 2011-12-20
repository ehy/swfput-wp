<?php
/*
 *  OptSection_0_0_2.inc.php
 *  
 *  Copyright 2011 Ed Hynan <freecode@verizon.net>
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
* Author URI: http://wpblog.example.org/
* License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/* text editor: use real tabs of 4 column width, LF line ends */

/**********************************************************************\
 *  Class defs                                                        *
\**********************************************************************/

/**
 * class for individual section of a settings/option page
 */
class OptSection_0_0_2 {
	// help detect class name conflicts; called by using code
	// const evh_opt_id = 0xED00AA33; // N.G. < 5.3
	private static $evh_opt_id = 0xED00AA33;
	public static function id_token () {
		return self::$evh_opt_id;
	}

	public $fields;       // array of OptField_0_0_2 instances
	public $id;           // section unique id; string
	public $label;        // not label, section header
	public $callback;     // to put section description under header -
	                      // if not given then
	                      // Options_0_0_2::settings_section()
	                      // is used; see that as an example

	public function __construct($sfields, $sid, $slabel,
		$scallback = '')
	{
		$this->fields = $sfields;
		$this->id = $sid;
		$this->label = $slabel;
		$this->callback = $scallback;
	}
}

?>
