<?php
/*
 *  mainact.inc.php
 *
 *  Copyright 2011 Ed Hynan <edhynan@gmail.com>
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
* Description: SWF video app with PHP/Ming, main A/S include
* Version: 0.1.0
* Author: Ed Hynan
* License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)
*/

/* text editor: use real tabs of 4 column width, LF line ends */

/**********************************************************************\
 *  PHP + module initialization/checks: to be done in enclosing scope *
\**********************************************************************/


// BEGIN main action script
// note the PHP variables within the here-doc: should be kept
// near top of script, assigned to AS vars, rather than sprinkled
// throughout script
$mainact = <<<OMM

///
/// global variable init section
///

// noScale mode; 'hand' scale proportionally in script
Stage.scaleMode = "noScale";
Stage.align = "TL";
_focusrect = false; // seems to be default, but just the same . . .
var stream = null;
var connection = null;
var sound = null;
var flvers = System.capabilities.version;
var curdate = new Date();
var obj_css = new TextField.StyleSheet();
var obj_css_url = "$obj_css_url";

var t;  // temp var
var tf; // temp func var

//
// here gather values from encloding PHP
//

// release or devel/debug? set in PHP as integer \$i_release
var b_release = $i_release ? true : false;
// should NetStream check media server policy file (best set false)
var bchkpolicyfile = $bchkpolicyfile;
// temp func to help assignment from enclosing PHP
tf = function (def, str, isok) {
	return str.length > 0 && isok != null ? isok : def;
};
var swfvs = tf(8, "$swfvs", $swfvs);
var initvolume = tf(50, "$initvolume", $initvolume);
var doloop = tf(false, "$doloop", $doloop);
var disablebar = tf(false, "$disablebar", $disablebar);
var showdeblockingitems = tf(false, "$showdeblockingitems", $showdeblockingitems);
var barpadding = tf(1, "$barpadding", $barpadding);
var barsubtr = tf(barpadding + 2, "$barsubtr", $barsubtr);
var barheight = tf(26, "$barheight", $barheight);
var barlength = tf(240 - barsubtr, "$barlength", $barlength);
var butwidthfactor = tf(0.60, "$butwidthfactor", $butwidthfactor);
var butwidth = tf(barheight * butwidthfactor + 1, "$butwidth", $butwidth);
var butheight = butwidth;
var barshowmargin = tf(2, "$barshowmargin", $barshowmargin);
var progressbarheight = tf((barheight - butheight) * 0.20, "$progressbarheight", $progressbarheight);
var progressbaroffs = tf((barheight - butheight) * 0.25 / 2.0, "$progressbaroffs", $progressbaroffs);
var progressbarlength = tf(barlength - progressbaroffs * 2, "$progressbarlength", $progressbarlength);
// need constant value for initial progressbarlength:
// in a button pointer coordinates are scaled to original
// dimensions, so resized values  cannot be used in, e.g.
// cross multiplication to find a proportional value
var const_pbar_len = progressbarlength;
var progressbarxoffs = tf((barlength - progressbarlength) / 2, "$progressbarxoffs", $progressbarxoffs);
var barshowincr = tf(1, "$barshowincr", $barshowincr);
var tickinterval = tf(50, "$tickinterval", $tickinterval);
var ptrinterval = tf(5, "$ptrinterval", $ptrinterval);
var streambuftime = tf(5, "$streambuftime", $streambuftime);
var initpause = tf(true, "$initpause", $initpause);
var initshowbar = tf(false, "$initshowbar", $initshowbar);
var initshowtime = tf(-1, "$initshowtime", $initshowtime);
var initshowtimemax = tf(-1, "$initshowtimemax", $initshowtimemax);
var doshowtxt = tf(true, "$doshowtxt", $doshowtxt);
var volbarlen = tf(butwidth * 4, "$volbarlen", $volbarlen);
// see comment above the definition of const_pbar_len
var const_vbar_len = volbarlen;
var volbarwid = tf(butheight / 2, "$volbarwid", $volbarwid);
var timetxt_rb = tf(10, "$timetxt_rb", $timetxt_rb);
var rtmbut = tf(bbar.spkrbut, "$rtmbut", $rtmbut);

var nomediahtml = "$nomediahtml";
var connrejecthtml = "$connrejecthtml";
var connfailhtml = "$connfailhtml";
var playfailhtml = "$playfailhtml";
var nomediamsg = "$nomediamsg";
var js_nomediamsg = "$js_nomediamsg";
var vurl = tf(null, "$vurl", "$vurl");
var eurl = tf(null, "$eurl", "$eurl");
// v_id -- if RTMP, stream id, 'playpath'
var v_id = "" == "$v_id" ? null : "$v_id";
// boolean, is true if media at URL is *known* audio, false if known not
var audb = "false" == "$audb" ? false : ("true" == "$audb" ? true:null);
// lose the temp func:
tf = null;
// temp literal: vid to show with audio only files
var v4aud = ""; // "$v4aud";
// optional initial image url
var iimage = "$iimage";
// optional initial image scale proportional vs. WxH full fit
var iiproportion = true;

var dopause = initpause;
var doshowbar = initshowbar;
// 2-8-13: loadonload changed to false: TODO: make it an option
var loadonload = false; // load media immediately on player load
// Timed bar hiding; has menu item to toggle
var doshowbartime = ! initshowbar;
var doscale = true;
var initshowoffset = 0;
var curkey = null; // current key press, ASCII
var ntick = 0; // incr in tick callback
var ptrtick = 0; // as above; used for pointer
var ptrtickmax = (1000 / tickinterval) * ptrinterval;
var isrunning = false; // set by stream events start and stop
// stream.time difference for display; e.g. for ffserver
var timediff = 0;
// to save volume on client
var volclient = null;

// filled by metadata, used with progress bars
var timetot = "";
var dl_tot = "";

// vars to be filled in NetStream.onMetaData()
var stream_canseekontime = null;
var stream_bytelength = null;
var stream_filesize = null;
var stream_seekpoints = null;
var stream_cuePoints = null;
var stream_videocodecid = null;
var stream_framerate = null;
var stream_videodatarate = null;
var stream_height = null;
var stream_width = null;
var stream_totalduration = null;
var stream_starttime = null;
var stream_duration = null;
// ** cannot find metadata on pixel or display ASPECT **
//var registrationWidth = null;
//var registrationHeight = null;
//var registrationX = null;
//var registrationY = null;
// found this aspect data in a few rtmp streams; server specific?
var stream_frameWidth = null;
var stream_displayWidth = null;
var stream_frameHeight = null;
var stream_displayHeight = null;
// aspect factors, calculated from metadate if available
var upixaspect = 1.0;
var afactW = 1;
var afactH = 1;
var stream_unknownkey = null;
var stream_unknownvar = null;
// but if the media is audio only (mp3 . . .)
var audio_duration = 0;
var audio_bytelength = 0;
// current audio time position
var audcurtime = 0;
// sound pause boolean
var dopauseaud = dopause;
// Sound object duration and position are in ms
var adiv = 1000;
// url RTMP stream protocol?
var brtmp = false;
// dummy values for streams unknown time/size
dummy_duration = 60 * 10;
dummy_bytestotal = 1024 * 1024 * 50;
b_use_stream_dummies = "{$stream_dummies}" == "true" ? true : false;
// should aspect be automatically set when it *MIGHT* be necessary?
var autoaspect = "{$autoaspect}" == "true" ? true : false;
var displayaspect = $displayaspect ;
var pixelaspect = $pixelaspect ;

// for handling 'NetStream.Play.Reset'
var resettick = 0;
var resettickint = 1500 / tickinterval; // one (1000), half (500) sec
// rtmp presentation time bug correction
var last_ct = 0;


///
/// begin functional section
///

///
/// utility function section
///

function null_proc () {}

// Encode elements of a path.  The closed flash plugin is flexible
// regarding URLs and paths, but GNash requires encoding as necessary.
// the builtin escape() does not handle path elements.
// Update 2013/07/22: in fact ActionScript escape() does all
// non-alphanumeric characters, which is simplistic, and it turns
// out some servers do not handle, e.g., escaped underscore '_';
// so, this proc is best as a fallback -- a properly escaped arg
// should be provided to the player if possible.
function pathesc(path) {
	var p = path;
	var pa = p.split('/');

	for ( var i = 0; i < pa.length; i++ ) {
		pa[i] = escape(pa[i]);
	}

	p = pa.join('/');

	return p;
}

// pathesc will make a mess of the URL proto, host, so handle that;
// this function may seem ugly, but only because it is; more
// importantly it is subject to errors, e.g. apparent protocol part
// is not sufficiently checked. Without regex functions or
// a decent representation of ansi C string functions, making
// this tight would require reproducing so much that should
// be provided by the language, or libraries -- maybe later,
// time permitting, strengthen this.
function urlesc(url) {
	var ta = url.split(':');
	var prot;
	var host;
	var path = '';
	var i;

	if ( ta.length < 2 ) {
		// not URL
		return pathesc(url);
	}
	if ( ! (ta[1].charAt(0) == '/' && ta[1].charAt(1) == '/') ) {
		// not URL
		return pathesc(url);
	}

	host = ta[1].substr(2);
	if ( host.length < 1 ) {
		// input error; e.g. 'proto://' alone
		return '';
	}

	// join on ':', which might have been in path when split(':')
	for ( i = 2; i < ta.length; i++ ) {
		host += ':' + ta[i];
	}

	i = host.indexOf('/', 1);
	if ( i >= 1 ) {
		path = pathesc(host.substr(i));
		host = host.substr(0, i);
	} else {
		path = "";
	}

	//prot = unescape(ta[0]) + '://';
	prot = ta[0] + '://';
	return prot + host + path;
}

