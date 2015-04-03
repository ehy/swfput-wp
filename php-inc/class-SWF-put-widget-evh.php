<?php
/*
 *      class-SWF-put-widget-evh.php
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
 * This is a class definition to provide a 'sidebar' widget
 * with the same capabilities as the main video object.
 */
class SWF_put_widget_evh extends WP_Widget {
	// main plugin class name
	const swfput_plugin = 'SWF_put_evh';
	// params helper class name
	const swfput_params = 'SWF_params_evh';
	// an instance of the main plugin class
	protected $plinst;
	
	// default width should not be wider than sidebar, but
	// widgets may be placed elsewhere, e.g. near bottom
	// 216x138 is suggested minimum size in Adobe docs,
	// because flash user settings dialog is clipped if 
	// window is smaller; AFAIK recent plugins refuse to map
	// the context menu rather than let it be clipped.
	// 216 is a bit wide for a sidebar (in some themes),
	// consider 200x150
	// Update: included JS now sizes the display if enclosing
	// div is sized smaller than its style.width; this is a
	// good thing.
	const defwidth  = 200; // is 4:3 aspect
	const defheight = 150; //


	public function __construct() {
		$this->plinst = SWF_put_evh::get_instance(false);
	
		$cl = __CLASS__;
		// Label shown on widgets page
		$lb =  __('SWFPut Video Player', 'swfput_l10n');
		// Description shown under label shown on widgets page
		$desc = __('Flash and HTML5 video for your widget areas', 'swfput_l10n');
		$opts = array('classname' => $cl, 'description' => $desc);

		// control opts width affects the parameters form,
		// height is ignored.  Width 400 allows long text fields
		// (not as log as most URL's), and informative (long) labels
		$copts = array('width' => 400, 'height' => 500);

		parent::__construct($cl, $lb, $opts, $copts);
	}

	// surely this code cannot run under PHP4, but anyway . . .
	public function SWF_put_widget_evh() {
		$this->__construct();
	}

	public function widget($args, $instance) {
		$opt = $this->plinst->get_widget_plugin_option();
		if ( $opt != 'true' ) {
			return;
		}
		
		$pr = self::swfput_params;
		$pr = new $pr();
		$pr->setnewarray($instance);

		if ( ! $pr->getvalue('width') ) {
			$pr->setvalue('width', self::defwidth);
		}
		if ( ! $pr->getvalue('height') ) {
			$pr->setvalue('height', self::defheight);
		}

		$pr->sanitize();
		$w = $pr->getvalue('width');
		$h = $pr->getvalue('height');

		// Added v. 1.0.7; 2014/01/24:
		// if wp_is_mobile is a defined function (wp 3.4?), then
		// honor new param 'mobiwidth' to set the dimensions
		// (proportionally to regular WxH) for mobile devices
		// user can set $mobiwidth 0 to disable this
		$mobiwidth = 0 + $pr->getvalue('mobiwidth');
		if ( $mobiwidth > 0 && function_exists('wp_is_mobile') ) {
			if ( wp_is_mobile() ) {
				$h = (int)($h * $mobiwidth / $w);
				$w = $mobiwidth;
			}
		}

		$cap = $this->plinst->wt($pr->getvalue('caption'));
		$uswf = $this->plinst->get_swf_url();
		// added 2.2: $pr->getvalue('align')
		$aln = 'align' . $pr->getvalue('align');

		// overdue: 2.1 removed deprecated align
		$dv = sprintf(
			'class="widget %s" style="width: %upx; max-width: 100%%"',
			$aln, $w);

		extract($args);

		// note *no default* for title; allow empty title so that
		// user may place this below another widget with
		// apparent continuity (subject to filters)
		$title = apply_filters('widget_title',
			empty($instance['title']) ? '' : $instance['title'],
			$instance, $this->id_base);

		echo $before_widget;

		// 2.2: title is now assigned to $elem['enter'] (below) which
		// places it just after opening of enclosing div in get_div()
		// so, no more "echo $before_title . $title . $after_title;"
		// but, before and after are still needed
		if ( $title ) {
			$title = $before_title . $title . $after_title;
		}

		if ( $cap ) {
			$cap = '<p class="caption"><span class="caption-span">'
				. $cap . '</span></p>';
		}

		// setup and print inner video div
		$ids  = $this->plinst->get_div_ids('widget-div');
		$elem = $this->plinst->get_player_elements($uswf, $pr, $ids);
		if ( $title ) {
			$elem['enter'] = $title;
		}
		printf('%s', $this->plinst->get_div($ids, $dv, $cap, $elem));

		echo $after_widget;
	}

	public function update($new_instance, $old_instance) {
		$pr = self::swfput_params;
		$pr = new $pr();
		
		if ( is_array($old_instance) ) {
			$pr->setnewarray($old_instance);
		}
		if ( is_array($new_instance) ) {
			$pr->setnewarray($new_instance);
		}
		
		$pr->sanitize();
		$i = $pr->getparams();
		if ( is_array($new_instance) ) {
			// for pesky checkboxes; not present if unchecked, but
			// present 'false' is wanted
			foreach ( $i as $k => $v ) {
				if ( ! array_key_exists($k, $new_instance) ) {
					$t = $pr->getdefault($k);
					// booleans == checkboxes
					if ( $t == 'true' || $t == 'false' ) {
						$i[$k] = 'false';
					}
				}
			}
		}

		if ( ! array_key_exists('caption', $i) ) {
			$i['caption'] = '';
		}
		if ( ! array_key_exists('title', $i) ) {
			$i['title'] = '';
		}
		if ( ! $i['width'] ) {
			$i['width'] = self::defwidth;
		}
		if ( ! $i['height'] ) {
			$i['height'] = self::defheight;
		}
		if ( ! $i['align'] ) {
			$i['align'] = $pr->getdefault('align');
		}

		return $i;
	}

	public function form($instance) {
		// EH: 20.07.2014
		// code and markup moved into file xed_widget_form.php
		// method is static, but use '->' to avoid NAME::
		require $this->plinst->mk_pluginincpath('xed_widget_form.php');
	}
} // End class SWF_put_widget_evh
?>
