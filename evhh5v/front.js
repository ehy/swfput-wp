//
//      This program is free software; you can redistribute it and/or modify
//      it under the terms of the GNU General Public License as published by
//      the Free Software Foundation; either version 2 of the License, or
//      (at your option) any later version.
//      
//      This program is distributed in the hope that it will be useful,
//      but WITHOUT ANY WARRANTY; without even the implied warranty of
//      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//      GNU General Public License for more details.
//      
//      You should have received a copy of the GNU General Public License
//      along with this program; if not, write to the Free Software
//      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//      MA 02110-1301, USA.
//

/**
 * For Wordpress public facing front end: optional JS presentation
 * of the flash player and alternate <video> and <img> -- meant
 * particularly for mobile device clients
 * 
 * Added evhh5v v. 1.0.7, 2014/01/26
 * (C) Ed Hynan 2014
 */

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

// build object element with its children and add to <div>
//
// dv is enclosing <div> id attribute,
// ob is flash <object> name,
// av is alt. <video> name,
// ai is alt. <img> name,
// att is json_encode'd attributes, etc.
/* Not needed in current implementation; let this remain temporarily
var evhh5v_builder = function(dv, ob, av, ai, att) {
	this.oname = ob;
	this.va_oname = av;
	this.ia_oname = ai;
	this.d = document.getElementById(dv);
	this.a = att;
	this.o = this.va_o = this.ia_o = false;
	this.pad = 0;
	if ( this.d ) {
		var p = this._style(this.d, "padding-left");
		if ( p )
			this.pad = Math.max(this.pad, parseInt(p));
		this.wdiv = this.d.offsetWidth;
		this.hdiv = this.d.offsetHeight;
		this.mk(this.d);
	} else {
		this.wdiv = null;
		this.hdiv = null;
	}
};
evhh5v_builder.prototype = {
	_style : function (el, sty) {
		return evhh5v_getstyle(el, sty);
	},
	_getdims : function (w, h) {
		var p2 = this.pad * 2;
		var tw = this.wdiv - p2;
		var r = tw / w;
		return [tw, Math.round(h * r)];
	},
	mk_aimg : function (a) {
		if ( a == undefined || a == "" ) {
			return false;
		}
		var o = document.createElement('img');
		if ( o ) {
			var dm = this._getdims(a["width"], a["height"]);
			o.id = a["id"];
			o.alt = a["alt"];
			o.style.marginLeft = o.style.marginRight = "auto";
			this.ia_o = o;

			o._swfo = this;
			if ( o.attachEvent ) { // MSIE 8?
				o.attachEvent("onload", this._imgsz);
			} else {
				o.addEventListener("load", this._imgsz, true);
			}
			o._swfwidth = dm[0]; o._swfheight = dm[1]; // see handler
			o.src = a["src"];
		}
		return this.ia_o;
	},
	_imgsz : function () {
		// members added here are used in evhh5v_sizer
		var that = this._swfo;
		var o = that.ia_o;
		if ( o.naturalWidth == undefined || o.naturalHeight == undefined ) {
			o.naturalWidth = o.width;
			o.naturalHeight = o.height;
		}
		o._ratio_user = o._swfwidth / o._swfheight;
		o._ratio_img  = o.width / o.height;
		o.width = o._swfwidth;
		o.height = o._swfheight;
		if ( o.complete == undefined ) {
			o.complete = true;
		}
	},
	mk_avid : function (a) {
		if ( a == undefined || a == "" ) {
			return false;
		}
		var o = document.createElement('video');
		if ( o ) {
			var dm =
			  this._getdims(a["width"], a["height"]);
			o.id = a["id"];
			o.width = dm[0]; o.height = dm[1];
			o.poster = a["poster"];
			if ( a["controls"] == "true" )
				o.controls = "true";
			o.preload = a["preload"];
			if ( a["autoplay"] == "true" )
				o.autoplay = "true";
			if ( a["loop"] == "true" )
				o.loop = "true";
			// try sources, prefer "probably" . . .
			var got = false;
			for ( var i = 0; i < a["srcs"].length; i++ ) {
				var t = a["srcs"][i];
				if ( t["type"] != "" && o.canPlayType(t["type"]) == "probably" ) {
					o.type = t["type"];
					o.src = t["src"];
					got = true;
					break;
				}
			}
			// . . . but accept "maybe"
			if ( got == false ) {
				for ( var i = 0; i < a["srcs"].length; i++ ) {
					var t = a["srcs"][i];
					if ( t["type"] != "" && o.canPlayType(t["type"]) == "maybe" ) {
						o.type = t["type"];
						o.src = t["src"];
						got = true;
						break;
					}
				}
			}
			// still false; maybe types were absent, but src
			// URL's OK? IAC, append <source>s and give the
			// video object a chance to do what it might
			if ( got == false ) {
				for ( var i = 0; i < a["srcs"].length; i++ ) {
					var t = a["srcs"][i];
					if ( t["type"] != "" ) {
						continue; // mime types were tested above
					}
					var so = document.createElement('source');
					if ( so ) {
						//so.type = t["type"];
						so.src = t["src"];
						o.appendChild(so);
						got = true;
					}
				}
			}
			if ( got == true ) {
				this.va_o = o;
			}
		}
		return this.va_o;
	},
	mk_obj : function (a) {
		if ( a == undefined || a == "" ) {
			return false;
		}
		var o = document.createElement('object');
		if ( o ) {
			var dm = this._getdims(a["width"], a["height"]);
			o.id = a["id"];
			o.width = dm[0]; o.height = dm[1];
			if ( a["ie"] == "true" ) {
				o.classid = a["classid"];
				o.codebase = a["codebase"];
			} else {
				o.data = a["data"];
				o.type = a["type"];
			}
			for ( var i = 0; i < a["parm"].length; i++ ) {
				var p = document.createElement('param');
				if ( p ) {
					var t = a["parm"][i];
					p.name = t["name"];
					p.value = t["value"];
					o.appendChild(p);
				}
			}
			this.o = o;
		}
		return this.o;
	},
	mk : function (dv) {
		var oi = this.mk_aimg(this.a["a_img"]);
		var ov = this.mk_avid(this.a["a_vid"]);
		var oo = this.mk_obj(this.a["obj"]);

		if ( ov ) {
			if ( oi ) {
				ov.appendChild(oi);
			} else {
				var p = document.createElement('p');
				if ( p ) {
					p.innerHTML = this.a["a_vid"]["altmsg"];
					ov.appendChild(p);
				}
			}
		}

		if ( oo ) {
			if ( ov ) {
				oo.appendChild(ov);
			} else if ( oi ) {
				oo.appendChild(oi);
			}
			dv.insertBefore(oo, dv.firstChild);
		} else if ( ov ) {
			dv.insertBefore(ov, dv.firstChild);
		} else if ( oi ) {
			dv.insertBefore(oi, dv.firstChild);
		} else {
			var p = document.createElement('p');
			if ( p ) {
				p.innerHTML = "video objects creation failed";
				dv.insertBefore(p, dv.firstChild);
			}
		}
	}
};
*/

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

