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

if ( preg_match($jatt['a_vid']['poster'], '/^[0-9]$/') ) {
	// TODO: see about loading enough WP to get at attachment IDs
	// but if so, probably use a nonce too -- as is, w/o loading
	// WP, we're only using info that is already being exposed on
	// front-end pages, but to query WP DB for attachments by ID
	// a nonce should be required too.
	$jatt['a_vid']['poster'] = '';
}

if ( ($k = getwithdef('altvideo', '')) != '' ) {
	$a = explode('|', $k);
	foreach ( $a as $k ) {
		$t = explode('?', trim($k));
		$v = array('src' => trim($t[0]));
		if ( isset($v[1]) ) {
			$v['type'] = trim($v[1]);
		}
		$jatt['a_vid']['srcs'][] = $v;
	}
}

$allvids[] = $jatt;

?><!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="<?php echo $cssurl ?>" type="text/css">
	<script type="text/javascript" src="<?php echo $jsurl ?>"></script>
	<style>
		.main-div {
			margin-top: 0px;
			margin-bottom: 0px;
			margin-left:  0px;
			margin-right: 0px;
			outline: 0px;
			padding: 0px 0px;
			max-width: 100%;
			min-width: 100%;
			max-height: 100%;
			min-height: 100%;
			width: 100%;
			border: 0px;
			background-color: black;
			position: relative;
			overflow: hidden;
			clip: auto;
		}
	</style>
</head>

<body>
<div id="main-div" class="main-div" style="max-width: 100%" >

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

<div id="<?php echo $parentdiv ?>" class="like-wp-caption" style="max-width: <?php echo "".$w ?>px;">
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
			error_log('source: ' . $src);
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
		"std" => array(
		  "barheight" => $barhi, "barwidth" => $w, "aspect" => $asp,
			"hidebar" => 'false')
	);
	if ( isset($v['pixelaspect']) ) {
		$oparm["std"]['pixelaspect'] = $v['pixelaspect'];
	}
	if ( isset($v['aspectautoadj']) ) {
		$oparm["std"]['aspectautoadj'] = $v['aspectautoadj'];
	}
	$parms = array("iparm" => $iparm, "oparm" => $oparm);
	?>
	<script type="text/javascript">
	evhh5v_controlbar_elements(<?php printf('%s', json_encode($parms)); ?>);
	new evhh5v_sizer("<?php echo $parentdiv ?>", "o_putswf_video_<?php echo "".$i ?>", "<?php echo $vidid ?>", "ia_o_putswf_video_<?php echo "".$i ?>", false);
	</script>
  </div>
<?php
	if ( array_key_exists('caption', $v) && $v['caption'] != '' ) {
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
