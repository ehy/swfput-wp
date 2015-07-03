/*
 *      front.js
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

/**
 * HTML5 <video> controller, providing a control bar and associated
 * graphical elements (wait spinner, initial centered play icon),
 * including the ability to paint the video on a <canvas> element
 * thus allowing asjustment for user-set aspect ratio (used also
 * as a workaroung for browser bugs).
 * 
 * Also included here is re-sizer code, which is works with parent
 * <div> and so can work with other contents; primarily flash
 * plugin video player of the SWFPut WordPress plugin for/with
 * which this was developed.
 * 
 * Added evhh5v v. 1.0.7, 2014/01/26
 * (C) Ed Hynan 2014
 */


/*
 * Notes on implementation:
 * Code herein is straight {ECMA,Java}script, working directly
 * with the DOM.
 * 
 * There are no libraries used.  It is intended that this code
 * might be reused in whole or in part in an arbitrary context,
 * including possible constraints on acceptable libraries.
 * 
 */


/**********************************************************************\
 *                                                                    *
 * Procedure to create the <object> and <param> elements that support *
 * the video controller and control bar, serving two related goals:   *
 * 1. the HTML5 video should remain useful where JavaScript is disa-  *
 *    bled, in which case the browser-native interface should work    *
 * 2. eliminates much of the page markup that would be necessary, and *
 *    and useless without JavaScript (with whatever side-effects).    *
 *                                                                    *
\**********************************************************************/

// build and add to DOM the elements needed for the svg control bar
// of the html5 video player; arg is a map[*] of map,
// ready for literal use. [* map as in assoc. array]
function evhh5v_controlbar_elements(parms, fixups) {
	// <video> we're associated with must be OK, or it's all pointless
	var vidobj = evhh5v_controlbar_elements_check(parms, false);
	if ( ! vidobj ) {
		return;
	}

	var ip = parms["iparm"];
	var num = ip["uniq"];
	var ivid = ip["vidid"];
	// additional parameters
	var op = parms["oparm"];

	// left on its own a preload attribute (other than none)
	// will not behave as expected due to timing and event sequence
	// reordering caused by our diddling -- set it to none and
	// let it be handled elswhere
	if ( op["std"]["preload"] !== "none" ) {
		vidobj.setAttribute("preload", "none");
	}

	// <video> is given a controls attribute for browsers
	// with JS disabled; but, since we're executing JS and
	// building a control bar, remove that attribute. With
	// luck, it will not even have been visible (It can be
	// on slow machines, that's something that must be accepted).
	vidobj.removeAttribute("controls");

	// defaults for object parameters
	var pdefs = {
		// parentdiv and auxdiv args *must* be given; they
		// are existing elements referred to here
		"parentdiv" : ip["parentdiv"], "auxdiv" : ip["auxdiv"],
		// these args can be provided defaults
		"id" : ip["id"] ? ip["id"] : "evhh5v_ctlbar_svg_" + num,
		"ctlbardiv" : ip["bardivid"] ? ip["bardivid"] : "evhh5v_ctlbar_div_" + num,
		"parent" : ip["barobjid"] ? ip["barobjid"] : "evhh5v_ctlbar_obj_" + num,
		"role" : ip["role"] ? ip["role"] : "bar"
	};

	if ( ! op["uniq"] ) {
		op["uniq"] = {};
	}
	for ( var k in pdefs ) {
		if ( k in op["uniq"] )
			continue;
		op["uniq"][k] = pdefs[k];
	}

	var url = ip["barurl"]; // also, a query is needed for MSIE
	var pdiv = op["uniq"]["parentdiv"];
	var adiv = op["uniq"]["auxdiv"];

	var bardiv = document.createElement('div');
	bardiv.setAttribute('id', op["uniq"]["ctlbardiv"]);
	bardiv.setAttribute('class', ip["divclass"]);
	bardiv.style.width = "" + ip["width"] + "px";

	var barobj = document.createElement('object');
	barobj.setAttribute('id', op["uniq"]["parent"]);
	barobj.setAttribute('class', ip["divclass"]);

	var p, v, sep, q = "";

	sep = "?";
	for ( var i in op ) {
		for ( var k in op[i] ) {
			v = "" + op[i][k];
			q += sep + k + "=" + v; // MSIE
			p = document.createElement('param');
			p.setAttribute('name', k);
			p.setAttribute('value', v);
			barobj.appendChild(p);

			sep = "&";
		}
	}

	barobj.style.width = "" + ip["width"] + "px";
	barobj.style.height = "" + ip["barheight"] + "px";
	barobj.setAttribute("onload", "evhh5v_ctlbarload(this, '"+pdiv+"'); false;");
	barobj.setAttribute('type', "image/svg+xml");
	barobj.setAttribute("data", url + (evhh5v_need_svg_query() ? "" : q));

	p = document.createElement('p');
	p.innerHTML = ip["altmsg"];
	barobj.appendChild(p);

	bardiv.appendChild(barobj);
	var alldiv = document.getElementById(adiv);

	// main bar svg done, now for the additional elements:

	// initial button
	url = ip["buturl"];
	var butdiv;

	butdiv = document.createElement('div');
	butdiv.setAttribute('id', "b_" + op["uniq"]["ctlbardiv"]);
	butdiv.setAttribute('class', ip["divclass"]);

	barobj = document.createElement('object');
	barobj.setAttribute('id', "b_" + op["uniq"]["parent"]);
	barobj.setAttribute('class', ip["divclass"]);

	q = "?" + "parentdiv=" + pdiv; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "parentdiv");
	p.setAttribute('value', pdiv);
	barobj.appendChild(p);
	q += "&" + "parent=" + "b_" + op["uniq"]["parent"]; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "parent");
	p.setAttribute('value', "b_" + op["uniq"]["parent"]);
	barobj.appendChild(p);
	q += "&" + "ctlbardiv=" + "b_" + op["uniq"]["ctlbardiv"]; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "ctlbardiv");
	p.setAttribute('value', "b_" + op["uniq"]["ctlbardiv"]);
	barobj.appendChild(p);
	q += "&" + "role=1st"; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "role");
	p.setAttribute('value', "1st");
	barobj.appendChild(p);

	barobj.setAttribute("onload", "evhh5v_ctlbutload(this, '"+pdiv+"'); false;");
	barobj.setAttribute('type', "image/svg+xml");
	barobj.setAttribute("data", url + (evhh5v_need_svg_query() ? "" : q));

	butdiv.appendChild(barobj);

	// volume slide control
	url = ip["volurl"];
	var voldiv;

	voldiv = document.createElement('div');
	voldiv.setAttribute('id', "v_" + op["uniq"]["ctlbardiv"]);
	voldiv.setAttribute('class', ip["divclass"]);

	barobj = document.createElement('object');
	barobj.setAttribute('id', "v_" + op["uniq"]["parent"]);
	barobj.setAttribute('class', ip["divclass"]);

	q = "?" + "parentdiv=" + pdiv; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "parentdiv");
	p.setAttribute('value', pdiv);
	barobj.appendChild(p);
	q += "&" + "parent=" + "v_" + op["uniq"]["parent"]; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "parent");
	p.setAttribute('value', "v_" + op["uniq"]["parent"]);
	barobj.appendChild(p);
	q += "&" + "ctlbardiv=" + "v_" + op["uniq"]["ctlbardiv"]; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "ctlbardiv");
	p.setAttribute('value', "v_" + op["uniq"]["ctlbardiv"]);
	barobj.appendChild(p);
	q += "&" + "role=vol"; // MSIE
	p = document.createElement('param');
	p.setAttribute('name', "role");
	p.setAttribute('value', "vol");
	barobj.appendChild(p);

	barobj.setAttribute("onload", "evhh5v_ctlvolload(this, '"+pdiv+"'); false;");
	barobj.setAttribute('type', "image/svg+xml");
	barobj.setAttribute("data", url + (evhh5v_need_svg_query() ? "" : q));

	voldiv.appendChild(barobj);

	// vol slider is appended before bar, leaving bar above so
	// it gets mouse events first -- an issue when vol ctl is scaled
	// small, when it still interferes with button events even
	// though it visually does not cover the bar -- but not if
	// the bar div is above
	alldiv.appendChild(voldiv);
	// the bar div should be below the button/wait div, and
	// above the volume slider div
	alldiv.appendChild(bardiv);
	// button/wait-spinner is appended, ultimately topmost
	alldiv.appendChild(butdiv);

	// finally, optional fixups; comment at definition
	if ( fixups !== undefined && fixups == true ) {
		evhh5v_fixup_elements(parms);
	}
};

// pre-check of video object; check types for at least
// "maybe" can play; if none found, but found a fallback
// <object> with id matching flash id in parms, then swap
// flash into parent position
// return false if altogether N.G. or swapped with flash,
// <video> object node elsewise
function evhh5v_controlbar_elements_check(parms, vidobj) {
	if ( ! vidobj ) {
		vidobj = document.getElementById(parms["iparm"]["vidid"]);
	}
	if ( ! vidobj ) {
		return false;
	}

	// flash fallback; might need this
	var swfobj = false;

	// sources
	var ss = [];
	ss.push(vidobj); // useful in "can play checks" below (src attr)
	for ( var i = 0; i < vidobj.childNodes.length; i++ ) {
		var t = vidobj.childNodes.item(i);
		var nnlc = t.nodeName.toLowerCase();

		if ( nnlc == 'object' ) {
			if ( parms.flashid !== undefined && parms.flashid === t.id ) {
				swfobj = evhh5v_get_flashsupport() ? t : false;
			}
			continue;
		}

		if ( nnlc == 'source' ) {
			ss.push(t);
		}
	}
	
	// in type checks collect source that might be OK for flash
	var sfs = [];
	// do can play type checks
	var maybe = 0, probably = 0, notype = 0, nsource = 0;
	while ( ss.length ) {
		var add = false, sff = false;
		var o = ss.shift();
		var s = o.getAttribute('src');
		var t = o.getAttribute('type');

		if ( ! s || s.length < 1 ) {
			continue;
		}
		nsource++;

		if ( ! t || t.length < 1 ) {
			// infer type from suffix; the set tested is
			// obviously subject to revision (expansion)
			if ( s.match(/.*\.(mp4|m4v|mv4)[ \t]*$/i) ) {
				t = 'video/mp4';
				add = true;
				sff = s; sfs.push(sff);
			} else if ( s.match(/.*\.(og[gv]|vorbis)[ \t]*$/i) ) {
				t = 'video/ogg';
				add = true;
			} else if ( s.match(/.*\.(webm|wbm|vp[89])[ \t]*$/i) ) {
				t = 'video/webm';
				add = true;
			} else if ( s.match(/.*\.(flv)[ \t]*$/i) ) {
				sff = s; sfs.push(sff);
			}
		}

		if ( ! t || t.length < 1 ) {
			notype++;
			continue;
		}

		if ( ! sff && t.match(/.*video\/(mp4|flv).*/i) ) {
			sff = s; sfs.push(sff);
		}

		var can = vidobj.canPlayType(t);
		if ( can == 'probably' ) {
			probably++;
		} else if ( can == 'maybe' ) {
			maybe++;
		} else {
			add = false;
		}

		// by the spec changing a source at this point should be
		// ineffective, but whether it is ineffective in some
		// browser is unknown, and it is harmless; IAC, it can be
		// referred to in future, and in fact our controller stop()
		// function creates a new vid and copies attrs/children,
		// and it will be effective there.
		if ( add ) {
			o.setAttribute('type', t);
		}
	}

	// if we have even a 'maybe' then go ahead
	if ( probably > 0 || maybe > 0 ) {
		return vidobj;
	}

	// at this point there is no H5V source with a
	// chance we like: if a flash <object> was found
	// as a child (fallback content), then swap it into
	// parenthood
	if ( swfobj !== false ) {
		var aux = vidobj.parentNode; // aux div for h5 vid
		var par = aux.parentNode; // div wanted as parent
		vidobj.removeChild(swfobj);
		
		var ch = [];
		for ( var i = 0; i < swfobj.childNodes.length; i++ ) {
			ch.push(swfobj.childNodes.item(i));
		}
		while ( ch.length ) {
			var t = ch.shift();
			var nnlc = t.nodeName.toLowerCase();

			// only need to keep params
			if ( nnlc == 'param' ) {
				continue;
			}

			// e.g. fallback img; make child of video
			swfobj.removeChild(t);
			vidobj.appendChild(t);
		}

		par.replaceChild(swfobj, aux);
		swfobj.appendChild(aux);

		// if flash-suitable types were found above, pass them
		// to the flash player on window load for use at the
		// player's discretion
		if ( window.addEventListener )
		window.addEventListener('load', function(e) {
			var id = swfobj.id;
			try {
				if ( swfobj.get_ack(id) != id ) {
					evhh5v_msg('FAILED evhswf ack from "'+id+'"');
					return;
				}
				
				for ( var i = 0, mx = sfs.length; i < mx; i++ ) {
					var t = encodeURI(sfs[i]);
					swfobj.add_alt_url(t, true);
				}
			} catch ( ex ) {
				evhh5v_msg('EXCEPTION calling evhswf: "'+ex.message+'"');
			}
		}, false);

		return false;
	}
	
	// Now, there is no decent H5V source, and no flash object
	// as fallback, but is this video fallback for flash?
	if ( parms.flashid && evhh5v_get_flashsupport() ) {
		swfobj = vidobj.parentNode.parentNode; // parent of aux div
		if ( swfobj.nodeName.toLowerCase() === 'object'
			&& parms.flashid === swfobj.id ) {
			// yes, H5V is fallback, but flash is supported
			if ( window.addEventListener )
			window.addEventListener('load', function(e) {
				var id = swfobj.id;
				try {
					if ( swfobj.get_ack(id) != id ) {
						evhh5v_msg('FAILED evhswf ack from "'+id+'"');
						return;
					}
					
					for ( var i = 0, mx = sfs.length; i < mx; i++ ) {
						var t = encodeURI(sfs[i]);
						swfobj.add_alt_url(t, true);
					}
				} catch ( ex ) {
					evhh5v_msg('EXCEPTION calling evhswf: "'+ex.message+'"');
				}
			}, false);

			return false;
		}
	}

	// return determines whether controller and bar are built;
	// a source, even of unknown type, makes it worth a try
	return (nsource > 0) ? vidobj : false;
}


/**********************************************************************\
 *                                                                    *
 * evhh5v_sizer, and support code: works based on browser changes to  *
 * and enclosing <div>, as happens with 'responsive' CSS and elements *
 *                                                                    *
 * cannot rely on the browser to resize video along with the other    *
 * elements -- the flash plugin in particular is not resized without  *
 * this, and the HTML5 video controller is designed to work with this *
 * rather that hook size events (i.e. it defines width & height       *
 * getter/setter properties that this uses like any property).        *
 *                                                                    *
 * this is not tied th the video controller                           *
 *                                                                    *
\**********************************************************************/

// (ugly hack to get resize event: save _adj instances, see below)
var evhh5v_sizer_instances = [];
var evhh5v_sizer_event_relay = function (load) {
	for ( var i = 0; i < evhh5v_sizer_instances.length; i++ ) {
		if ( evhh5v_ctlbarmap != undefined && evhh5v_sizer_instances[i].ctlbar == undefined ) {
			var did = evhh5v_sizer_instances[i].d;
			if ( did ) {
				did = evhh5v_ctlbarmap[did.id];
				if ( did && did["loaded"] ) {
					evhh5v_sizer_instances[i].add_ctlbar(did);
				}
			}
		}
		if ( load ) {
			// This proves necessary on 1st load: effectively,
			// resize video objects once with values obtained in
			// ctor -- *not* looking for change in div size . . .
			evhh5v_sizer_instances[i].resize();
		}
		// . . . and when this call is made width and aspect are
		// re-obtained, and sizes are set again.
		evhh5v_sizer_instances[i].handle_resize();
	}
};
// Setup initial resize for both window and document -- with
// window only, the change might be visible in slow environments
// (like an emulator), while with document alone I'm not certain
// all loaded objects are really ready. Hopefully, a redundant
// resize at same dimensions will not be visible (it seems OK).
// Note that the visible resize using window only was seen with
// android (4.?) native browser in emulator.
(function() {
	if ( window.addEventListener ) {
		var sizer_event_time = 250,
		    tmo = false,
		    f = function (e) {
				tmo = tmo || setTimeout(function() {
					tmo = false;
					evhh5v_sizer_event_relay(e.type === "load");
				}, sizer_event_time);
			};
		document.addEventListener("load", f, true);
		window.addEventListener("load",   f, true);
		window.addEventListener("resize", f, true);
	} else {
		var onlddpre = document.onload;
		var onldwpre = window.onload;
		var onszwpre = window.onresize;
		document.onload = function () {
			if ( typeof evhh5v_video_onlddpre === 'function' ) {
				onlddpre();
			}
			evhh5v_sizer_event_relay(true);
		};
		window.onload = function () {
			if ( typeof evhh5v_video_onldwpre === 'function' ) {
				onldwpre();
			}
			evhh5v_sizer_event_relay(true);
		};
		window.onresize = function () {
			if ( typeof evhh5v_video_onszwpre === 'function' ) {
				onszwpre();
			}
			evhh5v_sizer_event_relay(false);
		};
	}
}()); // func