// check if proto is rtmp
function check_rtmp(url) {
	var ta = url.split(':');

	switch ( ta[0] ) {
		case "rtmp":
			return true;
			break;
		case "rtmps":
			return true;
			break;
		case "rtmpe":
			return true;
			break;
		case "rtmpt":
			return true;
			break;
		default:
			return false;
			break;
	}

	return false;
}

// naive function just checks if extension is typical of audio
// files; which is of course error prone
// if checkdot is true, check .???, else just check last
// 3 chars e.g. because string is URL escaped
function check4aud(fname, checkdot) {
	var s;

	if ( checkdot ) {
		s = fname.substr(fname.length - 4);
		if ( s.charAt(0) != '.' ) {
			return false;
		}
		s = s.substr(1);
	} else {
		s = fname.substr(fname.length - 3);
	}

	// need to know exact list of supported audio types and
	// conventional extensions, but I don't
	switch ( s.toLowerCase() ) {
		case "mp3":
			return true;
			break;
/* I must have misunderstood what flash supports as audio:
   it seems codecs may be supported in/with video, but not
   as audio only. Comment all cases but mp3:
		case "aac":
			// lousy hack: audio formats might actually have times
			// presented on different scales; e.g. mp3 in ms, aac in s.
			// TODO find examples of other supported audio, find out
			// what those might be.
			adiv = 1;
			return true;
			break;
		case "f4a":
			return true;
			break;
		case "f4b":
			return true;
			break;
end commented cases
*/
		default:
			return false;
			break;
	}

	// not reached
	return false;
}

// decimal string of at least length 2, left zero padded
function n02(n) {
	var t = "" + n;
	while ( t.length < 2 ) {
		t = "0" + t;
	}
	return t;
}

// make HH:MM:SS from seconds (MM:SS if < hour)
function mktimtxt(timesecs) {
	var ts = Math.round(timesecs);
	var s = ts % 60;
	var m = Math.floor(ts / 60) % 60;
	var h = Math.floor(ts / 3600);
	if ( h > 0 ) {
		return n02(h) + ":" + n02(m) + ":" + n02(s);
	}
	return n02(m) + ":" + n02(s);
}

// add filter to object, e.g. disabled buttons; new the filter
function add_filter(item, filter) {
	var t = item.filters;
	t.push(filter);
	item.filters = t;
}

// If there is a direct way to get browser user agent string in
// flash, I haven't found it, so try an external JS function;
// this might be unreliable, so caller should expect false return.
function get_ua_string() {
	if ( _level0._evh_ua_string !== undefined ) {
		return _level0._evh_ua_string;
	}

	_level0._evh_ua_string = false;

	try {
		var xi;
		xi = flash.external.ExternalInterface === undefined
			? false : true;
		if ( xi ) {
			var f = "navigator.userAgent.valueOf";
			xi = flash.external.ExternalInterface.call(f);
			if ( typeof (xi) == "string" ) {
				_level0._evh_ua_string = xi;
			}
		}
	} catch ( e ) {
	}; // ming requires terminated try/catch

	return _level0._evh_ua_string;
}

// is the browser, or 'user agent', mobile?
// [lifted from JS code, which was lifted from WordPress php]
//var test_ua_is_mobile = true;
function ua_is_mobile() {
	if ( test_ua_is_mobile !== undefined ) {
		return test_ua_is_mobile;
	}

	if ( _level0._evh_ua_is_mobile_bool !== undefined ) {
		return _level0._evh_ua_is_mobile_bool;
	}

	_level0._evh_ua_is_mobile_bool = false;
	var ua = get_ua_string();
	
	if ( typeof (ua) == "string" )
	if (   ua.indexOf('Mobile') >= 0 // many mobile devices (all iPhone, iPad, etc.)
		|| ua.indexOf('Android') >= 0
		|| ua.indexOf('Silk/') >= 0
		|| ua.indexOf('Kindle') >= 0
		|| ua.indexOf('BlackBerry') >= 0
		|| ua.indexOf('Opera Mini') >= 0
		|| ua.indexOf('Opera Mobi') >= 0 ) {
		document._evh_ua_is_mobile_bool = true;
	}

	return _level0._evh_ua_is_mobile_bool;
};


///
/// end utility function section
///

///
/// begin control and interface section
///

// guarded against reevaluation
if ( guardinit == undefined ) {
	// prepare and add listeners
	ctl1M = {
		/*
		onMouseDown = function () {
		},
		onMouseUp = function () {
		},
		*/
		onMouseMove: function () {
			var bwid = barshowmargin;
			var x = _xmouse;
			var y = _ymouse;
			var w = Stage.width - bwid;
			var h = Stage.height - bwid;
			if ( x > bwid && y > bwid && x < w && y < h ) {
				if ( doshowbar == false ) {
					showhideBar(doshowbar = true);
				}
			} else {
				if ( doshowbar == true ) {
					showhideBar(doshowbar = false);
				}
			}

			Mouse.show();
			ptrtick = 0;

			if ( volgadget.vbarbut.mousedown ) {
				volgadget.vbarbut.onMouseMove();
			}
		},
		onMouseWheel: function (delta, target) {
			// this is not getting called in the GNU/Linux plugin
			// (although mouse wheel works in the debug textfield)
			if ( target.onMouseWheel != undefined ) {
				if ( target.onMouseWheel != null ) {
					target.onMouseWheel(delta);
				}
			}
		}
	};
	ctl1K = {
		onKeyDown: function () {
			// save key across events
			curkey = Key.getAscii();
		},
		onKeyUp: function () {
			// use keyUp for actions to allow escape
			if ( curkey == 32 ) {
				// space
				if ( ! brtmp ) {
					togglepause();
				}
			} else if ( curkey == 81 || curkey == 113 ) {
				// Q,q
				stopVideo();
			} else if ( curkey == 70 || curkey == 102 ) {
				// F,f
				doFullscreen();
			} else if ( curkey == 71 || curkey == 103 ) {
				// G,g
				if ( b_release == undefined || b_release == false ) {
					bbar.dbg._visible = ! bbar.dbg._visible;
					if ( bbar.dbg._visible ) {
						var xi = get_ua_string();
						if ( xi ) {
							adddbgtext("User agent == '"+xi+"'\n");
							adddbgtext("Is mobile == '"+ua_is_mobile()+"'\n");
						}
						adddbgtext("isrunning=="+isrunning+"\n");
					}
				}
			} else if ( curkey == 65 || curkey == 97 ) {
				// A,a
				// debugging broken gnash 0.8.10 aspect 0.561...
				if ( Math.abs(upixaspect - 1.0) < 0.001 ) {
					upixaspect =
					   parseFloat(System.capabilities.pixelAspectRatio);
					if ( upixaspect === NaN ||
						upixaspect < 0.125 /*arbitrary minimum*/ )
						upixaspect = 1.0;
				} else {
					upixaspect = 1.0;
				}
				// at 1st I thought flash had pixaspect inverted, partly
				// because, unknown to me, Ubuntu had hard coded 96 dpi
				// for the X display, which is quite wrong, so . . .
				// incorrect:
				//afactW = 1.0 / upixaspect;
				// correct:
				afactW = upixaspect;
			} else if ( curkey == 86 || curkey == 118 ) {
				// V,v
				volgadget._visible = ! volgadget._visible;
			} else if ( curkey == 60 || curkey == 62 ) {
				// <,>
				incrVolumeAdjust(curkey == 60 ? -10 : 10);
			} else if ( curkey == 83 || curkey == 115 ) {
				// S,s
				// wait movie; for devel
				if ( wait._visible == true ) {
					stopWait();
				} else {
					startWait();
				}
			}

			// HACK: clear curkey: sometimes escape key
			// causes repeat of last keyUp event!
			curkey = null;
		}
	};
	ctl1S = {
		// called if Stage.scaleMode == "noScale"
		onResize: function() {
			if ( video == null ) {
				return;
			}
			var vw = stream_width * afactW; // display aspect factors
			var vh = stream_height * afactH;
			var va = vw / vh; // aspect
			var sw = Stage.width;
			var sh = Stage.height;
			var sa = sw / sh;
			
			// allow natural scale or not
			if ( vw < sw && vh < sh ) {
				bbar.nosclbutdisable._visible = false;
			} else {
				bbar.nosclbutdisable._visible = true;
			}

			if ( ! doscale ) {
				if ( vw > Stage.width || vh > Stage.height ) {
					if ( sa > va ) { // Stage aspect wider
						video._width = sh * va;
						video._height = sh;
					} else { // narrower
						video._width = sw;
						video._height = sw * vh / vw;
					}
				} else {
					video._width = vw;
					video._height = vh;
				}
				video._x = (sw - video._width) / 2;
				video._y = (sh - video._height) / 2;
			} else {
				if ( sa > va ) { // Stage wider than video
					video._width = sh * va;
					video._height = sh;
					video._x = (sw - video._width) / 2;
					video._y = 0;
				} else { // narrower
					video._width = sw;
					video._height = sw * vh / vw;
					video._x = 0;
					video._y = (sh - video._height) / 2;
				}
			}

			// need to diddle the button icon
			if ( Stage.displayState == "fullScreen" ) { // vs. "normal"
				bbar.fullscrbut._visible = false;
				bbar.windscrbut._visible = true;
			} else {
				bbar.fullscrbut._visible = true;
				bbar.windscrbut._visible = false;
			}

			// interface objects re{size,position}
			resizeFace();
		}
	};
	// add listeners
	Mouse.addListener(ctl1M);
	Key.addListener(ctl1K);
	Stage.addListener(ctl1S);

	//	add context menu items
	var cbctxmenu = function(obj, menu) {
		for ( var n = 0; n < menu.customItems.length; n++ ) {
			menu.customItems[n].enabled = true;
		}
	};
	var cbmenusmooth = function(obj, item) {
		video.smoothing = item.val;
	};
	var cbmenudeblock = function(obj, item) {
		video.deblocking = item.val;
	};
	var cbmenuhidebar = function(obj, item) {
		doshowbartime = item.val = ! item.val;
		adddbgtext("HIDE BAR == " + doshowbartime + "\n");
	};

	var menusmoothT = new ContextMenuItem("$menusmoothT", cbmenusmooth, true);
	menusmoothT.val = true;
	var menusmoothF = new ContextMenuItem("$menusmoothF", cbmenusmooth);
	menusmoothF.val = false;
	var menuhidebar = new ContextMenuItem("$menuhidebar", cbmenuhidebar, true);
	menuhidebar.val = doshowbartime;
	var menufullscr = new ContextMenuItem("$menufullscr", toggleFullscreen, true);
	var ctxmenu = new ContextMenu(cbctxmenu);
	ctxmenu.customItems.push(menufullscr);
	ctxmenu.customItems.push(menusmoothT, menusmoothF, menuhidebar);
	if ( showdeblockingitems ) {
	var menudeblock0 = new ContextMenuItem("$menudeblock0", cbmenudeblock,true);
	menudeblock0.val = 0;
	var menudeblock1 = new ContextMenuItem("$menudeblock1", cbmenudeblock);
	menudeblock1.val = 1;
	var menudeblock2 = new ContextMenuItem("$menudeblock2", cbmenudeblock);
	menudeblock2.val = 2;
	var menudeblock3 = new ContextMenuItem("$menudeblock3", cbmenudeblock);
	menudeblock3.val = 3;
	var menudeblock4 = new ContextMenuItem("$menudeblock4", cbmenudeblock);
	menudeblock4.val = 4;
	var menudeblock5 = new ContextMenuItem("$menudeblock5", cbmenudeblock);
	menudeblock5.val = 5;
	var menudeblock6 = new ContextMenuItem("$menudeblock6", cbmenudeblock);
	menudeblock6.val = 6;
	var menudeblock7 = new ContextMenuItem("$menudeblock7", cbmenudeblock);
	menudeblock7.val = 7;
	ctxmenu.customItems.push(menudeblock0, menudeblock1, menudeblock2);
	ctxmenu.customItems.push(menudeblock3, menudeblock4, menudeblock5);
	ctxmenu.customItems.push(menudeblock6, menudeblock7);
	} // end if deblocking items

	_level0.menu = ctxmenu;

	guardinit = 1;
}

