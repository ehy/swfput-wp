<?php
/*
 *      xed_form.php
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
 * swfput.php:<main class>::put_xed_form(),
 * now moved here to reduce size of main unit --
 * this file is require'd and efficiency is not a concern
 * since the code only runs when admin selects a posts
 * or page page, (and then just once:).
 */

// method block entry: 'automatics' etc.

// cap check is done at registration of this callback
$pr = self::swfput_params; // 'SWF_params_evh' until changed ;-)
$pr = new $pr();
// Note on extract(): WP core had used this magic function extensively,
// but somewhere between 3.6 and 4.1 all uses were removed and not
// only one exists in 4.0, as is it now thought to make unreadble
// code.
// I'm leaving this one here: for comprehension see
// 'class SWF_params_evh' and look at the defaults array keys --
// this use is merely filling the form elements defaults
extract($pr->getparams());

$sc = self::shortcode;
// file select by ext pattern
$mpat = self::get_mfilter_pat();
// files array from uploads dirs (empty if none)
$rhu = self::r_find_uploads($mpat['m'], true);
$af = &$rhu['uploadfiles'];
$au = &$rhu['uploadsdir'];
$aa = &$rhu['medialib'];
// url base for upload dirs files
$ub = rtrim($au['baseurl'], '/') . '/';
// directory base for upload dirs files
$up = rtrim($au['basedir'], '/') . '/';
// id base for form and js
$id = 'SWFPut_putswf_video';
// label format string
$lbfmt = '<label for="%s_%s">%s</label>';
// table <input type="text"> format string
$infmt = '<input type="text" size="40" style="width:%u%%;" name="%sX%sX" id="%s_%s" value="%s" />';
// table <input type="checkbox"> format string
$ckfmt = '<input type="checkbox" name="%sX%sX" id="%s_%s" value="%s" %s/>';
// js function object
$job = $id . '_inst';
// form buttons format string
$bjfmt = '<input type="button" class="button" onclick="return %s.%s;" value="%s" />';
// form <select > format string
$slfmt = '<select name="%sX%sX" id="%s_%s" style="width:%u%%;" onchange="return %s.%s;">' . "\n";
// form <select > <optgroup > format string
$sgfmt = '<optgroup label="%s">' . "\n";
// form <select > <option > format string
$sofmt = '<option value="%s">%s</option>' . "\n";
// js send form values to editor method
$jfu = "send_xed(this.form,'{$id}','caption','{$sc}')";
// js reset form to defaults method
$jfur = "reset_fm(this.form,'{$id}')";
// js fill form from editor if possible
$jfuf = "from_xed(this.form,'{$id}','caption','{$sc}')";
// js replace last found shortcode in editor
$jfuc = "repl_xed(this.form,'{$id}','caption','{$sc}')";
// js delete last found shortcode from editor
$jfud = "rmsc_xed(this.form,'{$id}','caption','{$sc}')";
// js to copy from select/dropdown to text input
$jfsl = "form_cpval(this.form,'%s','%s','%s')";
// js to append from select/dropdown to text input
$jfap = "form_apval(this.form,'%s','%s','%s')";
// input text widths, wide, narrow
$iw = 100; $in = 8; // was: $in = 16;
// incr var for sliding divs
$ndiv = 0;
// button format for sliding divs
$dbfmt = '<input type="button" class="button" id="%s" value="%s" onclick="%s.%s" />';
// button values for sliding divs
$dbvhi = self::wt(__('Hide', 'swfput_l10n'));
$dbvsh = self::wt(__('Show', 'swfput_l10n'));
// js to show/hide sliding divs
$jdsh = "hideshow('%s', this.id, '{$dbvhi}', '{$dbvsh}')";
// class and base of id for sliding divs
$dvio = $id . '_odiv';
$dvii = $id . '_idiv';