// resize adjust:
// the enclosing <div> is scaled, and so its width from
// 'computed style' is used to adjust video and image
//
// dv is enclosing <div>, ob is flash <object>, av is alt. <video>,
// ai is alt. <img> [all preceding refer to id attribute], and
// and these may be 0 or null if the 'bld' arg is not 0 or null,
// but an instance of evhh5v_builder defined above
var evhh5v_sizer = function(dv, ob, av, ai, bld) {
	this.ia_rat = 1;    // ratio of original user-set width / height
	this.hpad = 0;
	this.vpad = 0;
	this.wdiv = null;
	this.bld = null;
	this.inresize = 0;

	// this object works with the element with id passed in dv,
	// a <div> or possibly another element with the
	// properties we need -- if the object cannot be had from
	// its id, then this was constructed in error
	this.d = document.getElementById(dv);
	if ( ! this.d ) {
		return;
	}

	this.o    = document.getElementById(ob);
	this.va_o = document.getElementById(av);
	this.ia_o = document.getElementById(ai);

	this.get_pads();
	this.wdiv = this.d.offsetWidth;

	// proportional image sizing is the trickiest bit here:
	// we will need to use the ratio of the specified dimensions
	if ( this.ia_o && this.ia_o.width > 1 ) {
		this.ia_rat = this.ia_o.width / this.ia_o.height;
	}

	// need max-width or browser does not scale div
	if ( this.d.style == undefined ||
		 this.d.style.maxWidth == undefined ||
		 this.d.style.maxWidth == "none" ||
		 this.d.style.maxWidth == "" ) {
		this.d.style.maxWidth = "100%";
	}

	// (ugly hack to get resize event: save _adj instances)
	evhh5v_sizer_instances.push(this);
};
evhh5v_sizer.prototype = {
	// For H5 video using non-default control bar, this interacts
	// with a wrapper object rather than the <video> itself; and,
	// the object is helpfully constructed here, to keep the
	// onload handler simple. The arg is a control bar object,
	// which the video wrapper needs; onload gets the bar from
	// a map that is populated as each bar svg instance loads.
	// If this is not called, the video is adjusted directly; in
	// fact the rest of this code should not consider the difference,
	// and behavior should be the same, provided that the wrapper
	// works well.
	add_ctlbar : function (bar) {
		if ( this.va_o instanceof evhh5v_controller ) {
			return; // done
		}
		if ( ! bar ) {
			evhh5v_msg("BAD CTLBAR == " + bar);
			return;
		}
		this.ctlbar = bar;
		this.va_o = new evhh5v_controller(this.va_o, bar, 0);
		this.va_o.mk();
	},
	_style : function (el, sty) {
		return evhh5v_getstyle(el, sty);
	},
	get_pads : function (el) {
		var p = this._style(this.d, "padding-left") || 0;
		this.hpad  = parseInt(p);
		p = this._style(this.d, "paddin g-right") || 0;
		this.hpad += parseInt(p);

		p = this._style(this.d, "padding-top") || 0;
		this.vpad  = parseInt(p);
		p = this._style(this.d, "padding-bottom") || 0;
		this.vpad += parseInt(p);
	},
	handle_resize : function () {
		if ( ! this.d || this.inresize != 0 )
			return;

		var dv = this.d;
		var wo = this.wdiv;
		var wn = dv.offsetWidth;

		// no longer want to return here since _int_rsz
		// is changing padding -- leave block for temp. reference
		if ( false && wn == wo ) {
			return;
		}

		this.wdiv = wn;

		this.get_pads();

		this.resize();
	},
	_int_rsz : function (o) {
		var dv = this.d;
		if ( ! dv  ) {
			return;
		}

		var wd = this.wdiv;
		if ( ! wd  ) {
			return;
		}

		var np = 0;
		var ho = o.height;
		var wo = o.width;
		var r  = wo / ho;
		var vd = evhh5v_view_dims();
		var maxh = vd.height - 16; // arbitrary pad; seems good

		// give external code an opportunity to disable the
		// viewable area adjustment here; specifically, it
		// makes a mess in iframes with complex interactions
		// such as with TinyMCE within WordPress admin.
		try {
			if ( evhh5v_sizer_maxheight_off !== undefined
			    && evhh5v_sizer_maxheight_off ) {
				maxh = wd / r + 1;
			}
		} catch (ex) {}

		// the viewable area adjustment
		if ( (wd / r) > maxh ) {
			wd = Math.round(maxh * r);
		}

		wd = Math.min(wd, vd.width);
		np = Math.round(Math.max((this.wdiv - wd) / 2 - 0.5, 0));
		wo = wd;
		ho = Math.round(wo / r);

		o.height = ho;
		o.width  = wo;

		// in try block to suppress browser console nag
		try {
			if ( o.pixelHeight !== undefined ) {
				o.pixelHeight = ho;
				o.pixelWidth  = wo;
			}
		} catch ( ex ) {}

		np = "" + np + 'px';
		dv.style.paddingLeft  = np;
		dv.style.paddingRight = np;
	},
	_int_imgrsz : function (o) { // for img: display proportionally
		if ( o.complete !== undefined && ! o.complete ) {
			return;
		}

		if ( o.naturalWidth === undefined || o.naturalHeight === undefined ) {
			// member _swfo is added by *_bld object (above), which
			// ensures natural[WH]* are defined; if they're not,
			// the object "load" handler was not called yet
			if ( o._swfo === undefined ) {
				// lacking browser support: add these members
				o.naturalWidth = o.width;
				o.naturalHeight = o.height;
			} else {
				return; // waiting for load: see *_bld above
			}
		}

		if ( o._ratio_user !== undefined ) {
			this.ia_rat = o._ratio_user;
		}

		var wd = this.wdiv;
		if ( wd == null ) {
			return;
		}

		wd -= this.hpad;

		var rd = this.ia_rat;
		var ri = o.naturalWidth / o.naturalHeight;
		if ( rd > ri ) {
			o.height = Math.round(wd / rd);
			o.width = Math.round(o.height * ri);
		} else {
			o.width = wd;
			o.height = Math.round(wd / ri);
		}
	},
	resize : function () {
		if ( ! this.d ) {
			return;
		}

		this.inresize = 1;
		if ( this.o ) {
			this._int_rsz(this.o);
		}
		if ( this.va_o ) {
			this._int_rsz(this.va_o);
		}
		if ( this.ia_o ) {
			this._int_imgrsz(this.ia_o);
		}
		this.inresize = 0;
	}
};


/**********************************************************************\
 *                                                                    *
 * Support object for FullScreen mode -- this mode is not finalized   *
 * so browser prefixed symbols are tested and used -- proves reliable *
 * in the major browsers                                              *
 *                                                                    *
\**********************************************************************/

//
// map of symbols derived from code with copyright, MIT license,
// and URL as follows (in original C-style comment):
/*!
* screenfull
* v1.1.1 - 2013-11-20
* https://github.com/sindresorhus/screenfull.js
* (c) Sindre Sorhus; MIT License
*/
// (thanks for the work saved)
// the rest of this object is original (EH)
var evhh5v_fullscreen = {
	// public methods
	//
	// is the fullscreen interface defined?
	if_defined : function() {
		return (this.get_symset_key() !== false);
	},
	// is the fullscreen interface usable?
	capable : function() {
		try { return ! (! this.enabled()); }
		catch ( e ) { return false; }
	},

	// public methods corresponding to fullscreen api:
	// these throw on error
	//
	// ~= requestFullscreen; takes element or uses document
	request : function(elm) {
		var el = elm === undefined ? document : elm;
		el[this.map_val("request")]();
	},
	// ~= exitFullscreen
	exit : function() {
		document[this.map_val("exit")]();
	},
	// return element ~= fullscreenElement
	element : function() {
		return document[this.map_val("element")];
	},
	// return boolean ~= fullscreenEnabled
	enabled : function() {
		return document[this.map_val("enabled")];
	},
	// add handler for ~= 'fullscreenchange'
	handle_change : function(fun, elm) {
		return this.handle_evt("change_evt", fun, elm);
	},
	// add handler for ~= 'fullscreenerror'
	handle_error : function(fun, elm) {
		return this.handle_evt("error_evt", fun, elm);
	},

	// private, no moleste
	//
	// methods
	handle_evt : function(kevt, fun, elm) {
		var n = "on" + this.map_val(kevt);
		var el = elm === undefined ? document : elm;
		var pre = el[n];
		el[n] = fun;
		return pre;
	},
	map_val : function(key) {
		if ( ! (key in this.idxmap) ) this._throw("invalid key: "+key);
		return (this.set_throw())[this.idxmap[key]];
	},
	set_throw : function() {
		var sset = this.get_symset();
		if ( sset === false ) this._throw();
		return sset;
	},
	get_symset_key : function() {
		if ( this.symset_key == undefined ) {
			var key = false;
			for ( var k in this.syms ) {
				if ( this.syms[k][this.idxmap["exit"]] in document ) {
					key = k;
					break;
				}
			}
			this.symset_key = key;
		}
		return this.symset_key;
	},
	get_symset : function() {
		if ( this.symset == undefined ) {
			var key = this.get_symset_key();
			this.symset = key === false ? false : this.syms[key];
		}
		return this.symset;
	},
	_throw : function(str) {
		throw ReferenceError(str == undefined ? this.def_msg : str);
	},

	// members
	def_msg : "fullscreen mode is not available",
	idxmap : {
		"request" : 0, "exit" : 1, "element" : 2,
		"enabled" : 3, "change_evt" : 4, "error_evt" : 5
	},
	syms : {
		"spec" :
		[
			'requestFullscreen',
			'exitFullscreen',
			'fullscreenElement',
			'fullscreenEnabled',
			'fullscreenchange',
			'fullscreenerror'
		],
		"wk" : // new WebKit
		[
			'webkitRequestFullscreen',
			'webkitExitFullscreen',
			'webkitFullscreenElement',
			'webkitFullscreenEnabled',
			'webkitfullscreenchange',
			'webkitfullscreenerror'

		],
		"wkold" : // old WebKit (Safari 5.1)
		[
			'webkitRequestFullScreen',
			'webkitCancelFullScreen',
			'webkitCurrentFullScreenElement',
			'webkitCancelFullScreen',
			'webkitfullscreenchange',
			'webkitfullscreenerror'

		],
		"moz" :
		[
			'mozRequestFullScreen',
			'mozCancelFullScreen',
			'mozFullScreenElement',
			'mozFullScreenEnabled',
			'mozfullscreenchange',
			'mozfullscreenerror'
		],
		"ms" :
		[
			'msRequestFullscreen',
			'msExitFullscreen',
			'msFullscreenElement',
			'msFullscreenEnabled',
			'msfullscreenchange',
			'msfullscreenerror'
			/* camel case not working, maybe because on+ is used
			'MSFullscreenChange',
			'MSFullscreenError'
			*/
		]
	}
};

// simple helper using the above
function evhh5v_fullscreen_ok() {
	return evhh5v_fullscreen.if_defined();
}


/**********************************************************************\
 *                                                                    *
 * SVG-based control bar construction and maintencance object         *
 *                                                                    *
\**********************************************************************/

// build svg control bar for video object
// this class is matched to svg in ctl(bar|but|vol).svg
// also includes associated objects: initial
// play arrow, animated data-wait spinner, and
// volume control slider gadget -- these latter
// are based on separate svg files
//
// ARGS: params = object passed back to parent document from
// svg inline js, built from <object><param> values (allowing
// one svg to be reused)
var evhh5v_controlbar = function(params) {
	this.OK = false;
	this.parms = params;
	this.doc   = params.docu_svg; // the svg document is not the same
	this.svg   = params.root_svg;
	this.ns    = this.svg.getAttribute("xmlns");
	this.rszo  = []; // assigned objects subject to resize

	// clear background for initial button for clicks
	this.inibut_use_clearbg = true;

	// for handler for play progress bar click
	this.prog_pl_click_cb = [];
	// volume slider horizontal, or vertical?
	this.vol_horz = false;

	// hypotenuse: in Math, or not
	if ( Math.hypot ) {
		this.hypot = function(x, y) { return Math.hypot(x, y); };
	}

	this.wndlength_orig =
	this.wndlength = parseInt(params['barwidth']);
	this.wndheight = parseInt(params['barheight']);
	this.barheight = parseInt(params['barheight']);
	this.sclfact   = this.barheight / 100;
	this.barpadding = 0;  // pad at controlbar edges
	this.btnstrokewid = 1 * this.sclfact;
	this.btnhighltwid = 1 * this.sclfact;
	this.strokewidthfact = 0.05; // * this.sclfact;
	this.var_init();
	this.mk();
};
evhh5v_controlbar.prototype = {

// helper for placing properties on the prototype; k must be a string
proto_set : function(k, v) {
	evhh5v_controlbar.prototype[k] = v;
},

// properties with parameters for
// the animated data transfer waiting
// ('buffering') spinner movie ("movie" as used in flash):

// wait movie radius
wrad : 40,
// wait movie number of elements
wnparts : 9,
// wait movie frames
wnfrms : 9,
// wait frames per second
wnfps : 12,
// initial play button stroke width -- well, not meant for the
// wait spinner but let it comingle here; we are not separatists
init_stroke : 9,

// Utility and math functions -- note that some comments reveal
// the code's origin, which is PHP code using the Ming swf building
// extension.

// use as epsilon:
fepsilon : 0.0001,

// equilateral triangle height from base and vice versa
// using ratio constants, calculated with Unix bc (scale = 20)
treq_r_bh : 1.15470053837925152901,
treq_r_hb : 0.86602540378443864676,
// in equi tri with base on x, this is the ratio of the triangle's
// center point y ordinate to the base, so an equi tri in quad 1 w/
// lower left vert at 0,0 has center point (base/2),(base*treq_mid_y)
// this == sqrt(sqr(tan(deg2rad(30))) - 0.25)
treq_mid_y : 0.28867513459481,

// equilateral triangle height from base
treqheight : function(base) {
	return base * this.treq_r_hb;
},
// equilateral triangle base from height
treqbase : function(height) {
	return height * this.treq_r_bh;
},
// angle unit conversion
pi_hemi : Math.PI / 180.0,
deg2rad : function(a) {
	return a * this.pi_hemi;
},
rad2deg : function(a) {
	return a / this.pi_hemi;
},
// because a browser's Math might not have hypot(); this.ctor may
// change this if hypot() is found.
hypot : function(x, y) { return Math.sqrt(x * x + y * y); },

/**
 *  Find length of line with endpoints $x0,$y0 and $x1,$y1
 */
line_length : function(x0, y0, x1, y1) {
	var dx = Math.abs(x1 - x0); var dy = Math.abs(y1 - y0);
	if ( dx < this.fepsilon ) { return dy; }
	if ( dy < this.fepsilon ) { return dx; }
	return this.hypot(dx, dy);
},

/**
 *  Rotate an array of points &$pts in the form:
 *  array(0 => array(0 => x, 1 => y), 1 => array(0 => x, 1 => y), ...)
 *  by $angle radians (clockwise) around center $ctrX, $ctrY
 *  Returns $pts
 */
points_rotate : function(pts, angle, ctrX, ctrY) {
	for ( var i = 0; i < pts.length; i++ ) {
		var x = pts[i][0] - ctrX; var y = pts[i][1] - ctrY;

		var flip = y < 0.0 ? true : false;
		if ( flip ) {
			x = -x; y = -y;
		}

		var r = this.line_length(x, y, 0.0, 0.0);
		if ( r < this.fepsilon ) {
			continue;
		}

		var a = Math.acos(x / r) + angle;
		x = Math.cos(a) * r;
		y = Math.sin(a) * r;
		if ( flip ) { x = -x; y = -y; }

		pts[i][0] = x + ctrX; pts[i][1] = y + ctrY;
	}

	return pts;
},

/**
 *  From an array of points $pts in the form:
 *  array(0 => array(0 => x, 1 => y), 1 => array(0 => x, 1 => y), ...)
 *  with first being starting control point and last two points
 *  being the same as first two points, draw cubic spline
 *  on shape &$obj from array $pts
 *  Array count should be a multiple of 3, + 1.
 */
svg_cubic :function(pts) {
	var x  = pts[0][0];
	var y  = pts[0][1];
	var r = "M " + x + " " + y;

	for ( var n = 1; n < pts.length - 3; n += 3 ) {
		var x0 = pts[n][0];
		var y0 = pts[n][1];
		var x1 = pts[n+1][0];
		var y1 = pts[n+1][1];
		var x2 = pts[n+2][0];
		var y2 = pts[n+2][1];
		r += " C " + x0 + " " + y0
			+ " " + x1 + " " + y1 + " " + x2 + " " + y2;
	}

	return r;
},
svg_drawcubic :function(obj, pts) {
	var p = this.svg_cubic(pts);
	obj.setAttribute("d", p + " Z");
	return obj;
},

/**
 *  From an array of points $pts in the form:
 *  array(0 => array(0 => x, 1 => y), 1 => array(0 => x, 1 => y), ...)
 *  each locating a vertice of a polygon, with the first and last
 *  points being the same and closing the figure, draw the figure on
 *  a Ming library shape object &$obj
 *  Returns $obj
 */
svg_poly : function(pts) {
	var x = pts[0][0];
	var y = pts[0][1];
	var r = "M " + x + " " + y;

	for ( var n = 1; n < pts.length; n++ ) {
		x = pts[n][0];
		y = pts[n][1];
		r += " L " + x + " " + y;
	}

	return r;
},
svg_drawpoly : function(obj, pts) {
	var p = this.svg_poly(pts);
	obj.setAttribute("d", p + " Z");
	return obj;
},

/**
 *  Draw equilateral triangle on a Ming library shape object
 *  From top-left $originX,$originY triangle will be centered in
 *  square $height,$height; i.e. will descend from y==$originY
 *  to y==$originY+$height, and x center of base will be at
 *  x==$originX+($height/2)
 *  Rotation by $angle radians will be centered on that square
 */
svg_treq_points : function(originX, originY, height, angle) {
	var h2 = height / 2.0;
	var base = this.treqbase(height);
	var b2 = base / 2.0;
	var boff = (height - base) / 2.0;

	var pts = [
		[(originX + boff)       , (originY + height)],
		[(originX + boff + base), (originY + height)],
		[(originX + boff + b2)  , (originY)         ],
		[(originX + boff)       , (originY + height)]
	];

	if ( angle ) {
		pts = this.points_rotate(pts, angle, originX + h2, originY + h2);
	}

	return pts.slice(0);
},
svg_treq : function(originX, originY, height, angle) {
	return this.svg_poly(this.svg_treq_points(originX, originY, height, angle));	
},
svg_drawtreq : function(obj, originX, originY, height, angle) {
	var p = this.svg_treq(originX, originY, height, angle);
	obj.setAttribute("d", p + " Z");
	return obj;
},

/**
 *  Draw equilateral triangle on a Ming library shape object
 *  with the triangle's center point at $originX,$originY
 *  Rotation by $angle radians will be centered on that point
 */
svg_treq2_points : function(originX, originY, height, angle) {
	var base = this.treqbase(height);
	var b2 = base / 2.0;
	var x0 = -b2;
	var y0 = base * this.treq_mid_y;
	var xoff = x0 + originX;
	var yoff = y0 + originY;

	var pts = [
		[xoff       , yoff         ],
		[xoff + base, yoff         ],
		[xoff + b2  , yoff - height],
		[xoff       , yoff         ]
	];

	if ( angle ) {
		pts = this.points_rotate(pts, angle, originX, originY);
	}

	return pts.slice(0);
},
svg_treq2 : function(originX, originY, height, angle) {
	return this.svg_poly(this.svg_treq2_points(originX, originY, height, angle));
},
svg_drawtreq2 : function(obj, originX, originY, height, angle) {
	var p = this.svg_treq2(originX, originY, height, angle);
	obj.setAttribute("d", p + " Z");
	return obj;
},

/**
 *  Draw rectangle on a Ming library shape object
 *  $originX, $originY set the top-left corner
 *  If $angle is not 0, rectangle is rotated on its center
 *  clockwise $angle radians
 */
svg_rect_points : function(originX, originY, wi, hi, angle) {
	var pts = [
		[originX       , originY       ],
		[(originX + wi), originY       ],
		[(originX + wi), (originY + hi)],
		[originX       , (originY + hi)],
		[originX       , originY       ]
	];

	if ( angle ) {
		var xo = originX + wi / 2.0;
		var yo = originY + hi / 2.0;

		pts = this.points_rotate(pts, angle, xo, yo);
	}

	return pts.slice(0);
},
svg_rect : function(originX, originY, wi, hi, angle) {
	return this.svg_poly(this.svg_rect_points(originX, originY, wi, hi, angle));
},
svg_drawrect : function(obj, originX, originY, wi, hi, angle) {
	var p = this.svg_rect(originX, originY, wi, hi, angle);
	obj.setAttribute("d", p + " Z");
	return obj;
},

// make svg objects
mk_button : function(clss, id, x, y, w, h, docu) {
	var doc = docu == undefined ? this.doc : docu;
	var ob = doc.createElementNS(this.ns, 'svg');
	ob.setAttribute("class", clss);
	ob.setAttribute("id", id);
	ob.setAttribute("x", x);
	ob.setAttribute("y", y);
	ob.setAttribute("width", w);
	ob.setAttribute("height", h);
	return ob;
},

mk_rect : function(clss, id, x, y, w, h, docu) {
	var doc = docu == undefined ? this.doc : docu;
	var ob = doc.createElementNS(this.ns, 'rect');
	ob.setAttribute("class", clss);
	ob.setAttribute("id", id);
	ob.setAttribute("x", x);
	ob.setAttribute("y", y);
	ob.setAttribute("width", w);
	ob.setAttribute("height", h);
	return ob;
},

mk_circle : function(clss, id, x, y, r, docu) {
	var doc = docu == undefined ? this.doc : docu;
	var ob = doc.createElementNS(this.ns, 'circle');
	ob.setAttribute("class", clss);
	ob.setAttribute("id", id);
	ob.setAttribute("cx", x);
	ob.setAttribute("cy", y);
	ob.setAttribute("r", r);
	return ob;
},

mk_ico : function(clss, id, x, y, w, h, docu) {
	var doc = docu == undefined ? this.doc : docu;
	var ob = doc.createElementNS(this.ns, 'path');
	ob.setAttribute("class", clss);
	ob.setAttribute("id", id);
	ob.setAttribute("x", x);
	ob.setAttribute("y", y);
	ob.setAttribute("width", w);
	ob.setAttribute("height", h);
	return ob;
},

put_rszo : function(o) {
	this.rszo.push(o);
},

mk_prog_pl : function(parentobj) {
	var barlength = this.barlength;
	var barheight = this.barheight;
	var progressbarheight = this.progressbarheight;
	var progressbaroffs   = this.progressbaroffs  ;
	var progressbarlength = this.progressbarlength;
	var progressbarxoffs  = this.progressbarxoffs ;
	
	var tx = progressbarxoffs;
	var ty = progressbaroffs;
	//var ty = barheight - (progressbarheight + progressbaroffs);

	var that = this;
	var dlclk = function(e) { that.prog_pl_click(e); };
	var bg = this.mk_rect("progseekbg", "prog_seekbg",
		tx, ty, progressbarlength, progressbarheight);
	parentobj.appendChild(bg);
	bg.addEventListener("click", dlclk, false);
	this.put_rszo(bg);
	var fg = this.mk_rect("progseekfg", "prog_seekfg",
		tx, ty, progressbarlength, progressbarheight);
	parentobj.appendChild(fg);
	fg.addEventListener("click", dlclk, false);
	this.put_rszo(fg);
	
	return [bg, fg];
},

mk_prog_dl : function(parentobj) {
	var barlength = this.barlength;
	var barheight = this.barheight;
	var progressbarheight = this.progressbarheight;
	var progressbaroffs   = this.progressbaroffs  ;
	var progressbarlength = this.progressbarlength;
	var progressbarxoffs  = this.progressbarxoffs ;
	
	var tx = progressbarxoffs;
	//var ty = progressbaroffs;
	var ty = barheight - (progressbarheight + progressbaroffs);

	var bg = this.mk_rect("progloadbg", "prog_loadbg",
		tx, ty, progressbarlength, progressbarheight);
	parentobj.appendChild(bg);
	this.put_rszo(bg);
	var fg = this.mk_rect("progloadfg", "prog_loadfg",
		tx, ty, progressbarlength, progressbarheight);
	parentobj.appendChild(fg);
	this.put_rszo(fg);
	
	return [bg, fg];
},

mk_bgrect : function(parentobj) {
	var barlength = this.barlength;
	var barheight = this.barheight;
	
	var bg = this.mk_rect("bgrect", "bgrect", 0, 0, barlength, barheight);
	bg.setAttribute("onclick", "svg_click(this);");
	parentobj.appendChild(bg);
	this.put_rszo(bg);
	
	return bg;
},

mk_cna : function () {
	var butwidth = this.butwidth;
	var butheight = this.butheight;
	var sw = butwidth * this.strokewidthfact; // for stroke width

	// make 'corner' arrays for scale/fullsreen button icons
	// offset into button (assumed circular)
	var t = 0.70710678; // sin||cos 45deg
	var cx = butwidth  / 2.0 - butwidth  / 2.0 * t + sw;
	var cy = butheight / 2.0 - butheight / 2.0 * t + sw;
	// side length . . .
	this.cnside = ((butwidth  + butheight) / 2.0) / 4.0 * 1.00 + 0.0;
	this.cnaout = [
			[0+cx           , 0+cy	  ],
			[this.cnside+cx , 0+cy     ],
			[0+cx           , this.cnside+cy],
			[0+cx           , 0+cy      ]
			];
	
	// make upside down for indicating opposite effect
	var cnhyp = this.hypot(this.cnside, this.cnside);
	var cnhyp2 = cnhyp / 2.0;
	var cnhi = Math.sqrt(this.cnside * this.cnside - cnhyp2 * cnhyp2);
	var cnhi2 = cnhi / 2.0;
	var cnoff = Math.sqrt(cnhi2 * cnhi2 / 2.0);
	cx -= cnoff;
	cy -= cnoff;
	this.cnain = [
			[this.cnside+cx, 0+cy           ],
			[this.cnside+cx, this.cnside+cy],
			[0+cx           , this.cnside+cy],
			[this.cnside+cx, 0+cy           ]
			];
},

mk_volume : function(parentobj, xoff) {
	var butwidth = this.butwidth;
	var butheight = this.butheight;
	var triangleheight = this.triangleheight;
	var stopheight = butheight / 2.0 - 0.5;
	var tx = butwidth * xoff;
	var ty = (this.barheight - butheight) / 2;
	var sw = butwidth * this.strokewidthfact; // for stroke width
	var r  = butwidth * 0.5;  // radius
	var rs = r - this.btnstrokewid;  // radius, for stroke
	var rh = r - this.btnhighltwid;  // radius, for highlight
	var cx, cy;

	// relay event handler
	var that = this;
	var hdl = function(e) {
		var t = this;
		return that.hdl_volctl(e, t);
	};

	var btn = this.mk_button("svgbutt", "volume",
		tx - sw / 2, ty - sw / 2, butwidth + sw, butheight + sw);
	btn.setAttribute("onclick", "svg_click(this);");
	btn.setAttribute("onmouseover", "setvisi('volume_highlight','visible');");
	btn.setAttribute("onmouseout", "setvisi('volume_highlight','hidden');");
	btn.addEventListener("wheel", hdl, false);
	var t = this.mk_circle("btn2", "volume_base", "50%", "50%", r);
	btn.appendChild(t);
	t = this.mk_circle("btnstroke", "volume_stroke", "50%", "50%", rs);
	btn.appendChild(t);
	btn.hlt = t = this.mk_circle("btnhighl", "volume_highlight", "50%", "50%", rh);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);

	// adjust dims for stroke padding
	butwidth += sw; butheight += sw;

	// shape for volume icon - out
	t = this.mk_ico("ico", "volumeico", 0, 0, butwidth, butheight);

	var mgdia = stopheight * 6.0 / 11.0;
	var u = triangleheight - this.trianglebase * this.treq_mid_y;
	cx = butwidth / 2.0 - u;
	cy = (butheight - mgdia) / 2.0;
	u = mgdia * 0.65;
	var s2 = this.svg_rect(cx, cy, u, mgdia, 0) + " Z";

	u = triangleheight;
	cx = butwidth  / 2.0;
	cy = butheight / 2.0;
	var s1 = this.svg_treq2(cx, cy, u, this.deg2rad(-90)) + " Z";
	s2 += " " + s1;

	t.setAttribute("d", s1);
	btn.appendChild(t);
	btn.ico = t;
	this.volumeico = t;

	t = this.mk_ico("ico", "volumeico2", 0, 0, butwidth, butheight);
	t.setAttribute("d", s2);
	btn.appendChild(t);
	btn.ico2 = t;
	this.volumeico2 = t;

	parentobj.appendChild(btn);
	
	return btn;
},