// callback for stream on finding metadata
stream_onMetaData = function(info) {
	// null anything wanted afresh for each stream
	stream_duration = stream_totalduration = null;
	stream_bytelength = null;
	stream_filesize = null;

// FLV:
//canseekontime=='true'
//bytelength=='20771448'
//totaldatarate=='775.984010012422'
//totalduration=='213.614'
//starttime=='0'
//
// MP4:
//trackinfo=='[object Object],[object Object]'
//audiochannels=='2'
//seekpoints=='[object Object],[object Object],...,[object Object]'
//aacaot=='2'
//avclevel=='30'
//avcprofile=='66'
//moovposition=='40'
//tags=='undefined,undefined,undefined,undefined,undefined,undefined'
//
// rtmp stream, mp4?
//displayHeight=='404'
//displayWidth=='720'
//frameHeight=='404'
//frameWidth=='720'
//height=='404'
//width=='720'

	adddbgtext("begin dbg stream_onMetaData\n");
	for ( var k in info ) {
		var v = info[k];

		if ( k == "canseekontime" ) { stream_canseekontime = v; } else
		if ( k == "bytelength" ) { stream_bytelength = v; } else
		if ( k == "filesize" ) { stream_filesize = v; } else
		if ( k == "seekpoints" ) { stream_seekpoints = v; } else
		if ( k == "cuePoints" ) { stream_cuePoints = v; } else
		if ( k == "videocodecid" ) { stream_videocodecid = v; } else
		if ( k == "framerate" ) { stream_framerate = v; } else
		if ( k == "videodatarate" ) { stream_videodatarate = v; } else
		if ( k == "height" ) { stream_height = v; } else
		if ( k == "width" ) { stream_width = v; } else
		if ( k == "duration" ) { stream_duration = v; } else
		if ( k == "totalduration" ) { stream_totalduration = v; } else
		if ( k == "starttime" ) { stream_starttime = v; } else
		if ( k == "comment" ) { stream_comment = v; } else
		if ( k == "frameHeight" ) { stream_frameHeight = v; } else
		if ( k == "frameWidth" ) { stream_frameWidth = v; } else
		if ( k == "displayWidth" ) { stream_displayWidth = v; } else
		if ( k == "displayHeight" ) { stream_displayHeight = v; } else
		{ stream_unknownkey = k; stream_unknownvar = v;
		}

		if ( b_release == false ) {
			switch ( k ) {
				case "seekpoints":
					adddbgtext(" Got: "+k+"==(omitted)\n");
					break;
				case "cuePoints":
					adddbgtext(" Got: "+k+"==(omitted)\n");
					break;
				default:
					adddbgtext(" Got: "+k+"=='"+v+"'\n");
					break;
			}
		}
	}
	adddbgtext("end dbg stream_onMetaData\n");

	if ( stream_duration == null ) {
		stream_duration = stream_totalduration;
	}
	// defective servers (ffserver) might issue 0
	if ( stream_duration == null || stream_duration == 0 ) {
		stream_duration = stream_totalduration = null;
	}
	if ( stream_duration == null && audb != true ) {
		if ( b_use_stream_dummies ) {
			//timetot = brtmp ? "|~~~~~~|" : "??:??:??";
			timetot = "|~~~~~~|";
		} else {
			timetot = "";
		}
	} else if ( audb != true ) {
		timetot = mktimtxt(stream_duration); //  + 0.5);
	}

	if ( stream_bytelength == null ) {
		stream_bytelength = stream_filesize;
	}
	// defective servers (ffserver) might issue 0
	if ( stream_bytelength == null || stream_bytelength == 0 ) {
		stream_bytelength = stream_filesize = null;
	}
	if ( stream.bytesTotal && audb != true ) {
		dl_tot = "" + Math.round(stream.bytesTotal / 1024) + "k";
	} else if ( stream_bytelength && audb != true ) {
		dl_tot = "" + Math.round(stream_bytelength / 1024) + "k";
	} else if ( audb != true ) {
		dl_tot = "?k";
	}

	if ( stream_width != null ) {
		video._width = stream_width;
	} else {
		stream_width = video._width = Stage.width;
	}
	if ( stream_height != null ) {
		video._height = stream_height;
	} else {
		stream_height = video._height = Stage.height;
	}

	// comment hack: testing w/ ffserver, it allows
	// setting 'comment' metadata, so use it for aspect
	// data -- '|' separated
	if ( stream_comment != null ) {
		var a = stream_comment.split("|");
		if ( a[0] == "saWsaHdaWdaH" ) {
			stream_frameWidth = a[1];
			stream_frameHeight = a[2];
			stream_displayWidth = a[3];
			stream_displayHeight = a[4];
			stream_comment = null;
			// set time difference for ffserver's broken timestamp;
			// see comment in ticker() function
			timediff = stream.time;
			adddbgtext(" T0th=='"+timediff+"'\n");
		} else if ( stream_comment == "ffserver" ) {
			timediff = stream.time;
			adddbgtext(" T0th=='"+timediff+"'\n");
		}
	}

	// external option displayaspect:
	if ( displayaspect != 0 ) {
		var t = displayaspect;
		autoaspect = false;
		if ( displayaspect == 'D' ) {
			t = Stage.width / Stage.height;
		}
		if ( stream_displayHeight == null ) {
			stream_displayHeight = stream_height;
		}
		stream_displayWidth = stream_displayHeight * t;
	// external option pixelaspect:
	} else if ( pixelaspect != 0 ) {
		autoaspect = false;
		if ( pixelaspect == 'S' ) {
			stream_displayWidth = Stage.width;
			stream_displayHeight = Stage.height;
		} else {
			stream_displayWidth = stream_width * pixelaspect;
			stream_displayHeight = stream_height;
		}
	}
	
	// optional auto adjust aspect for DVD-TV sizes w/o aspect metadata:
	// expecting common case of 4:3 aspect, but 16:9 (for 352x288) was
	// found at rtmp://rtmp.infomaniak.ch/livecast playpath=cineplume
	// most aspect adjustment will need user control
	if ( stream_displayWidth == null || stream_displayHeight == null ) {
		if ( autoaspect ) {
			var w = stream_width; var h = stream_height;
			// common sizes; cannot handle every possibility
			if ( w == 720 || w == 704 ) {
				if ( h == 480 || h == 576 ) {
					stream_displayWidth = 640;
					stream_displayHeight = 480;
				}
			}
			if ( w == 360 || w == 352 ) {
				// handle 360x240? is it common w/ square pixels?
				//if ( h == 288 || (h == 240 && w == 352) ){
				if ( h == 288 || h == 240 ) {
					stream_displayWidth = 320;
					stream_displayHeight = 240;
				}
			}
		}
	}
	if ( stream_displayWidth != null && stream_displayHeight != null ) {
		var va = stream_width / stream_height;
		var da = stream_displayWidth / stream_displayHeight;

		// at 1st I thought flash had pixaspect inverted, partly
		// because, unknown to me, Ubuntu had hard coded 96 dpi
		// for the X display, which is quite wrong, so . . .
		// incorrect:
		//afactW = 1.0/upixaspect; //System.capabilities.pixelAspectRatio;
		afactW = upixaspect; //System.capabilities.pixelAspectRatio;
		// correct:
		afactH = 1;
		if ( Math.abs(da - va) > 0.001 ) {
			afactW *= da * stream_height / stream_width;
		}
		adddbgtext(" Got: afactW=='"+afactW+"'\n");
		adddbgtext(" Got: afactH=='"+afactH+"'\n");
	}

	ctl1S.onResize();

	// use this point for more video settings
	video._visible = true;
	// if initial pause set w/ still frame offset, try seek now
	if ( dopause && initpause ) {
		var mx = stream_duration == null ? 0 :
			Math.min(streambuftime, stream_duration);
		if ( initshowtimemax != null && initshowtimemax > 0 ) {
			mx = Math.min(initshowtimemax, mx);
		}
		if ( initshowtime < 0 ) { // random
			initshowoffset = Math.random() * mx;
		} else if ( initshowtime > 0 ) { // specific
			initshowoffset =  initshowtime;
		} else {
			initshowoffset = 0;
		}
		initshowoffset = Math.min(initshowoffset, mx);
		adddbgtext(" InP "+initshowoffset+"\n");
		// really confuses flash plugin on streams -- causes
		// onMetaData() to be called again, inf loop until
		// stream.pause(false)
		if ( initshowoffset > 0  ) {
			stream.seek(initshowoffset);
			last_ct = 0;
		}
		initpause = false;
	}
};

