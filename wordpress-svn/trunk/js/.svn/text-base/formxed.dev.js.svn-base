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
 * For Wordpress shortcode tags in the post editor; js to put
 * html form with shortcode attributes in editor as shortcode
 * 
 * based on example at:
 * 	http://bluedogwebservices.com/wordpress-25-shortcodes/
 */

var SWFPut_putswf_video_xed = function () {};

SWFPut_putswf_video_xed.prototype = {
	defs : {
		url: "",
		cssurl: "",
		iimage: "",
		width: "240",
		height: "180",
		mobiwidth: "0",
		audio: "false",       
		aspectautoadj: "true",
		displayaspect: "0",   
		pixelaspect: "0",     
		volume: "50",         
		play: "false",        
		hidebar: "true",     
		disablebar: "false",  
		iimgbg: "true",
		barheight: "36",
		quality: "high",
		allowfull: "true",
		allowxdom: "false",
		loop: "false",
		mtype: "application/x-shockwave-flash",
		playpath: "",
		altvideo: "",
		classid: "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000",
		codebase: "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0"
	},
	map : {},
	last_from : 0,
	last_match : '',
	ltrim : function(s, ch) {
		var c = (ch === undefined) ? " " : ch;
		while ( s.charAt(0) === c )
			s = s.substring(1);
		return s;
	},
	rtrim : function(s, ch) {
		var c = (ch === undefined) ? " " : ch;
		while ( s.charAt(s.length - 1) === c )
			s = s.slice(0, s.length - 1);
		return s;
	},
	trim : function(s, ch) {
		var c = (ch === undefined) ? " " : ch;
		return this.rtrim(this.ltrim(s, c), c);
	},
	sanitize : function(fuzz) {
		var t;
		var k;
		var v;
		var m;
		if ( fuzz !== false )
			fuzz = true;
		// check against default keys; if instance map has
		// other keys, leave them
		for ( k in this['map'] ) {
			if ( (v = this['defs'][k]) === undefined ) {
				continue;
			}
			if ( (t = this.trim(this['map'][k])) == '' ) {
				continue;
			}
			switch ( k ) {
			// strings that must present positive integers
			case 'width':
			case 'height':
			case 'mobiwidth':
			case 'volume':
			case 'barheight':
				if ( k === 'barheight' && t === 'default' ) {
					continue;
				}
				if ( fuzz && /^\+?[0-9]+/.test(t) ) {
					t = '' + Math.abs(parseInt(t));
				}
				if ( ! /^[0-9]+$/.test(t) ) {
					t = v;
				}
				this['map'][k] = t;
				break;
			// strings that must present booleans
			case 'audio':
			case 'aspectautoadj':
			case 'play':
			case 'hidebar':
			case 'disablebar':
			case 'iimgbg':
			case 'allowfull':
			case 'allowxdom':
			case 'loop':
				t = t.toLowerCase();
				if ( t !== 'true' && t !== 'false' ) {
					if ( fuzz ) {
						var xt = /^(sc?h[yi]te?)?y(e((s|ah)!?)?)?$/;
						var xf = /^((he(ck|ll))?n(o!?)?)?$/;
						if ( /^\+?[0-9]+/.test(t) ) {
							t = parseInt(t) == 0 ? 'false' : 'true';
						} else if ( xf.test(t) ) {
							t = 'false';
						} else if ( xt.test(t) ) {
							t = 'true';
						} else {
							t = v;
						}
					} else {
						t = v;
					}
				}
				this['map'][k] = t;
				break;
			// special format: ratio strings
			case 'displayaspect':
			case 'pixelaspect':
				// exception: these allow one alpha as special flag,
				// or 0 to disable
				if ( /^[A-Z0]$/i.test(t) ) {
					this['map'][k] = t;
					break;
				}
				var px; var pw;
				if ( fuzz ) {
					// exception: allow INT|FLOAT or INT|FLOATsepINT|FLOAT
					px = /^\+?([0-9]+(\.[0-9]+)?)([Xx: \t\f\v\^\$\\\.\*\+\?\(\)\[\]\{\}\|\/,!@#%&_=`~><-]+([0-9]+(\.[0-9]+)?))?$/;
					// wanted: INTsepINT;
					pw  = /^([0-9]+)[Xx: \t\f\v\^\$\\\.\*\+\?\(\)\[\]\{\}\|\/,!@#%&_=`~><-]+([0-9]+)$/;
				} else {
					// exception: allow INT|FLOAT or INT|FLOATsepINT|FLOAT
					px = /^\+?([0-9]+(\.[0-9]+)?)([Xx:]([0-9]+(\.[0-9]+)?))?$/;
					// wanted: INTsepINT;
					pw  = /^([0-9]+)[Xx:]([0-9]+)$/;
				}
				if ( (m = px.exec(t)) !== null ) {
					this['map'][k] = m[1] + (m[4] ? (':' + m[4]) : ':1');
				} else if ( (m = pw.exec(t)) !== null ) {
					this['map'][k] = m[1] + ':' + m[2];
				} else {
					this['map'][k] = v;
				}
				break;
			// varied complex strings; not sanitized here
			case 'url':
			case 'cssurl':
			case 'iimage':
			case 'mtype':
			case 'playpath':
			case 'altvideo':
			case 'classid':
			case 'codebase':
				break;
			// for reference defaults discard any changes,
			// e.g. 'defaulturl'
			default:
				this['map'][k] = v;
				break;
			}
		}
	},
	// The next 2 get/set funcs were fine in Konqueror, Chromium, and
	// Firefox (Unix and MS); but of course MSIE is too lousy to
	// just work. Hence, the .isIE noise.
	get_edval : function() {
		if ( typeof tinymce != 'undefined' ) {
			var ed;
			if ( (ed = tinyMCE.activeEditor) && !ed.isHidden() ) {
                var bm;
				if ( tinymce.isIE ) {
					ed.focus();
					bm = ed.selection.getBookmark();
				}
				c = ed.getContent({format : 'raw'});
				if ( tinymce.isIE ) {
					ed.focus();
					ed.selection.moveToBookmark(bm);
				}
				return c;
			}			
		}
		return jQuery(edCanvas).val();
	},
	set_edval : function(setval) {
		if ( typeof tinymce != 'undefined' ) {
			var ed;
			if ( (ed = tinyMCE.activeEditor) && !ed.isHidden() ) {
                var bm;
				if ( tinymce.isIE ) {
					ed.focus();
					bm = ed.selection.getBookmark();
					ed.setContent('', {format : 'raw'});
				}
				var r = ed.setContent(setval, {format : 'raw'});
				if ( tinymce.isIE ) {
					ed.focus();
					ed.selection.moveToBookmark(bm);
				}
				return r;
			}			
		}
		return jQuery(edCanvas).val(setval);
	},
	mk_shortcode : function(cs, sc) {
		var c = this['map'][cs];
		delete this['map'][cs];
		
		this.sanitize();
		var atts = '';
		for ( var k in this['map'] ) {
			var v = this['map'][k];
			if ( this['defs'][k] == undefined || v == '' )
				continue;
			atts += ' ' + k + '="' + v + '"';
		}
		
		if ( atts == '' ) {
			return null;
		}
		
		var ret = '[' + sc + atts + ']';
		// update 2013/07/12: make this unconditional -- while
		// [/scode] is optional when there is no caption, its
		// absence causes an error when another shortcode
		// follows -- consider [scode] foo [scode]caption[/scode]
		// the first code is not terminated before [/] is seen
		if ( true || c.length > 0 ) {
			ret += c + '[/' + sc + ']';
		}
		return ret;
	},
	find_rbrack : function(l) {
		var p = 0;
		while ( p < l.length ) {
			if ( l.charAt(p) === ']' ) break;
			var t = l.substring(p);
			if ( t.length < 3 )
				return -1;
			var q = t.indexOf('"') + 1; p += q;
			if ( q <= 0 )
				return -1;
			t = t.substring(q);
			if ( t.length < 2 )
				return -1;
			q = t.indexOf('"') + 1; p += q;
			if ( q <= 0 )
				return -1;
			t = t.substring(q);
			while ( t.charAt(0) == ' ' ) {
				++p; t = t.substring(1);
			}
		}
		return p < l.length ? p : -1;
	},
	sc_from_line : function(l, cs, sc) {
		var cap = false;
		var p = l.indexOf("[/" + sc + "]", 0);
		if ( p > 0 ) {
			cap = true;
			l = l.slice(0, p);
		}
		l = this.ltrim(l);
		if ( l.charAt(0) == "]" ) {
			if ( cap )
				this['map'][cs] = l.substring(1);
			return true;
		}
		while ( (p = l.indexOf("=", 0)) > 0 ) {
			var k = l.slice(0, p);
			if ( k.length < 1 ) {
				return false;
			}
			l = l.substring(p + 1);
			if ( l.charAt(0) != '"' ) {
				return false;
			}
			l = l.substring(1);
			p = l.indexOf('"', 0);
			if ( p < 0 ) {
				return false;
			}
			this['map'][k] = l.slice(0, p);
			l = this.ltrim(l.substring(p + 1));
			if ( l.charAt(0) == "]" ) {
				if ( cap )
					this['map'][cs] = l.substring(1);
				return true;
			}
		}
		return false;
	},
	rmsc_xed : function(f, id, cs, sc) {
		if ( this.last_match == '' ) {
			return false;
		}
		var v = this.get_edval();
		if ( v == null ) {
			return false;
		}
		var sep = "[" + sc;
		var va = v.split(sep);
		if ( va.length < 2 ) {
			return false;
		}
		var oa = new Array();
		var i = 0, j = 0;
		var l;
		while ( i < va.length ) {
			l = va[i++];
			if ( l == this.last_match ) {
				break;
			}
			oa[j++] = l;
		}
		var p;
		if ( j >= va.length || (p = this.find_rbrack(l)) < 0 ) {
			delete oa;
			return false;
		}
		l = l.substring(p + 1);
		var ce = "[/" + sc + "]";
		p = l.indexOf(ce);
		if ( p >= 0 ) {
			l = l.substring(p + ce.length);
		}
		if ( l.length ) {
			oa[j ? (j - 1) : j++] += l;
		}
		while ( i < va.length ) {
			oa[j++] = va[i++];
		}
		try {
			this.set_edval(oa.join(sep));
			this.last_match = '';
		} catch ( e ) {}
		delete oa;
		return false;
	},
	repl_xed : function(f, id, cs, sc) {
		if ( this.last_match == '' ) {
			return false;
		}
		var v = this.get_edval();
		if ( v == null ) {
			return false;
		}
		var sep = "[" + sc;
		var va = v.split(sep);
		if ( va.length < 2 ) {
			return false;
		}
		this.fill_map(f, id);
		var c = this.mk_shortcode(cs, sc);
		if ( c == null ) {
			return false;
		}
		var i = 0;
		var l;
		for ( ; i < va.length; i++ ) {
			l = va[i];
			if ( l == this.last_match ) {
				break;
			}
		}
		if ( i >= va.length ) {
			return false;
		}
		var ce = "[/" + sc + "]";
		va[i] = c.substring(sep.length);
		var p = l.indexOf(ce);
		if ( p > 0 ) {
			p += ce.length;
			if ( l.length >= p )
				va[i] += l.substring(p);
		} else if ( (p = l.indexOf("]")) > 0 ) {
			if ( l.length > p )
				va[i] += l.substring(p + 1);
		}
		try {
			l = va[i];
			this.set_edval(va.join(sep));
			this.last_match = l;
		} catch ( e ) {}
		return false;
	},
	from_xed : function(f, id, cs, sc) {
		var v = this.get_edval();
		if ( v == null ) {
			return false;
		}
		var va = v.split("[" + sc);
		if ( va.length < 2 ) {
			return false;
		}
		this.set_fm('defs', f, id);
		if ( this.last_from >= va.length ) {
			this.last_from = 0;
		}
		var i = this.last_from;
		var iinit = i;
		for ( ; i < va.length; i++ ) {
			var l = va[i];
			this['map'] = {};
			if ( this.sc_from_line(l, cs, sc) == true ) {
				this.last_match = l;
				break;
			}
		}
		this.last_from = i + 1;
		if ( i < va.length ) {
			this.sanitize();
			this.set_fm('map', f, id);
		} else if ( iinit > 0 ) {
			// start again from 0
			this.last_match = '';
			this.from_xed(f, id, cs, sc);
		}
		return false;
	},
	fill_map : function(f, id) {
		var len = id.length + 1;
		var pat = "input[id^=" + id + "]";
		var all = jQuery(f).find(pat);
		var $this = this;
		this['map'] = {};
		all.each(function () {
			var v;
			var k = this.name.substring(len, this.name.length - 1);
			if ( this.type == "checkbox" ) {
				v = this.checked == undefined ? '' : this.checked;
				v = v == '' ? 'false' : 'true';
				if ( $this['defs'][k] == undefined ) {
					$this['map'][k] = v;
				} else {
					$this['map'][k] = v == $this['defs'][k] ? '' : v;
				}
			} else if ( this.type == "text" ) {
				v = this.value;
				if ( $this['defs'][k] != undefined ) {
					if ( $this['defs'][k] == v ) {
						// if it's a default, don't add it
						v = '';
					}
				}
				$this['map'][k] = v;
			}
		});
		this.sanitize();
	},
	send_xed : function(f, id, cs, sc) {
		this.fill_map(f, id);
		var r = this.mk_shortcode(cs, sc);
		if ( r != null ) {
			send_to_editor(r);
		}
		return false;
	},
	set_fm : function(mapname, f, id) {
		var len = id.length + 1;
		var pat = "input[id^=" + id + "]";
		var $this = this;
		var all = jQuery(f).find(pat);
		all.each(function () {
			var v;
			var k = this.name.substring(len, this.name.length - 1);
			if ( (v = $this[mapname][k]) != undefined ) {
				if ( this.type == "checkbox" ) {
					this.checked = v == 'true' ? 'checked' : '';
				} else if ( this.type == "text" ) {
					if ( true || v != '' ) {
						this.value = v;
					}
				}
			}
		});
		return false;
	},
	reset_fm : function(f, id) {
		return this.set_fm('defs', f, id);
	},
	form_cpval : function(f, id, fr, to) {
		var len = id.length + 1;
		var v = null;
		var pat = "*[id^=" + id + "]";
		var all = jQuery(f).find(pat);
		all.each(function () {
			if ( this.name != undefined ) {
				var k = this.name.substring(len, this.name.length - 1);
				if ( k == fr ) {
					v = this.value;
					return false;
				}
			}
		});
		if ( v == null ) {
			return false;
		}
		all.each(function () {
			if ( this.name != undefined ) {
				var k = this.name.substring(len, this.name.length - 1);
				if ( k == to ) {
					// EH: 2013/08/10 -- had unsuitable unescape(),
					// changed to decodeURIComponent()
					this.value = decodeURIComponent(v);
					return false;
				}
			}
		});
		return false;
	},
	elh : {},
	hideshow : function(id, btnid, txhide, txshow, sltype) {
		var sel = "[id="+id+"]";
		var btn = document.getElementById(btnid);
		var slt = (sltype === undefined) ? "normal" : sltype;
	
		if ( this.elh[id] === undefined || this.elh[id] === 0 ) {
			this.elh[id] = 1;
			jQuery(sel).slideUp(slt);
			if ( btn ) {
				btn.value = txshow;
			}
		} else {
			this.elh[id] = 0;
			jQuery(sel).slideDown(slt);
			if ( btn ) {
				btn.value = txhide;
			}
		}

		return false;
	}
};

var SWFPut_putswf_video_inst = new SWFPut_putswf_video_xed();