mk_fullscreen : function(parentobj, xoff) {
	var butwidth = this.butwidth;
	var butheight = this.butheight;
	var triangleheight = this.triangleheight;
	var tx = butwidth * xoff;
	var ty = (this.barheight - butheight) / 2;
	var sw = butwidth * this.strokewidthfact; // for stroke width
	var r  = butwidth * 0.5;  // radius
	var rs = r - this.btnstrokewid;  // radius, for stroke
	var rh = r - this.btnhighltwid;  // radius, for highlight

	if ( this.cnaout == undefined )
		this.mk_cna();

	var btn = this.mk_button("svgbutt", "fullscreen",
		tx - sw / 2, ty - sw / 2, butwidth + sw, butheight + sw);
	btn.setAttribute("onclick", "svg_click(this);");
	btn.setAttribute("onmouseover", "setvisi('fullscreen_highlight','visible');");
	btn.setAttribute("onmouseout", "setvisi('fullscreen_highlight','hidden');");
	var t = this.mk_circle("btn2", "fullscreen_base", "50%", "50%", r);
	btn.appendChild(t);
	t = this.mk_circle("btnstroke", "fullscreen_stroke", "50%", "50%", rs);
	btn.appendChild(t);
	btn.hlt = t = this.mk_circle("btnhighl", "fullscreen_highlight", "50%", "50%", rh);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);

	// for filter
	btn.disabfilter = this.disabfilter;

	// adjust dims for stroke padding
	butwidth += sw; butheight += sw;

	// shape for fullscreen icon - out
	t = this.mk_ico("ico", "fullscreenout", 0, 0, butwidth, butheight);

	var cx = butwidth  / 2.0;
	var cy = butheight / 2.0;
	var cna = this.cnaout;
	cna = this.points_rotate(cna, this.deg2rad(45), cx, cy);
	var ds = this.svg_poly(cna) + " Z";
	cna = this.points_rotate(cna, this.deg2rad(90), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	cna = this.cnaout;
	cna = this.points_rotate(cna, this.deg2rad(90), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	cna = this.cnaout;
	cna = this.points_rotate(cna, this.deg2rad(90), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	
	t.setAttribute("d", ds);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);
	btn.ico_out = t;
	this.fullscreenicoout = t;

	// shape for fullscreen icon - in
	t = this.mk_ico("ico", "fullscreenin", 0, 0, butwidth, butheight);

	cx = butwidth  / 2.0;
	cy = butheight / 2.0;
	cna = this.cnain;
	cna = this.points_rotate(cna, this.deg2rad(45), cx, cy);
	ds = this.svg_poly(cna) + " Z";
	cna = this.points_rotate(cna, this.deg2rad(90), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	cna = this.cnain;
	cna = this.points_rotate(cna, this.deg2rad(90), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	cna = this.cnain;
	cna = this.points_rotate(cna, this.deg2rad(90), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	
	t.setAttribute("d", ds);
	//t.setAttribute("visibility", "hidden");
	btn.appendChild(t);
	btn.ico_in = t;
	this.fullscreenicoin = t;

	parentobj.appendChild(btn);
	
	return btn;
},

mk_doscale : function(parentobj, xoff) {
	var butwidth = this.butwidth;
	var butheight = this.butheight;
	var triangleheight = this.triangleheight;
	var tx = butwidth * xoff;
	var ty = (this.barheight - butheight) / 2;
	var sw = butwidth * this.strokewidthfact; // for stroke width
	var r  = butwidth * 0.5;  // radius
	var rs = r - this.btnstrokewid;  // radius, for stroke
	var rh = r - this.btnhighltwid;  // radius, for highlight

	if ( this.cnaout == undefined )
		this.mk_cna();

	var btn = this.mk_button("svgbutt", "doscale",
		tx - sw / 2, ty - sw / 2, butwidth + sw, butheight + sw);
	btn.setAttribute("onclick", "svg_click(this);");
	btn.setAttribute("onmouseover", "setvisi('doscale_highlight','visible');");
	btn.setAttribute("onmouseout", "setvisi('doscale_highlight','hidden');");
	var t = this.mk_circle("btn2", "doscale_base", "50%", "50%", r);
	btn.appendChild(t);
	t = this.mk_circle("btnstroke", "doscale_stroke", "50%", "50%", rs);
	btn.appendChild(t);
	btn.hlt = t = this.mk_circle("btnhighl", "doscale_highlight", "50%", "50%", rh);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);

	// for filter
	btn.disabfilter = this.disabfilter;

	// adjust dims for stroke padding
	butwidth += sw; butheight += sw;

	// shape for doscale icon - out
	t = this.mk_ico("ico", "doscaleout", 0, 0, butwidth, butheight);

	var cx = butwidth  / 2.0;
	var cy = butheight / 2.0;
	var cna = this.cnaout;
	cna = this.points_rotate(cna, this.deg2rad(-45), cx, cy);
	var ds = this.svg_poly(cna) + " Z";
	cna = this.cnaout;
	cna = this.points_rotate(cna, this.deg2rad(180), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	var cnside = this.cnside;
	cx -= cnside / 2.0;
	cy -= cnside / 2.0;
	// inner rectangle
	var rs = this.svg_rect(cx, cy, cnside, cnside, 0);
	
	ds += " " + rs + " Z";
	t.setAttribute("d", ds);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);
	btn.ico_out = t;
	this.doscaleicoout = t;

	// shape for doscale icon - in
	t = this.mk_ico("ico", "doscalein", 0, 0, butwidth, butheight);

	cx = butwidth  / 2.0;
	cy = butheight / 2.0;
	cna = this.cnain;
	cna = this.points_rotate(cna, this.deg2rad(-45), cx, cy);
	ds = this.svg_poly(cna) + " Z";
	cna = this.cnain;
	cna = this.points_rotate(cna, this.deg2rad(180), cx, cy);
	ds += " " + this.svg_poly(cna) + " Z";
	
	ds += " " + rs + " Z";
	t.setAttribute("d", ds);
	//t.setAttribute("visibility", "hidden");
	btn.appendChild(t);
	btn.ico_in = t;
	this.doscaleicoin = t;

	parentobj.appendChild(btn);
	
	return btn;
},

mk_stop : function(parentobj, xoff) {
	var butwidth = this.butwidth;
	var butheight = this.butheight;
	var triangleheight = this.triangleheight;
	var tx = butwidth * xoff;
	var ty = (this.barheight - butheight) / 2;
	var sw = butwidth * this.strokewidthfact; // for stroke width
	var r  = butwidth * 0.5;  // radius
	var rs = r - this.btnstrokewid;  // radius, for stroke
	var rh = r - this.btnhighltwid;  // radius, for highlight
	
	var btn = this.mk_button("svgbutt", "stop",
		tx - sw / 2, ty - sw / 2, butwidth + sw, butheight + sw);
	btn.setAttribute("onclick", "svg_click(this);");
	btn.setAttribute("onmouseover", "setvisi('stop_highlight','visible');");
	btn.setAttribute("onmouseout", "setvisi('stop_highlight','hidden');");
	var t = this.mk_circle("btn2", "stop_base", "50%", "50%", r);
	btn.appendChild(t);
	t = this.mk_circle("btnstroke", "stop_stroke", "50%", "50%", rs);
	btn.appendChild(t);
	btn.hlt = t = this.mk_circle("btnhighl", "stop_highlight", "50%", "50%", rh);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);

	// for filter
	btn.disabfilter = this.disabfilter;

	// adjust dims for stroke padding
	butwidth += sw; butheight += sw;
	// shape for stop icon
	t = this.mk_ico("ico", "stopico", 0, 0, butwidth, butheight);

	var stopheight = butheight / 2.0 - 0.5; // -0.5 for button line width (?)
	var cx = (butwidth - stopheight) / 2.0;
	var cy = (butheight - stopheight) / 2.0;
	btn.ico = t = this.svg_drawrect(t, cx, cy, stopheight, stopheight);
	btn.appendChild(t);
	// save this, for visibility control
	this.stopico = t;

	parentobj.appendChild(btn);
	
	return btn;
},

// note: separate document, so it needs doc arg
mk_waitanim : function(parentobj, doc) {
	var wrad    = this.wrad;
	var wnparts = this.wnparts;
	var wnfrms  = this.wnfrms;
	var theight = 12.00 * 9 / wnparts;
	var wang = 360;
	var winc = wang / wnparts;
	var sidelen = wrad * 2.4;

	var sc = theight * 1.0;
	var xo = theight * 0.5;
	var yo = theight * -0.25;

	// curved arrow shaft, arrow head;
	// ultimately invariant data is put on prototype
	// Shaft (not the '70s song or movie)
	if ( this.arrow_shaft_data === undefined )
		this.proto_set("arrow_shaft_data", [
			[xo + 0.0573996 * sc, yo + 0.277178 * sc],
			[xo + 0.0606226 * sc, yo + 0.0199845 * sc],
			[xo + 0.57 * sc, yo + 0.03 * sc],
			[xo + 0.87 * sc, yo + 0.1 * sc],
			[xo + 1.16 * sc, yo + 0.21 * sc],
			[xo + 1.45417 * sc, yo + 0.437099 * sc],
			[xo + 1.27005 * sc, yo + 0.503488 * sc],
			[xo + 1.11376 * sc, yo + 0.462586 * sc],
			[xo + 1.1448 * sc, yo + 0.630027 * sc],
			[xo + 1.06325 * sc, yo + 0.863602 * sc],
			[xo + 0.878121 * sc, yo + 0.592868 * sc],
			[xo + 0.704932 * sc, yo + 0.416057 * sc],
			[xo + 0.447649 * sc, yo + 0.305126 * sc],
			[xo + 0.0573996 * sc, yo + 0.277178 * sc],
			[xo + 0.0606226 * sc, yo + 0.0199845 * sc]
		]);
	var pcub = this.arrow_shaft_data;
	// Head (for Backstage Pass)
	if ( this.arrow_head_data === undefined )
		this.proto_set("arrow_head_data", this.svg_treq_points(
			-theight / 2, -theight / 2, theight, this.deg2rad(-90)
		));
	var tpts = this.arrow_head_data;
	// the arrow suitable for svg path 'd'
	if ( this.arrow_svg_data === undefined )
		this.proto_set("arrow_svg_data", this.svg_poly(tpts) + " Z "
			+ this.svg_cubic(pcub) + " Z"
		);
	var data = this.arrow_svg_data;

	var btn = this.mk_button("svgbutt", "wait",
		0, 0, sidelen, sidelen, doc);
	btn.setAttribute("viewbox", "0 0 " + sidelen + " " + sidelen);
	btn.setAttribute("visibility", "hidden");
	var gmain = doc.createElementNS(this.ns, 'g');
	gmain.setAttribute("id", "waitgmain");
	gmain.setAttribute("transform", "translate(" + (sidelen / 2) + "," + (sidelen / 2) + ")");

	// object for spinner animation
	this.wait_anim_obj = {};
	var adat = this.wait_anim_obj;
	adat.transform_obj = btn;
	adat.transform_grp = gmain;
	adat.transform_idx = 0;
	adat.transform_max = wnfrms;
	adat.transform_deg = -360 / adat.transform_max;
	adat.transform_frm = [];
	for ( var i = 0; i < adat.transform_max; i++ ) {
		adat.transform_frm[i] = adat.transform_deg * i;
	}
	adat.transform_fps = this.wnfps;
	adat.is_running    = false;
	adat.timehandle    = false;
	adat.parent_obj    = this;
	if ( this.wait_anim_func === undefined )
		this.proto_set("wait_anim_func", function() {
			if ( this.is_running ) { // set bool to true before start
				if ( this.timehandle === false ) {
					var that = this;
					this.timehandle = setInterval(function() {
						that.anim_func();
					}, parseInt(1000 / this.transform_fps));
				} else {
					var o = this.transform_grp;
					var rv = this.transform_frm[this.transform_idx++];
					if ( this.transform_idx == this.transform_max )
						this.transform_idx = 0;
					if ( ! this.orig_trfm )
						this.orig_trfm = o.getAttribute("transform");
					o.setAttribute("transform",
						this.orig_trfm + " rotate("+rv+")");
				}
			} else { // set bool to false to stop
				if ( this.timehandle !== false ) {
					clearInterval(this.timehandle);
					this.timehandle = false;
				}
				this.transform_idx = 0;
				if ( this.orig_trfm ) {
					var o = this.transform_grp;
					o.setAttribute("transform", this.orig_trfm);
				}
			}
		});
	adat.anim_func = this.wait_anim_func;
	adat.start = function() { adat.is_running = true; adat.anim_func(); };
	adat.stop  = function() { adat.is_running = false; };

	// put the svg arrow elements
	for ( var i = 0; i < wnparts; i++ ) {
		var ad = winc * i;
		var a = this.deg2rad(ad);
		var ci = 1.0 - (ad / wang) + 0.2;
		var bl = parseInt(ci * 255), rg = parseInt((ci - 0.2) * 255);
		var clr = "rgb("+rg+","+rg+","+bl+")";
		var opa = "" + ci;

		sc = 1.0 - 0.5 * (ad / wang);

		// NOTE: Create group to be parent of the path element:
		// the translate transform was not taking effect on the
		// path, but it works on the 'g'; OTOH the scale and rotate
		// transforms are fine applied to the path. Go figguh.
		var g = doc.createElementNS(this.ns, 'g');
		var tr = -wrad;
		var ta = -a;
		g.setAttribute("transform",
			"translate(" + (tr * Math.sin(ta)) + ", " + (tr * Math.cos(ta)) + ")"
			);
		var p = doc.createElementNS(this.ns, 'path');
		p.setAttribute("style", "stroke:none;fill:"+clr+";opacity:"+opa);
		p.setAttribute("transform", 
			"scale(" + sc + ", " + sc + ") " +
			"rotate(" + ad + ") "
			);
		p.setAttribute("d", data);
		g.appendChild(p);
		gmain.appendChild(g);
	}

	btn.appendChild(gmain);

	this.wait_group = gmain;

	parentobj.appendChild(btn);
	return btn;
},

// note: separate document, so it needs doc arg
mk_inibut : function(parentobj, doc) {
	var r  = this.wrad;
	var sw = this.init_stroke;
	var butwidth  = (r + sw) * 2;
	var butheight = butwidth;
	var t;
	
	t = document.getElementById(this.b_parms["parent"]);
	t.style.width  = "" + butwidth  + "px";
	t.style.height = "" + butheight + "px";

	var btn = this.mk_button("svgbutt", "inibut",
		0, 0, butwidth, butheight, doc);
	btn.setAttribute("onclick", "svg_click(this);");
	if ( this.inibut_use_clearbg ) {
		// "ico_transbg" is very transparent, but visible,
		// "ico_clearbg" has 0 opacity, it's invisible
		var clss = false ? "ico_transbg" : "ico_clearbg";
		t = this.mk_circle(clss, "but_clearbg", "50%", "50%", r, doc);
		btn.appendChild(t);
		this.but_clearbg = t;
	}
	t = this.mk_circle("icoline", "but_circle", "50%", "50%", r, doc);
	btn.appendChild(t);
	this.but_circle = t;
	t = this.mk_ico("ico", "but_arrow", 0, 0, butwidth, butheight, doc);
	t = this.svg_drawtreq2(t, butwidth / 2.0, butheight/ 2.0, r, this.deg2rad(90));
	btn.appendChild(t);
	this.but_arrow = t;

	parentobj.appendChild(btn);
	return btn;
},

// note: separate document, so it needs doc arg
mk_volctl : function(parentobj, doc) {
	var t = this.butwidthfactor * parseInt(this.parms["barheight"]);
	var volbarlen = t * 4;
	var volbarwid = t / 2;
	var sw = volbarwid * 2.0;
	var len_all = volbarlen + sw;
	var horz = this.vol_horz;

	// event handlers; drag-like action
	var that = this;
	var hdlmd = function(e) {
		//var t = this;
		that.volctl_mousedown = 1;
		// (set|rel)Capture works beautifully in FFox, undef'd in webkit
		if ( e.target.setCapture !== undefined ) {
			e.target.setCapture();
		}
		return false;
	};
	var hdlmu = function(e) {
		if ( that.volctl_mousedown ) {
			var t = this;
			that.hdl_volctl(e, t);
		}
		// (set|rel)Capture works beautifully in FFox, undef'd in webkit
		if ( e.target.releaseCapture !== undefined ) {
			e.target.releaseCapture();
		}
		that.volctl_mousedown = 0;
		return false;
	};
	var hdlmm = function(e) {
		if ( that.volctl_mousedown ) {
			var t = this;
			that.hdl_volctl(e, t);
		}
		return false;
	};
	var hdlbg = function(e) {
		that.volctl_mousedown = 0;
	};
	var hdl   = function(e) {
		var t = this;
		that.hdl_volctl(e, t);
		return false;
	};

	// saving these values will simplify placement
	this.vol_width  = horz ? len_all : sw;
	this.vol_height = horz ? sw : len_all;

	t = document.getElementById(this.v_parms["parent"]);
	t.style.width  = "" + this.vol_width + "px";
	t.style.height = "" + this.vol_height + "px";

	var btn = this.mk_button("svgbutt", "volgadget",
		0, 0, this.vol_width, this.vol_height, doc);
	t = this.mk_ico("bgarea", "vol_bgarea",
		0, 0, this.vol_width, this.vol_height, doc);
	t.style.strokeWidth = sw;
	if ( horz ) {
		t = this.svg_drawpoly(t, [
			[volbarwid, volbarwid],
			[volbarlen+volbarwid, volbarwid],
			[volbarwid, volbarwid]
		]);
	} else {
		t = this.svg_drawpoly(t, [
			[volbarwid, volbarwid],
			[volbarwid, volbarlen+volbarwid],
			[volbarwid, volbarwid]
		]);
	}
	btn.appendChild(t);
	t.addEventListener("mouseover", hdlbg, false); // cancel mouse
	this.vol_bgarea = t;

	var bx = horz ? volbarwid : volbarwid / 2.0;
	var by = horz ? volbarwid / 2.0 : volbarwid;
	var bw = horz ? volbarlen : volbarwid;
	var bh = horz ? volbarwid : volbarlen;
	t = this.mk_rect("bgslide", "vol_bgslide", bx, by, bw, bh, doc);
	t.addEventListener("mousedown", hdlmd, false);
	t.addEventListener("mouseup", hdlmu, false);
	t.addEventListener("mousemove", hdlmm, false);
	t.addEventListener("wheel", hdl, false);
	// do not do "touchstart","touchend" -- whacks android browser(?) --
	// get by with "touchmove" alone
	t.addEventListener("touchmove", hdl, false);
	btn.appendChild(t);
	this.vol_bgslide = t;

	t = this.mk_rect("fgslide", "vol_fgslide", bx, by, bw, bh, doc);
	t.addEventListener("mousedown", hdlmd, false);
	t.addEventListener("mouseup", hdlmu, false);
	t.addEventListener("mousemove", hdlmm, false);
	t.addEventListener("wheel", hdl, false);
	t.addEventListener("touchmove", hdl, false);
	btn.appendChild(t);
	this.vol_fgslide = t;
	
	parentobj.appendChild(btn);
	return btn;
},

mk_playpause : function(parentobj, xoff) {
	var butwidth = this.butwidth;
	var butheight = this.butheight;
	var triangleheight = this.triangleheight;
	var tx = butwidth * xoff; var ty = (this.barheight - butheight) / 2;
	var sw = butwidth * this.strokewidthfact; // for stroke width
	var r  = butwidth * 0.5;          // radius
	var rs = r - this.btnstrokewid;  // radius, for stroke
	var rh = r - this.btnhighltwid;  // radius, for highlight
	
	var btn = this.mk_button("svgbutt", "playpause",
		tx - sw / 2, ty - sw / 2, butwidth + sw, butheight + sw);
	btn.setAttribute("onclick", "svg_click(this);");
	btn.setAttribute("onmouseover", "setvisi('playpause_highlight','visible');");
	btn.setAttribute("onmouseout", "setvisi('playpause_highlight','hidden');");
	var t = this.mk_circle("btn2", "playpause_base", "50%", "50%", r);
	btn.appendChild(t);
	t = this.mk_circle("btnstroke", "playpause_stroke", "50%", "50%", rs);
	btn.appendChild(t);
	t = this.mk_circle("btnhighl", "playpause_highlight", "50%", "50%", rh);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);

	// adjust dims for stroke padding
	butwidth += sw; butheight += sw;
	// shape for play/pause button icon in do play state
	t = this.mk_ico("ico", "playico", 0, 0, butwidth, butheight);
	t = this.svg_drawtreq2(t, butwidth / 2.0, butheight/ 2.0, triangleheight, this.deg2rad(90));
	//t.setAttribute("visibility", "hidden");
	btn.appendChild(t);
	// save this, for visibility control
	this.playico = t;

	// shape for play/pause button icon in do pause state
	var barwid = this.barwid;
	var barhigh = this.barhigh;
	t = this.mk_ico("icoline", "pauseico", 0, 0, butwidth, butheight);
	t.setAttribute("style", "stroke-width: " + barwid);
	t.setAttribute("d",
		"M " + (butwidth * 2 / 5 - (barwid / 2.0)) + " " + ((butheight - barhigh) / 2) +
		" l 0"+ " " + barhigh +
		" M " + (butwidth * 4 / 5 - (barwid / 2.0)) + " " + ((butheight - barhigh) / 2) +
		" l 0"+ " " + barhigh);
	t.setAttribute("visibility", "hidden");
	btn.appendChild(t);
	// save this, for visibility control
	this.pauseico = t;

	parentobj.appendChild(btn);
	
	return btn;
},

var_init : function() {
	var barsubtr = this.barpadding * 2;
	this.barlength = this.wndlength - barsubtr;
	this.butwidthfactor = 0.56;
	this.butwidth = Math.round(this.barheight * this.butwidthfactor) + 1;
	//butwidth += butwidth & 1; // make even
	this.butwidth |= 1; // make odd
	this.butheight = this.butwidth;
	this.triangleheight = this.butheight / 2.0;
	var t = Math.round(this.treqbase(this.triangleheight));

	// make odd or even to match butheight
	this.trianglebase = (Math.round(this.butheight) & 1) ? (t | 1) : ((t + 1) & ~1);
	this.triangleheight = this.treqheight(this.trianglebase);
	this.progressbarheight = (this.barheight - this.butheight) * 0.25;
	this.progressbaroffs =  ((this.barheight - this.butheight) * 0.20) / 2.0;
	this.progressbarlength = this.barlength - (this.progressbaroffs * 2);
	this.progressbarxoffs =  (this.barlength - this.progressbarlength) / 2.0;

	this.barwid = this.butwidth / 5; // - radius of endcaps
	this.barhigh = this.treqbase(this.triangleheight) - this.barwid;

	this.viewbox = "0 0 " + this.wndlength + " " + this.wndheight; // viewBox="0 0 w h" 
},

// for disbled button icons: refers to filter in svg <defs> block
disabfilter : 'url(#blur_dis)',

// event handler for play progress bar, which is used to seek
prog_pl_click : function(e) {
	if ( this.prog_pl_click_cb.length < 2 )
		return;

	// progressbarlength is broken currently, _pl_len is a temp.
	// workaround; see resize_bar
	//this.prog_pl_click_cb[0].call(this.prog_pl_click_cb[1], [e, this.progressbarlength]);
	this.prog_pl_click_cb[0].call(this.prog_pl_click_cb[1], [e, this._pl_len]);
},
// set handler for play progress bar click: function, this-to-use
add_prog_pl_click_cb : function(f_cb, v_this) {
	this.prog_pl_click_cb[0] = f_cb;
	this.prog_pl_click_cb[1] = v_this;
},

// the svg for the initial button is in a separate svg so there
// is the question of whether it has loaded -- this function
// can be called repeatedly: if not ready it returns false, if
// init already done returns true, and of course does the init
// when it can, and returns true
init_inibut : function() {
	if ( this.b_parms !== undefined ) {
		return true;
	}
	var k = this.parms["parentdiv"];
	if ( evhh5v_ctlbutmap[k] === undefined
		|| evhh5v_ctlbutmap[k]["loaded"] !== true ) {
		return false;
	}
	this.b_parms = evhh5v_ctlbutmap[k];

	var svg = this.b_parms.root_svg;
	var doc = this.b_parms.docu_svg;
	var but = doc.getElementById("g_inibut");
	this.inibut = this.mk_inibut(but, doc);
	but = doc.getElementById("g_wait");
	this.waitanim = this.mk_waitanim(but, doc);

	return true;
},
// comment as above
init_volctl : function() {
	if ( this.v_parms !== undefined ) {
		return true;
	}
	var k = this.parms["parentdiv"];
	if ( evhh5v_ctlvolmap[k] === undefined || evhh5v_ctlvolmap[k]["loaded"] !== true ) {
		return false;
	}
	this.v_parms = evhh5v_ctlvolmap[k];

	var svg = this.v_parms.root_svg;
	var doc = this.v_parms.docu_svg;
	var but = doc.getElementById("g_slider");
	this.volctl = this.mk_volctl(but, doc);
	this.volctlg = but;          // webkit needs group for transforms
	this.volctl.scalefactor = 1; // scaling for display size changes

	return true;
},

is_mobile : function() {
	if ( this.parms['mob'] !== undefined ) {
		return (this.parms['mob'] == 'true');
	}
	return evhh5v_ua_is_mobile();
},

// x position offset factors for buttons
xfacts : [0.5, 1.5, 2, 1.5, 2],

mk : function() {
	var mobi = this.is_mobile();
	var mnot = ! mobi;
	var svg = this.svg;
	var doc = this.doc;
	var ofs = 0;

	// set to draw volume slider hotizontal on mobile
	// devices
	this.vol_horz = mobi;

	if ( mobi ) {
		this.xfacts = [0.25, 1.75, 1.75, 1.75, 1.75];
	}
	
	// store for button default and current x.position and state
	this.button_data = {};
	var dat = this.button_data;

	svg.setAttribute("viewBox", this.viewbox);
	this.gall = doc.getElementById("g_all_g");

	//background rect
	var gbg = doc.getElementById("ctlbar_bg");
	this.bgrect = this.mk_bgrect(gbg);

	// buttons
	var gbn = doc.getElementById("g_button_1");
	dat["show"] = gbn;
	dat["hide"] = doc.getElementById("g_button_2");
	dat["hide"].setAttribute("visibility", "hidden");

	dat["play"] = {}; dat["play"].defx = ofs += this.xfacts[0];
	this.button_play = this.mk_playpause(gbn, dat["play"].defx);
	dat["play"].obj = this.button_play;

	dat["stop"] = {}; dat["stop"].defx = ofs += this.xfacts[1];
	this.button_stop = this.mk_stop(gbn, dat["stop"].defx);
	dat["stop"].obj = this.button_stop;
	this.stopbtn_disab();

	if ( true || mnot ) { // neither scale nor fullscreen for mobile
		dat["doscale"] = {}; dat["doscale"].defx = ofs += this.xfacts[2];
		this.button_doscale = this.mk_doscale(gbn, dat["doscale"].defx);
		dat["doscale"].obj = this.button_doscale;
		this.show_scalein();
		this.blur_doscale();

		dat["fullscreen"] = {}; dat["fullscreen"].defx = ofs += this.xfacts[3];
		this.button_fullscreen = this.mk_fullscreen(gbn, dat["fullscreen"].defx);
		dat["fullscreen"].obj = this.button_fullscreen;
		this.show_fullscreenout();
		this.blur_fullscreen();
	} else {
		this.button_doscale = this.button_fullscreen = false;
	}
	dat["volume"] = {}; dat["volume"].defx = ofs += this.xfacts[4];
	this.button_volume = this.mk_volume(gbn, dat["volume"].defx);
	dat["volume"].obj = this.button_volume;

	for ( var k in dat ) {
		var cur = dat[k];
		if ( cur.defx === undefined ) {
			continue;
		}

		var obj = cur.obj;
		cur.defx = obj.getAttribute("x");

		obj.x.baseVal.convertToSpecifiedUnits(
			obj.x.baseVal.SVG_LENGTHTYPE_PX);
		cur.defx_px = obj.x.baseVal.valueInSpecifiedUnits;
		obj.width.baseVal.convertToSpecifiedUnits(
			obj.width.baseVal.SVG_LENGTHTYPE_PX);
		cur.defwidth_px = obj.width.baseVal.valueInSpecifiedUnits;

	}

	// progress bars
	var gpl = doc.getElementById("prog_seek");
	this.progress_play = this.mk_prog_pl(gpl); // returns [bg, fg]
	var gdl = doc.getElementById("prog_load");
	this.progress_load = this.mk_prog_dl(gdl); // returns [bg, fg]

	// additional init, although readiness is not known
	this.init_inibut();
	this.init_volctl();

	this.OK = true;
},
// end internals
// and now, with yet-more indentation, the . . .

	// . . . public methods
	set_bar_visibility : function(visi) {
		this.svg.setAttribute("visibility", visi);
	},

	// handle narrow width: arg is boolean
	set_narrow : function(narrow) {
		var dat = this.button_data;
		
		if ( dat["doscale"] == undefined || dat["fullscreen"] == undefined ) {
			return;
		}

		if ( dat["doscale"].hidden && narrow ) {
			return;
		}
		if ( ! dat["doscale"].hidden && ! narrow ) {
			return;
		}

		var gs = dat["show"], gh = dat["hide"];

		if ( narrow ) {
			var x = dat["doscale"].obj.getAttribute("x");

			gs.removeChild(dat["doscale"].obj);
			gs.removeChild(dat["fullscreen"].obj);

			//gh.appendChild(dat["doscale"].obj);
			//gh.appendChild(dat["fullscreen"].obj);

			dat["doscale"].hidden = true;
			dat["volume"].obj.setAttribute("x", x);

			return;
		}

		dat["doscale"].hidden = false;
		dat["volume"].obj.setAttribute("x", dat["volume"].defx);

		//gh.removeChild(dat["doscale"].obj);
		//gh.removeChild(dat["fullscreen"].obj);

		gs.insertBefore(dat["fullscreen"].obj, dat["volume"].obj);
		gs.insertBefore(dat["doscale"].obj, dat["fullscreen"].obj);
	},

	// show wait spinner
	show_waitanim : function(x, y) {
		if ( ! this.init_inibut() ) {
			return false;
		}
		this.hide_inibut();

		var wa = this.waitanim;

		wa.width.baseVal.convertToSpecifiedUnits(
			wa.width.baseVal.SVG_LENGTHTYPE_PX),
		wa.height.baseVal.convertToSpecifiedUnits(
			wa.height.baseVal.SVG_LENGTHTYPE_PX);
		var w = wa.width.baseVal.valueInSpecifiedUnits,
			h = wa.height.baseVal.valueInSpecifiedUnits;

		var d = document.getElementById(this.b_parms["ctlbardiv"]);
		var l = (x - w / 2);
		var t = (y - h / 2);
		d.style.left = "" + l + "px";
		d.style.top  = "" + t + "px";

		var svg = this.b_parms.root_svg;
		svg.setAttribute("visibility", "visible");
		wa.setAttribute("visibility", "visible");
		this.wait_group.setAttribute("visibility", "visible");

		this.wait_anim_obj.start();

		return true;
	},
	hide_waitanim : function(x, y) {
		if ( ! this.init_inibut() ) {
			return false;
		}
		var wa = this.waitanim;

		wa.width.baseVal.convertToSpecifiedUnits(
			wa.width.baseVal.SVG_LENGTHTYPE_PX);
		var w = wa.width.baseVal.valueInSpecifiedUnits;

		var d = document.getElementById(this.b_parms["ctlbardiv"]);
		var svg = this.b_parms.root_svg;
		svg.setAttribute("visibility", "hidden");
		wa.setAttribute("visibility", "hidden");
		this.wait_group.setAttribute("visibility", "hidden");

		d.style.left = "" + (-w) + "px";
		d.style.top  = "0px";

		this.wait_anim_obj.stop();

		return true;
	},
	// show initial play button centered at x,y
	show_inibut : function(x, y) {
		if ( ! this.init_inibut() ) {
			return false;
		}

		this.inibut.width.baseVal.convertToSpecifiedUnits(
			this.inibut.width.baseVal.SVG_LENGTHTYPE_PX),
		this.inibut.height.baseVal.convertToSpecifiedUnits(
			this.inibut.height.baseVal.SVG_LENGTHTYPE_PX);
		var w = this.inibut.width.baseVal.valueInSpecifiedUnits,
			h = this.inibut.height.baseVal.valueInSpecifiedUnits;

		var d = document.getElementById(this.b_parms["ctlbardiv"]);
		var l = (x - w / 2);
		var t = (y - h / 2);
		d.style.left = "" + l + "px";
		d.style.top  = "" + t + "px";

		var svg = this.b_parms.root_svg;
		svg.setAttribute("visibility", "visible");
		this.inibut.setAttribute("visibility", "visible");
		if ( this.but_clearbg )
			this.but_clearbg.setAttribute("visibility", "visible");
		this.but_circle.setAttribute("visibility", "visible");
		this.but_arrow.setAttribute("visibility", "visible");

		return true;
	},
	hide_inibut : function() {
		if ( ! this.init_inibut() ) {
			return false;
		}

		this.inibut.width.baseVal.convertToSpecifiedUnits(
			this.inibut.width.baseVal.SVG_LENGTHTYPE_PX);
		var w = this.inibut.width.baseVal.valueInSpecifiedUnits;

		var svg = this.b_parms.root_svg;
		svg.setAttribute("visibility", "hidden");
		this.inibut.setAttribute("visibility", "hidden");
		if ( this.but_clearbg )
			this.but_clearbg.setAttribute("visibility", "hidden");
		this.but_circle.setAttribute("visibility", "hidden");
		this.but_arrow.setAttribute("visibility", "hidden");

		var d = document.getElementById(this.b_parms["ctlbardiv"]);
		d.style.left = "" + (-w) + "px";
		d.style.top  = "0px";

		return true;
	},

	// show volume slider with bottom at bottom, over vol button
	show_volctl : function(bottom, width) {
		if ( ! this.init_volctl() ) {
			return false;
		}

		var horz = this.vol_horz;
		var x;
		//x = parseFloat(this.button_volume.getAttribute("x"));
		this.button_volume.x.baseVal.convertToSpecifiedUnits(
			this.button_volume.x.baseVal.SVG_LENGTHTYPE_PX);
		x = this.button_volume.x.baseVal.valueInSpecifiedUnits;

		this.button_volume.width.baseVal.convertToSpecifiedUnits(
			this.button_volume.width.baseVal.SVG_LENGTHTYPE_PX);
		var bw = this.button_volume.width.baseVal.valueInSpecifiedUnits;

		/*
		*/
		this.volctl.height.baseVal.convertToSpecifiedUnits(
			this.volctl.height.baseVal.SVG_LENGTHTYPE_PX);
		var vh = this.volctl.height.baseVal.valueInSpecifiedUnits;
		this.volctl.width.baseVal.convertToSpecifiedUnits(
			this.volctl.width.baseVal.SVG_LENGTHTYPE_PX);
		var vw = this.volctl.width.baseVal.valueInSpecifiedUnits;

		var fact;
		// LOUSINESS: older browsers, e.g. Chromium 22.xx, might
		// get these arcane SVG wrong, even if the functions
		// are not undefined . . .
		// UPDATE: Chromium, and Android browser, are N.G. up to
		// at least 32.xx; it seems their SVGPoint.matrixTransform()
		// is broken.
		if ( this.button_volume.getCTM /*&& this.v_parms.root_svg.createSVGPoint*/ ) {
			var ctm = this.button_volume.getCTM();
			/* due to brokeness in chromium and android, we cannot
			 * do this the right way . . .
			var p = this.v_parms.root_svg.createSVGPoint();
			p.x = 100; p.y = 0;
			p = p.matrixTransform(ctm);
			fact = p.x / 100;
			* . . . but we only need simple scaling so we can cheat,
			* albeit with unfortunate use of context matrix
			* property name, an implementation detail:
			*/
			fact = ctm["a"];
		} else {
			// this worked everywhere but Android browser; I'm
			// baffled by the wrong values seen there:
			fact = this.wndlength_orig / this.wndlength;
		}

		x  *= fact;
		bw *= fact;
		if ( horz ) {
			x  += (bw - this.vol_width);
		} else {
			x  += (bw - this.vol_width) / 2.0;
		}

		var l = x;
		var t = bottom - this.vol_height;
		var scl = 1;

		if ( horz && (l < 0 || this.vol_width > width) ) {
			if ( this.vol_width > width ) {
				scl *= width / this.vol_width;
			}
			l = 0;
		} else if ( ! horz && (t < 0 || this.vol_height > bottom) ) {
			t = this.vol_height; // temporary reuse
			if ( this.vol_height > bottom ) {
				scl *= bottom / this.vol_height;
				t *= scl;
			}
			t = (bottom - t) / 2;
		}
		// NOTE use volctlg -- webkit does not apply transform
		// to this.volctl (an svg) directly (volctlg ia a 'g')
		this.volctlg.setAttribute("transform", "scale(" + scl + ")");
		this.volctl.scalefactor = scl;

		var d = document.getElementById(this.v_parms["ctlbardiv"]);
		d.style.left = "" + l + "px";
		d.style.top  = "" + t + "px";
		d.style.width  = "" + (vw * scl) + "px";
		d.style.height = "" + (vh * scl) + "px";

		var svg = this.v_parms.root_svg;
		svg.setAttribute("visibility", "visible");
		this.volctl.setAttribute("visibility", "visible");
		this.vol_bgarea.setAttribute("visibility", "visible");
		this.vol_bgslide.setAttribute("visibility", "visible");
		this.vol_fgslide.setAttribute("visibility", "visible");

		return true;
	},
	hide_volctl : function() {
		if ( ! this.init_volctl() ) {
			return false;
		}

		this.volctl.width.baseVal.convertToSpecifiedUnits(
			this.volctl.width.baseVal.SVG_LENGTHTYPE_PX);
		var w = this.volctl.width.baseVal.valueInSpecifiedUnits;

		var svg = this.v_parms.root_svg;
		svg.setAttribute("visibility", "hidden");
		this.volctl.setAttribute("visibility", "hidden");
		this.vol_bgarea.setAttribute("visibility", "hidden");
		this.vol_bgslide.setAttribute("visibility", "hidden");
		this.vol_fgslide.setAttribute("visibility", "hidden");

		var d = document.getElementById(this.v_parms["ctlbardiv"]);
		d.style.left = "" + (-w) + "px";
		d.style.top  = "0px";

		return true;
	},
	// make volume slider indicate a value. (0,1)
	scale_volctl : function(v) {
		if ( ! this.init_volctl() ) {
			return false;
		}

		var horz = this.vol_horz;
		var bg, fg;
		bg = this.vol_bgslide;
		fg = this.vol_fgslide;
		
		v = Math.max(0, Math.min(1, v));
		
		if ( horz ) {
			var bw = parseFloat(bg.getAttribute("width"));
			var nv = bw * v;
			fg.setAttribute("width", "" + nv + "px");
		} else {
			var bh = parseFloat(bg.getAttribute("height"));
			var bt = parseFloat(bg.getAttribute("y"));
			var nv = bh * v;
			var nt = bt + bh - nv;
			
			fg.setAttribute("y", "" + nt + "px");
			fg.setAttribute("height", "" + nv + "px");
		}
	},
	// handler for slide control clicks and such
	hdl_volctl : function(e, ob) {
		e.preventDefault();

		if ( this.controller_handle_volume === undefined ) {
			return false;
		}

		var horz = this.vol_horz;
		var bg, fg, dim, dir;
		bg = this.vol_bgslide;
		fg = this.vol_fgslide;
		
		if ( horz ) {
			dim = "width";
			dir = "x";
		} else {
			dim = "height";
			dir = "y";
		}
		var fh = parseFloat(fg.getAttribute(dim));
		var bh = parseFloat(bg.getAttribute(dim));
		var bt = parseFloat(bg.getAttribute(dir));

		var v;
		if ( e.type === "wheel" ) {
			// no, cannot use delta directly;
			// units inconsistent among browsers
			//v = fh - e.deltaY;
			v = fh - ((e.deltaY < 0) ? -3 : 3);
		} else if ( e.type === "touchmove" ) {
			var t = parseFloat(horz
				? (0 - e.changedTouches[0].clientX)
				: e.changedTouches[0].clientY);
			if ( ! isFinite(t) )
				return;
			if ( ! this.vol_touchstart ) {
				this.vol_touchstart = 0;
			}
			var cur = t - this.vol_touchstart;
			v = (fh - cur) / this.volctl.scalefactor;
			this.vol_touchstart = t;
		} else if ( horz ) { // mouse{down,up,move}
			v = e.clientX / this.volctl.scalefactor - bt;
		} else { // mouse{down,up,move}
			v = bh - (e.clientY / this.volctl.scalefactor - bt);
		}

		this.controller_handle_volume(v / bh);
		return false;
	},


	resize_bar : function(w, h) {
		var oh = this.barheight; //this.wndheight;
		var ow = this.wndlength;
		var nw = oh * w / h;

		this.wndlength = nw;
		this.var_init();
		nw = this.barlength;
		var pnw = this.progressbarlength;

		// temp workaround until length calc is fixed:
		this._pl_len = w;
	
		this.svg.setAttribute("viewBox", this.viewbox);
	
		for ( var i = 0; i < this.rszo.length; i++ ) {
			var t = this.rszo[i].id == "bgrect" ? nw : pnw;
			this.rszo[i].setAttribute("width", t);
		}

		var obj = this.button_data["volume"].obj;
		obj.width.baseVal.convertToSpecifiedUnits(
			obj.width.baseVal.SVG_LENGTHTYPE_PX);
		var mxw = obj.width.baseVal.valueInSpecifiedUnits;
		var mxx = this.button_data["volume"].defx_px;
		var max = mxx + mxw;
		// right pad == leftmost button.x
		max += this.button_data["play"].defx_px;

		this.set_narrow(w < max);
	},

	show_dl_active : function() {
		this.progress_load[1].setAttribute("class", "progloadfgdl");
	},
	show_dl_inactive : function() {
		this.progress_load[1].setAttribute("class", "progloadfg");
	},

	progress_pl : function(t) {
		this.progress_play[1].setAttribute("width", t * this.progressbarlength);
	},

	show_fullscreenout : function() {
		var btn = this.button_fullscreen;
		if ( ! btn ) return;
		this.fullscreenicoin.setAttribute("visibility", "hidden");
		this.fullscreenicoout.setAttribute("visibility", "visible");
		btn.hlt.setAttribute("visibility", "hidden");
	},
	show_fullscreenin : function() {
		var btn = this.button_fullscreen;
		if ( ! btn ) return;
		this.fullscreenicoout.setAttribute("visibility", "hidden");
		this.fullscreenicoin.setAttribute("visibility", "visible");
		btn.hlt.setAttribute("visibility", "hidden");
	},
	blur_fullscreen : function () {
		var btn = this.button_fullscreen;
		if ( ! btn ) return;
		btn.removeAttribute("onclick");
		btn.removeAttribute("onmouseover");
		btn.removeAttribute("onmouseout");
		btn.hlt.setAttribute("visibility", "hidden");
		btn.style.cursor = "inherit";
		btn.ico_out.setAttribute("filter", btn.disabfilter);
	},
	unblur_fullscreen : function() {
		var btn = this.button_fullscreen;
		if ( ! btn ) return;
		btn.setAttribute("onclick", "svg_click(this);");
		btn.setAttribute("onmouseover", "setvisi('fullscreen_highlight','visible');");
		btn.setAttribute("onmouseout",  "setvisi('fullscreen_highlight','hidden');");
		btn.style.cursor = "pointer";
		btn.ico_out.removeAttribute("filter");
	},

	show_scaleout : function() {
		this.doscaleicoin.setAttribute("visibility", "hidden");
		this.doscaleicoout.setAttribute("visibility", "visible");
	},
	show_scalein : function() {
		this.doscaleicoout.setAttribute("visibility", "hidden");
		this.doscaleicoin.setAttribute("visibility", "visible");
	},
	blur_doscale : function () {
		var btn = this.button_doscale;
		if ( ! btn ) return;
		btn.removeAttribute("onclick");
		btn.removeAttribute("onmouseover");
		btn.removeAttribute("onmouseout");
		btn.hlt.setAttribute("visibility", "hidden");
		btn.style.cursor = "inherit";
		btn.ico_in.setAttribute("filter", btn.disabfilter);
		btn.ico_out.setAttribute("filter", btn.disabfilter);
	},
	unblur_doscale : function() {
		var btn = this.button_doscale;
		if ( ! btn ) return;
		btn.setAttribute("onclick", "svg_click(this);");
		btn.setAttribute("onmouseover", "setvisi('doscale_highlight','visible');");
		btn.setAttribute("onmouseout",  "setvisi('doscale_highlight','hidden');");
		btn.style.cursor = "pointer";
		btn.ico_in.removeAttribute("filter");
		btn.ico_out.removeAttribute("filter");
	},

	show_playico : function() {
		this.pauseico.setAttribute("visibility", "hidden");
		this.playico.setAttribute("visibility", "visible");
	},
	show_pauseico : function() {
		this.playico.setAttribute("visibility", "hidden");
		this.pauseico.setAttribute("visibility", "visible");
	},

	stopbtn_disab : function() {
		var btn = this.button_stop;
		if ( ! btn ) return;
		btn.removeAttribute("onclick");
		btn.removeAttribute("onmouseover");
		btn.removeAttribute("onmouseout");
		btn.hlt.setAttribute("visibility", "hidden");
		btn.style.cursor = "inherit";
		btn.ico.setAttribute("filter", btn.disabfilter);
	},
	stopbtn_enab : function() {
		var btn = this.button_stop;
		if ( ! btn ) return;
		btn.setAttribute("onclick", "svg_click(this);");
		btn.setAttribute("onmouseover", "setvisi('stop_highlight','visible');");
		btn.setAttribute("onmouseout", "setvisi('stop_highlight','hidden');");
		btn.style.cursor = "pointer";
		btn.ico.removeAttribute("filter");
	},

	endmember : this
};

