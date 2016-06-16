<?php
/*
 *      class-SWF-params-evh.php
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

/**
 * This is a class definition to hold parameters used
 * in video objects, with some methods for accessing
 * the parameters (simple and self-explanatory).
 * The sanitize() method is the largest and as name suggests,
 * constrains applied parameter values.
 */
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
		'mobiwidth' => '0',        // width if ( wp_is_mobile() )
		'audio' => 'false',        // source is audio; (mp3 is detected)
		'aspectautoadj' => 'false', // adj. common sizes, e.g. 720x480
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
		'defaultplaypath' => '',
		// <object>
		'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
		'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0',
		// align, for alignment class in markup:
		// left, center, right, none
		'align' => 'center',
		'preload' => 'image'
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
			case 'mobiwidth':
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
			// strings with a set of valid values that can be checked
			case 'align':
				switch ( $t ) {
					case 'left':
					case 'right':
					case 'center':
					case 'none':
						break;
					default:
						$i[$k] = $v;
						break;
				}
				break;
			case 'preload':
				switch ( $t ) {
					case 'none':
					case 'metadata':
					case 'auto':
					case 'image':
						break;
					default:
						$i[$k] = $v;
						break;
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
?>
