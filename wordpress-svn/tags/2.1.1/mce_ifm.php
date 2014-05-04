<?php
/*
 *      mce_ifm.php
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

/*
 * produce document with evhh5v video elements suitable for display
 * as an iframe for the tinymce 'Visual' posts editor, as set up
 * by a tinymce plugin loaded by the SWFPut WP plugin.
 * 
 * This is driven by proper arguments (each encoded) and makes a
 * single video player instance if expectations are met.
 */

$reqs = array('width','height','barheight',
	'a', 'i', 'u'
);

foreach ( $reqs as $k ) {
	if ( ! isset($_REQUEST[$k]) ) {
		die("WRONG");
	}
}

function getwithdef($k, $def) {
	if ( ! isset($_REQUEST[$k]) ) {
		return $def;
	}
	return urldecode($_REQUEST[$k]);
}

// Need WP DB and functions: WP installation ABSPATH
// should be 'a' in query, BUT if possible the main
// plugin file wrote ABSPATH into wpabspath.php as
// var $wpabspath, which is good because that file's
// contents are as secure as this file's . . .
$fn = rtrim(dirname(__FILE__), '/') . '/wpabspath.php';
if ( is_readable($fn) && is_file($fn) ) {
	include $fn;
}

// . . . but it might not have been possible to prepare the file
// wpabspath.php due to safe_mode, file mode, and such, so
// fall back to the following if needed:
if ( ! isset($wpabspath) ||
	! $wpabspath || $wpabspath === '' ||
	  $wpabspath === 'REPLACEME' ) {

	$wpabspath = getwithdef('a', '');
	
	// non-Einsteinian relativity: path of observer *shall*
	// be *not* relative *but* ABSOLUTE *and* move *only forward*
	if ( $wpabspath == '' ||
		preg_match(',^\.\./,', $wpabspath) ||
		preg_match(',/\.\./,', $wpabspath) ||
		! preg_match(',^/,', $wpabspath) ) {
		die("PATH OF RIGHTEOUSNESS FORSAKEN");
	}

	$wpabspath = rtrim($wpabspath, '/');
	
	// $_SERVER['SCRIPT_FILENAME'], not  __FILE__,
	// used to compare abspath -- why, you ask?
	// __FILE__ might dereference symbolic links, but
	// the plugin directory path might include
	// symlinks; also, the JS that uses its own URL to
	// invoke this had its PATH prepared using WP_PLUGIN_DIR,
	// which is by default WP_CONTENT_DIR . '/plugins', and
	// WP_CONTENT_DIR is by default ABSPATH . 'wp-content',
	// so they should match for the length $wpabspath
	// unless *some other factor* is in play.
	// One other factor, and a not improbable point of
	// failure is that sites can give definitions of
	// those macros that do not follow the above -- so
	// die with a gentle and informative message. (No
	// WP loaded, so no i18n, with regret.)
	// If not obvious, the concern is like two installs
	// on one host like 'd1/wp' and 'd2/wp' and trickster
	// puts the wrong dN in place, and it seems to succeed
	// because we load wp-load.php, but the wrong one.
	// Not a problem for core files because they are in
	// known relative locations (unlike plugin files) and
	// can rely on "dirname(__FILE__)" and such. Sigh.
	$t1 = explode('/', $wpabspath);
	$t2 = explode('/', $_SERVER['SCRIPT_FILENAME']);
	for ( $i = 0; $i < count($t1); $i++ ) {
		if ( $t1[$i] !== $t2[$i] ) {
			die("Security: plugin directory descent is not from\n" .
				"the expected path, probably due to a non-standard\n" .
				"location of the plugins directory in this WordPress\n" .
				"installation. This plugin script requires that the\n" .
				"plugins directory be descended from the WordPress\n" .
				"install directory, if it cannot write to its own\n" .
				"directory, to guard against path attacks -- sorry!");
		}
	}
}

