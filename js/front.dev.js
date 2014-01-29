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
 * Added SWFPut v. 1.0.7, 2014/01/26
 * (C) Ed Hynan 2014
 */

// borrowed from:
// http://robertnyman.com/2006/04/24/get-the-rendered-style-of-an-element/
// get 'computed' style of element
var SWFPut_putswf_video_getstyle = function (el, sty) {
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
var SWFPut_putswf_video_bld = function(dv, ob, av, ai, att) {
	this.oname = ob;
	this.va_oname = av;
	this.ia_oname = ai;
	this.d = document.getElementById(dv);
	this.a = att;
	if ( this.d ) {
		var p = this._style(this.d, "padding-left");
		if ( p )
			this.pad = Math.max(this.pad, parseInt(p));
		this.wdiv = this.d.offsetWidth;
		this.hdiv = this.d.offsetHeight;
		this.mk(this.d);
	}
};
SWFPut_putswf_video_bld.prototype = {
	d : null,
	o : false, va_o : false, ia_o : false,
	oname : null, va_oname : null, ia_oname : null,
	a : null,
	pad : 0,
	wdiv : null, hdiv : null,
	wobj : null, hobj : null,
	_style : function (el, sty) {
		return SWFPut_putswf_video_getstyle(el, sty);
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
			o.width = dm[0]; o.height = dm[1];
			o.alt = a["alt"];
			o.src = a["src"];
			this.ia_o = o;
		}
		return this.ia_o;
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
			this.ia_o = o;
		}
		return this.ia_o;
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
			dv.insertBefore(oo, dv.lastChild);
		} else if ( ov ) {
			dv.insertBefore(ov, dv.lastChild);
		} else if ( oi ) {
			dv.insertBefore(oi, dv.lastChild);
		} else {
			var p = document.createElement('p');
			if ( p ) {
				p.innerHTML = "video objects creation failed";
				dv.insertBefore(p, dv.lastChild);
			}
		}
	}
};

// (ugly hack to get resize event: save _adj instances, see below)
var SWFPut_putswf_video_szhack = [];

// resize adjust:
// the enclosing <div> is scaled, and so its width from
// 'computed style' is used to adjust video and image
//
// dv is enclosing <div>, ob is flash <object>, av is alt. <video>,
// ai is alt. <img> [all preceding refer to id attribute], and
// and these may be 0 or null if the 'bld' arg is not 0 or null,
// but an instance of SWFPut_putswf_video_bld defined above
var SWFPut_putswf_video_adj = function(dv, ob, av, ai, bld) {
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
		this.o    = document.getElementById(ob);
		this.va_o = document.getElementById(av);
		this.ia_o = document.getElementById(ai);
		if ( this.d ) {
			var p = this._style(this.d, "padding-left");
			if ( p )
				this.pad = Math.max(this.pad, parseInt(p));
			this.wdiv = this.d.offsetWidth;
		}
	}
	if ( this.d ) {
		// need max-width or browser does not scale div
		if ( this.d.style == undefined ||
			 this.d.style.maxWidth == undefined ||
			 this.d.style.maxWidth == "none" ||
			 this.d.style.maxWidth == "" ) {
			//console.log("maxWidth: '" + this.d.style.maxWidth + "'");
			this.d.style.maxWidth = "100%";
		}
		// (ugly hack to get resize event: save _adj instances)
		SWFPut_putswf_video_szhack[SWFPut_putswf_video_szhack.length] = this;
		this._int_set_resize();
	}
};
SWFPut_putswf_video_adj.prototype = {
	d : null,
	o : null, va_o : null, ia_o : null,
	pad : 0, wdiv : null,
	bld : null,
	inresize : 0,
	_style : function (el, sty) {
		return SWFPut_putswf_video_getstyle(el, sty);
	},
	// (ugly hack to get resize event: the event is
	// delivered only for certain objects (incl. window))
	_int_set_resize : function () {
		if ( window.attachEvent ) { // MSIE 8?
			window.attachEvent("onresize", this._int_handle_resize);
		} else {
			window.addEventListener("resize", this._int_handle_resize, true);
		}
	},
	// (ugly hack to get resize event: use saved _adj instances --
	// note the timeout: observing FFox 26 the event seems to be
	// delivered before rather that after changes (?!?) so the
	// timeout unreliably delays processing until changes can
	// be detected)
	_int_handle_resize : function () {
		setTimeout(function () {
			var that = null;
			for ( var i = 0; i < SWFPut_putswf_video_szhack.length; i++ ) {
				that = SWFPut_putswf_video_szhack[i];
				that.handle_resize();
			}
			if ( that != null ) {
				that._int_set_resize();
			}
		}, 100);
	},
	handle_resize : function () {
		var that = this;
		if ( that.inresize != 0 )
			return;
		var dv = that.d;
		var wo = that.wdiv;
		var wn = dv.offsetWidth;
		if ( wn == wo )
			return;
		that.wdiv = wn;
		var p = that._style(dv, "padding-left");
		if ( p ) {
			that.pad = parseInt(p);
		}
		that.resize();
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
		o.width = o.pixelWidth = wd;
		o.height = o.pixelHeight = Math.round(wd / r);
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
			this._int_rsz(this.ia_o);
		}
		this.inresize = 0;
	}
};