connection_onStatus = function(stat) {
	switch ( stat.code ) {
	case 'NetConnection.Connect.Rejected':
		adddbgtext("Got stat.code 'NetConnection.Connect.Rejected'\n");
		conn_reject();
		stopWait();
		stopVideo();
		break;
	case 'NetConnection.Connect.Closed':
		adddbgtext('Got "NetConnection.Connect.Closed"\n');
		stopWait();
		if ( ! doloop ) {
			stopVideo();
		}
		break;
	case 'NetConnection.Connect.Success':
		adddbgtext('Got "NetConnection.Connect.Success"\n');
		ConnectedStartVideo();
		break;
	case 'NetConnection.Connect.Failed':
		adddbgtext('Got "NetConnection.Connect.Failed"\n');
		conn_fail();
		stopWait();
		stopVideo();
		break;
	case 'NetConnection.Connect.ProxyAuthFailed':
		adddbgtext('Got "NetConnection.Connect.ProxyAuthFailed"\n');
		break;
	case 'NetConnection.Connect.SSLNotAvailable':
		adddbgtext('Got "NetConnection.Connect.SSLNotAvailable"\n');
		break;
	case 'NetConnection.Connect.SSLHandshakeFailed':
		adddbgtext('Got "NetConnection.Connect.SSLHandshakeFailed"\n');
		break;
	case 'NetConnection.Connect.CertificateExpired':
		adddbgtext('Got "NetConnection.Connect.CertificateExpired"\n');
		break;
	case 'NetConnection.Connect.CertificatePrincipalMismatch':
		adddbgtext('Got "NetConnection.Connect.CertificatePrincipalMismatch"\n');
		break;
	case 'NetConnection.Connect.CertificateUntrustedSigner':
		adddbgtext('Got "NetConnection.Connect.CertificateUntrustedSigner"\n');
		break;
	case 'NetConnection.Connect.CertificateRevoked':
		adddbgtext('Got "NetConnection.Connect.CertificateRevoked"\n');
		break;
	case 'NetConnection.Connect.CertificateInvalid':
		adddbgtext('Got "NetConnection.Connect.CertificateInvalid"\n');
		break;
	case 'NetConnection.Connect.CertificateAPIError':
		adddbgtext('Got "NetConnection.Connect.CertificateAPIError"\n');
		break;
	case 'NetConnection.Connect.NetworkChange':
		adddbgtext('Got "NetConnection.Connect.NetworkChange"\n');
		break;

	default:
		adddbgtext(" Got connStat: "+stat.code+"\n");
		break;
	}
};

stream_onStatus = function(stat) {
	// only if doing primarily video; else video is just
	// eye candy to accompany primary audio
	if ( audb == true ) {
		if ( stat.code == 'NetStream.Play.Start' ) {
			isrunning = true;
			resettick = 0;
			hideinfohtml();
			postStartVideo();
			adddbgtext(" Got onStat: "+stat.code+"\n");
		} else if ( isrunning && stat.code == 'NetStream.Play.Stop' ) {
			stream.seek(0);
			last_ct = 0;
		}
		return;
	}

	switch ( stat.code ) {
	case 'NetStream.Seek.Notify':
		if ( ! dopause ) {
			startWait();
		}
		isrunning = true;
		last_ct = 0;
		resettick = 0;
		break;
	case 'NetStream.Buffer.Empty':
		if ( isrunning && ! dopause ) {
			startWait();
		}
		resettick = 0;
		break;
	case 'NetStream.Buffer.Full':
		stopWait();
		resettick = 0;
		break;
	case 'NetStream.Buffer.Flush':
		//stopWait();
		resettick = 0;
		break;
	case 'NetStream.Play.Start':
		isrunning = true;
		resettick = 0;
		hideinfohtml();
		postStartVideo();
		adddbgtext(" Got onStat: "+stat.code+"\n");
		break;
	case 'NetStream.Play.Stop':
		adddbgtext(" Got onStat: "+stat.code+"\n");
		resettick = 0;
		stopWait();
		if ( ! brtmp && doloop ) {
			stream.seek(0);
		}
		isrunning = false;
		if ( volclient != undefined && volclient != null ) {
			volclient.flush();
		}
		break;
	case 'NetStream.Play.Failed':
		play_fail();
		stopWait();
		stopVideo();
		break;
	case 'NetStream.Play.StreamNotFound':
		not_found();
		stopWait();
		stopVideo();
		break;
	case 'NetStream.Seek.InvalidTime':
		// TODO: what should be done here?
		adddbgtext(" Got onStat: "+stat.code+"\n");
		stopWait();
		break;
	case 'NetStream.Play.Reset':
		// found this code observing rtmp streams
		startWait();
		resettick = resettickint;
		adddbgtext(" Got onStat: "+stat.code+"\n");
		break;

	default:
		adddbgtext(" Got onStat: "+stat.code+"\n");
		break;
	}
};

// stream media only
stream_onPlayStatus = function(stat) {
	adddbgtext("--stream_onPlayStatus: '"+stat.code+"'\n");
	// I haven't seen this:
	if ( stat.code == 'NetStream.Play.Complete' ) {
		resettick = 0;
		stopVideo();
		stopWait();
		if ( volclient != undefined && volclient != null ) {
			volclient.flush();
		}
	}
};

// default for unhandled .level == error status events
System.onStatus = function (err) {
	adddbgtext("--System.onStatus: '"+err.code+"'\n");
};

// onTextData appeared in flash 9 beta, according to Adobe coder blog.
// Would be used for e.g. subtitles in mp4
// http://www.3gpp.org/ftp/Specs/html-info/26245.htm
stream_onTextData = function(dat) {
	adddbgtext("begin dbg stream_onTextData\n");
	for ( var k in dat ) {
		var v = dat[k];
		adddbgtext(" Got: "+k+"=='"+v+"'\n");
	}
	adddbgtext("end dbg stream_onTextData\n");
};

// callback for sound objects when played out
sound_onComplete = function() {
	stopWait();
	if ( ! doloop ) {
		isrunning = false;
		if ( volclient != undefined && volclient != null ) {
			volclient.flush();
		}
	} else {
		sound.stop();
		sound.start(0);
	}
};