// helpers to be called from svg browsing context JS, because
// 'onfoo' will execute in that context
function evhh5v_setvisi(obj, visi) {
	if ( obj ) {
		obj.setAttribute("visibility", visi);
	}
};

function evhh5v_svg_click(obj, parms) {
	var bar = evhh5v_ctlbarmap[parms["parentdiv"]];
	if ( ! bar || ! bar["loaded"] || ! bar.evhh5v_controller ) {
		return;
	}
	bar.evhh5v_controller.button_click(obj);
};


/**********************************************************************\
 *                                                                    *
 * The HTML5 video controller object -- scaling, fullscreen, aspect,  *
 * event handling, control bar control (and browser bugs)             *
 *                                                                    *
\**********************************************************************/

var evhh5v_controller = function(vid, ctlbar, pad) {
	vid.removeAttribute("controls"); // should be done, but be sure
	this._vid = vid;
	this.ctlbar = ctlbar;
	this.bar = ctlbar.evhh5v_controlbar;
	this.pad = pad;
	this.handlermap = {};

	this._x = this._y = 0;

	this.auxdiv = document.getElementById(this.ctlbar["auxdiv"]);
	this.bardiv = document.getElementById(this.ctlbar["ctlbardiv"]);
	this.div_bg_clr = evhh5v_getstyle(this.auxdiv, 'background-color');
	this.auxdivclass = this.auxdiv.getAttribute("class");

	this.tickinterval_divisor = 1000 / this.tickinterval;
	this.ptrtickmax = this.tickinterval_divisor * this.ptrinterval;
	this.ptrtick = 0;

	this.doshowbartime = false;
	if ( this.params['hidebar'] !== undefined
		&& this.params['hidebar'] == 'true' ) {
		this.doshowbartime = true;
	}
	this.doshowbar = true;
	if ( this.params['disablebar'] !== undefined
		&& this.params['disablebar'] == 'true' ) {
		this.disablebar = true;
		this.doshowbar = false;
		this.doshowbartime = false;
	}

	this.allowfull = true;
	if ( this.params['allowfull'] !== undefined
		&& this.params['allowfull'] == 'false' ) {
		this.allowfull = false;
	}

	this.barpadding = 2;
	this.yshowpos = this.bar_y = this.height - this.barheight;

	this.doscale = true;

	// add handlers that the control bar services
	var that = this;
	this.bar.add_prog_pl_click_cb(this.prog_pl_click_cb, that);

	// need a tick counter
	this.ntick = 0;

	this._vid.setAttribute("class", "evhh5v_mouseptr_normal");

	// optional display aspect ratio, disabled with 0:
	// allow server code to pass a real number, or user-
	// provided arg of form 'number not-numeric-or-dot number'
	// where the separator is flexible to allow 4:3, 16x9, 20/9
	// and such; for simplicity allow any separator except '.'[*],
	// for obvious reason -- also allow negatives for possible
	// future use like flipping
	// [*] while dot-as-fractional-separator is locale dependent,
	// server is expected to provide real numbers in 'C' locale
	if ( ctlbar['aspect'] !== undefined ) {
		var r;
		r = /\b(-?[0-9]+)[^0-9\.](-?[0-9]+)\b/.exec(""+ctlbar['aspect']);
		r = r ? (r[1] / r[2]) : parseFloat(ctlbar['aspect']);
		r = isFinite(r) ? r : 0;
		// disallow unreasonable values ('reasonable' is arbitrary)
		if ( Math.abs(r) < 0.5 || Math.abs(r) > 10 ) {
			r = 0;
		}
		// For now, ignore negatives
		this.aspect = Math.abs(r);
	} else {
		this.aspect = 0;
	}
	this.is_canvas = false;
};
evhh5v_controller.prototype = {
	aspect_min : 0.0, // minimum aspect difference for canvas hack
	tickinterval : 50, // alt 100,
	ptrinterval : 5,
	barshowincr : 2, //1,
	barshowmargin : 2,
	default_init_vol : 50, // 0-100
	mouse_hide_class : "evhh5v_mouseptr_hidden",
	mouse_show_class : "evhh5v_mouseptr_normal",

	// Bug that appeared in Chrom(e|ium)/33.?
	// Chrom(e|ium)/33.* has broken display re-draw when
	// parent div of wait indicator is moved into place.
	// Earlier Chrom* were fine (in this regard). Note that
	// I mean the Chrom* component that is used in Opera too,
	// the exact same bug is seen there with latest version
	// and it too reports /33.* in its UA string.
	// I spent hours looking for a workaround but found
	// nothing, except to disable the spinner.
	// Update: bug is only exacerbated by showing the wait
	// spinner, not triggered -- even w/o spinner, the poster
	// intermittently appears on seek. Apparently, having
	// to composite the spinner too really gets it confused.
	// Update: bug evolves with Chromium/34 beta, and with
	// Chromium/35 the poster is drawn on seek completely and
	// consistently, and looks very much like an intentional
	// feature, so let 35+ show the spinner and revisit this
	// if necessary
	chrome_draw_bug :
		/Chrom(e|ium)\/3[3-4]\./i.test(navigator["userAgent"]),
		// /Chrom(e|ium)\/([4-9][0-9]|3[3-9])\./i.test(navigator["userAgent"]),

	// hack: this member should have been called 'params' rather
	// than 'ctlbar'; replacement will be arduous, so use this
	// getter as a "params" alias until the member name is changed
	get params() { return this.ctlbar; },

	mk : function() {
		this.v.evhh5v_controller = this;
		this.ctlbar.evhh5v_controller = this;
		this.height = this.v.height;
		this.width = this.v.width;
	
		// the play param, implies the autoplay attribute
		if ( this.params['play'] !== undefined ) {
			if ( this.params['play'] == 'true' ) {
				this.autoplay = true;
			}
		} else {
			this.autoplay = false;
		}
		
		if ( this.allowfull && evhh5v_fullscreen_ok() ) {
			this.bar.unblur_fullscreen();
		} else {
			this.bar.blur_fullscreen();
		}

		if ( this.disablebar ) {
			this.bar.set_bar_visibility("hidden");
			this.showhideBar(this.doshowbar = false);
		} else {
			this.set_bar_y(this.bar_y);
			this.bar.set_bar_visibility("visible");
		}

		// set use of canvas as needed; see comment at func def
		this.setup_canvas();

		this.install_handlers();

		var that = this;

		this.bar.controller_handle_volume = function(pct) {
			// delay pointer/bar hiding for volume interaction
			that.ptrtick = 0;
			// paranoia check
			if ( ! isFinite(pct) ) {
				return;
			}
			//assign volume value
			var v = that._vid;
			if ( v.volume !== undefined ) {
				pct = Math.max(0, Math.min(1, pct));
				// volumechanged handler updates indicator
				v.volume = that.init_vol = pct;
			}
		}

		this.bar.scale_volctl(1);

		if ( this.autoplay ) {
			// this.play() will be invoked in the
			// loadedmetadata event handler
			this._vid.setAttribute("preload", "metadata");
		} else {
			this._vid.setAttribute("preload", this.params["preload"]);

			// initial play button: this continues with a recursive
			// timer, which will adjust for size changes albeit with
			// a lag, until play() has been done once (and is then
			// never seen again)
			var f = function() {
				if ( that.has_been_played ) {
					that.bar.hide_inibut();
					return;
				}
				that.bar.show_inibut(that.width / 2, that.height / 2);
				setTimeout(f, 1000);
			};
			f();
		}
	},
	// init on metadata event
	on_metadata : function () {
		if ( this.init_vol === undefined ) {
			var t = this.params['volume'] !== undefined
				? parseFloat(this.params['volume'])
				: this.default_init_vol;
			if ( ! isFinite(t) ) {
				t = this.default_init_vol;
			}
			this.init_vol = Math.max(0, Math.min(1, t / 100.0));
		}
		this._vid.volume = this.init_vol;

		if ( this.autoplay ) {
			this.play();
		}

		// on metadata receipt, if not playing, H5 video might show
		// a video frame like a poster, if there is no poster attr.
		// If a canvas is used here, any such frame must be drawn
		// explicitely. Doing so is consistent with H5 video intent.
		if ( this.is_canvas && ! this.playing && ! this._cnv_poster ) {
			if ( this._vid.getAttribute("preload") !== "none" ) {
				this.canvas_clear();
				this.put_canvas_frame_single_timeout(50);
			}
		}
	},

	// H5 video spec to date (02-2014) does not provide the means
	// to adjust display aspect and always uses intrinsic pixel
	// dimensions (CSS3 may partially help, see below), but the media
	// might have non-square pixels, or broken metadata with wrong
	// aspect, and w/o adjustment the display will be wrong.
	// So, if given an aspect ratio parameter, take extraordinary
	// measures to get the display right: a hack to have the
	// video frames displayed on a canvas element, which does
	// provide (through its 'context' sub-object) the means to
	// control display proportion and position.
	// The video element is replaced (on the parent) with a canvas,
	// and the video is maintained in a detached state. A recursive
	// timeout ~= [reasonable frame rate] puts frames on the
	// canvas-context with adjustments. More CPU is used, but it's
	// not horrible; the worst is the extra complication.
	// This aspect problem might be addressed partially with new
	// CSS3 '(-?-)object-fit' and '(-?-)object-position', but that
	// approach raises new problems, not least of which are browser
	// support and lesser control. For now, use this brutish canvas
	// hack, and if future time and motivation permit, try detecting
	// whether CSS can do the trick and switch to that approach if so.
	setup_canvas : function() {
		var params = this.params;
		var force = false;

		// consider parameters pertaining to display aspect,
		// but only if display aspect was not specified because
		// that overrides the others
		if ( this.aspect <= 0 ) {
			var t;
			// "pixelaspect" is used if values for video data
			// are available (but intended display is not)
			t = params["pixelaspect"];
			if ( t !== undefined ) {
				var r;
				r = /\b(-?[0-9]+)[^0-9\.](-?[0-9]+)\b/.exec(""+t);
				r = r ? (r[1] / r[2]) : parseFloat(t);
				r = isFinite(r) ? r : 0;
				// disallow unreasonable values ('reasonable' is arbitrary)
				if ( ! (Math.abs(r) < 0.5 || Math.abs(r) > 10) ) {
					// For now, ignore negatives
					this.pixelaspect = Math.abs(r);
				}
			}
			// "aspectautoadj" is a helper to set 4:3 display aspect
			// if video intrinsic dimensions suggest DVD source
			t = params["aspectautoadj"];
			if ( ! this.pixelaspect && t !== undefined ) {
				this.aspectautoadj = (t == 'true');
			}
		}
		
		force = (this.pixelaspect || this.aspectautoadj);

		// Normally, use the canvas hack only when aspect must be
		// adjusted.  This Opera test was made necessary by a series
		// of Opera on Unix bugs: 1st, when this code is used
		// as fallback under an object element (as in SWFPut plugin)
		// an older (FreeBSD) version would not size the parent <div>
		// properly. That was solved with a hack elsewhere that
		// reparents our auxiliary div from the <object> to the
		// enclosing <div>, which worked on the older FBSD version.
		// Next, testing with current Opera on GNU/Linux, found that
		// the video frames would not show on play (audio OK) until
		// playback was paused and then restarted. Sheesh! Don't know
		// if this bug is excited by the reparenting that squashed the
		// 1st bug, but IAC the display on canvas seems fine, so
		// force the canvas hack for Opera. (Whew.)
		if ( /Opera/i.test(navigator["userAgent"]) ) {
			force = true;
		}

		if ( ! force ) {
			if ( this.aspect <= 0
				|| Math.abs(this.aspect - this.width / this.height)
					< this.aspect_min ) {
				return;
			}
		}

		var cw = this.width, ch = this.height;
		this._cnv = document.createElement('canvas');
		var pt = this._vid.parentNode;
		pt.replaceChild(this._cnv, this._vid);
		this.is_canvas = true;
		this._cnv.width = cw;
		this._cnv.height = ch;
		this.setup_aspect_factors();
		this.get_canvas_context();
		this.canvas_clear();

		var that = this;
		var pstr = this._vid.getAttribute("poster");
		if ( pstr && pstr != '' ) {
			this._cnv_poster = document.createElement('img');
			this._cnv_poster.onload = function () {
				that.put_canvas_poster();
			};
			this._cnv_poster.src = pstr;
		}

		this._cnv.setAttribute("class", "evhh5v_mouseptr_normal");
	},
	get_canvas_context : function() {
		if ( this.is_canvas ) {
			this._ctx = this._cnv.getContext('2d');
			return this._ctx;
		}
		return null;
	},
	// put any video poster on canvas, if in use; or, current frame if paused
	put_canvas_poster : function() {
		if ( ! this.playing && this.is_canvas
			&& isFinite(this._vid.currentTime)
			&& this._vid.currentTime > 0 ) {
			this.canvas_clear();
			this.put_canvas_frame_single();
		} else if ( this.is_canvas && this._cnv_poster != undefined  && ! this.playing ) {
			this.canvas_clear();
			var cw = this.width, ch = this.height;
			var iw = this._cnv_poster.width, ih = this._cnv_poster.height, ix, iy;
			var origaspect = cw / ch, aspect = iw / ih;
			if ( aspect > origaspect ) {
				iw = cw;
				ih = cw / aspect;
				ix = 0;
				iy = (ch - ih) / 2.0;
			} else {
				iw = ch * aspect;
				ih = ch;
				ix = (cw - iw) / 2.0;
				iy = 0;
			}
			this._ctx.drawImage(this._cnv_poster, ix, iy, iw, ih);
		} else if ( ! this.playing ) {
			this.canvas_clear();
		}
	},
	// clear canvas
	canvas_clear : function() {
		var ctx;
		if ( this.is_canvas && (ctx = this.get_canvas_context()) ) {
			var cw = this.width, ch = this.height;
			ctx.fillStyle = this.div_bg_clr;
			ctx.fillRect(0, 0, this.width, this.height);
		}
	},
	// canvas hack is all(-ish) about display aspect so figure it out
	// and store the factors and offsets for frame painting
	setup_aspect_factors : function() {
		var video = this._vid;
		var cw = this.width, ch = this.height;

		if ( ! this.gotmetadata ) {
			// width & height setters call this, so do the setting
			this.v.width = cw;
			this.v.height = ch;
			return;
		} else if ( this.pixelaspect && this.aspect <= 0 ) {
			// NOTE: pixelaspect (& aspectautoadj below) will not
			// work for sources with aspect metadata because the
			// 'intrinsic' dimensions are adjusted for that; but
			// pixelaspect will work for broken metadata if a
			// a suitable value is given, e.g. pixelaspect 8:9
			// given for 720x480 4:3 video with display aspect metadata
			// that claims 1.5 (720:480).
			var w = video.videoWidth;
			var h = video.videoHeight;
			this.aspect = (w * this.pixelaspect) / h;
		} else if ( this.aspectautoadj && this.aspect <= 0 ) {
			var w = video.videoWidth;
			var h = video.videoHeight;
			// common sizes; cannot handle every possibility
			if ( w == 720 || w == 704 ) {
				if ( h == 480 || h == 576 ) {
					this.aspect = 4.0 / 3.0;
				}
			}
			if ( w == 360 || w == 352 ) {
				// handle 360x240? is it common w/ square pixels?
				if ( h == 288 || h == 240 ) {
					this.aspect = 4.0 / 3.0;
				}
			}
		}

		this.origaspect = cw / ch;
		var vw = video.videoWidth;
		var vh = video.videoHeight;
		var aspectW =
			(( this.aspect <= 0 ||
			Math.abs(this.aspect - this.width / this.height) < this.aspect_min )
			? (vw / vh) : this.aspect) * vh / vw;

		vw *= aspectW;

		// code here to eob adapted from SWF player
		var va = vw / vh; // aspect
		var sw = this.width;  // 's' for 'Stage', because this code
		var sh = this.height; // is from flash program ActionScript
		var sa = sw / sh;

		// allow natural scale or not
		if ( vw < sw && vh < sh ) {
			this.bar.unblur_doscale();
		} else {
			this.bar.blur_doscale();
		}

		if ( ! this.doscale ) {
			if ( vw > sw || vh > sh ) {
				if ( sa > va ) { // Stage aspect wider
					this._width = sh * va;
					this._height = sh;
				} else { // narrower
					this._width = sw;
					this._height = sw * vh / vw;
				}
			} else {
				this._width = vw;
				this._height = vh;
			}
			this._x = (sw - this._width) / 2;
			this._y = (sh - this._height) / 2;
		} else {
			if ( sa > va ) { // Stage wider than video
				this._width = sh * va;
				this._height = sh;
				this._x = (sw - this._width) / 2;
				this._y = 0;
			} else { // narrower
				this._width = sw;
				this._height = sw * vh / vw;
				this._x = 0;
				this._y = (sh - this._height) / 2;
			}
		}

		if ( ! this.is_canvas ) {
			// ffox (27) bug: if margin is not set 0 here, then
			// marginLeft below might not be effective
			video.style.margin = "0px";
			cw = Math.round(Math.max(0, this._x));
			ch = Math.round(Math.max(0, this._y));
			video.width  = this._width;
			video.height = this._height;
			video.style.marginLeft   = cw + "px";
			video.style.marginTop    = ch + "px";
			video.style.marginRight  = cw + "px";
			video.style.marginBottom = ch + "px";
		} else {
			this._cnv.width = sw; this._cnv.height = sh;
		}
	},
	// put a frame on the canvas . . .
	// There are two versions of frame timer procs -- unless I
	// decide on one and delete the other. Of course, one set must
	// be commented at any time. One uses setInterval(), the other
	// uses recursive setTimeout() -- so far performance diffs
	// are infinitesimal, ephemeral, and very likely imaginary.
	/*
	*/
	put_canvas_frame : function() {
		if ( ! this.is_canvas || this.frame_timer || this._vid.paused || this._vid.ended ) {
			return;
		}

		var that = this;
		this.frame_timer = setInterval(function () {
			that._ctx.drawImage(that._vid, that._x, that._y, that._width, that._height);
		}, this.canvas_frame_timeout);
	},
	end_canvas_frame : function() {
		if ( ! this.frame_timer ) return;
		clearInterval(this.frame_timer);
		this.frame_timer = false;
	},
	canvas_frame_timeout : 16,
	// put a *single* frame on the canvas, e.g. when trying to get
	// poster to appear
	put_canvas_frame_single : function() {
		var ctx;
		if ( this.is_canvas && (ctx = this.get_canvas_context()) ) {
			ctx.drawImage(this._vid, this._x, this._y, this._width, this._height);
		}
	},
	put_canvas_frame_single_timeout : function(timeout) {
		var that = this;
		this.canvas_frame_single_timer = setTimeout(function () {
			that.put_canvas_frame_single();
		}, timeout == undefined ? 50 : timeout);
	},
	setpad : function(pad) {
		this.pad = pad;
	},
	// get the control bar svg height
	get barheight() {
		return parseInt(this.ctlbar["barheight"]);
	},
	// get the object in place as video
	get v() {
		return this.is_canvas ? this._cnv : this._vid;
	},
	// [gs]etters for width and height, allowing interface resize code
	// to simply read and assign as it would with a browser object;
	// note the additional objects handled on size assignment: the
	// interface resize code handles an outer enclosing <div>, but
	// this code handles inner divs specific to this implementation,
	// and the svg based control bar
	get width() { return this.set_width == undefined ? this.v.width : this.set_width; },
	set width(v) {
		if ( this.in_fullscreen ) return; // special case refusal
		this.put_width(v);
	},
	put_width : function(v) {
		this.hide_volctl();
		this.set_width = v;
		var t;

		t = document.getElementById(this.ctlbar["parent"]);
		t.style.width = v + "px";
		t = this.auxdiv;
		t.style.width = v + "px";

		this.setup_aspect_factors(); this.put_canvas_poster();
		if ( this.ctlbar.evhh5v_controlbar ) {
			this.ctlbar.evhh5v_controlbar.resize_bar(v, this.barheight);
			this.play_progress_update();
		}

		t = this.bardiv;
		t.style.width = v + "px";
		t.style.left = this.pad + "px";
	},
	get height() { return this.set_height == undefined ? this.v.height : this.set_height; },
	set height(v) {
		if ( this.in_fullscreen ) return; // special case refusal
		this.put_height(v);
	},
	put_height : function(v) {
		this.hide_volctl();
		var t;
		var diff = v - this.height;

		t = this.auxdiv;
		t.style.left = "0px"; t.style.top = "0px"; t.style.height = "" + v + "px";

		this.set_height = v;

		var bh = this.barheight;
		t = document.getElementById(this.ctlbar["parent"]);
		t.style.height = bh + "px";

		this.setup_aspect_factors(); this.put_canvas_poster();

		t = this.bardiv;
		t.style.height = bh + "px";
		this.bar_y += diff; this.yshowpos += diff;
		t.style.top = this.bar_y + "px";
		t.style.left = this.pad + "px";
	},
	// these are for the resizing JS that handles this; no effect
	get pixelWidth() { if ( this.v.pixelWidth !== undefined ) return this.v.pixelWidth; return undefined; },
	set pixelWidth(v) { this.v.pixelWidth = v; },
	get pixelHeight() { if ( this.v.pixelHeight !== undefined ) return this.v.pixelHeight; return undefined; },
	set pixelHeight(v) { this.v.pixelHeight = v; },

	// size hack for going fullscreen -- it is the enclosing div that
	// is fullscreen'd, and here we follow
	fs_resize : function() {
		if ( ! this.in_fullscreen ) return; // only fullscreen
		var w = window.screen.width, h = window.screen.height;
		this.put_height(h);
		this.put_width(w);
	},

	// event dispatcher -- we can ensure that this === this this,
	// and the call sequence
	callbk : function(evt) {
		var that;
		if ( (that = this.evhh5v_controller) == undefined ) {
			return;
		}
		var ename = evt.type;
		var map = that.handlermap;

		/* debugging
			if ( that.evcnt === undefined ) that.evcnt = {};
			if ( that.evcnt[ename] === undefined ) that.evcnt[ename] = 0;
			that.evcnt[ename]++;
		*/

		if ( map[ename] != undefined ) {
			for ( var i = 0, mx = map[ename].length; i < mx; i++ ) {
				var f = map[ename][i];
				if ( f && typeof (f) == "function" ) {
					f.call(that, evt);
				}
			}
		}
	},
	// event handler installer -- installs from current handlermap
	// on obj arg; e.g., if stop() replaces video object
	_obj_add_evt : function(obj, bubool) {
		if ( typeof (bubool) !== "boolean" ) {
			bubool = false;
		}

		for ( var k in this.handlermap ) {
			obj.addEventListener(k, this.callbk, bubool);
		}
	},
	// event handler installer -- internal for addEventListener below
	add_evt : function(ename, callbk, bubool) {
		var inst = false;
		if ( this.handlermap[ename] == undefined ) {
			this.handlermap[ename] = [];
			inst = true;
		}
		this.handlermap[ename].push(callbk);
		if ( inst && this._vid ) {
			this._vid.addEventListener(ename, this.callbk, bubool);
		}
	},
	// add event handler call: like DOM objects method, except
	// name arg will accept array of strings, or one string
	// Note: may be called any number of times for same events
	// to install several handlers for each, *but* the bubble boolean
	// *only has effect* with the first installation 
	addEventListener : function(ename, callbk, bubool) {
		if ( typeof (bubool) !== "boolean" ) {
			bubool = false;
		}

		if ( typeof ename === "string" ) {
			this.add_evt(ename, callbk, bubool);
		} else if ( ename instanceof Array ) {
			var mx = ename.length;
			for ( var i = 0; i < mx; i++ ) {
				this.add_evt(ename[i], callbk, bubool);
			}
		}
	},
	// events
	install_handlers : function(newvid) {
		// NOTE: it is arranged that this in handler will be this this
		var newv = false;
		if ( newvid === true ) {
			newv = true;
			this.handlermap = {}; // clear from old vid
		}

		// It's proven difficult (maybe impossible with current state
		// of h5v implementations) to get the behavior of a buffering
		// wait indicator, working in the customary way.
		// In a flash program this is easy: there is a NetStream
		// event "onStatus" which fires with balanced codes
		// "NetStream.Buffer.Empty" and "NetStream.Buffer.Full"
		// and these suffice for the bulk of the wait behavior.
		//
		// My first hope was that HTML video would have equivalents,
		// and whatwg docs *seemed* to suggest "waiting"/"playing"[*]
		// might do, but no, "waiting" is not delivered when expected
		// in all browsers.
		// Testing with various rate limits, several events have been
		// tried, and the magic combo still eludes me.
		// By its name, "stalled" might seem pertinent, but apparently
		// it is not. Let's not even fall into the per-browser bog,
		// in which e.g. "suspend" and "progress" are radically
		// different between FFox and Chromium.
		//
		// 25.03.2014 I have the behavior I want in FFox (28.0), in at
		// least the current testing; but, Chromium/33. is not delivering
		// "waiting" even when it has insufficient data and display
		// is stalled (this is w/ 76.8kbs rate limit). This will
		// have to do, there is no more time now.
		// MSIE 11: sometimes fires waiting, but then closes with
		// seeked rather than playing; also occasionally raises stalled,
		// but not regularly and apparently w/o balanced resume event.
		// Opera on MSW: webkit. Horrible. No wonder sites like
		// YouTube have not simply switched to HTML5 video.
		//
		// [*] whatwg says, re. "waiting":
		//		Playback has stopped because the next frame
		//		is not available, but the user agent expects
		//		that frame to become available in due course.
		// Sounds just right -- but it is delivered only sparingly.
		// (testing: "seeking" "seeked", , "suspend", "progress", "canplay", "stalled", )
		var wait_ev = ["waiting"];
		if ( /Chrom(e|ium)\/([0-2][0-9]|3[0-2])\./i.test(navigator["userAgent"]) ) {
			wait_ev.push("seeking");
		}
		this.addEventListener(wait_ev, function(e) {
			this.show_wait();
			//evhh5v_msg("WAIT SPINNER START: " + e.type);
		}, false);
		this.addEventListener(["seeked", "canplaythrough", "playing", "loadeddata", "ended"], function(e) {
			this.hide_wait();
			//evhh5v_msg("WAIT SPINNER STOP: " + e.type);
		}, false);
		this.addEventListener(["ended"], function(e) {
			if  ( this.evcnt !== undefined ) {
				for ( var k in this.evcnt ) {
					evhh5v_msg("EVENT count for '"+k+"': " + this.evcnt[k]);
					this.evcnt[k] = 0;
				}
			}
		}, false);

		this.addEventListener("play", function(e) {
			this.get_canvas_context();
			this.canvas_clear();
			this.has_been_played = true;
			this.stop_forced = false;
			this.playing = true;
			this.bar.hide_inibut();
			this.put_canvas_frame();
			this.bar.show_pauseico();
			this.bar.stopbtn_enab();
			this.showhideBar(this.doshowbar = false);
		}, false);

		this.addEventListener("pause", function(e) {
			this.end_canvas_frame();
			this.playing = false;
			this.bar.show_playico();
			this.bar.stopbtn_enab();
			this.hide_wait();

			// the stop() proc wants to replace the video
			// object, and pauses it first; but replacement
			// would occur before a pause event is delivered,
			// so it will place its work in a function object
			// assigned to this.stop_invoked_proc which is
			// called here as needed
			if ( this.stop_invoked_proc ) {
				var f = this.stop_invoked_proc;
				this.stop_invoked_proc = false;
				f.call(this);
			}
		}, false);

		this.addEventListener("playing", function(e) {
			this.playing = true;
			// repeat the next two for Opera which, after ended,
			// if played again, the icons are not updated, as if
			// play event is not sent
			this.bar.show_pauseico();
			this.bar.stopbtn_enab();
		}, false);
	
		this.addEventListener("suspend", function(e) {
			// this is complicated by odd behavior in chromium|webkit:
			// with progress and suspend coming in rapid succession, so
			// that the dl active effect is useless; try a timeout to
			// delay the inactivation.
			if ( ! this.susptimer ) {
				var that = this.bar;
				this.susptimer = setTimeout(function() {that.show_dl_inactive();}, 3000);
			}
		}, false);
		this.addEventListener("progress", function(e) {
			// see comment on "suspend"
			if ( this.susptimer ) {
				clearTimeout(this.susptimer); this.susptimer = false;
				var that = this.bar;
				this.susptimer = setTimeout(function() {that.show_dl_inactive();}, 3000);
			}
			this.bar.show_dl_active();
		}, false);

		this.addEventListener(["loadedmetadata", "loadeddata", "emptied"], function(e) {
			this.bar.show_dl_inactive();
		}, false);
		this.addEventListener(["loadedmetadata", "resize"], function(e) {
			if ( e.type === "loadedmetadata" ) {
				this.on_metadata();
				this.gotmetadata = true;
			} else if ( e.type === "resize" ) {
				// NOTE: the resize event is *not* for changed page
				// display size; it's for changed 'intrinsic' dimensions
				// videoWidth & videoHeight, e.g. change in src or even
				// within current src. I have no single source that
				// triggers this and as yet have not set up a test
				// with src changes, but it should work in principle
				// if this.setup_aspect_factors() has correct data
				// (*aspect and such will likely be invalid after a
				// src change; as yet this code does not support such).
				// Update: Chromium 24 beta and 35 unstable are
				// generating "resize" -- why?
				if ( false ) {
					evhh5v_msg("Got RESIZE: w == " + 
						this._vid.videoWidth + ", h == " +
						this._vid.videoHeight);
				}
			}
			// a little brute force display adjustment to metadata
			this.setup_aspect_factors();
			var h = this.height, w = this.width;
			this.height = h; this.width = w;
		}, false);
		this.addEventListener(["volumechange", "loadedmetadata", "loadeddata", "loadstart", "playing"], function(e) {
			var v = this._vid;
			if ( v.volume !== undefined ) {
				var pct = Math.max(0, Math.min(1, v.volume));
				this.bar.scale_volctl(pct);
			} else {
				this.bar.scale_volctl(1);
			}
		}, false);

		this.addEventListener(["ended", "error", "abort"], function(e) {
			if ( true || e.type !== "error" || this._vid.error ) {
				this.hide_wait();
			}
			if ( e.type === "error" && ! this._vid.error ) {
				// ffox (27) is raising this, with _vid.error === null,
				// on our resize after entering fullscreen,
				// probably in error. IAC, it is proving to be not only
				// non-fatal, but unnoticeable in playback and further
				// consequence; so, just return
				//evhh5v_msg("DBG event error : video.error === " + this._vid.error);
				return;
			} else if ( e.type !== "ended" ) {
				var t = this._vid.error;
				// without error indicator response cannot be
				// determined, so this this is guesswork subject
				// to revision
				if ( ! t ) {
					//evhh5v_msg("DBG error||abort: .error === "+t);
					return;
				}
				//evhh5v_msg("DBG error||abort: .error.code === "+t.code);
				// use try in case MediaError constants are undefined
				try {
					switch ( t.code ) {
						case MediaError.MEDIA_ERR_NETWORK:
							// notify and stop
							alert(
							"A network error stopped the media fetch; "
							+ "try again when the network is working"
							);
							// intentional fallthough
						case MediaError.MEDIA_ERR_ABORTED:
							// user requests abort, no notify
							var tv = this;
							// invoke stop() to get a clean state
							// that will allow retrying, use timeout
							// to avoid in-handler problems
							setTimeout(function(){tv.stop();}, 256);
							return;
						case MediaError.MEDIA_ERR_DECODE:
							alert(
							"A media decoding error occured. " +
							"Contact the web browser vendor or " +
							"the server administrator"
							);
							break;
						case MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED:
							alert(
							"The current media is not supported by " +
							"the browser's media player"
							);
							break;
						default:
							alert(
							"An unknown media player error occurred " +
							"with error code value " + t.code
							);
					}
				} catch ( ex ) {
					// do nothing, fall through, hope . . .
				}
			} else if ( e.type === "ended" ) {
				// different browsers. or different versions of the
				// same, are not consistent with the pause state on end.
				if ( ! this._vid.paused ) {
					this.pause();
				}
			}

			this.end_canvas_frame();
			this.playing = false;
			this.bar.stopbtn_disab();
			this.bar.show_playico();
			this.bar.progress_pl(1);
			this.bar.show_dl_inactive();
		}, false);

		var msevt = [
			"mouseover", "mouseout", "mousemove", "click",
			"touchstart", "touchend", "touchmove",
			"touchenter", "touchleave", "touchcancel"
		];
		this.addEventListener(msevt, function(e) {
			var te = ! (e.changedTouches === undefined);
			switch ( e.type ) {
				case "mouseover":
				case "touchenter":
					break;
				case "mouseout":
				case "touchleave":
					break;
				case "mousemove":
				case "touchmove":
					// because this might also be handle by parent div
					e.stopPropagation();

					if ( this.rekonq_mousebug ) {
						// the test above is due to a bug in the
						// rekonq browser (Ubuntu 12.04) in which
						// the mouse_hide() in the ticker function
						// causes a mousemove event! So, the ticker
						// sets this.rekonq_mousebug to a positive
						// and decrements it to 0, avoiding the
						// cycle. Sheesh.
						return;
					}

					var co;
					if ( te ) {
						e.preventDefault();
						co = this.mouse_coords(e.changedTouches[0]);
					} else {
						co = this.mouse_coords(e);
					}
		
					var x = co["x"], y = co["y"];

					var bwid = this.barshowmargin;
					var w = this.width - bwid;
					var h = this.height - bwid;
					if ( x > bwid && y > bwid && x < w && y < h ) {
						if ( this.doshowbar == false ) {
							this.showhideBar(this.doshowbar = true);
						}
					} else {
						if ( this.doshowbar == true ) {
							this.showhideBar(this.doshowbar = false);
						}
					}

					this.mouse_show();
					this.ptrtick = 0;

					break;
				case "click":
					// because this might also be handle by parent div
					e.stopPropagation();
					this.playpause();
					break;
				case "dblclick":
					break;
				default:
					evhh5v_msg("GOT MOUSE EVENT: " + e.type);
			}
		}, false);

		// keys: fortunately key control is not important, because
		// only ffox is delivering key events for video; and not
		// for canvas or div. Chromium and Opera yield no key events
		// on any of these elements. MSIE not tried yet.
		// Nevertheless [everthemore], add key handling for at least
		// ffox w/o canvas hack.
		var kbevt = [
			"keyup", "keydown"
		];
		this.addEventListener(kbevt, function(e) {
			e.stopPropagation();
			switch ( e.type ) {
				case "keydown":
					this.curkey = e.keyCode; // || e.which;
					break;
				case "keyup":
					if ( this.curkey == 32 ) {
						this.playpause();
					} else if ( this.curkey == 81 || this.curkey == 113 ) {
						// Q,q
						this.stop();
					} else if ( this.curkey == 70 || this.curkey == 102 ) {
						// F,f
						if ( this.allowfull ) {
							this.fullscreen();
						}
					} else if ( this.curkey == 71 || this.curkey == 103 ) {
						// G,g
						// was for debugging in the flash player ... maybe
						// implement later
						if ( this.dbg_key === undefined ) {
							this.dbg_key = false;
						}
						this.dbg_key = ! this.dbg_key;
					} else if ( this.curkey == 65 || this.curkey == 97 ) {
						// A,a
						// was for debugging stage pixel aspect in the
						// gnash player -- use for display aspect here
						if ( this.saved_aspect === undefined ) {
							this.saved_aspect = this.aspect;
						}
						this.aspect = this.aspect ? 0 : this.saved_aspect;
					} else if ( this.curkey == 86 || this.curkey == 118 ) {
						// V,v
						//this.volgadget._visible = ! this.volgadget._visible;
					} else if ( this.curkey == 60 || this.curkey == 62 ) {
						// <,>
						//incrVolumeAdjust(this.curkey == 60 ? -10 : 10);
					} else if ( this.curkey == 83 || this.curkey == 115 ) {
						// S,s
						// wait movie; for devel
						/*
						if ( this.wait._visible == true ) {
							this.stop_wait();
						} else {
							this.start_wait();
						}
						*/
					}
		
					// HACK: clear this.curkey: sometimes escape key
					// causes repeat of last keyUp event! [that was
					// from the flash player, but nulling is a good
					// idea anyway]
					this.curkey = null;
					break;
			}
		}, false);

		if ( ! newv ) {
			var that = this;
			// when using canvas, it gets the mouse,
			// w/o canvas, div might get mouse if video is not scaled
			var o = this.is_canvas ? this._cnv : this.auxdiv;
			// all
			var eva = msevt.concat(kbevt);
			for ( var s in eva ) {
				o.addEventListener(eva[s], function(e) {
					that.callbk.call(that._vid, e);
				}, false);
			}
		
			// use this point to start the ticker-timer
			this.mk_state_timer();
		}
	},

	// a timer to test states and adjust interface accordingly
	mk_state_timer : function() {
		if ( this.statetimer ) {
			return;
		}
		var that = this;
		this.statetimer = setInterval(function() {
			that.do_state_timer();
		}, this.tickinterval);
	},
	rm_state_timer : function() {
		if ( ! this.statetimer ) {
			return;
		}
		clearInterval(this.statetimer);
		this.statetimer = false;
	},
	// procedure for state timer
	do_state_timer : function() {
		if ( this.ntick++ === 2147483647 ) {
			// nova-flow
			this.ntick = 0;
		}
		
		if ( ! this.bar.volctl_mousedown ) {
			// this hack is due to a bug in the
			// rekonq browser (Ubuntu 12.04) in which
			// the mouse_hide() in the this function
			// causes a mousemove event! So, the ticker
			// sets this.rekonq_mousebug to a positive
			// and decrements it to 0, avoiding the
			// cycle. Sheesh.
			if ( this.rekonq_mousebug )
				this.rekonq_mousebug--;
	
			if ( ++this.ptrtick >= this.ptrtickmax ) {
				this.rekonq_mousebug = parseInt(this.ptrtickmax/10);
				this.mouse_hide();
				if ( this.doshowbartime ) {
					this.showhideBar(this.doshowbar = false);
				}
				this.ptrtick = 0;
			}
		} else {
			this.ptrtick = 0;
		}

		var prgupd = this.ntick & 1;
		
		if ( prgupd && ! (this._vid.paused || this._vid.ended) ) {
			this.play_progress_update();
		}

		if ( this.yshowpos > this.bar_y ) {
			this.bar_y = Math.min(this.bar_y + this.barshowincr, this.yshowpos);
			this.set_bar_y(this.bar_y);
			// when bar is fully hidden also hide sound volume gadget
			if ( this.yshowpos == this.bar_y ) {
				this.hide_volctl();
			}
		} else if ( this.yshowpos < this.bar_y ) {
			this.bar_y = Math.max(this.bar_y - this.barshowincr, this.yshowpos);
			this.set_bar_y(this.bar_y);
		}
	},

	// update play progress indication on the control bar
	play_progress_update : function() {
		// paranoia: .duration is init'd to NaN
		// before metadata is loaded, and Opera complains
		// on the log console, so be testingful here
		var t;
		if ( (t = this._vid.currentTime) == undefined || ! isFinite(t) ) return;
		var d;
		if ( (d = this._vid.duration) == undefined || ! isFinite(d) || d <= 0 ) return;
		this.bar.progress_pl(t / d);
		return;
	},

	// mouse diddlers
	// pointer show/hide, CSS or .style -- not working in Opera,
	// web-searching suggests cursor:none is simply not supported --
	// and testing with MSIE 11 shows it needs the .style assignment
	// rather than the CSS change via class.
	// Update: Opera on MSW is working.
	mouse_hide : function() {
		if ( ! this.mouse_hidden ) {
			this.mouse_hidden = true;
			this.v.setAttribute("class", this.mouse_hide_class);
			this.auxdiv.setAttribute("class", this.auxdivclass + " " + this.mouse_hide_class);
			this.v.style.cursor      = "none";
			this.auxdiv.style.cursor = "none";
		}
	},
	mouse_show : function() {
		if ( this.mouse_hidden ) {
			this.mouse_hidden = false;
			this.v.setAttribute("class", this.mouse_show_class);
			this.auxdiv.setAttribute("class", this.auxdivclass);
			this.v.style.cursor      = "default";
			this.auxdiv.style.cursor = "default";
		}
	},
	// vid-area-relative coords of mouse event
	mouse_coords : function(evt) {
		var r = this.auxdiv.getBoundingClientRect();
		var x = evt.clientX - r.left, y = evt.clientY - r.top;
		// yes, we're getting fractions (ffox && webkit)
		return {"x" : Math.round(x), "y" : Math.round(y)};
	},

	// control bar diddlers
	showhideBar : function(bshow) {
		var h = this.barheight;
		var show = this.height - h - this.barpadding;
		var hide = show + h + this.barpadding * 2;
		var p = bshow ? show : hide;
	
		if ( this.disablebar ) {
			this.yshowpos = hide;
			this.set_bar_y(hide);
			this.hide_volctl();
		} else if ( bshow && this.bar_y >= p ) {
			this.yshowpos = p;
		} else if ( ! bshow && this.bar_y <= p ) {
			this.yshowpos = p;
		}
	},
	set_bar_y : function(y) {
		var t = document.getElementById(this.ctlbar["ctlbardiv"]);
		t.style.top = y + "px";
	},

	// callback for play progress bar click -- delivered from
	// control bar -- dat is array [event, bar-length]
	prog_pl_click_cb : function(dat) {
		var t;
		if ( (t = this._vid.currentTime) == undefined || ! isFinite(t) )
			return;
		var d;
		if ( (d = this._vid.duration) == undefined || ! isFinite(d) || d <= 0 )
			return;

		// false because the SWF player  leaves it paused
		if ( false && this._vid.paused || this._vid.ended )
			this.play();
		
		// NOTE: the clientX here is relative to the bar's svg doc,
		// not this page document, and is therefore useful relative to
		// bar length; but, that assumes progress indicator left-end
		// position ~= svg document leftmost point -- obviously, that
		// holds true only per design and this might need revision
		// for other control bar designs.
		// TODO: address the above.
		var cx = dat[0].clientX;
		var cw = dat[1];
		t = d * (cx / cw);
		this._vid.currentTime = t;
		this.bar.progress_pl(t / d);
		if ( ! this.playing ) {
			this.put_canvas_frame_single_timeout();
			//this.put_canvas_frame_single();
		}
	},

	// std acts
	play : function() {
		this._vid.play();
	},
	pause : function() {
		this._vid.pause();
	},
	// toggle++ for play and pause
	playpause : function() {
		var video = this._vid;

		if ( video.ended ) {
			video.currentTime = 0;
			this.play();
		} else if ( video.paused ) {
			// TODO: consider making this an option, for more
			// aggresive DL once play() is started (probably,
			// according to whatwg, although useragent may
			// ultimately go freestylin')
			//video.setAttribute("preload", "auto");
			this.play();
		} else {
			this.pause();
		}
	},
	// the control bar has a stop button (as the SWF player does),
	// but H5 video does not have a similar method: to emulate the
	// SWF player, this *should* 1) stop playback 2) stop any media
	// network transfer 3) re-init so that play() again is as if it
	// was the first time -- do as much of this as possible and
	// make the behavior at least look the same.
	stop : function() {
		this.stop_forced = true;
		this.hide_wait();
		
		// put our work in a function object to be invoked in
		// pause event handler (with this as this)
		var proc = function() {

		// make new similar video
		var tv = document.createElement('video');
		var att = ["loop", "width", "height", "id", "class", "name"];
		var poster = this._vid.getAttribute("poster");
		while ( att.length ) {
			var tn = att.shift();
			var ta;
			if ( ! (ta = this._vid.getAttribute(tn)) ) continue;
			tv.setAttribute(tn, ta);
		}

		// before swfput 2.1 preload was always set to "none" but
		// it seems to be impossible to get the new video object
		// to display the poster if it is set to none, even though
		// the poster is displayed on original page parse even
		// when preload is none. So, try using "metadata" with which
		// the poster is displayed, and hope (probably vainly) that
		// it does not cause a net fetch due to caching, or that
		// at least fetch will be small and quick.
		tv.setAttribute("preload", this.gotmetadata?"metadata":"none");

		// copy nodes
		while ( this._vid.hasChildNodes() ) {
			var tn = this._vid.firstChild.cloneNode(true);
			tv.appendChild(tn);
			this._vid.removeChild(this._vid.firstChild);
		}

		// unload shotgun on old video
		this._vid.src = null;
		this._vid.removeAttribute("src");
		// spec says currentSrc readonly; ffox and webkit do not complain
		try { this._vid.currentSrc = null; } catch(e) {}
		// hopefully, w/ no source, this will stop current transfers;
		// per spec it should, and yes, it provides a way to stop
		// webkit from fetching the media
		this._vid.load();

		// hook up new video; ready for play()
		if ( ! this.is_canvas ) {
			this._vid.parentNode.replaceChild(tv, this._vid);
		}
		try { delete this._vid; } catch(e) {}

		this._vid = tv;
		this._vid.evhh5v_controller = this;
		this.setup_aspect_factors();
		this._obj_add_evt(this._vid);
		this.gotmetadata = this.playing = false;
		if ( poster ) {
			this._vid.setAttribute("poster", poster);
		}
		this.put_canvas_poster();

		// control bar maintenance
		this.bar.show_playico();
		this.bar.progress_pl(1);
		this.bar.stopbtn_disab();

		}; // end function object

		// if not paused or ended, pause calls the function,
		// but otherwise we call it here
		if ( this._vid.paused || this._vid.ended ) {
			proc.call(this);
		} else {
			this.stop_invoked_proc = proc;
			this._vid.pause();
		}
	},
	// the scale button: default is to display w/ scale to fit on
	// one or both ords; this allows vid to show at 'natural' size
	// (if it is in fact smaller than display area on both ords)
	do_scale : function() {
		this.doscale = ! this.doscale;
		this.setup_aspect_factors();
		this.put_canvas_frame_single();
		if ( this.doscale ) {
			this.bar.show_scalein();
		} else {
			this.bar.show_scaleout();
		}
	},
	// go fullscreen where possible
	fullscreen : function() {
		// possible?
		if ( ! (this.allowfull && evhh5v_fullscreen.capable()) ) {
			this.bar.blur_fullscreen();
			if ( this.allowfull ) {
				alert("Full screen mode is not available.");
			}
			return;
		}

		var p = this.auxdiv; // this gets fullscreen'd

		// toggle
		try {
			var el = evhh5v_fullscreen.element();
			if ( el == undefined ) {
				this.fs_dimstore = [this.height, this.width];
				var t = this;

				this.orig_fs_change_func =
					evhh5v_fullscreen.handle_change(function(evt) {
						// enter fullscreen
						if ( evhh5v_fullscreen.element() == p ) {
							t.in_fullscreen = true;
							t.fs_resize();
							t.bar.show_fullscreenin();
							return;
						}
						// leave fullscreen
						t.in_fullscreen = false;
						t.height = t.fs_dimstore[0];
						t.width  = t.fs_dimstore[1];
						evhh5v_fullscreen.handle_change(t.orig_fs_change_func);
						evhh5v_fullscreen.handle_error(t.orig_fs_error_func);
						t.orig_fs_change_func = null; // release reference
						t.orig_fs_error_func = null;
						t.bar.show_fullscreenout();
					});
				this.orig_fs_error_func =
					evhh5v_fullscreen.handle_error(function(evt) {
						evhh5v_fullscreen.handle_change(t.orig_fs_change_func);
						evhh5v_fullscreen.handle_error(t.orig_fs_error_func);
						t.orig_fs_change_func = null; // release reference
						t.orig_fs_error_func = null;
						alert("Full screen mode failed.");
					});

				evhh5v_fullscreen.request(p);
			} else if ( el == p ) {
				evhh5v_fullscreen.exit();
			}
		} catch ( ex ) {
			alert(ex.name + ': "' + ex.message + '"');
		}
	},
	volctl_showing: false,
	togglevolctl : function() {
		// delay pointer/bar hiding for volume interaction
		this.ptrtick = 0;

		if ( this.volctl_showing == undefined ) {
			this.volctl_showing = false;
		}

		if ( ! this.volctl_showing ) {
			this.show_volctl();
		} else {
			this.hide_volctl();
		}
	},
	show_volctl : function(bot) {
		if ( ! this.volctl_showing ) {
			if ( bot == undefined )
				bot = this.height - this.barheight - 3;
			this.volctl_showing = true;
			this.bar.show_volctl(bot, this.width);
		}
	},
	hide_volctl : function() {
		if ( this.volctl_showing === true ) {
			this.volctl_showing = false;
			this.bar.hide_volctl();
		}
	},
	bar_bg_click : function() {
		this.hide_volctl();
		// testing only
		if ( false ) {
			if ( ! this.wait_showing ) {
				this.show_wait();
			} else {
				this.hide_wait();
			}
		}
	},
	show_wait_ok : function() {
		if ( this.chrome_show_wait_bad === undefined ) {
			this.chrome_show_wait_bad =
				this.params['chromium_force_show_wait'] ?
				false : this.chrome_draw_bug;
		}
		if ( this.chrome_show_wait_bad ) {
			return false;
		}

		if ( this.wait_showing || this.stop_forced || ! this.has_been_played ) {
			return false;
		}
		
		return true;
	},
	show_wait : function() {
		if ( this.show_wait_ok() ) {
			this.wait_showing = true;
			var that = this;
			this.show_wait_handle = setTimeout(function() {
				if ( that.show_wait_handle !== false ) { // cancelled?
					that.bar.show_waitanim(that.width / 2, that.height / 2);
				}
				that.show_wait_handle = false;
			}, 125);
		}
	},
	hide_wait : function() {
		// Like show_wait(), use a timeout to hide. It seems that on
		// occasion an asynchronous event to cause hiding is delivered
		// so quickly that 'this.wait_showing = true;' (in show_wait())
		// has not executed yet, and so wait indicator is not cancelled,
		// because it does not test true.
		// Using a timeout should let the bool be set and the
		// test be valid; the period might need tuning.
		var that = this;
		setTimeout(function() {
			if ( that.wait_showing !== undefined && that.wait_showing ) {
				if ( that.show_wait_handle ) {
					clearTimeout(that.show_wait_handle);
					that.show_wait_handle = false;
				}
				that.bar.hide_waitanim();
				that.wait_showing = false;
			}
		}, 100);
	},
	show_wait_now : function() {
		if ( ! this.wait_showing && ! this.stop_forced && this.has_been_played ) {
			this.wait_showing = true;
			this.bar.show_waitanim(this.width / 2, this.height / 2);
		}
	},
	hide_wait_now : function() {
		if ( this.wait_showing !== undefined && this.wait_showing ) {
			this.bar.hide_waitanim();
			this.wait_showing = false;
		}
	},

	// handle control bar click per object.id -- obj is probably
	// a button on the control bar with expected id; but, no
	// other property than id should be used so that this method
	// may be invoked like foo.button_click({id: 'stop'});
	button_click : function(obj) {
		switch ( obj.id ) {
			case "playpause":
			case "inibut":
				this.playpause();
				break;
			case "stop":
				this.stop();
				break;
			case "doscale":
				this.do_scale();
				break;
			case "fullscreen":
				this.fullscreen();
				break;
			case "volume":
				this.togglevolctl();
				break;
			case "bgrect":
				this.bar_bg_click();
				break;
		}
	},

	// autonomous philosophical discharge:
	protoplasmaticism : true
};