$wpabspath = rtrim($wpabspath, '/') . '/wp-load.php';
if ( ! is_file($wpabspath) || ! is_readable($wpabspath) ) {
	die("PATH LEADS TO EREHWON");
}

require($wpabspath);

// Checks against ticket stored as option referenced in query data
$usr = getwithdef('u', '');
$vopt = get_option('swfput_mceifm'); // hardcode name - never from query
if ( ! ($vopt && isset($vopt[$usr])) ) {
	die("FAILED");
}
$vopt = $vopt[$usr]; // only want this one
if ( (int)$vopt[0] !== (int)getwithdef('i', '') ) {
	die("NO AUTH");
}
if ( (int)$vopt[2] < ((int)time() - (int)$vopt[1]) ) {
	die("EXPIRED ticket (please update post before continuing)");
}
if ( strcmp($vopt[3], $_SERVER['REMOTE_ADDR']) ) {
	die("BAD CLIENT");
}
if ( isset($_SERVER['REMOTE_HOST']) && strcmp($vopt[4], $_SERVER['REMOTE_HOST']) ) {
	die("BAD CLIENT HOST");
}

// DATA setup
$t = explode('/', $_SERVER['REQUEST_URI']);
$t[count($t) - 1] = 'evhh5v/evhh5v.css';
$cssurl = implode('/', $t);
$t[count($t) - 1] = 'evhh5v/front.min.js';
$jsurl = implode('/', $t);
$t[count($t) - 1] = 'evhh5v/ctlbar.svg';
$ctlbar = implode('/', $t);
$t[count($t) - 1] = 'evhh5v/ctlvol.svg';
$ctlvol = implode('/', $t);
$t[count($t) - 1] = 'evhh5v/ctrbut.svg';
$ctrbut = implode('/', $t);

// relative URLs that work from pages generated by WP
// will not work relative to the URL of this script
function fix_url($u) {
	if ( ! preg_match(',^ *[a-z]+://[^/]+/,i', $u) ) {
		$tpr = isset($_SERVER['HTTPS']);
		if ( $tpr ) {
			$tpr = $_SERVER['HTTPS'];
			$tpr = ($tpr === '' || strcasecmp($tpr, 'off') === 0)
				? 'http://' : 'https://';
		} else {
			$tpr = 'http://';
		}
		
		$tsv = isset($_SERVER['SERVER_NAME']);
		if ( $tsv ) {
			$tsv = $_SERVER['SERVER_NAME'];
		}
		if ( ! $tsv ) {
			$tsv = isset($_SERVER['SERVER_ADDR']);
			$tsv = $tsv ? $_SERVER['SERVER_ADDR'] : '';
		}

		if ( $tsv === '' ) {
			$tpr = '';
		} else if ( false && isset($_SERVER['SERVER_PORT']) ) {
			$tpt = '' . $_SERVER['SERVER_PORT'];
			// cannot use this logic: assumption that https==443
			// and http==80, and that specifying port for anything
			// else is good, is a false assumption
			if ( false && (($tpr === 'http://' && $tpt !== '80')
				|| ($tpr === 'https://' && $tpt !== '443')) ) {
				$tsv = explode(':', $tsv);
				$tsv = $tsv[0] . ':' . $tpt;
			// even this is n.g.: hosting hacks are prone to
			// change server vars in unpredictable ways that do not
			// match the form of a valid request to the host --
			// so don't get clever here (and leave this code as
			// a reminder) -- if name:port is needed, user must
			// know that and provide suitable URLs.
			} else if ( false && $tpt !== '80' && $tpt !== '443' ) {
				$tsv = explode(':', $tsv);
				$tsv = $tsv[0] . ':' . $tpt;
			}
		}

		$u = $tpr . $tsv . '/' . ltrim($u, '/');
	}
	return $u;
}