// like stream_onMetaData, but for sound object if audio only
sound_onLoad = function(succeeded) {
	if ( ! succeeded ) {
		isrunning = false;
		not_found();
		stopWait();
		return;
	}
	isrunning = true;
	audio_duration = this.duration;
	audio_bytelength = this.getBytesTotal();
	adddbgtext(" AUD duration: " + audio_duration + "\n");
	adddbgtext(" AUD bytelength: " + audio_bytelength + "\n");

	if ( ! audio_duration ) {
		if ( b_use_stream_dummies ) {
			//timetot = "??:??:??";
			timetot = "|~~~~~~|";
		}
	} else {
		timetot = mktimtxt(Math.floor(audio_duration / adiv));
	}

	if ( audio_bytelength ) {
		dl_tot = "" + Math.round(audio_bytelength / 1024) + "k";
	} else {
		dl_tot = "?k";
	}

	// wait-a-while will have been started in startVideo()
	stopWait();
};

function putdbgtext(txt) {
	if ( b_release == true ) { return; }
	bbar.dbg.text = txt;
}

function adddbgtext(txt) {
	if ( b_release == true ) { return; }
	bbar.dbg.text += txt;
}

function putinfohtml(txt) {
	itxt.htmlText = txt;
	itxt._visible = true;
}

function addinfohtml(txt) {
	itxt.htmlText += txt;
	itxt._visible = true;
}

var infotimer = null;
function hideinfohtml() {
	itxt._visible = false;
	if ( infotimer != null ) {
		clearTimeout(infotimer);
		infotimer = null;
	}
}

// showing the html field often failed when player was just started,
// using a small delay seems reliable
function show_not_found(html) {
	putinfohtml(html);
/*
	// js alert: temp, or additional
	var jmsg = js_nomediamsg;
	if ( false && swfvs >= 8 ) {
		// Adobe flash 8 docs say this should work, but no . . .
		alert(jmsg);
	} else {
		_root.geturl("javascript:alert('" + jmsg + "')");
	}
*/
}

// showing the html field often failed when player was just started,
// using a small delay seems reliable
function not_found() {
	if ( infotimer != null ) {
		// already called
		return;
	}
	infotimer = setTimeout(show_not_found, 500, nomediahtml);
}

function conn_reject() {
	if ( infotimer != null ) {
		// already called
		return;
	}
	infotimer = setTimeout(show_not_found, 500, connrejecthtml);
}

function conn_fail() {
	if ( infotimer != null ) {
		// already called
		return;
	}
	infotimer = setTimeout(show_not_found, 500, connfailhtml);
}

function play_fail() {
	if ( infotimer != null ) {
		// already called
		return;
	}
	infotimer = setTimeout(show_not_found, 500, playfailhtml);
}

function real_startWait() {
	wait._visible = true;
	wait.gotoAndPlay(1);
}

// wait would show from flurry of status messages, empty/full,
// at end of video file; the flurry seems to end quickly so
// using a small delay seems reliable
var waittimer = null;
function stopWait() {
	if ( waittimer != null ) {
		clearTimeout(waittimer);
		waittimer = null;
	}
	wait._visible = false;
	wait.gotoAndStop(1);
}

// wait would show from flurry of status messages, empty/full,
// at end of video file; the flurry seems to end quickly so
// using a small delay seems reliable
function startWait() {
	if ( waittimer != null ) {
		// already called
		return;
	}
	waittimer = setTimeout(real_startWait, 500, null);
}

// resize interface items, when Stage size changes, and on init
// TODO: make separate initsizeFace() and resizeFace(); resize...
// can be simplified to use relative values from Stage difference,
// init must handle any start size
function resizeFace() {
	var sw = Stage.width;
	var sh = Stage.height;

	// informational text adjustment
	itxt._width = sw / 2.0;
	itxt._height = sh * 3.0 / 4.0;
	itxt._x = sw / 4.0;
	itxt._y = sh / 8.0;

	// background click handler; initial sizw is 1x1, so maintain scale
	if ( bghndlr ) {
		var bgo = bghndlr;
		bgo._x = bgo._y = 0;
		bgo._xscale = sw * 100;
		bgo._yscale = sh * 100;
		bgo.tabEnabled = false;
		bgo.useHandCursor = false;
	}

	// wait movie adjustment
	wait._x = sw / 2;
	wait._y = sh / 2;

	// initial button/image adjustment (if not started yet)
	if ( inibut !== null ) {
		inibut.resize();
	}

	// control bar adjustment
	bbar.resize();

	// some gadgets are relative to bar yhome
	if ( ua_is_mobile() ) { // horizontal
		var x, y;
		y = bbar.yhome - 3 - volbarwid * 2;
		x = rtmbut._x - volbarlen + (butwidth - volbarwid) / 2;
		volgadget._y = y;
		volgadget._x = x;
	} else { // rotated -90 -- vertical
		var x, y;
		y = bbar.yhome - 3; //- volbarwid * 2;
		x = rtmbut._x + (butwidth - volbarwid) / 2;
		volgadget._y = y;
		volgadget._x = x;
	}
}

var bbar_resize = function () {
	this._x = 0 + barpadding;
	this.ctlpanel._x = 0 + barpadding;

	barlength = Stage.width - barsubtr;
	butwidth =  barheight * butwidthfactor;
	butheight = butwidth;

	progressbarlength = barlength - (progressbaroffs * 2);
	progressbarxoffs = (barlength - progressbarlength) / 2;

	// control bar y placement accounts for hiding offscreen --
	// 'yhome' is the base y position
	var y = Stage.height - barheight - barpadding;
	var t = y - this.yhome;
	this.yhome = y;
	this.yshowpos += t;
	this._y += t;

	// order of width changes is important; textfield
	// width is not based on Stage width ( but _x is )
	// these lines are meant to force autosizing
	t = this.tmtxt.text;
	this.tmtxt.autoSize = true;
	this.tmtxt.text = "00:00:00/00:00:00";
	var w = this.tmtxt._width;
	this.tmtxt.autoSize = false;
	this.tmtxt.text = t;
	this.tmtxt._width = w;
	this.dltxt.autoSize = false;
	this.dltxt._width = w;
	this.tmtxt._height = this.dltxt._height;

	// order of width changes is important; if Stage is now wider,
	// then bar should be widened before its children, and vice versa
	if ( barlength > this._width ) {
		this._width = barlength;
		this.ctlpanel._width = barlength;
	}

	// progress bar adjustment here,
	// between possible bbar width adjustments
	this.progpb._width = progressbarlength;
	this.progpl._width = progressbarlength;
	this.progdlb._width = progressbarlength;
	this.progdl._width = progressbarlength;
	this.progpb._x = progressbarxoffs;
	this.progpl._x = progressbarxoffs;
	this.progdlb._x = progressbarxoffs;
	this.progdl._x = progressbarxoffs;

	// text _x set here, between possible bbar width adjustments
	this.tmtxt._x = barlength - (w + timetxt_rb);
	this.dltxt._x = this.tmtxt._x;

	// see comment above at "if ( barlength > this._width )"
	if ( barlength < this._width ) {
		this.ctlpanel._width = barlength;
		this._width = barlength;
	}

	// hide the playback and download text fields if
	// width is too narrow for nice display
	// (rtmbut is set in php to rightmost button)
	if ( (rtmbut._x + rtmbut._width + timetxt_rb) > this.tmtxt._x ) {
		this.tmtxt._visible = false;
		this.dltxt._visible = false;
	} else {
		this.tmtxt._visible = doshowtxt;
		this.dltxt._visible = doshowtxt;
	}

	// BUG: only with audio (mp3), and initial image and so media
	// not loading until start button -- when immediately switched
	// to full screen after start, the backing 'ctlpanel' does not
	// resize properly (width too small). But, all other objects
	// resized here do resize properly!  I can see nothing in the
	// code that could interfere with ctlpanel so I believe it's
	// a flash plugin bug.
	// Re-diddling, as below, has been working.
	this.ctlpanel._x = 0 + barpadding;
	this.ctlpanel._width = barlength;
};

var inibut_resize = function () {
	this._x = Stage.width / 2;
	this._y = Stage.height / 2;

	var m = this.initialimg;
	if ( m && m.ok ) {
		var xs = m._xscale / 100;
		var ys = m._yscale / 100;
		var ia = (m._width * xs) / (m._height * ys);
		var sa = Stage.width / Stage.height;
		
		m._x = -this._x;
		m._y = -this._y;

		if ( ! _root.iiproportion ) {
			m._xscale = Stage.width / m._width * 100.0 * xs;
			m._yscale = Stage.height / m._height * 100.0 * ys;
		} else if ( sa > ia ) {
			var sc = Stage.height / m._height * 100.0 * xs;
			m._xscale = sc;
			m._yscale = sc;
			m._x += (Stage.width - m._width) / 2;
		} else {
			var sc = Stage.width / m._width * 100.0 * ys;
			m._xscale = sc;
			m._yscale = sc;
			m._y += (Stage.height - m._height) / 2;
		}
	}
};

// click callback for control bar background
function ctlpanelHit () {
	volgadget._visible = false;
	hideinfohtml();
}

// click callback for initial play button
function initialbutHit () {
	togglepause();
}

