<?php
/*
 *      xed_widget_form.php
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
 * These are the original contents of method 
 * swfput.php:<widget class>::form(),
 * now moved here to reduce size of main unit --
 * this file is require'd and efficiency is not a concern
 * since the code only runs when admin selects 
 * Appearance->widgets, and the new WP live widget thingies.
 */

// method entry: 'automatics' etc.
$wt = 'wptexturize';  // display with char translations
// still being 5.2 compatible; anon funcs appeared in 5.3
//$ht = function($v) { return htmlentities($v, ENT_QUOTES, 'UTF-8'); };
$ht = 'swfput_php52_htmlent'; // just escape without char translations
// NOTE on encoding: do *not* use JS::unescape()!
// decodeURIComponent() should use the page charset (which
// still leaves room for error; this code assumes UTF-8 presently)
$et = 'rawurlencode'; // %XX -- for transfer

// make data instance
$pr = self::swfput_params;
$pr = new $pr(array('width' => self::defwidth,
	'height' => self::defheight,
	'mobiwidth' => '0')); // new in 1.0.7
$instance = wp_parse_args((array)$instance, $pr->getparams());

$val = '';
if ( array_key_exists('title', $instance) ) {
	$val = $wt($instance['title']);
}
$id = $this->get_field_id('title');
$nm = $this->get_field_name('title');
$tl = $wt(__('Widget title:', 'swfput_l10n'));

// file select by ext pattern
$mpat = $this->plinst->get_mfilter_pat();
// files array from uploads dirs (empty if none)
$rhu = $this->plinst->r_find_uploads($mpat['m'], true);
$af = &$rhu['uploadfiles'];
$au = &$rhu['uploadsdir'];
$aa = &$rhu['medialib'];
// url base for upload dirs files
$ub = rtrim($au['baseurl'], '/') . '/';
// directory base for upload dirs files
$up = rtrim($au['basedir'], '/') . '/';
$slfmt =
	'<select class="widefat" name="%s" id="%s" onchange="%s">';
$sgfmt = '<optgroup label="%s">' . "\n";
$sofmt = '<option value="%s">%s</option>' . "\n";
// expect jQuery to be loaded by WP
$jsfmt = "jQuery('[id=%s]').val";
// BAD
//$jsfmt .= '(unescape(this.options[selectedIndex].value))';
// better
$jsfmt .= '(decodeURIComponent(this.options[selectedIndex].value))';
$jsfmt .= ';return false;';

$jafmt = "var t=jQuery('[id=%s]'),t1=t.val(),t2=";
$jafmt .= 'decodeURIComponent(this.options[selectedIndex].value);';
$jafmt .= "t1+=(t1.length>0&&t2.length>0)?' | ':'';t.val(t1+t2);";
$jafmt .= 'return false;';