// normalize H5V type string from user input -- NOT using
// W3.org form, which does not work in existing browsers
// such as Opera (at least GNU/Linux), or older FFox (v 10+?)
// W3 experimental H5 validator says form made here is in error,
// but we assume that if approved and useful are a 
// one-or-the-other choice then the latter is preferred
function clean_h5vid_type($str) {
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

// now, setup for and do output
$allvids = array();
$vnum = 0;

$jatt = array('a_img' => '', 'a_vid' => '', 'obj' => '');

$jatt['a_vid'] = array(
	'width'     => getwithdef('width', '320'),
	'height'	=> getwithdef('height', '240'),
	'barheight'	=> getwithdef('barheight', '36'),
	'id'        => getwithdef('id', 'vh5_n_' . $vnum++),
	'poster'    => getwithdef('iimage', ''),
	'controls'  => 'true',
	'preload'   => 'none',
	'autoplay'  => getwithdef('play', 'false'),
	'loop'      => getwithdef('loop', 'false'),
	'srcs'      => array(),
	'altmsg'    => getwithdef('altmsg', 'Video is not available'),
	'caption'	=> getwithdef('caption', ''),
	'aspect'	=> getwithdef('aspect', '0')
);

$vstdk = array('play', 'loop', 'volume',
	'hidebar', 'disablebar',
	'aspectautoadj', 'aspect',
	'displayaspect', 'pixelaspect',
	'barheight', 'barwidth',
	'allowfull', 'mob'
);
$vstd = array();
foreach ( $vstdk as $k ) {
	if ( isset($jatt['a_vid'][$k]) ) {
		$vstd[$k] = $jatt['a_vid'][$k];
	} else {
		$vstd[$k] = getwithdef($k, '');
	}
}
$vstd['barwidth'] = getwithdef('width', '');
$jatt['a_vid']['std'] = $vstd;

function maybe_get_attach($a) {
	if ( preg_match('/^[0-9]+$/', $a) ) {
		$u = wp_get_attachment_url($a);
		if ( $u ) {
			$a = $u;
		}
	}
	if ( trim($a) != '' ) {
		$a = fix_url($a);
	}
	return $a;
}

$jatt['a_vid']['poster'] = maybe_get_attach($jatt['a_vid']['poster']);

if ( ($k = getwithdef('altvideo', '')) != '' ) {
	$a = explode('|', $k);
	foreach ( $a as $k ) {
		$t = explode('?', trim($k));
		$v = array('src' => maybe_get_attach(trim($t[0])));
		if ( ! isset($t[1]) ) {
			// not given: infer from suffix,
			// patterns always subject to revision
			$pats = array(
				'/.*\.(mp4|m4v|mv4)$/i',
				'/.*\.(og[gv]|vorbis)$/i',
				'/.*\.(webm|wbm|vp[89])$/i'
			);
			if ( preg_match($pats[0], $v['src']) ) {
				$tv[1] = 'video/mp4';
			} else if ( preg_match($pats[1], $v['src']) ) {
				$tv[1] = 'video/ogg';
			} else if ( preg_match($pats[2], $v['src']) ) {
				$tv[1] = 'video/webm';
			}
			// not fatal if not found
			if ( isset($tv[1]) ) {
				$v['type'] = $tv[1];
			}
		} else {
			$t[1] = clean_h5vid_type($t[1]);
			if ( $t[1] !== '' ) {
				$v['type'] = $t[1];
			}
		}
		$jatt['a_vid']['srcs'][] = $v;
	}
}
$fl_url = maybe_get_attach(trim(getwithdef('url', '')));
if ( preg_match('/.+\.(mp4|m4v)$/i', $fl_url) ) {
	$jatt['a_vid']['srcs'][] =
		array('src' => $fl_url, 'type' => 'video/mp4');
}

$allvids[] = $jatt;

// The page: note that this targets an iframe context, so several
// elements are optional, e.g. <title> is left out here, but other
// optional elements are here for form or functionality
?><!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="<?php echo $cssurl ?>" type="text/css">
	<script type="text/javascript" src="<?php echo $jsurl ?>"></script>
	<style>
		.main-div {
			margin: 0px;
			outline: 0px;
			padding: 0px 0px;
			border: 0px;
			background-color: black;
		}
	</style>
</head>

<body>
<div id="main-div" class="main-div">

<?php for ( $i = 0; $i < count($allvids); $i++ ) {
	$jatt = $allvids[$i];
	$v = $jatt['a_vid'];
	$ss = $v['srcs'];
	$w = $v['width'];
	$h = $v['height'];
	$barhi = $v['barheight'];
	$asp = array_key_exists('aspect', $v) ? $v['aspect'] : '0';
	$parentdiv = "div_wp_media_".$i;
	$auxdiv = "div_vidoj_".$i;
	$vidid = "va_o_putswf_video_".$i;
?>

<div id="<?php echo $parentdiv ?>" class="like-wp-caption" style="width: <?php echo "".$w ?>px; max-width: <?php echo "".$w ?>px">
  <div id="<?php echo $auxdiv ?>" class="evhh5v_vidobjdiv">
	<video id="<?php echo $vidid ?>" <?php if ( $v['controls'] === 'true' ) echo "controls"; ?> <?php if ( $v['loop'] === 'true' ) echo "loop"; ?> preload="<?php echo "".$v['preload'] ?>" poster="<?php echo "".$v['poster'] ?>" height="<?php echo "".$h ?>" width="<?php echo "".$w ?>">
	<?php
		// sources
		for ( $j = 0; $j < count($ss); $j++ ) {
			$s = $ss[$j];
			printf('<source src="%s"%s>'."\n", $s['src'],
				isset($s['type']) && $s['type'] != '' ?
					sprintf(' type="%s"', $s['type']) : ''
			);
		}
		if ( array_key_exists('tracks', $v) )
		for ( $j = 0; $j < count($v['tracks']); $j++ ) {
			$tr = $v['tracks'][$j];
			echo '<track ';
			foreach ( $tr as $k => $trval ) { printf('%s="%s" ', $k, $trval); }
			echo ">\n";
		}
	?>
	<p><span><?php echo $v['altmsg'] ?></span></p>
	</video>
	<?php
	/*
	 * assemble parameters for control bar builder:
	 * "iparm" are generally needed parameters
	 * "oparm" are for <param> children of <object>
	 * some items may be repeated to keep the JS simple and orderly
	 * * OPTIONAL fields do not appear here;
	 * * see JS evhh5v_controlbar_elements
	 */
	$iparm = array("uniq" => $i,
		"barurl" => $ctlbar,
		"buturl" => $ctrbut,
		"volurl" => $ctlvol,
		"divclass" => "evhh5v_cbardiv", "vidid" => $vidid,
		"parentdiv" => $parentdiv, "auxdiv" => $auxdiv,
		"width" => $w, "barheight" => $barhi,
		"altmsg" => "<span id=\"span_objerr_".$i."\" class=\"evhh5v_cbardiv\">Control bar loading failed.</span>"
	);
	$oparm = array(
		// these must be appended with uniq id
		"uniq" => array("id" => "evh_h5v_ctlbar_svg_" . $i),
		// these must *not* be appended with uniq id
		"std" => $v['std']
	);

	$parms = array("iparm" => $iparm, "oparm" => $oparm);
	?>
	<script type="text/javascript">
	evhh5v_controlbar_elements(<?php printf('%s', json_encode($parms)); ?>, true);
	new evhh5v_sizer("<?php echo $parentdiv ?>", "o_putswf_video_<?php echo "".$i ?>", "<?php echo $vidid ?>", "ia_o_putswf_video_<?php echo "".$i ?>", false);
	</script>
  </div>
<?php
	// disabled with false: caption is drawn in tinymce editor
	if ( false && array_key_exists('caption', $v) && $v['caption'] != '' ) {
		printf("\n\t\t<p><span class=\"evhh5v_evh-caption\">%s</span></p>\n\n", $v['caption']);
	}
?>
</div>

<?php // end loop for ( $i = 0; $i < count($allvids); $i++ ) {
} ?>

</div>
</body>
</html>
<?php
?>