/**********************************************************************\
 *                                                                    *
 * Support procedures and data structures for the SVG control bar     *
 *                                                                    *
\**********************************************************************/

var evhh5v_ctlbarmap = {};
var evhh5v_ctlbutmap = {};
var evhh5v_ctlvolmap = {};

// each loaded instance of the bar svg will call this from
// its own (child browsing context) script and pass the
// parameters it receives from the object (tag) that loaded it;
// this is how each is id'd and glued to associated objects
function evhh5v_put_ctlbarmap(parms) {
	if ( ! parms["parentdiv"] || ! parms["role"] ) {
		evhh5v_msg("evhh5v_put_ctlbarmap was passed a foul object: no parentdiv or role: " + parms);
		return; 
	}
	var map;
	switch ( parms["role"] ) {
		case "1st": map = evhh5v_ctlbutmap; break;
		case "vol": map = evhh5v_ctlvolmap; break;
		case "bar":
		default:	map = evhh5v_ctlbarmap; break;
	}
	map[parms["parentdiv"]] = parms;
	map[parms["parentdiv"]]["loaded"] = false;
};

// these evhh5v_*load procs are assigned to the inline
// 'onload' attributes of the *bar SVG <object>s primarily
// to set the 'loaded' property of the relevant data structure
// member; evhh5v_ctlbarload also constructs the evhh5v_controlbar
// object that builds and maintains the SVG elements
function evhh5v_ctlbarload(obj, divid) {
	var p = evhh5v_ctlbarmap[divid];
	p.evhh5v_controlbar = new evhh5v_controlbar(p);
	p.evhh5v_controlbar.resize_bar(p["barwidth"], p["barheight"]);
	p["loaded"] = true;
};