// click callback for timeline progress bar
function plprogHit () {
	var px = bbar.progpb._xmouse;
	last_ct = 0;

	if ( audb == true && audio_duration ) {
		// see comment above the definition of const_pbar_len
		var pos = Math.floor(audio_duration / adiv * px / const_pbar_len);
		sound.stop();
		// last_ct, isrunning are handled in seek.Notify for NetStream,
		// but for Sound must be handled here (no similar evants)
		last_ct = 0;
		isrunning = true;
		sound.start(pos);
		adddbgtext(" APOS: " + pos + "\n");
	} else if ( audb == false && stream_duration ) {
		// see comment above the definition of const_pbar_len
		stream.seek(stream_duration * px / const_pbar_len);
	} else if ( audb == false ) {
		// TODO: better options for seeking in 'sizeless' streams
		var off = px > (bbar.progpb._width / 2) ? 30 : -30;
		stream.seek(stream.time + off);
	}
}

function showhideBar(bshow) {
	var show = Stage.height - bbar._height - barpadding;
	var hide = show + bbar._height + barpadding * 2;
	var p = bshow ? show : hide;

	if ( bshow && bbar._y >= p ) {
		bbar.yshowpos = p;
	} else if ( ! bshow && bbar._y <= p ) {
		bbar.yshowpos = p;
	}
};

obj_onMouseDown = function() { this.mousedown = true;  };
obj_onMouseUp   = function() { this.mousedown = false; };

function doVolumeCtl() {
	volgadget._visible = ! volgadget._visible;
}

// this must be assigned to MovieClip; N.F. for Button
volbar_onMouseWheel = function(d) {
	incrVolumeAdjust(d);
};

volbar_onMouseMove = function() {
	if ( this.mousedown ) {
		setVolumeAdjust(this._xmouse);
	}
};

function doVolumeAdjust() {
	var m = volgadget.vbarbut._xmouse;
	setVolumeAdjust(m);
}

function setVolumeAdjust(scale) {
	var v;
	var m = scale;
	// see comment above the definition of const_vbar_len
	var l = const_vbar_len; //volgadget.vbarbut._width;
	var p = m / l;

	// BUG: this gets called at load time with strange values
	if ( m < 0 || m > l ) {
		return;
	}
	// do this only if sound has been created
	if ( sound == undefined || sound == null ) {
		return;
	}

	v = Math.round(100 * p);
	sound.setVolume(v);
	v = sound.getVolume();
	volgadget.vbarind._width = Math.round(l * v / 100);

	// save setting for client
	if ( volclient ) {
		volclient.data.volume = v;
	}
}

function incrVolumeAdjust(incr) {
	var v;

	if ( sound == undefined || sound == null ) {
		return;
	}

	v = Math.round(sound.getVolume() + incr);
	v = Math.min(100, Math.max(0, v));
	sound.setVolume(v);
	v = sound.getVolume();
	volgadget.vbarind._width = Math.round(const_vbar_len * v / 100);

	// save setting for client
	if ( volclient ) {
		volclient.data.volume = v;
	}
}

function checkFullscreen() {
	if ( Stage.displayState == "fullScreen" ) {
		return true;
	}
	return false;
}

function doFullscreen() {
	Stage.displayState = "fullScreen"; // vs. "normal"
}

function toggleFullscreen() {
	if ( Stage.displayState == "fullScreen" ) { // vs. "normal"
		Stage.displayState = "normal";
	} else {
		Stage.displayState = "fullScreen";
	}
}

function toggleDoScale() {
	if ( doscale = ! doscale ) {
		bbar.dosclbut._visible = false;
		bbar.nosclbut._visible = true;
	} else {
		bbar.dosclbut._visible = true;
		bbar.nosclbut._visible = false;
	}

	ctl1S.onResize();
}

function pauseAudio(bpause) {
	if ( audb == false ) {
		return;
	}
	if ( ! bpause ) {
		sound.start(audcurtime);
		showhideBar(doshowbar = false);
		return;
	}
	audcurtime = Math.floor(sound.position / adiv);
	sound.stop();
}

function pauseVideo(bpause) {
	bbar.playbut._visible = bpause;
	bbar.pausebut._visible = ! bbar.playbut._visible;
	if ( bpause ) {
		stopWait();
	} else if ( initshowoffset > 0 ) {
		// unpausing from initial show-frame; seek to 0
		stream.seek(0);
		initshowoffset = 0;
		last_ct = 0;
	}
	if ( ! bpause ) {
		showhideBar(doshowbar = false);
	}
	stream.pause(bpause);
}

function togglepauseAudio() {
	if ( audb == false ) {
		return;
	}

	if ( false && sound == null ) { // had been stopped; restart
		dopauseaud = true;
		startVideo();
	}
	if ( sound != null ) { // OK
		pauseAudio(dopauseaud = ! dopauseaud);
	}
}

function togglepauseVideo() {
	if ( stream == null ) { // had been stopped; restart
		dopause = true;
		startVideo();
	}
	if ( stream != null ) { // OK
		pauseVideo(dopause = ! dopause);
	}
}

togglepause = function() {
	if ( inibut !== null ) {
		//inibut.gotoAndStop(1);
		inibut.stop();
		inibut.initialbut.enabled = inibut.initialimg.enabled = false;
		inibut.initialbut._visible = inibut.initialimg._visible = false;
		inibut.enabled = false;
		inibut._visible = false;
		delete inibut.initialimg.imld;
		inibut.initialimg = null;
		inibut = null;
	}
	togglepauseAudio(); togglepauseVideo();
};

function stopVideo() {
	isrunning = false;

	if ( volclient != undefined ) {
		volclient.flush();
	}

	stopWait();

	// video is not deleted; it is setup by Ming and
	// might be trouble if deleted and new'd
	//video.clear();
	video.attachVideo(null);

	if ( sound != undefined && sound != null ) {
		if ( audb ) {
			sound.stop();
		}
		sound.attachAudio(null);
		delete sound;
		sound = null;
	}

	if ( stream != undefined && stream != null ) {
		stream.close();
		delete stream;
		stream = null;
	}

	if ( connection != undefined && connection != null ) {
		connection.close();
		delete connection;
		connection = null;
	}

	bbar.playbut._visible = true;
	bbar.pausebut._visible = ! bbar.playbut._visible;
	bbar.pausebutdisable._visible = false;
	bbar.stopbut._visible = false;
	bbar.stopbutdisable._visible = true;
	bbar.tmtxt._visible = false;
	bbar.dltxt._visible = false;
	isrunning = false;
}

function startVideo() {
	stopVideo();
	startWait();

	bbar.progpl._width = 1;
	bbar.progdl._width = 1;
	bbar.stopbut._visible = true;
	bbar.stopbutdisable._visible = false;

	bbar.fullscrbut._visible = checkFullScreen() ? false : true;
	bbar.windscrbut._visible = ! bbar.fullscrbut._visible;

	try {
		connection = new NetConnection();
		// connect() true return does not mean success!
		// most errors must be handled in onStatus
		connection.onStatus = connection_onStatus;
		if ( ! connection.connect(brtmp ? vurl : null) ) {
			throw new Error(".connect false return");
		}
	} catch ( e ) {
		conn_fail();
		stopWait();
		stopVideo();
		adddbgtext(".connect() exception '"+e.message+"'\n");
		return;
	};
}

// to be called by handler of NetConnection 'connected' event;
// not directly
function ConnectedStartVideo() {
	audcurtime = 0;
	isrunning = true;

	stream = new NetStream(connection);
	stream.onMetaData = stream_onMetaData;
	stream.onStatus = stream_onStatus;
	stream.onPlayStatus = stream_onPlayStatus;
	// as of flash 9 beta; subtitles
	// http://www.3gpp.org/ftp/Specs/html-info/26245.htm
	if ( swfvs >= 9 ) {
		stream.onTextData = stream_onTextData;
	}
	stream.setBufferTime(streambuftime);

	video._visible = false;
	video.attachVideo(stream);
	video.menu = ctxmenu;
	video.menu.hideBuiltInItems();

	sound = new Sound();
	if ( audb == true ) {
		sound.onLoad = sound_onLoad;
		sound.onSoundComplete = sound_onComplete;
		sound.checkPolicyFile(bchkpolicyfile);
		sound.loadSound(vurl, true);
		if ( v4aud != null ) {
			stream.play(v4aud);
		}
	} else {
		var p = brtmp ? v_id : vurl;
		sound.attachAudio(stream);
		if ( ! brtmp ) {
			stream.checkPolicyFile(bchkpolicyfile);
		}
		stream.play(p);
	}

	// setup volume
	if ( volclient.data.volume != undefined ) {
		var v = volclient.data.volume;
		sound.setVolume(v);
		v = sound.getVolume();
		// note constant const_vbar_len not used here, as
		// gadget might be resized
		volgadget.vbarind._width = Math.round(volbarlen * v / 100);
	}

	// initial pause state according to boolean dopause
	if ( ! brtmp ) {
		pauseAudio(dopauseaud);
		pauseVideo(dopause);
		bbar.pausebutdisable._visible = false;
	}
	if ( initshowbar != null ) {
		doshowbar = initshowbar;
		// not used again
		initshowbar = null;
	}
	showhideBar(doshowbar);
}