// build and add to DOM the elements needed for the svg control bar
// of the html5 video player; arg is a map[*] of map,
// ready for literal use. [* map as in assoc. array]
function evhh5v_controlbar_elements(parms) {
	var ip = parms["iparm"];
	var num = ip["uniq"];
	var ivid = ip["vidid"];

	// <video> we're associated with must be OK, or it's all pointless
	var vidobj = document.getElementById(ivid);
	if ( ! vidobj ) {
		return;
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
	var op = parms["oparm"];
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

	var p, v, sep = "?", q = "";

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
	alldiv.appendChild(bardiv);

	// main bar svg done, now for the additional elements:

	// initial button
	url = ip["buturl"];

	bardiv = document.createElement('div');
	bardiv.setAttribute('id', "b_" + op["uniq"]["ctlbardiv"]);
	bardiv.setAttribute('class', ip["divclass"]);

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

	bardiv.appendChild(barobj);
	alldiv.appendChild(bardiv);

	// volume slide control
	url = ip["volurl"];

	bardiv = document.createElement('div');
	bardiv.setAttribute('id', "v_" + op["uniq"]["ctlbardiv"]);
	bardiv.setAttribute('class', ip["divclass"]);

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

	bardiv.appendChild(barobj);
	alldiv.appendChild(bardiv);
};

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
if ( window.addEventListener ) {
	var f = function (e) { evhh5v_sizer_event_relay(e.type == "load" ? true : false); };
	document.addEventListener("load", f, true);
	window.addEventListener("load",   f, true);
	window.addEventListener("resize", f, true);
} else {
	evhh5v_video_onlddpre = document.onload;
	evhh5v_video_onldwpre = window.onload;
	evhh5v_video_onszwpre = window.onresize;
	document.onload = function () {
		if ( typeof evhh5v_video_onlddpre === 'function' ) {
			evhh5v_video_onlddpre();
		}
		evhh5v_sizer_event_relay(true);
	};
	window.onload = function () {
		if ( typeof evhh5v_video_onldwpre === 'function' ) {
			evhh5v_video_onldwpre();
		}
		evhh5v_sizer_event_relay(true);
	};
	window.onresize = function () {
		if ( typeof evhh5v_video_onszwpre === 'function' ) {
			evhh5v_video_onszwpre();
		}
		evhh5v_sizer_event_relay(false);
	};
}

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
	this.pad = 0;
	this.wdiv = null;
	this.bld = null;
	this.inresize = 0;
	if ( bld ) {
		this.bld  = bld;
		this.d    = bld.d;
		this.o    = bld.o;
		this.va_o = bld.va_o;
		this.ia_o = bld.ia_o;
		this.pad  = bld.pad;
		this.wdiv = bld.wdiv;
	} else {
		this.d    = document.getElementById(dv);
		if ( this.d ) {
			this.o    = document.getElementById(ob);
			this.va_o = document.getElementById(av);
			this.ia_o = document.getElementById(ai);
			var p = this._style(this.d, "padding-left");
			if ( p )
				this.pad = Math.max(this.pad, parseInt(p));
			this.wdiv = this.d.offsetWidth;
		}
	}
	if ( this.d ) {
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
	}
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
	add_ctlbar : function(bar) {
		if ( this.va_o instanceof evhh5v_controller ) {
			return; // done
		}
		if ( ! bar ) {
			console.log("BAD CTLBAR == " + bar);
			return;
		}
		this.ctlbar = bar;
		this.va_o = new evhh5v_controller(this.va_o, bar, 0);
		this.va_o.mk();
	},
	_style : function (el, sty) {
		return evhh5v_getstyle(el, sty);
	},
	handle_resize : function () {
		if ( this.d === null )
			return;
		if ( this.inresize != 0 )
			return;
		var dv = this.d;
		var wo = this.wdiv;
		var wn = dv.offsetWidth;
		if ( wn == wo )
			return;
		this.wdiv = wn;
		var p = this._style(dv, "padding-left");
		if ( p ) {
			this.pad = parseInt(p);
		}
		this.resize();
	},
	_int_rsz : function (o) {
		var wd = this.wdiv;
		if ( wd == null )
			return;
		wd -= this.pad * 2;
		var wo = o.width;
		if ( (wd - wo) == 0 )
			return;
		var r = wo / o.height;
		o.height = o.pixelHeight = Math.round(wd / r);
		o.width = o.pixelWidth = wd;
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
		if ( wd == null )
			return;
		wd -= this.pad * 2;
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
		if ( ! this.d )
			return;
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

// helper to use fullscreen where available
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
	// invoke ~= requestFullscreen; takes element or uses document
	request : function(elm) {
		var el = elm === undefined ? document : elm;
		el[this.map_val("request")]();
	},
	// invoke ~= exitFullscreen
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
	// private methods
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

	// private members
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


/**
 * svg-based control bar for HTML5 video
 */


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
deg2rad : function(a) {
	return a * Math.PI / 180.0;
},
rad2deg : function(a) {
	return a / (Math.PI / 180.0);
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

	// data object for spinner animation
	this.wait_anim_dat = {};
	var adat = this.wait_anim_dat;
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
	//t = this.mk_circle("ico_clearbg", "but_clearbg", "50%", "50%", r, doc);
	t = this.mk_circle("ico_transbg", "but_clearbg", "50%", "50%", r, doc);
	btn.appendChild(t);
	this.but_clearbg = t;
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

	// relay event handler
	var that = this;
	var hdl = function(e) {
		var t = this;
		return that.hdl_volctl(e, t);
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
	this.vol_bgarea = t;

	var bx = horz ? volbarwid : volbarwid / 2.0;
	var by = horz ? volbarwid / 2.0 : volbarwid;
	var bw = horz ? volbarlen : volbarwid;
	var bh = horz ? volbarwid : volbarlen;
	t = this.mk_rect("bgslide", "vol_bgslide", bx, by, bw, bh, doc);
	t.addEventListener("click", hdl, false);
	t.addEventListener("wheel", hdl, false);
	// do not do "touchstart","touchend" -- whacks android browser(?) --
	// get by with "touchmove" alone
	t.addEventListener("touchmove", hdl, false);
	btn.appendChild(t);
	this.vol_bgslide = t;

	t = this.mk_rect("fgslide", "vol_fgslide", bx, by, bw, bh, doc);
	t.addEventListener("click", hdl, false);
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
// x position offset factors for buttons
xfacts : [0.5, 1.5, 2, 1.5, 2],

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
	if ( evhh5v_ctlbutmap[k] === undefined || evhh5v_ctlbutmap[k]["loaded"] !== true ) {
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

	return true;
},

mk : function() {
	var mobi = evhh5v_ua_is_mobile();
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

	svg.setAttribute("viewBox", this.viewbox);
	this.gall = doc.getElementById("g_all_g");

	//background rect
	var gbg = doc.getElementById("ctlbar_bg");
	this.bgrect = this.mk_bgrect(gbg);

	// buttons
	var gbn = doc.getElementById("g_button_1");
	this.button_play = this.mk_playpause(gbn, ofs += this.xfacts[0]);
	this.button_stop = this.mk_stop(gbn, ofs += this.xfacts[1]);
	this.stopbtn_disab();
	if ( mnot ) { // neither scale nor fullscreen for mobile
		this.button_doscale = this.mk_doscale(gbn, ofs += this.xfacts[2]);
		this.show_scalein();
		this.blur_doscale();
		this.button_fullscreen = this.mk_fullscreen(gbn, ofs += this.xfacts[3]);
		this.show_fullscreenout();
		this.blur_fullscreen();
	} else {
		this.button_doscale = this.button_fullscreen = false;
	}
	this.button_volume = this.mk_volume(gbn, ofs += this.xfacts[4]);

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

		this.wait_anim_dat.start();

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

		var svg = this.b_parms.root_svg;
		svg.setAttribute("visibility", "hidden");
		wa.setAttribute("visibility", "hidden");
		this.wait_group.setAttribute("visibility", "hidden");

		var d = document.getElementById(this.b_parms["ctlbardiv"]);
		d.style.left = "" + (-w) + "px";
		d.style.top  = "0px";

		this.wait_anim_dat.stop();

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
		this.but_clearbg.setAttribute("visibility", "hidden");
		this.but_circle.setAttribute("visibility", "hidden");
		this.but_arrow.setAttribute("visibility", "hidden");

		var d = document.getElementById(this.b_parms["ctlbardiv"]);
		d.style.left = "" + (-w) + "px";
		d.style.top  = "0px";

		return true;
	},

	// show volume slider with bottom at bottom, over vol button
	show_volctl : function(bottom) {
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
			this.volctl.height.baseVal.SVG_LENGTHTYPE_PX);
		var bw = this.button_volume.width.baseVal.valueInSpecifiedUnits;

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
		x  += bw - this.vol_width;

		var d = document.getElementById(this.v_parms["ctlbardiv"]);
		var l = x;// + ();
		var t = bottom - this.vol_height;
		d.style.left = "" + l + "px";
		d.style.top  = "" + t + "px";

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
			v = fh - cur;
			this.vol_touchstart = t;
		} else if ( horz ) { // click
			v = e.clientX - bt;
		} else { // click
			v = bh - (e.clientY - bt);
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
		//console.log("NEWLEN " + w + ", NEW BAR LEN " + pnw);
		// temp workaround until length calc is fixed:
		this._pl_len = w;
	
		this.svg.setAttribute("viewBox", this.viewbox);
	
		for ( var i = 0; i < this.rszo.length; i++ ) {
			var t = this.rszo[i].id == "bgrect" ? nw : pnw;
			this.rszo[i].setAttribute("width", t);
		}
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

var evhh5v_controller = function(vid, ctlbar, pad) {
	vid.removeAttribute("controls"); // should be done, but be sure
	this._vid = vid;
	this.ctlbar = ctlbar;
	this.bar = ctlbar.evhh5v_controlbar;
	this.pad = pad;
	this.handlermap = {};

	this.auxdiv = document.getElementById(this.ctlbar["auxdiv"]);
	this.bardiv = document.getElementById(this.ctlbar["ctlbardiv"]);
	this.div_bg_clr = evhh5v_getstyle(this.auxdiv, 'background-color');
	this.auxdivclass = this.auxdiv.getAttribute("class");

	this.tickinterval_divisor = 1000 / this.tickinterval;
	this.ptrtickmax = this.tickinterval_divisor * this.ptrinterval;
	this.ptrtick = 0;
	this.doshowbartime = false; //true;
	this.doshowbar     = true;

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
	barshowincr : 1,
	barshowmargin : 2,
	mouse_hide_class : "evhh5v_mouseptr_hidden",
	mouse_show_class : "evhh5v_mouseptr_normal",
	mk : function() {
		this.v.evhh5v_controller = this;
		this.ctlbar.evhh5v_controller = this;
		this.height = this.v.height;
		this.width = this.v.width;
	
		if ( evhh5v_fullscreen_ok() ) {
			this.bar.unblur_fullscreen();
		} else {
			this.bar.blur_fullscreen();
		}

		this.set_bar_y(this.bar_y);

		// set use of canvas as needed; see comment at func def
		this.setup_canvas();

		this.bar.set_bar_visibility("visible");

		this.install_handlers();

		// initial play button: this continues with a recursive
		// timer, which will adjust for size changes albeit with
		// a lag, until play() has been done once (and is then
		// never seen again)
		var that = this;
		var f = function() {
			if ( that.has_been_played ) {
				that.bar.hide_inibut();
				return;
			}
			that.bar.show_inibut(that.width / 2, that.height / 2);
			setTimeout(f, 1000);
		};
		f();

		this.bar.controller_handle_volume = function(pct) {
			if ( ! isFinite(pct) ) {
				return;
			}
			var v = that._vid;
			if ( v.volume !== undefined ) {
				pct = Math.max(0, Math.min(1, pct));
				v.volume = pct;
				// no, use volumechanged handler
				//that.bar.scale_volctl(pct);
			}
		}
		this.bar.scale_volctl(1);
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
		if ( this.aspect <= 0 || Math.abs(this.aspect - this.width / this.height) < this.aspect_min ) {
			return;
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
			//this._cnv_poster = new Image();
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
		if ( ! this.playing && this.is_canvas && isFinite(this._vid.currentTime) && this._vid.currentTime > 0 ) {
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
	// canvas hack is all about display aspect so figure it out and
	// store the factors and offsets on the video object
	setup_aspect_factors : function() {
		var cw = this.width, ch = this.height;
		if ( ! this.gotmetadata ) {
			// width & height setters call this, so do the setting
			this.v.width = cw;
			this.v.height = ch;
			return;
		}

		this.origaspect = cw / ch;
		var video = this._vid;
		var vw = video.videoWidth;
		var vh = video.videoHeight;
		var aspectW =
			(( this.aspect <= 0 || Math.abs(this.aspect - this.width / this.height) < this.aspect_min )
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
	/*
	put_canvas_frame : function() {
		if ( ! this.is_canvas || this._vid.paused || this._vid.ended ) {
			return;
		}
		
		this._ctx.drawImage(this._vid, this._x, this._y, this._width, this._height);
		
		var that = this;
		// the timeout takes arg is ms., so ideally an arg of 33 would
		// get ~30 fps, but of course timers do not necessarily
		// deliver on time; therefore the rate is increased to
		// reduce the apparent 'dropped frames' -- a price is
		// paid in cpu load, but this does not seem too bad on
		// modern hardware -- an Intel quad core circa 2009 shows,
		// by merely casual observation, mind you, roughly 10-15
		// pct[*] more on one core compared to straight H5 video with
		// arg 16 (~60 fps); simply using arg 33 the load is not
		// noticeably greater, but playback is noticeably jerky
		// [*] this varies widely with browsers and conditions and
		// only gives the most vague idea
		// TODO: allow user control by parameter
		this.frame_timer = setTimeout(function () {
			that.put_canvas_frame();
		}, this.canvas_frame_timeout);
	},
	end_canvas_frame : function() {
		if ( ! this.frame_timer ) return;
		clearTimeout(this.frame_timer);
		this.frame_timer = false;
	},
	 */
	canvas_frame_timeout : 16,
	// put a *single* frame on the canvas, e.g. when trying to get
	// poster to appear
	put_canvas_frame_single : function() {
		var ctx;
		if ( this.is_canvas && (ctx = this.get_canvas_context()) ) {
			ctx.drawImage(this._vid, this._x, this._y, this._width, this._height);
		}
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
		var diff = v - this.height;
		this.set_height = v;
		var bh = this.barheight;
		var t;
		t = document.getElementById(this.ctlbar["parent"]);
		t.style.height = bh + "px";
		t = this.auxdiv;
		t.style.left = "0px"; t.style.top = "0px"; t.style.height = "" + v + "px";

		this.setup_aspect_factors(); this.put_canvas_poster();
		t = this.bardiv;
		t.style.height = bh + "px";
		this.bar_y += diff; this.yshowpos += diff;
		t.style.top = this.bar_y + "px";
		t.style.left = this.pad + "px";
	},
	// these are for the resizing JS that handles this; no effect
	get pixelWidth() { return this.v.pixelWidth; }, set pixelWidth(v) { this.v.pixelWidth = v; },
	get pixelHeight() { return this.v.pixelHeight; }, set pixelHeight(v) { this.v.pixelHeight = v; },

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
		if ( that.handlermap[ename] != undefined ) {
			for ( var i = 0; i < that.handlermap[ename].length; i++ ) {
				if ( typeof that.handlermap[ename][i] == "function" ) {
					that.handlermap[ename][i].call(that, evt);
					//console.log("DISPATCHED event: " + ename);
				}
			}
		}
	},
	// event handler installer
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
	// add event handler call: filter events, use internal handler
	// take one event name, or array of names and install for each
	addEventListener : function(ename, callbk, bubool) {
		var t;
		if ( typeof ename === "string" ) {
			t = [ename];
		} else if ( ename instanceof Array ) {
			t = ename.slice(0);
		}
		for ( var i = 0; i < t.length; i++ ) {
			switch ( t[i] ) {
				// video
				case "loadstart":
				case "progress":
				case "suspend":
				case "abort":
				case "error":
				case "emptied":
				case "stalled":
				case "loadedmetadata":
				case "loadeddata":
				case "canplay":
				case "canplaythrough":
				case "playing":
				case "waiting":
				case "seeking":
				case "seeked":
				case "ended":
				case "durationchange":
				case "timeupdate":
				case "play":
				case "pause":
				case "ratechange":
				case "resize":
				case "volumechange":
				// others:
				case "click":
				case "touchstart":
				case "touchend":
				case "touchmove":
				case "touchenter":
				case "touchleave":
				case "touchcancel":
				case "mouseover":
				case "mouseout":
				case "mousemove":
				case "keyup":
				case "keydown":
					this.add_evt(t[i], callbk, bubool);
					break;
				default:
					console.log('evhh5v_controller: unexpected event added: "' + ename + '"');
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

		// set that hopefully works for the wait indicator
		this.addEventListener(["stalled", "waiting", "seeking"], function(e) {
			this.show_wait();
			//console.log("WAIT SPINNER START: " + e.type);
		}, false);
		this.addEventListener(["canplaythrough", "canplay", "playing", "seeked", "loadeddata", "ended"], function(e) {
			this.hide_wait();
			//console.log("WAIT SPINNER STOP: " + e.type);
		}, false);

		this.addEventListener("play", function(e) {
			this.playing = true;
			this.get_canvas_context();
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
			this.showhideBar(this.doshowbar = true);
			this.hide_wait();
		}, false);

		this.addEventListener("playing", function(e) {
			this.playing = true;
			// repeat the next two for Opera which, after ended,
			// if played again, the icons are not updated, as if
			// play event is not set
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
		this.addEventListener("loadedmetadata", function(e) {
			this.gotmetadata = true;
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
				console.log("DBG event error : video.error === " + this._vid.error);
				return;
			} else if ( e.type !== "ended" ) {
				var t = this._vid.error;
				// without error indicator response cannot be
				// determined, so this this is guesswork subject
				// to revision
				if ( ! t ) {
					console.log("DBG error||abort: .error === "+t);
					return;
				}
				console.log("DBG error||abort: .error.code === "+t.code);
				// use try in case MediaError constants are undefined
				try {
					switch ( t.code ) {
						case MediaError.MEDIA_ERR_NETWORK:
							// notify and stop
							alert(
							"An unknown network error stopped the media " +
							"fetch; try again when network is working"
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
							"An  error occured in media playback, " +
							"kindly contact browser vendor or " +
							"server administrator"
							);
							break;
						case MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED:
							alert(
							"The current media is not supported by " +
							"the browser's player"
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
			}
			this.end_canvas_frame();
			this.playing = false;
			this.bar.stopbtn_disab();
			this.bar.show_playico();
			this.bar.progress_pl(1);
			this.bar.show_dl_inactive();
			this.showhideBar(this.doshowbar = true);
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

					if ( this.webkit_mousebug1 ) {
						// the test above is due to a bug in the
						// rekonq browser (Ubuntu 12.04) in which
						// the mouse_hide() in the ticker function
						// causes a mousemove event! So, the ticker
						// sets this.webkit_mousebug1 to a positive
						// and decrements it to 0, avoiding the
						// cycle. Sheesh.
						return;
					}

					var co;
					if ( te ) {
						e.preventDefault();
						//evhh5v_do_dbg_obj(e.changedTouches[0]);
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

					/* if ( volgadget.vbarbut.mousedown ) {
						volgadget.vbarbut.onMouseMove();
					} */
					break;
				case "click":
					// because this might also be handle by parent div
					e.stopPropagation();
					this.playpause();
					break;
				case "dblclick":
					break;
				default:
					console.log("GOT MOUSE EVENT: " + e.type);
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
						this.fullscreen();
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
		if ( this.ntick++ == 2147483647 ) {
			// nova-flow
			this.ntick = 0;
		}
		
		// this hack is due to a bug in the
		// rekonq browser (Ubuntu 12.04) in which
		// the mouse_hide() in the this function
		// causes a mousemove event! So, the ticker
		// sets this.webkit_mousebug1 to a positive
		// and decrements it to 0, avoiding the
		// cycle. Sheesh.
		if ( this.webkit_mousebug1 )
			this.webkit_mousebug1--;

		if ( ++this.ptrtick >= this.ptrtickmax ) {
			this.webkit_mousebug1 = parseInt(this.ptrtickmax/10);//Oy!
			this.mouse_hide();
			if ( this.doshowbartime ) {
				this.showhideBar(this.doshowbar = false);
			}
			this.ptrtick = 0;
		}

		var intrvl2 = this.ntick & 1;
		
		if ( intrvl2 && ! (this._vid.paused || this._vid.ended) ) {
			this.play_progress_update();
		}

		if ( this.yshowpos > this.bar_y ) {
			this.bar_y = Math.min(this.bar_y + this.barshowincr, this.yshowpos);
			this.set_bar_y(this.bar_y);
			// when bar is fully hidden also hide sound volume gadget
			if ( this.yshowpos == this.bar_y ) {
				//volgadget._visible = false;
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
	
		if ( bshow && this.bar_y >= p ) {
			this.yshowpos = p;
		} else if ( ! bshow && this.bar_y <= p ) {
			this.yshowpos = p;
		}
		
		//if ( ! bshow )
		//	this.hide_volctl();
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
			this.put_canvas_frame_single();
		}
	},

	// std acts
	play : function() {
		this.has_been_played = true;
		this.stop_forced = false;
		this.bar.hide_inibut();
		this.canvas_clear();
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
	// make the behavior at least look the same [after trying some
	// simple things, and some insane things, it seems simple
	// load() does the trick; the name doesn't suggest it would,
	// but more slogging through the w3/whatwg spec did! No no no,
	// while load alone worked for wobkit, behavior is different
	// in ffox (of course), so discharge both barrels anyway]
	stop : function() {
		this.stop_forced = true;
		this.hide_wait();
		/* these two were tried, but N.G. overall -- left
		 * in place temporarily for reference 
		// some per browser hacks seem to work
		if ( false && this._vid.mozHasAudio !== undefined ) {
			// this is not reliable: disabled
			this._vid.pause();
			this._vid.src = "";
			this._vid.removeAttribute("src");
		} else if ( false && this._vid.webkitDecodedFrameCount !== undefined ) {
			this._vid.load(); // stops webkit download, causes ffox download; wonderful!
			evhh5v_do_dbg_obj(["WEBKIT -- SRC REMOVED"]);
		// but generally, since the spec does not provide a sensible
		// way, a method of madness + shotgun must be employed:
		} else {
		*/
		if ( true ) {
			this._vid.pause();
	
			// make new similar video
			var tv = document.createElement('video');
			var att = ["poster", "loop", "width", "height", "id", "class", "name"];
			while ( att.length ) {
				var tn = att.shift();
				var ta;
				if ( ! (ta = this._vid.getAttribute(tn)) ) continue;
				tv.setAttribute(tn, ta);
			}
			// regardless of original value, it seems that after user
			// hits stop, the only reasonable 'preload' is 'none' --
			// and obviously 'autostart' is not wanted
			tv.setAttribute("preload", "none");
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
			this.install_handlers(true);
		}

		this.gotmetadata = this.playing = false;
		this.put_canvas_poster();
		// control bar maintenance
		this.bar.show_playico();
		this.bar.progress_pl(1);
		// simple call to disable stop button is ineffective (ffox),
		// probably because we're within the click handler of that
		// same button; so, do it with a timeout
		var that = this;
		setTimeout(function() { that.bar.stopbtn_disab(); }, 256);
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
		if ( ! evhh5v_fullscreen.capable() ) {
			this.bar.blur_fullscreen();
			alert("As it turns out, full screen mode is not available. Sigh.");
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
						alert("Full screen mode failed for an unknown reason.");
					});

				evhh5v_fullscreen.request(p);
			} else if ( el == p ) {
				evhh5v_fullscreen.exit();
			}
		} catch ( ex ) {
			alert(ex.name + ": '" + ex.message + "'");
		}
	},
	togglevolctl : function() {
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
			this.bar.show_volctl(bot);
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
	show_wait : function() {
		// Unfortunate set of conditions resulting from from misc.
		// lame-brainednesses; e.g., "stalled" event issued on
		// page load before video has been play()ed
		if ( ! this.wait_showing && ! this.stop_forced && this.has_been_played ) {
			var that = this;
			this.show_wait_handle = setTimeout(function() {
				that.bar.show_waitanim(that.width / 2, that.height / 2);
				that.show_wait_handle = false;
			}, 500);
			this.wait_showing = true;
		}
	},
	hide_wait : function() {
		if ( this.wait_showing !== undefined && this.wait_showing ) {
			if ( this.show_wait_handle ) {
				clearTimeout(this.show_wait_handle);
				this.show_wait_handle = false;
			}
			this.bar.hide_waitanim();
			this.wait_showing = false;
		}
	},

	// handle control bar click per object.id
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

/* each loaded instance of the bar svg will call this from
 * its own script and pass the parameters it receives from
 * the object (tag) that loaded it; this is how each is id'd
 * and glued to associated objects
 */
var evhh5v_ctlbarmap = {};
var evhh5v_ctlbutmap = {};
var evhh5v_ctlvolmap = {};

function evhh5v_put_ctlbarmap(parms) {
	if ( ! parms["parentdiv"] || ! parms["role"] ) {
		console.log("evhh5v_put_ctlbarmap was passed a foul object: no parentdiv or role: " + parms);
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


function evhh5v_do_dbg_obj(o) {
/* debug object function -- comment-out for release;
 * just body content, not definition, so refs may remain
	var dt;
	if ( ! (dt = document.getElementById("dbg_area")) ) return;
	
	var n = 0;
	for ( var k in o ) {
		dt.value += "" + (n++) + " [" + k + "]) " + o[k] + "\n";
	}
 */
}