function evhh5v_ctlbutload(obj, divid) {
	evhh5v_ctlbutmap[divid]["loaded"] = true;
}

function evhh5v_ctlvolload(obj, divid) {
	evhh5v_ctlvolmap[divid]["loaded"] = true;
}


/**********************************************************************\
 *                                                                    *
 * Support procedures for all the above                               *
 *                                                                    *
\**********************************************************************/

// preferably do not add query string to svg url where
// it is known to be not needed (actually, I know only
// that MSIE *does* need it, but it is safer to be certain
// it is not needed) -- this function will test only once
// and return true if we are not confident the user agent
// does *not* need a query string for the svg parameters
function evhh5v_need_svg_query() {
	if ( document.evhh5v_need_svg_query_bool !== undefined ) {
		return document.evhh5v_need_svg_query_bool;
	}
	document.evhh5v_need_svg_query_bool = ( !
	/(FireFox|WebKit|KHTML|Chrom[ie]|Safari|OPR\/|Opera)/i.test(navigator["userAgent"])
	) == false;
	
	return document.evhh5v_need_svg_query_bool;
};

// is the browser, or 'user agent', mobile?
// [lifted from WordPress php]
function evhh5v_ua_is_mobile() {
	if ( document.evhh5v_ua_is_mobile_bool !== undefined ) {
		return document.evhh5v_ua_is_mobile_bool;
	}

	document.evhh5v_ua_is_mobile_bool = false;
	var ua = navigator["userAgent"];
	
	if (   ua.indexOf('Mobile') >= 0 // many mobile devices (all iPhone, iPad, etc.)
		|| ua.indexOf('Android') >= 0
		|| ua.indexOf('Silk/') >= 0
		|| ua.indexOf('Kindle') >= 0
		|| ua.indexOf('BlackBerry') >= 0
		|| ua.indexOf('Opera Mini') >= 0
		|| ua.indexOf('Opera Mobi') >= 0 ) {
		document.evhh5v_ua_is_mobile_bool = true;
	}

	return document.evhh5v_ua_is_mobile_bool;
};

