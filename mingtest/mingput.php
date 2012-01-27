<?php
/*
 *  mingput.php
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
* Description: build SWF video app with PHP/Ming, put on stdout
* Version: 0.1.0
* Author: Ed Hynan
* Author URI: http://wpblog.example.org/
* License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/* text editor: use real tabs of 4 column width, LF line ends */

/**********************************************************************\
 *  PHP + module initialization/checks:                               *
\**********************************************************************/


// set this const to false on release
// (originally had used 'const', not realizing it is new to php 5.3)
//const develtime = true;
define( 'develtime', true );
$i_release = develtime ? 0 : 1;

if ( develtime ) {
	error_reporting(E_STRICT | E_ALL);
}

// PHP docs say: if dl() is prohibited by enable_dl or safe_mode,
// dl() will emit an error and stop execution.
// That could stop e.g. a weblog page; but,
// user must have ensured proper setup before installing . . .
// docs also say dl() is removed from 'some' PHP5.3 SAPI's, and that is
// the case with Apache PHP module. but anyway . . .
if ( ! extension_loaded('ming') ) {
    $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
    $mod = $prefix . 'ming.' . PHP_SHLIB_SUFFIX;
    if ( ! ( function_exists('dl') && dl($mod) ) ) {
		// will not not see message if dl() makes PHP stop
		$m = "%s extension not loaded and dl(%s) FAILED; ";
		$m .= "%s cannot produce Shockwave Flash program!\n";
		$m = sprintf($m, $mod, $mod, __FILE__);
		die($m);
	}
}

// set boolean to check CLI mode; it's easier than string comparison
$climode = php_sapi_name() == 'cli' ? true : false;

/**********************************************************************\
 *  PHP procedures:                                                   *
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

/**
 * encode a path for a URL, e.g. from parse_url['path']
 * $func might also be urlencode(), or user defined
 * inheritable
 */
if ( ! function_exists('upathencode') ) :
function upathencode($p, $func = 'rawurlencode')
{
	return implode('/',
		array_map($func,
			explode('/', $p) ) );
}
endif; // if ( ! function_exists('upathencode') :

/**
 * check that URL passed in query is OK; re{encode,escape}
 * $args is array of boolean values, plus two regex pats -- all optional
 * requirehost, requirepath, rejuser, rejport, rejquery, rejfrag (bools)
 * rxproto, rxpath (regex search patterns); consider requirehost to
 * imply proto is required
 * inheritable
 */