// begin form
?>
	<!-- form buttons, in a table -->
	<table id="<?php echo $id . '_buttons'; ?>"><tr><td>
		<span  class="submit">
		<?php
			$l = self::wt(__('Fill from post', 'swfput_l10n'));
			printf($bjfmt, $job, $jfuf, $l);
			$l = self::wt(__('Replace current in post', 'swfput_l10n'));
			printf($bjfmt, $job, $jfuc, $l);
			$l = self::wt(__('Delete current in post', 'swfput_l10n'));
			printf($bjfmt, $job, $jfud, $l);
			$l = self::wt(__('Place new in post', 'swfput_l10n'));
			printf($bjfmt, $job, $jfu, $l);
			$l = self::wt(__('Reset defaults', 'swfput_l10n'));
			printf($bjfmt, $job, $jfur, $l);
		?>
		</span>
	</td></tr></table>

	<?php $ndiv++;
		$dvon = $dvio . $ndiv;
		$dvin = $dvii . $ndiv;
		$dvib = $dvin . '_btn';
		$jdft = sprintf($jdsh, $dvin);
	?>
	<div class="<?php echo $dvio; ?>" id="<?php echo $dvon; ?>">
	<span class="submit">
		<?php printf($dbfmt, $dvib, $dbvhi, $job, $jdft); ?>
	</span>
	<h3 class="hndle"><span><?php
		echo self::wt(__('Media', 'swfput_l10n')); ?></span></h3>
	<div class="<?php echo $dvii; ?>" id="<?php echo $dvin; ?>">

	<p>
	<?php $k = 'caption';
		$l = self::wt(__('Caption:', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
	</p><p>
	<?php $k = 'url';
		$l = self::wt(__('Flash video URL or media library ID (.flv or .mp4):', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
	</p>
	<?php
		// if there are upload files, print <select >
		$kl = $k;
		if ( count($af) > 0 ) {
			echo "<p>\n";
			$k = 'files';
			$jfcp = sprintf($jfsl, $id, $k, $kl);
			$l = self::wt(__('Select flash video URL from uploads directory:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			// <select>
			printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
			// <options>
			printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
			foreach ( $af as $d => $e ) {
				$hit = array();
				for ( $i = 0; $i < count($e); $i++ )
					if ( preg_match($mpat['av'], $e[$i]) )
						$hit[] = &$af[$d][$i];
				if ( empty($hit) )
					continue;
				printf($sgfmt, self::ht($d));
				foreach ( $hit as $fv ) {
					$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
					$fv = self::ht($fv);
					printf($sofmt, self::et($tu), $fv);
				}
				echo "</optgroup>\n";
			}
			// end select
			echo "</select><br />\n";
			echo "</p>\n";
		} // end if there are upload files
		if ( ! empty($aa) ) {
			echo "<p>\n";
			$k = 'atch';
			$jfcp = sprintf($jfsl, $id, $k, $kl);
			$l = self::wt(__('Select ID for flash video from media library:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			// <select>
			printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
			// <options>
			printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
			foreach ( $aa as $fn => $fi ) {
				$m = basename($fn);
				if ( ! preg_match($mpat['av'], $m) )
					continue;
				$ts = $m . " (" . $fi . ")";
				printf($sofmt, self::et($fi), self::ht($ts));
			}
			// end select
			echo "</select><br />\n";
			echo "</p>\n";
		} // end if there are upload files
	?>
	<p>
	<?php /* Remove MP3 audio (v. 1.0.8) $k = 'audio';
		$l = self::wt(__('Medium is audio: ', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		$ck = $$k == 'true' ? 'checked="checked" ' : '';
		printf($ckfmt, $id, $k, $id, $k, $$k, $ck); ?>
	</p><p>
	<?php */ $k = 'altvideo'; 
		$l = self::wt(__('HTML5 video URLs or media library IDs (.mp4, .webm, .ogv):', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
	</p>
	<?php
		// if there are upload files, print <select >
		$kl = $k;
		if ( count($af) > 0 ) {
			echo "<p>\n";
			$k = 'h5files';
			$jfcp = sprintf($jfap, $id, $k, $kl);
			$l = self::wt(__('Select HTML5 video URL from uploads directory (appends):', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			// <select>
			printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
			// <options>
			printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
			foreach ( $af as $d => $e ) {
				$hit = array();
				for ( $i = 0; $i < count($e); $i++ )
					if ( preg_match($mpat['h5av'], $e[$i]) )
						$hit[] = &$af[$d][$i];
				if ( empty($hit) )
					continue;
				printf($sgfmt, self::ht($d));
				foreach ( $hit as $fv ) {
					$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
					$fv = self::ht($fv);
					printf($sofmt, self::et($tu), $fv);
				}
				echo "</optgroup>\n";
			}
			// end select
			echo "</select><br />\n";
			echo "</p>\n";
		} // end if there are upload files
		if ( ! empty($aa) ) {
			echo "<p>\n";
			$k = 'h5atch';
			$jfcp = sprintf($jfap, $id, $k, $kl);
			$l = self::wt(__('Select ID for HTML5 video from media library (appends):', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			// <select>
			printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
			// <options>
			printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
			foreach ( $aa as $fn => $fi ) {
				$m = basename($fn);
				if ( ! preg_match($mpat['h5av'], $m) )
					continue;
				$ts = $m . " (" . $fi . ")";
				printf($sofmt, self::et($fi), self::ht($ts));
			}
			// end select
			echo "</select><br />\n";
			echo "</p>\n";
		} // end if there are upload files
	?>
	<p>
	<?php $k = 'playpath'; 
		$l = self::wt(__('Playpath (rtmp):', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
	</p><p>
	<?php $k = 'iimage';
		$l = self::wt(__('Url of initial image file (optional):', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		printf($infmt, $iw, $id, $k, $id, $k, $$k); ?>
	</p>
	<?php
		// if there are upload files, print <select >
		$kl = $k;
		if ( count($af) > 0 ) {
			echo "<p>\n";
			$k = 'ifiles';
			$jfcp = sprintf($jfsl, $id, $k, $kl);
			$l = self::wt(__('Load image from uploads directory:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			// <select>
			printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
			// <options>
			printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
			foreach ( $af as $d => $e ) {
				$hit = array();
				for ( $i = 0; $i < count($e); $i++ )
					if ( preg_match($mpat['i'], $e[$i]) )
						$hit[] = &$af[$d][$i];
				if ( empty($hit) )
					continue;
				printf($sgfmt, self::ht($d));
				foreach ( $hit as $fv ) {
					$tu = rtrim($ub, '/') . '/' . $d . '/' . $fv;
					$fv = self::ht($fv);
					printf($sofmt, self::et($tu), $fv);
				}
				echo "</optgroup>\n";
			}
			// end select
			echo "</select><br />\n";
			echo "</p>\n";
		} // end if there are upload files
		if ( ! empty($aa) ) {
			echo "<p>\n";
			$k = 'iatch';
			$jfcp = sprintf($jfsl, $id, $k, $kl);
			$l = self::wt(__('Load image ID from media library:', 'swfput_l10n'));
			printf($lbfmt, $id, $k, $l);
			// <select>
			printf($slfmt, $id, $k, $id, $k, $iw, $job, $jfcp);
			// <options>
			printf($sofmt, '', self::wt(__('none', 'swfput_l10n')));
			foreach ( $aa as $fn => $fi ) {
				$m = basename($fn);
				if ( ! preg_match($mpat['i'], $m) )
					continue;
				$ts = $m . " (" . $fi . ")";
				printf($sofmt, self::et($fi), self::ht($ts));
			}
			// end select
			echo "</select><br />\n";
			echo "</p>\n";
		} // end if there are upload files
	?>
	<p>
	<?php $k = 'iimgbg';
		$l = self::wt(__('Use initial image as no-video alternate: ', 'swfput_l10n'));
		printf($lbfmt, $id, $k, $l);
		$ck = $$k == 'true' ? 'checked="checked" ' : '';
		printf($ckfmt, $id, $k, $id, $k, $$k, $ck); ?>
	</p>

	</div></div>
	<?php $ndiv++;
		$dvon = $dvio . $ndiv;
		$dvin = $dvii . $ndiv;
		$dvib = $dvin . '_btn';
		$jdft = sprintf($jdsh, $dvin);
	?>
	<div class="<?php echo $dvio; ?>" id="<?php echo $dvon; ?>">
	<span class="submit">
		<?php printf($dbfmt, $dvib, $dbvhi, $job, $jdft); ?>
	</span>
	<h3 class="hndle"><span><?php
		echo self::wt(__('Dimensions', 'swfput_l10n')); ?></span></h3>
	<div class="<?php echo $dvii; ?>" id="<?php echo $dvin; ?>">

	<?php $els = array(
		array('width', '<p>', ' &#215; ', $in, 'inp',
			__('Pixel Width: ', 'swfput_l10n')),
		array('height', '', '</p>', $in, 'inp',
			__('Height: ', 'swfput_l10n')),
		array('mobiwidth', '<p>', '</p>', $in, 'inp',
			__('Mobile width (0 disables): ', 'swfput_l10n')),
		array('aspectautoadj', '<p>', '</p>', $in, 'chk',
			__('Auto aspect (e.g. 360x240 to 4:3): ', 'swfput_l10n')),
		array('displayaspect', '<p>', '</p>', $in, 'inp',
			__('Display aspect (e.g. 4:3, precludes Auto): ', 'swfput_l10n')),
		array('pixelaspect', '<p>', '</p>', $in, 'inp',
			__('Pixel aspect (e.g. 8:9, precluded by Display): ', 'swfput_l10n'))
		);
		foreach ( $els as $el ) {
			$k = $el[0];
			echo $el[1];
			$type = &$el[4];
			$l = self::wt($el[5]);
			printf($lbfmt, $id, $k, $l);
			if ( $type == 'inp' ) {
				printf($infmt, $el[3], $id, $k, $id, $k, $$k);
			} else if ( $type == 'chk' ) {
				$ck = $$k == 'true' ? 'checked="checked" ' : '';
				printf($ckfmt, $id, $k, $id, $k, $$k, $ck);
			}
			echo $el[2];
		}
	?>

	</div></div>
	<?php $ndiv++;
		$dvon = $dvio . $ndiv;
		$dvin = $dvii . $ndiv;
		$dvib = $dvin . '_btn';
		$jdft = sprintf($jdsh, $dvin);
	?>
	<div class="<?php echo $dvio; ?>" id="<?php echo $dvon; ?>">
	<span class="submit">
		<?php printf($dbfmt, $dvib, $dbvhi, $job, $jdft); ?>
	</span>
	<h3 class="hndle"><span><?php
		echo self::wt(__('Behavior', 'swfput_l10n')); ?></span></h3>
	<div class="<?php echo $dvii; ?>" id="<?php echo $dvin; ?>">
	
	<p>
	<?php // EH: 20.07.2014 -- added align options
	$val = 'align';
	$cur = isset($$val) ? $$val : $pr->getdefault('align');
	$l = self::wt(__('Alignment (in page or post): ', 'swfput_l10n'));
	$aval = array(
		'left' => __('left', 'swfput_l10n'),
		'center' => __('center', 'swfput_l10n'),
		'right' => __('right', 'swfput_l10n'),
		'none' => __('none', 'swfput_l10n')
	);
	printf($lbfmt."\n", $id, $val, $l);
	foreach ( $aval as $k => $v ) {
		$l = self::wt($v);
		$tv = ($k === $cur) ? ' checked="checked"' : '';
		printf("\t".'<label>&nbsp; %s <input id="%s_%s" name="%sX%sX" value="%s"%s type="radio" /></label>'."\n",
			$l, $id, $val, $id, $val, $k, $tv
		);
	}
	?>
	</p>

	<p>
	<?php // EH: 30.07.2014 -- added preload options
	$val = 'preload';
	$cur = isset($$val) ? $$val : $pr->getdefault('preload');
	$l = self::wt(__('Video preload: ', 'swfput_l10n'));
	$aval = array(
		'none' => __('none', 'swfput_l10n'),
		'metadata' => __('metadata', 'swfput_l10n'),
		'auto' => __('auto', 'swfput_l10n'),
		'image' => __('per initial image: "none" or "preload"', 'swfput_l10n')
	);
	printf($lbfmt."\n", $id, $val, $l);
	foreach ( $aval as $k => $v ) {
		$l = self::wt($v);
		$tv = ($k === $cur) ? ' checked="checked"' : '';
		printf("\t".'<label>&nbsp; %s <input id="%s_%s" name="%sX%sX" value="%s"%s type="radio" /></label>'."\n",
			$l, $id, $val, $id, $val, $k, $tv
		);
	}
	?>
	</p>

	<?php $els = array(
		array('volume', '<p>', '</p>', $in, 'inp',
			__('Initial volume (0-100): ', 'swfput_l10n')),
		array('play', '<p>', '</p>', $in, 'chk',
			__('Play on load (else waits for play button): ', 'swfput_l10n')),
		array('loop', '<p>', '</p>', $in, 'chk',
			__('Loop play: ', 'swfput_l10n')),
		array('hidebar', '<p>', '</p>', $in, 'chk',
			__('Hide control bar initially: ', 'swfput_l10n')),
		array('disablebar', '<p>', '</p>', $in, 'chk',
			__('Hide and disable control bar: ', 'swfput_l10n')),
		array('allowfull', '<p>', '</p>', $in, 'chk',
			__('Allow full screen: ', 'swfput_l10n')),
		array('barheight', '<p>', '</p>', $in, 'inp',
			__('Control bar Height (30-60): ', 'swfput_l10n'))
		);
		foreach ( $els as $el ) {
			$k = $el[0];
			echo $el[1];
			$type = &$el[4];
			$l = self::wt($el[5]);
			printf($lbfmt, $id, $k, $l);
			if ( $type == 'inp' ) {
				printf($infmt, $el[3], $id, $k, $id, $k, $$k);
			} else if ( $type == 'chk' ) {
				$ck = $$k == 'true' ? 'checked="checked" ' : '';
				printf($ckfmt, $id, $k, $id, $k, $$k, $ck);
			}
			echo $el[2];
		}
	?>

	</div></div>