// begin form
?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $wt($instance['caption']);
	$id = $this->get_field_id('caption');
	$nm = $this->get_field_name('caption');
	$tl = $wt(__('Caption:', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $instance['url'];
	$id = $this->get_field_id('url');
	$nm = $this->get_field_name('url');
	$tl = $wt(__('Url or media library ID for flash video:', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php // selects for URLs and attachment id's
	// escape url field id for jQuery selector
	$id = $this->plinst->esc_jqsel($id);
	$js = sprintf($jsfmt, $id);
	// optional print <select >
	if ( count($af) > 0 ) {
		$id = $this->get_field_id('files');
		$k = $this->get_field_name('files');
		$tl = $wt(__('Url for flash video from uploads directory:', 'swfput_l10n'));
		printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
		// <select>
		printf($slfmt . "\n", $k, $id, $js);
		// <options>
		printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
		foreach ( $af as $d => $e ) {
			$hit = array();
			for ( $i = 0; $i < count($e); $i++ )
				if ( preg_match($mpat['av'], $e[$i]) )
					$hit[] = &$af[$d][$i];
			if ( empty($hit) )
				continue;
			printf($sgfmt, $ht($d));
			foreach ( $hit as $fv ) {
				$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
				$fv = $ht($fv);
				printf($sofmt, $et($tu), $fv);
			}
			echo "</optgroup>\n";
		}
		// end select
		echo "</select></td></tr>\n";
	} // end if there are upload files
	if ( ! empty($aa) ) {
		$id = $this->get_field_id('atch');
		$k = $this->get_field_name('atch');
		$tl = $wt(__('Select ID from media library for flash video:', 'swfput_l10n'));
		printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
		// <select>
		printf($slfmt . "\n", $k, $id, $js);
		// <options>
		printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
		foreach ( $aa as $fn => $fi ) {
			$m = basename($fn);
			if ( ! preg_match($mpat['av'], $m) )
				continue;
			$ts = $m . " (" . $fi . ")";
			printf($sofmt, $et($fi), $ht($ts));
		}
		// end select
		echo "</select></td></tr>\n";
	} // end if there are upload files
	?>

	<?php /*
	// audio checkbox
	$val = $instance['audio'];
	$id = $this->get_field_id('audio');
	$nm = $this->get_field_name('audio');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Medium is audio (e.g. *.mp3): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php */
	$val = $instance['altvideo'];
	$id = $this->get_field_id('altvideo');
	$nm = $this->get_field_name('altvideo');
	$tl = $wt(__('URLs for HTML5 video (.mp4, .webm, .ogv):', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php // selects for URLs and attachment id's
	// escape url field id for jQuery selector
	$id = $this->plinst->esc_jqsel($id);
	$js = sprintf($jafmt, $id);
	// optional print <select >
	if ( count($af) > 0 ) {
		$id = $this->get_field_id('h5files');
		$k = $this->get_field_name('h5files');
		$tl = $wt(__('Url for HTML5 video from uploads directory (appends):', 'swfput_l10n'));
		printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
		// <select>
		printf($slfmt . "\n", $k, $id, $js);
		// <options>
		printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
		foreach ( $af as $d => $e ) {
			$hit = array();
			for ( $i = 0; $i < count($e); $i++ )
				if ( preg_match($mpat['h5av'], $e[$i]) )
					$hit[] = &$af[$d][$i];
			if ( empty($hit) )
				continue;
			printf($sgfmt, $ht($d));
			foreach ( $hit as $fv ) {
				$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
				$fv = $ht($fv);
				printf($sofmt, $et($tu), $fv);
			}
			echo "</optgroup>\n";
		}
		// end select
		echo "</select></td></tr>\n";
	} // end if there are upload files
	if ( ! empty($aa) ) {
		$id = $this->get_field_id('h5atch');
		$k = $this->get_field_name('h5atch');
		$tl = $wt(__('Select ID from media library for HTML5 video (appends):', 'swfput_l10n'));
		printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
		// <select>
		printf($slfmt . "\n", $k, $id, $js);
		// <options>
		printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
		foreach ( $aa as $fn => $fi ) {
			$m = basename($fn);
			if ( ! preg_match($mpat['h5av'], $m) )
				continue;
			$ts = $m . " (" . $fi . ")";
			printf($sofmt, $et($fi), $ht($ts));
		}
		// end select
		echo "</select></td></tr>\n";
	} // end if there are upload files
	?>

	<?php
	$val = $instance['playpath'];
	$id = $this->get_field_id('playpath');
	$nm = $this->get_field_name('playpath');
	$tl = $wt(__('Playpath (rtmp) or co-video (mp3):', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $instance['iimage'];
	$id = $this->get_field_id('iimage');
	$nm = $this->get_field_name('iimage');
	$tl = $wt(__('Url of initial image file (optional):', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php // selects for URLs and attachment id's
	// escape url field id for jQuery selector
	$id = $this->plinst->esc_jqsel($id);
	$js = sprintf($jsfmt, $id);
	// optional print <select >
	if ( count($af) > 0 ) {
		$id = $this->get_field_id('ifiles');
		$k = $this->get_field_name('ifiles');
		$tl = $wt(__('Load image from uploads directory:', 'swfput_l10n'));
		printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
		// <select>
		printf($slfmt . "\n", $k, $id, $js);
		// <options>
		printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
		foreach ( $af as $d => $e ) {
			$hit = array();
			for ( $i = 0; $i < count($e); $i++ )
				if ( preg_match($mpat['i'], $e[$i]) )
					$hit[] = &$af[$d][$i];
			if ( empty($hit) )
				continue;
			printf($sgfmt, $ht($d));
			foreach ( $hit as $fv ) {
				$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
				$fv = $ht($fv);
				printf($sofmt, $et($tu), $fv);
			}
			echo "</optgroup>\n";
		}
		// end select
		echo "</select></td></tr>\n";
	} // end if there are upload files
	if ( ! empty($aa) ) {
		$id = $this->get_field_id('iatch');
		$k = $this->get_field_name('iatch');
		$tl = $wt(__('Load image ID from media library:', 'swfput_l10n'));
		printf('<p><label for="%s">%s</label>' . "\n", $id, $tl);
		// <select>
		printf($slfmt . "\n", $k, $id, $js);
		// <options>
		printf($sofmt, '', $wt(__('none', 'swfput_l10n')));
		foreach ( $aa as $fn => $fi ) {
			$m = basename($fn);
			if ( ! preg_match($mpat['i'], $m) )
				continue;
			$ts = $m . " (" . $fi . ")";
			printf($sofmt, $et($fi), $ht($ts));
		}
		// end select
		echo "</select></td></tr>\n";
	} // end if there are upload files
	?>

	<?php
	// initial as 'bg', alternate checkbox
	$val = $instance['iimgbg'];
	$id = $this->get_field_id('iimgbg');
	$nm = $this->get_field_name('iimgbg');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Use initial image as no-video alternate: ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $wt($instance['width']);
	$id = $this->get_field_id('width');
	$nm = $this->get_field_name('width');
	$tl = sprintf(__('Width (default %u): ', 'swfput_l10n'), self::defwidth);
	$tl = $wt($tl);
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $wt($instance['height']);
	$id = $this->get_field_id('height');
	$nm = $this->get_field_name('height');
	$tl = sprintf(__('Height (default %u): ', 'swfput_l10n'), self::defheight);
	$tl = $wt($tl);
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $wt($instance['mobiwidth']);
	$id = $this->get_field_id('mobiwidth');
	$nm = $this->get_field_name('mobiwidth');
	$tl = $wt(__('Mobile width (0 disables) :', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $instance['aspectautoadj'];
	$id = $this->get_field_id('aspectautoadj');
	$nm = $this->get_field_name('aspectautoadj');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Auto aspect (e.g. 360x240 to 4:3): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $instance['displayaspect'];
	$id = $this->get_field_id('displayaspect');
	$nm = $this->get_field_name('displayaspect');
	$tl = $wt(__('Display aspect (e.g. 4:3, precludes Auto): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $instance['pixelaspect'];
	$id = $this->get_field_id('pixelaspect');
	$nm = $this->get_field_name('pixelaspect');
	$tl = $wt(__('Pixel aspect (e.g. 8:9, precluded by Display): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php // EH: 20.07.2014 -- added align options
	$val = $instance['align'];
	$id = $this->get_field_id('align');
	$nm = $this->get_field_name('align');
	$tl = $wt(__('Alignment (in page or post): ', 'swfput_l10n'));
	$aval = array(
		'left' => __('left', 'swfput_l10n'),
		'center' => __('center', 'swfput_l10n'),
		'right' => __('right', 'swfput_l10n'),
		'none' => __('none', 'swfput_l10n')
	);
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label><br />
	<?php
	foreach ( $aval as $k => $v ) {
		$tl = $wt($v);
		$tv = ($k === $val) ? ' checked="checked"' : '';
		printf("\t".'<label><input id="%s" name="%s" value="%s"%s type="radio">&nbsp;%s</label><br />'."\n",
			$id, $nm, $k, $tv, $tl
		);
	}
	?>
	</p>

	<?php
	$val = $wt($instance['volume']);
	$id = $this->get_field_id('volume');
	$nm = $this->get_field_name('volume');
	$tl = $wt(__('Initial volume (0-100): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
	$val = $instance['play'];
	$id = $this->get_field_id('play');
	$nm = $this->get_field_name('play');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Play on load (else waits for play button): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $instance['loop'];
	$id = $this->get_field_id('loop');
	$nm = $this->get_field_name('loop');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Loop play: ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $instance['hidebar'];
	$id = $this->get_field_id('hidebar');
	$nm = $this->get_field_name('hidebar');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Hide control bar initially: ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $instance['disablebar'];
	$id = $this->get_field_id('disablebar');
	$nm = $this->get_field_name('disablebar');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Hide and disable control bar: ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $instance['allowfull'];
	$id = $this->get_field_id('allowfull');
	$nm = $this->get_field_name('allowfull');
	$ck = $val == 'true' ? ' checked="checked"' : ''; $val = 'true';
	$tl = $wt(__('Allow full screen: ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;" type="checkbox"
		value="<?php echo $val; ?>"<?php echo $ck; ?> /></p>

	<?php
	$val = $ht($instance['barheight']);
	$id = $this->get_field_id('barheight');
	$nm = $this->get_field_name('barheight');
	$tl = $wt(__('Control bar Height (30-60): ', 'swfput_l10n'));
	?>
	<p><label for="<?php echo $id; ?>"><?php echo $tl; ?></label>
	<input class="widefat" id="<?php echo $id; ?>"
		name="<?php echo $nm; ?>" style="width:16%;"
		type="text" value="<?php echo $val; ?>" /></p>

	<?php