//
function postStartVideo () {
	if ( brtmp ) {
		// pause does not work well with rtmp streams
		dopause = false;
		bbar.playbut._visible = false;
		bbar.pausebut._visible = ! bbar.playbut._visible;
		bbar.pausebut.enabled = false;
		bbar.pausebutdisable._visible = true;
	}
	// rtmp presentation time bug correction
	last_ct = 0;
}

function ticker () {
	ntick++;
	ptrtick++;

	if ( ptrtick >= ptrtickmax ) {
		Mouse.hide();
		if ( doshowbartime ) {
			showhideBar(doshowbar = false);
		}
		ptrtick = 0;
	}

	if ( resettick > 0 ) {
		if ( --resettick == 0 ) {
			startVideo();
		}
	}

	if ( isrunning && (ntick & 1) ) {
		var d;
		var ct;
		var bl;
		var bt;
		// bbar.progpb width is representative of progress bars,
		// and has been resized as necessary
		var bwid = bbar.progpb._width;

		// Hmm . . . ffserver sends broken timestamps, so correct
		// it with the non-zero time as subtrahend; when data has
		// good timestamps this will cause a few ms of inaccuracy,
		// but for display it should not be perceptible
		if ( initshowoffset == 0 && timediff == 0.0 && audb != true ) {
			if ( (timediff = stream.time) > 0.0 ) {
				adddbgtext(" T1st=='"+timediff+"'\n");
			}
		}

		if ( audb == true ) {
			d  = sound != null ? (audio_duration/adiv) : 1;
			ct = sound != null ? (sound.position/adiv) : 0;
			bl = sound != null ? sound.getBytesLoaded() : 0;
			bt = sound != null ? audio_bytelength : 1;
		} else {
			d  = stream_duration;
			ct = stream.time;
			bl = stream.bytesLoaded;
			bt = stream.bytesTotal;
		}

		// may happen when objects are invalid
		if ( isNaN(ct) ) { ct = 0 + timediff; }
		if ( isNaN(bl) ) { bl = 0; }

		ct -= timediff; // see comment '// Hmm . . . ' above
		// another oddity seen with rtmp streams: time does not strictly
		// increase, e.g. seconds like 08, 07, 08, 09, 08, 09 . . .
		// so correct that
		last_ct = ct = Math.max(last_ct, ct);

		if ( d == null || isNaN(d) ) {
			if ( b_use_stream_dummies ) {
				var w = bwid * (ct % dummy_duration) / dummy_duration;
				bbar.progpl._width = w;
			} else {
				//bbar.progpl._width = bwid / 2;
				bbar.progpl._width = 1;
			}
		} else {
			bbar.progpl._width = bwid * ct / d;
		}

		bbar.tmtxt.text = mktimtxt(ct);
		if ( timetot != "" ) {
			bbar.tmtxt.text += "/" + timetot;
		}

		if ( bt <= 0 || isNaN(bt) || bt == null ) {
			var d = Math.round(bl / 1024);
			if ( b_use_stream_dummies ) {
				var w = bwid * (bl%dummy_bytestotal) / dummy_bytestotal;
				bbar.progdl._width = w;
				bbar.dltxt.text = d > 0 ? "" + d+"k" : "|~~~~~~~~~~~~|";
			} else {
				//bbar.progdl._width = bwid / 2;
				bbar.progdl._width = 1;
				bbar.dltxt.text = bl ? "" + d + "k" : "";
			}
		} else {
			var p = n02(Math.round(100 * bl / bt));
			bbar.progdl._width = bwid * bl / bt;
			bbar.dltxt.text = p + "% " + dl_tot;
		}
	}

	if ( bbar.yshowpos > bbar._y ) {
		bbar._y = Math.min(bbar._y + barshowincr, bbar.yshowpos);
		// when bar is fully hidden also hide sound volume gadget
		if ( bbar.yshowpos == bbar._y ) {
			volgadget._visible = false;
		}
	} else if ( bbar.yshowpos < bbar._y ) {
		bbar._y = Math.max(bbar._y - barshowincr, bbar.yshowpos);
	}
}

///
/// initial execution: roughly equivalent to main()
///

// setup timer function
setInterval(this, "ticker", tickinterval);

// externals: loading, etc..
obj_css.onLoad = function(ok) {
	if ( ! ok ) {
		adddbgtext(" css load failed!\n");
		// see obj.css for notes on styles
		// textAlign:'center' has no effect
		obj_css.setStyle(".bodytxt", {
		    color:'#50FF50',
		    fontFamily:'Bookman,Times,serif',
		    fontSize:'18',
		    textAlign:'center',
		    display:'block'
		});
		obj_css.setStyle(".headtxt", {
		    color:'#FF5050',
		    fontFamily:'Bookman,Times,serif',
		    fontSize:'24',
		    fontWeight:'bold',
		    textAlign:'center',
		    display:'block'
		});
		obj_css.setStyle(".footer", {
		    color:'#5050FF',
		    fontFamily:'Helvetica,Arial,sans-serif',
		    fontSize:'21',
		    textAlign:'center',
		    display:'block'
		});
	}
	itxt.styleSheet = obj_css;
};
if ( _level0.ST != undefined && _level0.ST != '' ) {
	obj_css_url = _level0.ST;
}
obj_css.load(obj_css_url);

// default, has context menu item
video.smoothing = true;

// other setup
bbar.resize = bbar_resize;
bbar.yhome = bbar.yshowpos = bbar._y;
add_filter(bbar.stopbutdisable, new flash.filters.BlurFilter(3, 3, 3));
bbar.stopbutdisable.useHandCursor = false;
bbar.stopbutdisable.enabled = false;
bbar.stopbut.useHandCursor = true;
add_filter(bbar.pausebutdisable, new flash.filters.BlurFilter(4, 4, 4));
bbar.pausebutdisable.useHandCursor = false;
bbar.pausebutdisable.enabled = false;
bbar.pausebut.useHandCursor = true;
bbar.dosclbut._visible = ! doscale;
bbar.dosclbut.useHandCursor = true;
bbar.nosclbut._visible = doscale;
bbar.nosclbut.useHandCursor = true;
add_filter(bbar.nosclbutdisable, new flash.filters.BlurFilter(3, 3, 3));
bbar.nosclbutdisable.useHandCursor = false;
bbar.nosclbutdisable.enabled = false;
bbar.nosclbutdisable._visible = false;
bbar.fullscrbut._visible = checkFullScreen() ? false : true;
bbar.windscrbut._visible = ! bbar.fullscrbut._visible;
bbar.fscrbut.useHandCursor = true;
bbar.spkrbut.useHandCursor = true;
// mousewheel works for MovieClip, but not button
bbar.spkrmsw.onMouseWheel = volbar_onMouseWheel;
bbar.spkrmsw.mouseWheelEnabled = true;
itxt._visible = false;
itxt.tabEnabled = false;
itxt.autoSize = false;
itxt.background = false;
volgadget._visible = false;
// mousewheel works for MovieClip, but not button
volgadget.vbarmsw.onMouseWheel = volbar_onMouseWheel;
volgadget.vbarmsw.mouseWheelEnabled = true;
volgadget.vbarbut.mousedown = false;
volgadget.vbarbut.onPress     = obj_onMouseDown;
volgadget.vbarbut.onRelease   = obj_onMouseUp;
volgadget.vbarbut.onReleaseOutside   = obj_onMouseUp;
volgadget.vbarbut.onMouseMove = volbar_onMouseMove;
// Gnash on Ubuntu 10.04, Debian 6.0 does not show hand cursor;
// lines below failed to force it.
bbar.ctlpanel.useHandCursor = false;
bbar.ppbut.useHandCursor = true;
bbar.progpb.useHandCursor = true;
bbar.progpb.tabEnabled = false;
// changed debug parent
if ( bbar.dbg == undefined ) {
	bbar.dbg = dbg;
}
bbar.dbg._visible = false;
bbar.dbg.backgroundColor = 0x1E1E20;
bbar.dbg.border = true;
bbar.dbg.tabEnabled = false;
bbar.tmtxt.type = "dynamic";
bbar.tmtxt.multiline = false;
bbar.tmtxt.selectable = false;
bbar.tmtxt.tabEnabled = false;
bbar.tmtxt._visible = false;
bbar.tmtxt.wordWrap = false;
bbar.tmtxt.autoSize = true;
bbar.dltxt.type = "dynamic";
bbar.dltxt.multiline = false;
bbar.dltxt.selectable = false;
bbar.dltxt.tabEnabled = false;
bbar.dltxt._visible = false;
bbar.dltxt.wordWrap = false;
bbar.dltxt.autoSize = true;

// without builtin video URL, get one -- this is the case
// with pre-built .swf, not on the fly PHP CGI
if ( (vurl == null || vurl == "") && _level0.FN != undefined ) {
	// param F2 is preferably provided as an escaped URL, because
	// A/S escape() used in urlesc(_level0.FN) is too simplistic
	// for some sites, e.g. archive dot org rejects escaped '_'
	if ( _level0.F2 != undefined ) {
		adddbgtext(" F2: '" + _level0.F2 + "'\n");
		vurl = _level0.F2;
	} else if ( ! (eurl == null || eurl == "") ) {
		vurl = eurl;
	} else {
		vurl = urlesc(_level0.FN);
	}
	adddbgtext(" FN: '" + _level0.FN + "'\n");
	adddbgtext(" HI: '" + _level0.HI + "'\n");
	adddbgtext(" WI: '" + _level0.WI + "'\n");
}
adddbgtext(" vurl: '" + vurl + "'\n");

