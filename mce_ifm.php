<?php
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

/*
 * produce document with evhh5v video elements suitable for display
 * as an iframe for the tinymce 'Visual' posts editor, as set up
 * by a tinymce plugin loaded by the SWFPut WP plugin.
 * 
 * This is driven by proper arguments (each encoded) and makes a
 * single video player instance if expectations are met.
 */

$reqs = array('width','height','barheight',
	'a', 'i'
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

// Need WP help
require(getwithdef('a', '') . '/wp-load.php');

// Checks against naughtiness
$vopt = get_option('swfput_mceifm');
if ( ! $vopt ) {
	die("FAILED");
}
if ( (int)$vopt[0] !== (int)getwithdef('i', '') ) {
	die("NO AUTH");
}
if ( (int)$vopt[2] < ((int)time() - (int)$vopt[1]) ) {
	die("EXPIRED");
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

$allvids = array();
$vnum = 0;

$jatt = array('a_img' => '', 'a_vid' => '', 'obj' => '');

$jatt['a_vid'] = array(
	'width'     => getwithdef('width', ''),
	'height'	=> getwithdef('height', ''),
	'barheight'	=> getwithdef('barheight', '36'),
	'id'        => getwithdef('id', 'vh5_n_' . $vnum++),
	'poster'    => getwithdef('iimage', ''),
	'controls'  => 'true',
	'preload'   => 'none',
	'autoplay'  => 'false',
	'loop'      => 'false',
	'srcs'      => array(),
	'altmsg'    => getwithdef('altmsg', 'Video is not available'),
	'caption'	=> getwithdef('caption', ''),
	'aspect'	=> getwithdef('aspect', '4:3')
);

function maybe_get_attach($a) {
	if ( preg_match('/^[0-9]+$/', $a) ) {
		$u = wp_get_attachment_url($a);
		if ( $u ) {
			$a = $u;
		}
	}
	return $a;
}

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

$jatt['a_vid']['poster'] = maybe_get_attach($jatt['a_vid']['poster']);

if ( ($k = getwithdef('altvideo', '')) != '' ) {
	$a = explode('|', $k);
	foreach ( $a as $k ) {
		$t = explode('?', trim($k));
		$v = array('src' => maybe_get_attach(trim($t[0])));
		if ( isset($v[1]) ) {
			$v['type'] = trim($v[1]);
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
	$asp = array_key_exists('aspect', $v) ? $v['aspect'] : 0;
	$parentdiv = "div_wp_media_".$i;
	$auxdiv = "div_vidoj_".$i;
	$vidid = "va_o_putswf_video_".$i;
?>

<div id="<?php echo $parentdiv ?>" class="like-wp-caption" style="width: <?php echo "".$w ?>px; max-width: <?php echo "".$w ?>px">
  <div id="<?php echo $auxdiv ?>" class="evhh5v_vidobjdiv">
	<video id="<?php echo $vidid ?>" <?php echo $v['controls'] === 'true' ? "controls" : "" ?> <?php echo $v['autoplay'] === 'true' ? "autoplay" : "" ?> <?php echo $v['loop'] === 'true' ? "loop" : "" ?> preload="<?php echo "".$v['preload'] ?>" poster="<?php echo "".$v['poster'] ?>" height="<?php echo "".$h ?>" width="<?php echo "".$w ?>">
	<?php
		// sources
		for ( $j = 0; $j < count($ss); $j++ ) {
			$s = $ss[$j];
			$src = sprintf('<source src="%s"%s>'."\n", $s['src'],
				isset($s['type']) && $s['type'] != '' ?
					sprintf(' type="%s"', $s['type']) : ''
			);
			//error_log('source: ' . $src);
			echo $src;
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