if ( ! function_exists('check_url_arg') ) :
function check_url_arg($url, $args = array())
{
	extract($args);
	$vurl = '';
	$p = '/';
	$ua = parse_url($url);
	if ( array_key_exists('path', $ua) ) {
		$t = ltrim($ua['path'], '/');
		if ( isset($rxpath) ) {
			if ( ! preg_match($rxpath, $t) ) {
				return false;
			}
		}
		$p .= upathencode($t);
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
endif; // if ( ! function_exists('check_url_arg') :

// Is PHP clever about float comparisons internally?
// IAC, knowing the scale of numbers used here
// use an epsilon:
define( 'fepsilon', 0.0001 );

// equilateral triangle height from base and vice versa
// using ratio constants, calculated with bc (scale = 20)
define( 'treq_r_bh', 1.15470053837925152901 );
define( 'treq_r_hb', 0.86602540378443864676 );
// equilateral triangle height from base
function treqheight($base)
{
	//return sqrt(($base * $base) * 0.75);
	return $base * treq_r_hb;
}
// equilateral triangle base from height
function treqbase($height)
{
	//return sqrt(($height * $height) / 0.75);
	return $height * treq_r_bh;
}
// in equi tri, not rotated, this is the ratio of the triangle's
// center point y ordinate to the base, so an equi tri in quad 1 w/
// lower left vert at 0,0 has center point (base/2),(base*treq_mid_y)
// this == sqrt(sqr(tan(deg2rad(30))) - 0.25)
define( 'treq_mid_y', 0.28867513459481 );

/**
 *  Find length of line with endpoints $x0,$y0 and $x1,$y1
 */
function line_length($x0, $y0, $x1, $y1)
{
	$dx = abs($x1 - $x0); $dy = abs($y1 - $y0);
	if ( $dx < fepsilon ) { return $dy; }
	if ( $dy < fepsilon ) { return $dx; }
	return hypot($dx, $dy);
}

/**
 *  Rotate an array of points &$pts in the form:
 *  array(0 => array(0 => x, 1 => y), 1 => array(0 => x, 1 => y), ...)
 *  by $angle radians (clockwise) around center $ctrX, $ctrY
 *  Returns $pts
 */
function points_rotate(&$pts, $angle, $ctrX, $ctrY)
{
	foreach ( $pts as $i => &$pt ) {
		$x = &$pt[0]; $y = &$pt[1];

		$x -= $ctrX; $y -= $ctrY;
		// flip: might seem odd; found it necessary once with
		// Symantec C++ 7.x (long time ago). It's harmless.
		$flip = $y < 0.0 ? true : false;
		if ( $flip ) {
			$x = -$x; $y = -$y;
		}

		$r = line_length($x, $y, 0.0, 0.0);
		if ( abs($r) < fepsilon ) {
			if ( $flip ) { $x = -$x; $y = -$y; }
			$x += $ctrX; $y += $ctrY;
			continue;
		}

		$a = acos($x / $r) + $angle;
		$x = cos($a) * $r;
		$y = sin($a) * $r;
		if ( $flip ) { $x = -$x; $y = -$y; }
		$x += $ctrX; $y += $ctrY;
	}

	return $pts;
}

/**
 *  From an array of points $pts in the form:
 *  array(0 => array(0 => x, 1 => y), 1 => array(0 => x, 1 => y), ...)
 *  with first being starting control point and last two points
 *  being the same as first two points, draw cubic spline
 *  on shape &$obj from array $pts
 *  Array count should be a multiple of 3, + 1.
 */
function mingshape_drawcubic(&$obj, $pts)
{
	$x  = $pts[0][0];
	$y  = $pts[0][1];
	$obj->movePenTo($x, $y);

	for ( $n = 1; $n < count($pts) - 3; $n++ ) {
		$x0 = $pts[$n][0];
		$y0 = $pts[$n][1];
		$x1 = $pts[$n+1][0];
		$y1 = $pts[$n+1][1];
		$x2 = $pts[$n+2][0];
		$y2 = $pts[$n+2][1];

		$obj->drawCubicTo($x0, $y0, $x1, $y1, $x2, $y2);
	}

	return $obj;
}

/**
 *  From an array of points $pts in the form:
 *  array(0 => array(0 => x, 1 => y), 1 => array(0 => x, 1 => y), ...)
 *  each locating a vertice of a polygon, with the first and last
 *  points being the same and closing the figure, draw the figure on
 *  a Ming library shape object &$obj
 *  Returns $obj
 */
function mingshape_drawpoly(&$obj, $pts)
{
	$x = $pts[0][0];
	$y = $pts[0][1];

	$obj->movePenTo($x, $y);

	for ( $n = 1; $n < count($pts); $n++ ) {
		$x = $pts[$n][0];
		$y = $pts[$n][1];

		$obj->drawLineTo($x, $y);
	}

	return $obj;
}

/**
 *  Draw equilateral triangle on a Ming library shape object
 *  From top-left $originX,$originY triangle will be centered in
 *  square $height,$height; i.e. will descend from y==$originY
 *  to y==$originY+$height, and x center of base will be at
 *  x==$originX+($height/2)
 *  Rotation by $angle radians will be centered on that square
 */
function
mingshape_drawtreq(&$obj, $originX, $originY, $height, $angle = 0)
{
	$h2 = $height / 2.0;
	$base = treqbase($height);
	$b2 = $base / 2.0;
	$boff = ($height - $base) / 2.0;

	$pts = array(
		0 => array(0 => ($originX + $boff),
				1 => ($originY + $height)),
		1 => array(0 => ($originX + $boff + $base),
				1 => ($originY + $height)),
		2 => array(0 => ($originX + $boff + $b2),
				1 => ($originY)),
		3 => array(0 => ($originX + $boff),
				1 => ($originY + $height))
	);

	if ( $angle ) {
		points_rotate($pts, $angle, $originX + $h2, $originY + $h2);
	}

	return mingshape_drawpoly($obj, $pts);
}

/**
 *  Draw equilateral triangle on a Ming library shape object
 *  with the triangle's center point at $originX,$originY
 *  Rotation by $angle radians will be centered on that point
 */
function
mingshape_drawtreq2(&$obj, $originX, $originY, $height, $angle = 0)
{
	$base = treqbase($height);
	$b2 = $base / 2.0;
	$x0 = -$b2;
	$y0 = $base * treq_mid_y;
	$xoff = $x0 + $originX;
	$yoff = $y0 + $originY;

	$pts = array(
		0 => array(0 => $xoff, 1 => $yoff),
		1 => array(0 => $xoff + $base, 1 => $yoff),
		2 => array(0 => $xoff + $b2, 1 => $yoff - $height),
		3 => array(0 => $xoff, 1 => $yoff)
	);

	if ( $angle ) {
		points_rotate($pts, $angle, $originX, $originY);
	}

	return mingshape_drawpoly($obj, $pts);
}

/**
 *  Draw rectangle on a Ming library shape object
 *  $originX, $originY set the top-left corner
 *  If $angle is not 0, rectangle is rotated on its center
 *  clockwise $angle radians
 */
function
mingshape_drawrect(&$obj, $originX, $originY, $wi, $hi, $angle = 0)
{
	$pts = array(
		0 => array(0 => $originX,
				1 => $originY),
		1 => array(0 => ($originX + $wi),
				1 => $originY),
		2 => array(0 => ($originX + $wi),
				1 => ($originY + $hi)),
		3 => array(0 => $originX,
				1 => ($originY + $hi)),
		4 => array(0 => $originX,
				1 => $originY)
	);

	if ( $angle ) {
		$w2 = $wi / 2.0;
		$h2 = $hi / 2.0;

		points_rotate($pts, $angle, $originX + $w2, $originY + $h2);
	}

	return mingshape_drawpoly($obj, $pts);
}

/**********************************************************************\
 *  global properties: with defaults not subject to arguments         *
 *  these can/should be hand edited                                   *
\**********************************************************************/

// set flash version
// only v. 4-9 presently
$swfvs = 8;
// compiled output compression level integer 0-9 as for zlib
$swfcomp = 9;

$vurl = '';
// Additional URLs, css etc.
$obj_css_url = 'obj.css';


// a message to display on media failure (stream status StreamNotFound)
$nomediahdr = __('Error:');
$nomediamsg = __('The media request failed (stream not found).');
$js_nomediamsg = __('Flash Player: ') . $nomediamsg;
$nomediahdrht = htmlentities($nomediahdr, ENT_QUOTES, 'UTF-8');
$nomediamsght = htmlentities($nomediamsg, ENT_QUOTES, 'UTF-8');
$dismiss =
	htmlentities(__('(click this line or the control bar to dismiss)'),
	ENT_QUOTES, 'UTF-8');
// plugin supports only a small subset of html tags; keep it simple
// *AND* use only 'single quotes' or variable will not expand
// properly (or try quoting like \\")
$nomediahtml = <<<OYV
	<p class='headtxt'>$nomediahdrht</p><br />
	<p class='bodytxt'>$nomediamsght</p><br />
	<p class='footer'><a href='asfunction:hideinfohtml, null'>$dismiss
	</a></p><br />
OYV;

$connrejectmsg = __('The media request failed (connection rejected).');
$connrejecthtml = <<<OYV
	<p class='headtxt'>$nomediahdrht</p><br />
	<p class='bodytxt'>$connrejectmsg</p><br />
	<p class='footer'><a href='asfunction:hideinfohtml, null'>$dismiss
	</a></p><br />
OYV;

$connfailmsg = __('The media request failed (connection failed).');
$connfailhtml = <<<OYV
	<p class='headtxt'>$nomediahdrht</p><br />
	<p class='bodytxt'>$connfailmsg</p><br />
	<p class='footer'><a href='asfunction:hideinfohtml, null'>$dismiss
	</a></p><br />
OYV;

$playfailmsg = __('The media request failed (play failed).');
$playfailhtml = <<<OYV
	<p class='headtxt'>$nomediahdrht</p><br />
	<p class='bodytxt'>$playfailmsg</p><br />
	<p class='footer'><a href='asfunction:hideinfohtml, null'>$dismiss
	</a></p><br />
OYV;

// context menu item labels
$menusmoothT = __('Set Video Smoothing On');
$menusmoothF = __('Set Video Smoothing Off');
$menudeblock0 = __('Video compressor deblocking as needed');
$menudeblock1 = __('No deblocking filter');
$menudeblock2 = __('Sorenson deblocking');
$menudeblock3 = __('On2 deblocking, no deringing');
$menudeblock4 = __('On2 deblocking, fast deringing');
$menudeblock5 = __('On2 deblocking, better deringing (5)');
$menudeblock6 = __('Deblocking 6 (==5 as of Flash 8)');
$menudeblock7 = __('Deblocking 7 (==5 as of Flash 8)');
$menufullscr = __('View Fullscreen');
// The above items for deblocking are for older flv codecs; it seems
// h264 is common now -- also they might be confusing -- so hand
// adjust this option (string true||false):
$showdeblockingitems = 'false';

/**********************************************************************\
 *  global properties: with defaults subject to arguments             *
\**********************************************************************/

// default dimensions of 'Stage'
//$wndlength = 640;
//$wndheight = 480;
// 216x138 is minimum size; else flash user settings dialog is clipped
$wndlength = 216;
$wndheight = 138;

if ( $climode ) {
	// for cli args must be equivalents of the query args;
	// they're put into an array to use the same foreach/switch
	$av = array();
	for ( $t = 1; $t < $argc; $t++ ) {
		$a = explode('=', $argv[$t]);
		$av[$a[0]] = $a[1];
	}
} else { // ! $climode
	// request method?
	if ( array_key_exists('FN', $_POST) ) {
		$av = &$_POST;
	} else {
		$av = &$_GET;
	}
} // if ( ! $climode ) {

// pick out args
foreach ( $av as $k => $v ) {
	switch ( $k ) {
		case 'IDV':
			$v_id = urldecode($v);
			break;
		case 'WI':
			if ( is_int(0 + $v) ) {
				$wndlength = 0 + $v;
			} else {
				error_log($k." got non int '".$v."'");
			}
			break;
		case 'HI':
			if ( is_int(0 + $v) ) {
				$wndheight = 0 + $v;
			} else {
				error_log($k." got non int '".$v."'");
			}
			break;
		case 'FN': // media url
			if ( $v ) {
				$a = array(
					'requirehost' => false, // can use orig host
					'requirepath' => true,
					'rejfrag' => true,
					// no, don't try to match extension; who knows?
					//'rxpath' => '/.*\.(flv|f4v|mp4|m4v|mp3)$/i',
					'rxproto' => '/^(https?|rtmp[a-z]{0,2})$/'
					);
				$vurl = check_url_arg($v, $a);
				if ( $vurl === false ) {
					die("unacceptable URL: '" . $v . "'");
				}
			} else {
				die("empty URL argument");
			}
			break;
		case 'ST': // player css
			if ( $v ) {
				$a = array(
					'requirehost' => false, // can use orig host
					'requirepath' => true,
					'rejuser' => true,
					'rejquery' => true,
					'rejfrag' => true,
					'rxpath' => '/.*\.css$/i',
					'rxproto' => '/^https?$/'
					);
				$obj_css_url = check_url_arg($v, $a);
				if ( $obj_css_url === false ) {
					die("unacceptable URL: '" . $v . "'");
				}
			}
			break;
		case 'PL':
			if ( $v == 'true' ) {
				$initpause = 'false';
			}
			break;
		case 'HB':
			if ( $v == 'true' ) {
				$initshowbar = 'false';
			}
			break;
		case 'VL':
			if ( $v ) {
				$t = round(max(0, min(100, 0 + $v)));
				$initvolume = $t;
			}
			break;
		case 'LP':
			if ( $v == 'true' ) {
				$doloop = $v;
			}
			break;
		case 'DB':
			if ( $v == 'true' ) {
				$disablebar = $v;
			}
			break;
		case 'AU':
			if ( $v == 'true' ) {
				$audb = 'true';
			}
			break;
		case 'AA':
			if ( $v == 'false' ) {
				$autoaspect = 'false';
			}
			break;
		case 'DA':
			if ( $v != 'D' ) {
				$t = '/^([0-9\.]+)[x:]([0-9\.]+)$/';
				if ( preg_match($t, $v, $m) ) {
					$t = 0.0 + $m[1]; $m = 0.0 + $m[2];
					if ( $t > 0.0 && $m > 0.0 ) {
						$displayaspect = $t / $m;
					}
				}
			} else {
				$displayaspect = $v;
			}
			break;
		case 'PA':
			if ( $v != 'S' ) {
				$t = '/^([0-9\.]+)[x:]([0-9\.]+)$/';
				if ( preg_match($t, $v, $m) ) {
					$t = 0.0 + $m[1]; $m = 0.0 + $m[2];
					if ( $t > 0.0 && $m > 0.0 ) {
						$pixelaspect = $t / $m;
					}
				}
			} else {
				$pixelaspect = $v;
			}
			break;
		case 'BH':
			if ( $v && $v !== 'default' ) {
				$t = round(max(20, min(80, 0 + $v)));
				$barheight = $t;
			}
			break;
		default:
			error_log("Unexpected arg '".$k."' => '".$v."'");
		}
}

/**********************************************************************\
 *  global properties: with defaults not subject to arguments         *
 *  these may hand edited with careful checking                       *
\**********************************************************************/

// the main movie rate; affects MovieClip 'sprites',
// currently just the wait movieclip
define( 'ming_default_movie_rate', 12 );
$movierate = ming_default_movie_rate;
// v_id -- if RTMP, stream id (playpath)
if ( !isset($v_id) )
	$v_id = null;
// boolean, is true if media at URL is *known* audio, false if known not
// if null, set in actionscript for extension .mp3
if ( !isset($audb) )
	$audb = null;
// use dummy values to force progress bar action on size-less streams?
// string representation of boolean
$stream_dummies = "false";
// should aspect be automatically set when it *MIGHT* be necessary?
// e.i. 720x480, 720x576, 360x240, 360x288 displayed in 4:3
// string representation of boolean
if ( !isset($autoaspect) )
	$autoaspect = "true";
// display aspect, gt 0, or 0 means do not use
// special value 'D' means take value from "Stage" dimensions
if ( !isset($displayaspect) )
	$displayaspect = 0;
// pixel aspect, gt 0, or 0 means do not use; displayaspect takes
// precedence; special value 'S' means display at "Stage" dimensions
if ( !isset($pixelaspect) )
	$pixelaspect = 0;
// Sound/NetStream check media server policy file? (best set false!)
// string representation of boolean
$bchkpolicyfile = "false";
// a silent video to provide eye candy when playing audio (mp3)
// URL, or file at SWF URL
$v4aud = "drop2.flv";

// default values and booleans
$doshowtxt = 'true'; // show text fields for playtime and DL?
$barshowmargin = 2; // must enter Stage by this many pixels to show bar
$barshowincr = 2;  // controlbar slides on/off screen this many pixels
$tickinterval = 50; // ms arg to setInterval(); check ticker() in ascr
$ptrinterval = 5; // seconds until unmoving pointer icon is hidden
$streambuftime = 5; // seconds of stream buffering
if ( !isset($initpause) )
	$initpause   = 'true'; // initial pause video?
if ( !isset($initshowbar) )
	$initshowbar = 'true'; // initial show control bar?
if ( !isset($initvolume) )
	$initvolume = 50; // initial volume if no local setting
if ( !isset($doloop) )
	$doloop = 'false'; // loop the media?
if ( !isset($disablebar) )
	$disablebar = 'false'; // hide and disable control bar?
$initshowtime  =   -1;  // if $initpause == true: try to show,
						// while initially paused, the image at
						// this time location, rather than start
						// of stream -- 0 for start if stream,
						// > 0 integer in seconds for specific time,
						// < 0 for random time
$initshowtimemax  =  5;  // max for above if random, but . . .
// . . . doomed to fail if > $streambuftime
$initshowtime = min($initshowtime, $streambuftime);
$initshowtimemax = min($initshowtimemax, $streambuftime);
$barheightlg = 40;
$barheightsm = 26;
$barheightthreshhold = 360;
// wait movie arrow shaft: straight rect or fancy?
$waitarrowstraight = false;
// wait movie radius
$wrad = 40;
// wait movie number of elements
$wnparts = 9;
// wait movie frames
$wnfrms = 9;
// shoe central box in scale icon?
$scaleicobox = true;

//
// colors of interface parts
//

// control bar
$barR = 20; $barG = 20; $barB = 25; $barA = 140;
//$barR = 20; $barG = 20; $barB = 10; $barA = 170;
// buttons
$butR = 30; $butG = 10; $butB = 15; $butA = 80;
//$butR = 30; $butG = 10; $butB = 15; $butA = 180;
// button outline
$blineW = 1; $blineR = 0; $blineG = 0; $blineB = 0; $blineA = 240;
// button highlight outline -- width < 0 means adjust to bar height
$bhighW = -1; $bhighR = 220; $bhighG = 220; $bhighB = 240; $bhighA = 170;
//$bhighW = 1; $bhighR = 220; $bhighG = 220; $bhighB = 240; $bhighA = 170;
// icons (e.g. on buttons)
$icoR = 240; $icoG = 240; $icoB = 255; $icoA = 170;
// icon outlines
//$ilineW = 1; $ilineR = 240; $ilineG = 240; $ilineB = 240; $ilineA = 180;
$ilineW = 0; $ilineR = 0; $ilineG = 0; $ilineB = 0; $ilineA = 0;
// play time progress bar
$progplR = 245; $progplG = 210; $progplB = 215; $progplA = 120;
//$progplR = 200; $progplG = 10; $progplB = 15; $progplA = 180;
// data transfer progress bar
$progdlR = 215; $progdlG = 210; $progdlB = 245; $progdlA = 120;
//$progdlR = 35; $progdlG = 20; $progdlB = 120; $progdlA = 180;
// progress bar backing
$progpbR =  20; $progpbG =  0; $progpbB =  5; $progpbA = 120;


/**********************************************************************\
 *  global properties:                                                *
 *  these should not be hand edited                                   *
\**********************************************************************/

// Dimensions, loci and such that are not modified by args
$barpadding = 0; // pad at controlbar edges
$barpadding = max(0, $barpadding); // positive only
$barsubtr = $barpadding * 2;
if ( !isset($barheight) ) {
	$barheight =
		$wndheight > $barheightthreshhold ? $barheightlg : $barheightsm;
	$barhfact = (0.0+$barheight) / (0.0+$barheightlg);
} else {
	$barhfact = 1;
}
$barlength = $wndlength - $barsubtr;
$barX = $barpadding;
$barY = $wndheight - $barheight - $barpadding;
$butwidthfactor = 0.56;
$butwidth = round($barheight * $butwidthfactor) + 1;
//$butwidth += $butwidth & 1; // make even
$butwidth |= 1; // make odd
$butheight = $butwidth;
$triangleheight = $butheight / 2.0;
$t = round(treqbase($triangleheight));
// make odd or even to match $butheight
$trianglebase = round($butheight) & 1 ? ($t | 1) : (($t + 1) & ~1);
$triangleheight = treqheight($trianglebase);
$progressbarheight = ($barheight - $butheight) * 0.25;
$progressbaroffs =  (($barheight - $butheight) * 0.20) / 2.0;
$progressbarlength = $barlength - ($progressbaroffs * 2);
$progressbarxoffs =  ($barlength - $progressbarlength) / 2.0;

if ( $bhighW < 0 ) {
	// var $barheight [20,40], $bhighW [1,2]
	$bhighW = 1.0 + ($barheight - 20.0) / (40.0 - 20.0);
}


/**********************************************************************\
 *  Ming library object building (plus misc.)                         *
\**********************************************************************/


// libming init
//ming_useswfversion($swfvs);
// this (20) is the default in libming:
ming_setscale(20.0);
// not sure about threshold -- ming library default is 10000
// cannot see difference with lower value.  Check again if
// more/larger cubics are used
//ming_setcubicthreshold(3);

// compression level: ineffective if arg given to movie->output() alone
// setting level 0 turns compression off but still marks swf as
// compressed (and adds 11 bytes to uncompressed size).
// So, to turn off compression, set $swfcomp = null
if ( is_int($swfcomp) && $swfcomp >= 0 && $swfcomp <= 9 ) {
	ming_setswfcompression($swfcomp);
}

// main php ming movie:
$movie = new SWFMovie($swfvs);
$movie->setRate($movierate);
// Dimensions should be taken from env., e.g. embedding
//$movie->setDimension($wndlength, $wndheight);
$movie->setBackground(0, 0, 0);


//
// make the shapes for interface objects
//

// some helper funcs
function new_shape_atts ( // common shape alloc and init attributes
	// outline attributes
	$lW, $lR, $lG, $lB, $lA,
	// fill attributes
	$fR, $fG, $fB, $fA)
{
	$s = makeSWFShape();
	$s->setLine($lW, $lR, $lG, $lB, $lA);
	$s->setRightFill($fR, $fG, $fB, $fA);
	return $s;
}

function new_icon () // common icon shape alloc and init default
{
	global $ilineW, $ilineR, $ilineG, $ilineB, $ilineA;
	global $icoR, $icoG, $icoB, $icoA;
	return new_shape_atts($ilineW, $ilineR, $ilineG, $ilineB, $ilineA,
		$icoR, $icoG, $icoB, $icoA);
}

// The following 2 helper funcs were hit and miss attempts to
// suppress a bogus WARNING sent to server's error log.  These did not
// help, and nothing in a script can help.  The problem is in the
// ming php module which does not guard against it's initialization
// function being called more than once -- it is actually called with
// each and any script run by the php module -- and each call calls
// the library's Ming_init(), which resets a global version var to a
// default each time; so each time a script sets the SWF version there
// is the bogus warning about changing versions during a run.
// I've pointed this out on a Ming mail list so the devs should
// be aware of it.
function makeSWFAction ($act)
{
	global $swfvs;

	$t = new SWFAction($act);
	// undocumented SWFAction::compile(version) found in module source,
	// hoping this will suppress the bug that spams httpd error.log
	// with bogus warnings about version change
	//$t->compile($swfvs);
	return $t;
}

function makeSWFShape ()
{
	global $swfvs;

	$t = new SWFShape();
	// undoc'd SWFShape::useVersion(version) found in module source,
	// hoping this will suppress the bug that spams httpd error.log
	// with bogus warnings about version change
	//$t->useVersion($swfvs);
	return $t;
}


// control bar background shape
$barshape = new_shape_atts(0, 0, 0, 0, 0, $barR, $barG, $barB, $barA);
mingshape_drawrect($barshape, 0, 0, $barlength, $barheight);

// progress bar shapes
// playprogress: playback/time progress bar shape
$prplshape = new_shape_atts(0,
	$progplR, $progplG, $progplB, $progplA,
	$progplR, $progplG, $progplB, $progplA);
mingshape_drawrect($prplshape, 0, 0,
					$progressbarlength, $progressbarheight);
// downloadprogress: download status progress bar shape
$prdlshape = new_shape_atts(0,
	$progdlR, $progdlG, $progdlB, $progdlA,
	$progdlR, $progdlG, $progdlB, $progdlA);
mingshape_drawrect($prdlshape, 0, 0,
					$progressbarlength, $progressbarheight);
// backing for playprogress and downloadprogress
$prpbshape = new_shape_atts(0,
	$progpbR, $progpbG, $progpbB, $progpbA,
	$progpbR, $progpbG, $progpbB, $progpbA);
mingshape_drawrect($prpbshape, 0, 0,
					$progressbarlength, $progressbarheight);

// control button background shape
$butshape = new_shape_atts($blineW,
	$blineR, $blineG, $blineB, $blineA,
	$butR, $butG, $butB, $butA);
$butshape->movePenTo($butwidth / 2, $butheight / 2);
$butshape->drawCircle($butheight / 2);
// control button highlight shape
$buthighlightshape = new_shape_atts($bhighW,
	$bhighR, $bhighG, $bhighB, $bhighA,
	0, 0, 0, 0);
$buthighlightshape->movePenTo($butwidth / 2, $butheight / 2);
$buthighlightshape->drawCircle($butheight / 2);
// control button invisible shape shape - for grabbing MovieClip events
$butinvisibleshape = new_shape_atts(0, 0, 0, 0, 0, 0, 0, 0, 0);
$butinvisibleshape->movePenTo($butwidth / 2, $butheight / 2);
$butinvisibleshape->drawCircle($butheight / 2);

// shape for play/pause button icon in do play state
$playshape = new_icon();
if ( false ) {
	$t = $trianglebase * treq_mid_y;
	mingshape_drawtreq($playshape,
		$butwidth / 2 - $t, ($butheight - $triangleheight) / 2,
		$triangleheight, deg2rad(90));
} else {
	mingshape_drawtreq2($playshape,
		$butwidth / 2.0, $butheight/ 2.0,
		$triangleheight, deg2rad(90));
}

// shape for play/pause button icon in do pause state
$barwid = $butwidth / 5;
$barhigh = treqbase($triangleheight) - $barwid; // - radius of endcaps
$pauseshape = makeSWFShape();
// note: line color set as fill color: using thick lines
$pauseshape->setLine($barwid, $icoR, $icoG, $icoB, $icoA);
$pauseshape->movePenTo($butwidth * 2 / 5 - ($barwid / 2.0),
	($butheight - $barhigh) / 2);
$pauseshape->drawLine(0, $barhigh);
$pauseshape->movePenTo($butwidth * 4 / 5 - ($barwid / 2.0),
	($butheight - $barhigh) / 2);
$pauseshape->drawLine(0, $barhigh);

// shape for stop button icon
$stopheight = $butheight / 2.0 - 0.5; // -0.5 for button line width (?)
$cx = ($butwidth - $stopheight) / 2.0;
$cy = ($butheight - $stopheight) / 2.0;
$stopshape = new_icon();
mingshape_drawrect($stopshape, $cx, $cy, $stopheight, $stopheight);

// make 'corner' arrays for scale/fullsreen button icons
if ( true ) {
	// offset into button (assumed circular)
	$t = 0.70710678; // sin||cos 45deg
	$tx = $cx = $butwidth  / 2.0 - $butwidth  / 2.0 * $t + $blineW/2.0;
	$ty = $cy = $butheight / 2.0 - $butheight / 2.0 * $t + $blineW/2.0;
	// side length . . .
	$cnside = (($butwidth  + $butheight) / 2.0) / 4.0 * 1.00 + 0.0;
} else {
	// first thought was corners should coincide w/ stop icon box; N.G.
	$tx = $cx = ($butwidth  - $stopheight) / 2.0 + $blineW / 2.0;
	$ty = $cy = ($butheight - $stopheight) / 2.0 + $blineW / 2.0;
	$cnside = $stopheight / 3.0 * 1.125;
}
$cnaout = array(0 => array(0 => 0+$cx, 1 => 0+$cy),
		1 => array(0 => $cnside+$cx, 1 => 0+$cy),
		2 => array(0 => 0+$cx, 1 => $cnside+$cy),
		3 => array(0 => 0+$cx, 1 => 0+$cy));
// make go full screen icon shape
$fullscrshape = new_shape_atts(0, 0, 0, 0, 0,
	$icoR, $icoG, $icoB, $icoA);
$cna = $cnaout;
$cx =  $butwidth / 2.0;
$cy = $butheight / 2.0;
mingshape_drawpoly($fullscrshape, $cna);
points_rotate($cna, deg2rad(90), $cx, $cy);
mingshape_drawpoly($fullscrshape, $cna);
points_rotate($cna, deg2rad(90), $cx, $cy);
mingshape_drawpoly($fullscrshape, $cna);
points_rotate($cna, deg2rad(90), $cx, $cy);
mingshape_drawpoly($fullscrshape, $cna);

// make leave full screen icon shape
$cnhyp = hypot($cnside, $cnside);
$cnhyp2 = $cnhyp / 2.0;
$cnhi = sqrt($cnside * $cnside - $cnhyp2 * $cnhyp2);
$cnhi2 = $cnhi / 2.0;
$cnoff = sqrt($cnhi2 * $cnhi2 / 2.0);
$cx = $tx - $cnoff;
$cy = $ty - $cnoff;
$cnain = array(0 => array(0 => $cnside+$cx, 1 => 0+$cy),
		1 => array(0 => $cnside+$cx, 1 => $cnside+$cy),
		2 => array(0 => 0+$cx, 1 => $cnside+$cy),
		3 => array(0 => $cnside+$cx, 1 => 0+$cy));
$xfullscrshape = new_shape_atts(0, 0, 0, 0, 0,
	$icoR, $icoG, $icoB, $icoA);
$ina = $cnain;
$cx =  $butwidth / 2.0;
$cy = $butheight / 2.0;
mingshape_drawpoly($xfullscrshape, $ina);
points_rotate($ina, deg2rad(90), $cx, $cy);
mingshape_drawpoly($xfullscrshape, $ina);
points_rotate($ina, deg2rad(90), $cx, $cy);
mingshape_drawpoly($xfullscrshape, $ina);
points_rotate($ina, deg2rad(90), $cx, $cy);
mingshape_drawpoly($xfullscrshape, $ina);

// make do scale screen icon shape, reusing 'corners'
$cx =  $butwidth / 2.0;
$cy = $butheight / 2.0;
$doscaleshape = new_shape_atts(0, 0, 0, 0, 0,
	$icoR, $icoG, $icoB, $icoA);
$cna = $cnaout;
points_rotate($cna, deg2rad(-45), $cx, $cy);
mingshape_drawpoly($doscaleshape, $cna);
points_rotate($cna, deg2rad(180), $cx, $cy);
mingshape_drawpoly($doscaleshape, $cna);
if ( $scaleicobox ) {
	// rect at center
	$cx -= $cnside / 2.0;
	$cy -= $cnside / 2.0;
	mingshape_drawrect($doscaleshape, $cx, $cy, $cnside, $cnside);
}
// make no scale screen icon shape
$cx =  $butwidth / 2.0;
$cy = $butheight / 2.0;
$xdoscaleshape = new_shape_atts(0, 0, 0, 0, 0,
	$icoR, $icoG, $icoB, $icoA);
$ina = $cnain;
points_rotate($ina, deg2rad(-45), $cx, $cy);
mingshape_drawpoly($xdoscaleshape, $ina);
points_rotate($ina, deg2rad(180), $cx, $cy);
mingshape_drawpoly($xdoscaleshape, $ina);
if ( $scaleicobox ) {
	// rect at center
	$cx -= $cnside / 2.0;
	$cy -= $cnside / 2.0;
	mingshape_drawrect($xdoscaleshape, $cx, $cy, $cnside, $cnside);
}

// make speaker icon for volume button
$spkrshape = new_shape_atts(0, 0, 0, 0, 0,
	$icoR, $icoG, $icoB, $icoA);
$spkrshape2 = new_shape_atts(0, 0, 0, 0, 0,
	$icoR, $icoG, $icoB, $icoA);
$t = round($stopheight / 2.0);
// make odd or even to match $butheight
$t = round($butheight) & 1 ? ($t | 1) : ($t & ~1);
$spkrect = $t;
if ( false ) {
	$cx = floor(($butwidth  - $spkrect) / 4.0) - 1;
	$cy = floor(($butheight - $spkrect) / 2.0) + 0;
	//mingshape_drawrect($spkrshape, $cx, $cy, $spkrect, $spkrect);
	mingshape_drawrect($spkrshape2, $cx, $cy, $spkrect, $spkrect);
	$t = $triangleheight;
	$cx = ($butwidth  - $t) / 2.0;
	$cy = ($butheight - $t) / 2.0;
	mingshape_drawtreq($spkrshape , $cx, $cy, $t, deg2rad(-90));
	mingshape_drawtreq($spkrshape2, $cx, $cy, $t, deg2rad(-90));
} else {
	$t = $triangleheight - $trianglebase * treq_mid_y;
	$cx = $butwidth / 2.0 - $t;
	$cy = floor(($butheight - $spkrect) / 2.0);
	$t = $spkrect * 0.65;
	//mingshape_drawrect($spkrshape, $cx, $cy, $t, $spkrect);
	mingshape_drawrect($spkrshape2, $cx, $cy, $t, $spkrect);
	$t = $triangleheight;
	$cx = $butwidth  / 2.0;
	$cy = $butheight / 2.0;
	mingshape_drawtreq2($spkrshape , $cx, $cy, $t, deg2rad(-90));
	mingshape_drawtreq2($spkrshape2, $cx, $cy, $t, deg2rad(-90));
}

//
// make the interface objects with the shapes made above
//

// helper funcs
function add_child_obj (&$parent, &$child, $name, $mX, $mY)
{
	$t = $parent->add($child);
	$t->setName($name);
	$t->moveTo($mX, $mY);
	return $t;
}

function add_panel_obj (&$child, $name, $mX, $mY)
{
	global $ctlpanel;
	return add_child_obj($ctlpanel, $child, $name, $mX, $mY);
}

function add_movie_obj (&$child, $name, $mX, $mY)
{
	global $movie;
	return add_child_obj($movie, $child, $name, $mX, $mY);
}

// control panel sprite/movie
// this requires variable name for further use - much of the following
// can use temporary variables because there are no further references
$ctlpanel = new SWFMovieClip();
$t = new SWFButton();
$t->addShape($barshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_OVER);
// effect: flashes clear on press w/o SWFBUTTON_DOWN
//	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addAction(makeSWFAction("_root.ctlpanelHit();"),
	SWFBUTTON_HIT);
add_panel_obj($t, "ctlpanel", 0, 0);

// playback/time progress bar BACKING
// this button is active; clicking seeks in play time
$tx = $progressbarxoffs;
$ty = $progressbaroffs;
$t = new SWFButton();
$t->addShape($prpbshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addAction(makeSWFAction("_root.plprogHit();"), SWFBUTTON_HIT);
// add playback/time progress bar BACKING to panel
add_panel_obj($t, "progpb", $tx, $ty);

// playback/time progress bar indicator
$t = new SWFMovieClip();
$t->add($prplshape);
$t->nextFrame();
// add playback/time progress bar indicator to panel
add_panel_obj($t, "progpl", $tx, $ty);

// stream-DL progress bar BACKING
$tx = $progressbarxoffs;
$ty = $barheight - ($progressbarheight + $progressbaroffs);
$t = new SWFMovieClip();
$t->add($prpbshape);
$t->nextFrame();
// add stream-DL progress bar BACKING
add_panel_obj($t, "progdlb", $tx, $ty);
// stream-DL progress bar indicator
$t = new SWFMovieClip();
$t->add($prdlshape);
$t->nextFrame();
// add stream-DL progress bar indicator
add_panel_obj($t, "progdl", $tx, $ty);

// make play/pause buttons:
// play
$tx = $butwidth * 0.5; $ty = ($barheight - $butheight) / 2;
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($playshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.togglepause();"));
add_panel_obj($t, "playbut", $tx, $ty);
// pause
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($pauseshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.togglepause();"));
add_panel_obj($t, "pausebut", $tx, $ty);
// pause disabled; e.g. rtmp stream with which resume often fails
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($pauseshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.null_proc();"));
add_panel_obj($t, "pausebutdisable", $tx, $ty);

// add stop button + dummy to indicate disabled
// stop
$tx = $butwidth * 2; $ty = ($barheight - $butheight) / 2;
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($stopshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.stopVideo();"));
add_panel_obj($t, "stopbut", $tx, $ty);
// stop disabled
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($stopshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.null_proc();"));
add_panel_obj($t, "stopbutdisable", $tx, $ty);

// add doscale/noscale buttons
// doscale
$tx = $butwidth * 4; $ty = ($barheight - $butheight) / 2;
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($doscaleshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.toggleDoScale();"));
add_panel_obj($t, "dosclbut", $tx, $ty);
// windowed; more like natural size, arrows point inward
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($xdoscaleshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.toggleDoScale();"));
add_panel_obj($t, "nosclbut", $tx, $ty);
// windowed disabled; natural W and/or H greater than current
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($xdoscaleshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.null_proc();"));
add_panel_obj($t, "nosclbutdisable", $tx, $ty);

// add fullscreen/windowed buttons
// fullscreen
$tx = $butwidth * 5.5; $ty = ($barheight - $butheight) / 2;
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($fullscrshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.toggleFullscreen();"));
add_panel_obj($t, "fullscrbut", $tx, $ty);
// windowed
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($xfullscrshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.toggleFullscreen();"));
add_panel_obj($t, "windscrbut", $tx, $ty);

// add volume button (displays slider control)
$tx = $butwidth * 7.5; $ty = ($barheight - $butheight) / 2;
$t = new SWFButton();
$t->addShape($butshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($spkrshape,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($spkrshape2,
	SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$t->addShape($buthighlightshape, SWFBUTTON_OVER);
$t->setAction(makeSWFAction("_root.doVolumeCtl();"));
add_panel_obj($t, "spkrbut", $tx, $ty);
// invisible movieclip over speaker button for mousewheel events
$t = new SWFMovieClip();
$t->add($butinvisibleshape);
$t->nextFrame();
add_panel_obj($t, "spkrmsw", $tx, $ty);

// keep name of rightmost button for use in actionscript
$rtmbut = "bbar.spkrbut";

// make volume adjust gadget
$volbarlen = $butwidth * 4;
$volbarwid = $butheight / 2;
// gadget backing:
$volbarbkshape = makeSWFShape();
$volbarbkshape->setLine($volbarwid * 2, $barR, $barG, $barB, $barA);
$volbarbkshape->movePenTo(0, $volbarwid / 2);
$volbarbkshape->drawLineTo($volbarlen, $volbarwid / 2);
$volbarbkshape->drawLineTo(0, $volbarwid / 2);
// gadget button: button
$volbarbutshape = new_shape_atts(0, $barR, $barG, $barB, $barA,
	$progpbR, $progpbG, $progpbB, $progpbA);
mingshape_drawrect($volbarbutshape, 0, 0, $volbarlen, $volbarwid);
// gadget indicator
$volbarindshape = new_shape_atts(0, $barR, $barG, $barB, $barA,
	$progplR, $progplG, $progplB, $progplA);
mingshape_drawrect($volbarindshape, 0, 0, $volbarlen, $volbarwid);
// invisible movie to get mousewheel events
$volbarmswshape = new_shape_atts(0, 0,0,0,0,0,0,0,0);
mingshape_drawrect($volbarmswshape, 0, 0, $volbarlen, $volbarwid);
// gadget movie clip: (setName("volgadget");)
$volbarmovie = new SWFMovieClip();
// gadget backing movie clip:
$volbarbk = new SWFMovieClip();
$volbarbk->add($volbarbkshape);
$volbarbk->nextFrame();
add_child_obj($volbarmovie, $volbarbk, "vbarbk", $volbarwid, 0);
// gadget button:
$volbarbut = new SWFButton();
$volbarbut->addShape($volbarbutshape,
	SWFBUTTON_HIT | SWFBUTTON_UP | SWFBUTTON_DOWN | SWFBUTTON_OVER);
$volbarbut->addAction(makeSWFAction("_root.doVolumeAdjust();"),
	SWFBUTTON_HIT);
add_child_obj($volbarmovie, $volbarbut, "vbarbut", $volbarwid, 0);
// gadget indicator movie clip:
$volbarind = new SWFMovieClip();
$volbarind->add($volbarindshape);
$volbarind->nextFrame();
add_child_obj($volbarmovie, $volbarind, "vbarind", $volbarwid, 0);
// gadget invisible mousewheel grabber movie clip:
$volbarmsw = new SWFMovieClip();
$volbarmsw->add($volbarmswshape);
$volbarmsw->nextFrame();
add_child_obj($volbarmovie, $volbarmsw, "vbarmsw", $volbarwid, 0);
// advance gadget movie clip:
$volbarmovie->nextFrame();

// Text objects

$txtminheight = $barheight <= 32 ? true : false;
$txtheight = $txtminheight ? 8 : 10;
$monofont = new SWFBrowserFont("_typewriter");
$sansfont = new SWFBrowserFont("_sans");
$seriffont = new SWFBrowserFont("_serif");
// DBG: textfield for arbitrary output wanted during development;
// default hidden, is shown by 'g' key -- optionally disable
// for release
$t = new SWFTextField(SWFTEXTFIELD_DRAWBOX  | SWFTEXTFIELD_MULTILINE |
						SWFTEXTFIELD_WORDWRAP);
$t->setBounds(206, 128);
$t->setFont($sansfont);
$t->setHeight($txtheight); //12);
$t->setLength(800);
$t->setColor(255, 255, 255, 220); //(10, 15, 20, 255);
$t->addString("DBG:\n");
// add to movie last, above other objects
$txtdbg = $t;

// text display for the playback time
$timetxtwid = 130; // initial: resized in actionscript
$timetxt_rb = $txtminheight ? $barshowmargin :  10;  // r. justify pos
// Text object to display current/total video time in HH:MM:SS
$t = new SWFTextField(0);
$t->setBounds($timetxtwid, $barheight / 3);
$t->setFont($monofont);
$t->setColor($icoR, $icoG, $icoB, $icoA);
$t->setHeight(max($barheight / 4, $txtheight));
$t->addString("00:00:00/99:59:59");
add_panel_obj($t, "tmtxt",
	$barlength - $timetxtwid, $progressbarheight * 2 - 2);

// text display for data transfer (DL)
$t = new SWFTextField(0);
$t->setBounds($timetxtwid, $barheight / 3);
$t->setFont($monofont);
$t->setColor($icoR, $icoG, $icoB, $icoA);
$t->setHeight(max($barheight / 4, $txtheight));
$t->addString("100%102400000000k");
add_panel_obj($t, "dltxt",
	$barlength - $timetxtwid,
	$progressbarheight * 2 + $barheight / 3 - 2);

// make info textfield to display information to user
   /*
    * SWFTEXTFIELD_DRAWBOX draws the outline of the textfield
    * SWFTEXTFIELD_HASLENGTH --EH: logs error about undefined constant
    * SWFTEXTFIELD_HTML allows text markup using HTML-tags
    * SWFTEXTFIELD_MULTILINE allows multiple lines
    * SWFTEXTFIELD_NOEDIT indicates that the field shouldn't be user-editable
    * SWFTEXTFIELD_NOSELECT makes the field non-selectable
    * SWFTEXTFIELD_PASSWORD obscures the data entry
    * SWFTEXTFIELD_WORDWRAP allows text to wrap
*/
// *VERY* picky about bits at runtime; naming too: must use
// setName here, and on return-object when added to movie.
// do not use SWFTEXTFIELD_NOEDIT w/ html field -- prevents
// application of css attributes
$infotxt = new SWFTextField(
	SWFTEXTFIELD_HTML | SWFTEXTFIELD_MULTILINE |
	SWFTEXTFIELD_WORDWRAP |
	0
);
$infotxt->setBounds($wndlength / 2.0, $wndheight * 3 / 4.0);
$infotxt->setName("infotxt");
// other properties from css and actionscript

// make wait-a-while movie to show while stream buffer is filling, etc.
$theight = 12.00 * 9 / (0.0+$wnparts);
$wang = 360;
$winc = $wang / $wnparts;
$wmovie = new SWFMovieClip();
$waitshape = new_icon();
mingshape_drawtreq($waitshape,
	-$theight / 2, -$theight / 2, $theight, deg2rad(-90));
if ( $waitarrowstraight ) {
	// plain rectangle arrow shaft
	mingshape_drawrect($waitshape, $theight * 0.5,
						$theight * -0.25,
						$theight * 1.25,
						$theight * 0.5);
} else {
	$sc = $theight * 1.0;
	$xo = $theight * 0.5;
	$yo = $theight * -0.25;

	// curved arrow shaft
	$pcub = array(
	0 => array(0 => $xo + 0.0573996 * $sc,  1 => $yo + 0.277178 * $sc),
	1 => array(0 => $xo + 0.0606226 * $sc,  1 => $yo + 0.0199845 * $sc),
	2 => array(0 => $xo + 0.57 * $sc,  1 => $yo + 0.03 * $sc),
	3 => array(0 => $xo + 0.87 * $sc,  1 => $yo + 0.1 * $sc),
	4 => array(0 => $xo + 1.16 * $sc,  1 => $yo + 0.21 * $sc),
	5 => array(0 => $xo + 1.45417 * $sc,  1 => $yo + 0.437099 * $sc),
	6 => array(0 => $xo + 1.27005 * $sc,  1 => $yo + 0.503488 * $sc),
	7 => array(0 => $xo + 1.11376 * $sc,  1 => $yo + 0.462586 * $sc),
	8 => array(0 => $xo + 1.1448 * $sc,  1 => $yo + 0.630027 * $sc),
	9 => array(0 => $xo + 1.06325 * $sc,  1 => $yo + 0.863602 * $sc),
	10 => array(0 => $xo + 0.878121 * $sc,  1 => $yo + 0.592868 * $sc),
	11 => array(0 => $xo + 0.704932 * $sc,  1 => $yo + 0.416057 * $sc),
	12 => array(0 => $xo + 0.447649 * $sc,  1 => $yo + 0.305126 * $sc),
	13 => array(0 => $xo + 0.0573996 * $sc,  1 => $yo + 0.277178 * $sc),
	14 => array(0 => $xo + 0.0606226 * $sc,  1 => $yo + 0.0199845 * $sc)
	);
	mingshape_drawcubic($waitshape, $pcub);
}
$tmovie = new SWFMovieClip();
for ( $i = 0; $i < $wnparts; $i++ ) {
	$ad = $winc * $i;
	$a = deg2rad($ad);
	$ci = 1.0 - ($ad / $wang) + 0.2;
	$sc = 1.0 - 0.5 * ($ad / $wang);
	$wdisp = $tmovie->add($waitshape);
	$wdisp->multColor($ci, $ci, $ci + 0.2, $ci + 0.4);
	$wdisp->scale($sc, $sc);
	// angles negated for direction of apparent motion
	$wdisp->rotate(-$ad);
	$wdisp->moveTo(-$wrad * sin(-$a), -$wrad * cos(-$a));
}
$tmovie->nextFrame();
// there is no way to set MovieClip frame rate -- the wait spinner
// was developed w/ 9 parts, therefore 9 frames, which apparently
// cycles over the default per sec., and looks nice
// IAC, interesting effects come from tuning number-of-parts
// vs. number-of-frames
$t = ($wnfrms / (0.0 + $wnparts));
for ( $i = 0; $i < $wnfrms; $i++ ) {
	$r = $winc * ($i / $t);
	$wdisp = $wmovie->add($tmovie);
	$wdisp->rotate($r);
	$wmovie->nextFrame();
	// interesting effect if the next line is commented
	$wmovie->remove($wdisp);
}

// Control panel 'movie' complete now: set current state as a 'frame'
// by advancing to next frame; without this nothing will display
$ctlpanel->nextFrame();

// ming video stream
add_movie_obj(new SWFVideoStream(), "video", 0, 0);

// add the control bar to the movie
add_movie_obj($ctlpanel, "bbar", 0, 0);

// add the wait-a-while movie to the movie
add_movie_obj($wmovie, "wait", $wndlength / 2, $wndheight / 2);

// add the volume gadget to the movie
add_movie_obj($volbarmovie, "volgadget",
	$butwidth * 7.5 - $volbarlen / 2,
	$barY - $barheight - $volbarwid*2);

// add info textfield  to the movie
add_movie_obj($infotxt, "itxt", $wndlength / 4.0, $wndheight / 8.0);

// add debug textfield
add_movie_obj($txtdbg, "dbg", 5, 5);

/**********************************************************************\
 *  Main Flash Action script, as a PHP here-doc                       *
\**********************************************************************/

// BEGIN main action script
// this include defines '$mainact', the main body of actionscript
require_once 'mainact.inc.php';
// END main action script

// now add the actionscript
$movie->add(makeSWFAction($mainact));


/**********************************************************************\
 *  Finally, Ming output of SWF program                               *
\**********************************************************************/


if ( ! $climode ) {
	header('Content-type: application/x-shockwave-flash');
} // if ( ! $climode ) {

if ( $movie->output() == false ) {
	error_log(__('SWF application output() FAILED!'));
}
?>
