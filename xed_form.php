<?php
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

	<?php
?>