// Unfortunately some browser brokenness must be handled under
// some circumstances; e.g., a not-current Opera under FreeBSD
// tested fine with all this until it was integrated with the
// the SWFPut WordPress plugin, in which context the outer <div>
// was not adjusting its height properly (seemingly confused that
// the first fallback element under the flash <object> was a <div>
// because in earlier versions of the plugin there was no problem:
// the div is the most obvious difference).
// So, corrective measures may be added here as needed.
function evhh5v_fixup_elements(parms) {
	var ip = parms["iparm"];

	if ( /Opera/i.test(navigator["userAgent"]) ) {
		var t = document.getElementById(ip["auxdiv"]);
		if ( t && t.parentNode.nodeName.toLowerCase() === "object" ) {
			var p = t.parentNode;
			var d = p.parentNode;
			p.removeChild(t);
			d.replaceChild(t, p);
		}
	}
}

// borrowed from:
// http://robertnyman.com/2006/04/24/get-the-rendered-style-of-an-element/
// get 'computed' style of element
var evhh5v_getstyle = function (el, sty) {
	var v = 0;
	if ( document.defaultView && document.defaultView.getComputedStyle ) {
		v = document.defaultView.getComputedStyle(el, "").getPropertyValue(sty);
	} else if ( el.currentStyle ) {
		sty = sty.replace(/\-(\w)/g, function (m1, p1) {
			return p1.toUpperCase();
		});
		v = el.currentStyle[sty];
	}
	return v;
};