// get initial image if url is provided
if ( (iimage == null || iimage == "") && _level0.II != undefined ) {
	iimage = urlesc(_level0.II);
	adddbgtext(" II: '" + _level0.II + "'\n");
}

// Misc. Gnash hacks
if ( flvers.indexOf("10,1,999,0") >= 0 ) {
	adddbgtext("Gnash bug hacks . . .\n");
	// Gnash pixelaspect ratio is a dummy, and inverted too ( < 1.0 )
	// and in version 0.8.10 some bug makes it give a value of 0.5....
	upixaspect = 1.0;
	// Gnash 0.8.10 gets confused by dimensions of main 'movie' and
	// sizes the stage wrong: try to correct that (BTW 0.8.8 is OK)
	// (possibly same bug: Gnash 0.8.10 standalone ignores commandline
	// width and height args)
	if ( _level0.GNSZH == undefined || _level0.GNSZH == true ) {
		// this nasty hack actually worked, Ubuntu 11.10 in
		// kvm virtual machine, display on Vinagre vnc client,
		// without any visible flashing; effect on fast display
		// currently unknown.
		toggleFullscreen();
		toggleFullscreen();
	}
}

adddbgtext("pixelAspectRatio " + System.capabilities.pixelAspectRatio + "\n");
adddbgtext("upixaspect " + upixaspect + "\n");

// is there a stream id (rtmp playpath)?
if ( v_id == null && _level0.IDV != undefined && _level0.IDV != '' ) {
	v_id = _level0.IDV;
}

// boolean rtmp: by protocol
// update: overload v_id: playpath for rtmp, or vid to accompany audio
//if ( (brtmp = check_rtmp(vurl)) == false ) {
	// not rtmp? don't want v_id
	//v_id = null;
//}
brtmp = check_rtmp(vurl);
adddbgtext(" v_id: '" + v_id + "'\n");

// is stream known to be audio only?
if ( ! brtmp && audb == null && _level0.AU != undefined ) {
	audb = _level0.AU == "true" ? true :
		(_level0.AU == "false" ? false : null);
	adddbgtext(" AU: '" + _level0.AU + "'\n");
}
// as above, and there was no _level0.AU
if ( audb == null || audb == false ) {
	audb = check4aud(vurl, false);
}
adddbgtext(" audb: '" + audb + "'\n");
// if audio, use v_id for accompanying video
if ( audb ) {
	v4aud = urlesc(v_id);
	adddbgtext(" v4aud: '" + v4aud + "'\n");
}

// initial interface and size setup
// mobile adjustments
if ( ua_is_mobile() ) {
	volgadget._rotation = 0;

	var offs;
	offs = 0.25;
	bbar.playbut._x = butwidth * offs;
	bbar.pausebut._x = butwidth * offs;
	bbar.pausebutdisable._x = butwidth * offs;
	offs += 1.75;
	bbar.stopbut._x = butwidth * offs;
	bbar.stopbutdisable._x = butwidth * offs;
	offs += 1.75;
	bbar.spkrbut._x = butwidth * offs;
	bbar.spkrmsw._x = butwidth * offs;
	
	// surprisingly difficult to get rid of buttons: some of
	// this ugly junk could be removed, time permitting, but
	// for now, shotgun.
	var e = ["onDragOut","onDragOver","onKeyUp","onKillFocus",
		"onPress","onRelease","onReleaseOutside","onRollOut",
		"onRollOver","onSetFocus"];
	var b = [bbar.dosclbut, bbar.nosclbut, bbar.nosclbutdisable,
		bbar.fullscrbut, bbar.windscrbut];
	offs = bbar.spkrbut._x + butwidth;
	for ( var i in b ) {
		var c = b[i];
		c._x = offs;
		c._xscale = 0;
		c._yscale = 0;
		for ( var j in e ) {
			c[e[j]] = null;
		}
		c._visible = false;
		c.enabled = false;
		delete c;
	}
} else {
	volgadget._rotation = -90;
}

if ( _level0.DB != undefined && _level0.DB != '' ) {
	disablebar = _level0.DB == 'true' ? true : false;
	adddbgtext(" DB: '" + _level0.DB + "'\n");
}
if ( disablebar == true ) {
	bbar.enabled = false;
	bbar._visible = false;
}

resizeFace();

// get client 'SharedObject' data if available
volclient = SharedObject.getLocal("volume");
if ( volclient.data.volume == undefined || disablebar ) {
	if ( _level0.VL != undefined && _level0.VL != '' ) {
		initvolume = Math.max(0, Math.min(100, 0 + parseInt(_level0.VL)));
		adddbgtext(" VL: '" + _level0.VL + "'\n");
	}
	if ( volclient == undefined ) {
		volclient = {};
		volclient.flush = function(){};
	}
	if ( volclient.data == undefined ) {
		volclient.data = {};
	}
	volclient.data.volume = initvolume;
}

if ( _level0.PL != undefined && _level0.PL != '' ) {
	dopause = _level0.PL == 'true' ? false : true;
	initpause = dopauseaud = dopause;
	adddbgtext(" PL: '" + _level0.PL + "'\n");
}

if ( _level0.HB != undefined && _level0.HB != '' ) {
	doshowbar = initshowbar = _level0.HB == 'true' ? false : true;
	menuhidebar.val = doshowbartime = ! initshowbar;
	adddbgtext(" HB: '" + _level0.HB + "'\n");
}

if ( _level0.LP != undefined && _level0.LP != '' ) {
	doloop = _level0.LP == 'true' ? true : false;
	adddbgtext(" LP: '" + _level0.LP + "'\n");
}

if ( _level0.AA != undefined && _level0.AA != '' ) {
	autoaspect = _level0.AA == 'true' ? true : false;
	adddbgtext(" AA: '" + _level0.AA + "'\n");
}

if ( _level0.DA != undefined && _level0.DA != '' ) {
	displayaspect = _level0.DA;
	// sadly, AS2 does not support RegExp(): always false
	// I've read AS3 has support; but that's no help here
	//if ( RegExp('^[1-9][0-9]*[x:][1-9][0-9]*$').exec(_level0.DA) )
	if ( _level0.DA != 'D' ) {
		var t = displayaspect.split(":");
		if ( t.length != 2 ) {
			t = displayaspect.split("x");
		}
		if ( t.length != 2 ) {
			displayaspect = 0;
		} else {
			displayaspect = 
				(0.0 + parseFloat(t[0])) / (0.0 + parseFloat(t[1]));
			displayaspect = Math.max(0, displayaspect);
			autoaspect = false;
		}
	}
	adddbgtext(" DA: '" + _level0.DA + "'\n");
}

if ( _level0.PA != undefined && _level0.PA != '' ) {
	pixelaspect = _level0.PA;
	if ( _level0.PA != 'S' ) {
		var t = pixelaspect.split(":");
		if ( t.length != 2 ) {
			t = pixelaspect.split("x");
		}
		if ( t.length != 2 ) {
			pixelaspect = 0;
		} else {
			pixelaspect = 
				(0.0 + parseFloat(t[0])) / (0.0 + parseFloat(t[1]));
			pixelaspect = Math.max(0, pixelaspect);
		}
	}
	adddbgtext(" PA: '" + _level0.PA + "'\n");
}

adddbgtext("Flash v. " + flvers + "\n");

// set up initial button, and image if url
// TODO: make image proportional vs. fitted an option
inibut.initialimg.ok = false;
inibut.resize = inibut_resize;
if ( brtmp || initpause ) {
	inibut.initialbut.useHandCursor = true;
	inibut._x = Stage.width / 2;
	inibut._y = Stage.height / 2;
	inibut.initialbut._visible = inibut.initialbut.enabled = true;
	inibut.initialimg._visible = inibut.initialimg.enabled = false;
	inibut._visible = inibut.enabled = true;
	if ( iimage ) {
		// initial image to display implies no load until start button
		initpause = dopauseaud = dopause = false;
		loadonload = false;
		t = {
			onLoadInit: function (m) {
				var bv = bbar._visible; // load corrupts bbar drawing
				bbar._visible = false;
				m.ok = true;
				inibut.resize();
				m._visible = m.enabled = true;
				bbar._visible = bv;
			}
		};
		inibut.initialimg.imld = new MovieClipLoader();
		inibut.initialimg.imld.addListener(t);
		inibut.initialimg.imld.loadClip(iimage, inibut.initialimg);
	}
} else {
	inibut.initialbut.enabled = inibut.initialimg.enabled = false;
	inibut.initialbut._visible = inibut.initialimg._visible = false;
	inibut.enabled = false;
	inibut._visible = false;
	inibut.initialimg = null;
	inibut = null;
	loadonload = true;
}

// start the movie, but . . .
// initial pause is handled elsewhere except for rtmp which does not
// handle pause well, so simulate initial pause here if requested
dopauseaud = dopause;
if ( ! (dopause && brtmp) && loadonload ) {
	startVideo();
} else {
	stopVideo();
	audcurtime = 0;
}

OMM;
// END main action script

?>