// check browser for swflash support
var evhh5v_get_flashsupport = function (el, sty) {
	if ( document.evhh5v_get_flashsupport_found === undefined ) {
		if ( ! navigator.plugins["Shockwave Flash"] ) {
			document.evhh5v_get_flashsupport_found = false;
		} else {
			document.evhh5v_get_flashsupport_found = true;
		}
	}
	
	return document.evhh5v_get_flashsupport_found;
}

// use window.dump() to print messages if it is a function,
// else console.log(); console.log() is forced if 'cons' is
// defined and true -- messages are prefixed with "EVHMSG: "
// unsless pfx is defined in which case it is used
var evhh5v_msg_off = true;
var evhh5v_msg = function (msg, cons, pfx) {
	if ( evhh5v_msg_off ) {
		return;
	}

	var m = (pfx || "EVHMSG: ") + msg;
	
	if ( (cons !== undefined && cons)
	    || typeof window.dump !== 'function' ) {
		console.log(m);
	} else {
		window.dump(m + "\n");
	}
};

// EH: following horrible hack is from comp.lang.javascript FAQ,
// original name isDocumentElementHeightOff -- return true if
// document.body.clientHeight/Width should be used, false if
// document.documentElement.clientHeight/Width should be used --
// original comment follows:
// Used to feature test Opera returning wrong values 
// for documentElement.clientHeight. 
// The results of this function should be cached, 
// so it does not need to be called more than once.
var evhh5v_view_horrible_dim_hack_result = null;
var evhh5v_view_horrible_dim_hack = function () { 
	if ( evhh5v_view_horrible_dim_hack_result === null ) {
		var d = document.documentElement;
		if ( d && d.clientHeight === 0 ) {
			evhh5v_view_horrible_dim_hack_result = true;
		}
	}

	if ( evhh5v_view_horrible_dim_hack_result === null ) {
		var d = document,
		    div = d.createElement('div');
		div.style.height = "9000px";
		d.body.insertBefore(div, d.body.firstChild);
		evhh5v_view_horrible_dim_hack_result =
		    (d.documentElement.clientHeight > 8800);
		d.body.removeChild(div);
	}

	return evhh5v_view_horrible_dim_hack_result;
}

// get the view (or 'viewport'), i.e. *visible*, dimensions --
// this is a fine example of the insanity of browser incompatibilities
var evhh5v_view_dims = function () {
	var d = {};
	
	if ( typeof document.clientHeight === 'number' ) {
		d["width"]  = document.clientWidth;
		d["height"] = document.clientHeight;
	} else if ( evhh5v_view_horrible_dim_hack() ) {
		d["width"]  = document.body.clientWidth;
		d["height"] = document.body.clientHeight;
	} else {
		d["width"]  = document.documentElement.clientWidth;
		d["height"] = document.documentElement.clientHeight;
	}
	
	return d;
};
