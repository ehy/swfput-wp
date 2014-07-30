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

var SWFPut_putswf_video_xed = function () {
	this.map = {};
	this.last_from = 0;
	this.last_match = '';

	// placeholder token data: regretable hack for SWFPut video
	// plugin for the tinymce, with WordPress; this is to hold
	// place in a <dd> element which normally holds a caption,
	// but when there is no caption, because tinymce strips out
	// or refuses to render a whole <dl> when a <dd> is empty
	if ( this.fpo === undefined ) {
		SWFPut_putswf_video_xed.prototype.fpo = {};
		var t = this.fpo;
		t.cmt = '<!-- do not strip me -->';
		t.ent = t.cmt;
		t.enx = t.ent;
		var eenc = document.createElement('div');
		eenc.innerHTML = t.ent;
		t.enc = eenc.textContent || eenc.innerText || t.ent;
		t.rxs = '((' + t.cmt + ')|(' + t.enx + ')|(' + t.enc + '))';
		t.rxx = '.*' + t.rxs + '.*';
		t.is  = function(s, eq) {
			return s.match(RegExp(eq ? t.rxs : t.rxx));
		};
	}

	// we might be contructed before tinymce is loaded/setup,
	// but we need to know about it, so use a 1s timer which,
	// should not really affect load, until we can get what we
	// need. *not* using a max because e.g. network errors can
	// make indefinite delays, and user might continue with
	// the same loaded page; so, rely on 1s timer being mild
	// even if it continues throughout the session because
	// tinymce is not in use.
	if ( this.ini_timer === undefined ) {
		SWFPut_putswf_video_xed.prototype.ini_timer = "working";
		var that = this, f = function() {
			if ( typeof tinymce === 'undefined' ) {
				that.ini_timer = setTimeout(f, 1000);
				return;
			}

			that.ini_timer = "done";

			that.tmce_ma = parseInt(tinymce.majorVersion);
			that.tmce_mn = parseFloat(tinymce.minorVersion);

			// tmce 3.4.x is in WP 3.3.1, and 3.2.7 in WP 3.0.2;
			if ( that.tmce_ma < 4 && that.tmce_mn < 4.0 ) {
				that.put_at_cursor = that.put_at_cursor_OLD;
				that.set_edval = that.set_edval_OLD;
				that.get_edval = that.get_edval_OLD;
			} else {
				that.get_mce_dat();
			}
		};
		f();
	}
};
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
		codebase: "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0",
		align: "center",
		preload: "image"
	},
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
			// strings with a set of valid values that can be checked
			case 'align':
				switch ( t ) {
					case 'left':
					case 'right':
					case 'center':
					case 'none':
						break;
					default:
						this['map'][k] = v;
						break;
				}
				break;
			case 'preload':
				switch ( t ) {
					case 'none':
					case 'metadata':
					case 'auto':
					case 'image':
						break;
					default:
						this['map'][k] = v;
						break;
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
	// always test retval.ed!
	get_mce_dat : function() {
		if ( this.ini_timer !== "done" ) {
			return { "ed" : false };
		}

		if ( typeof(this.tmv) === 'undefined' ) {
			// static or invariant
			var r = {};
			r.v   = this.tmce_ma;
			r.vmn = this.tmce_mn;
			r.old = (r.v < 4);
			r.ng  = (r.old && r.vmn < 4.0);
			this.tmv = r;
		}
		// dynamic
		this.tmv.ed  = tinymce.activeEditor || false;
		this.tmv.hid = this.tmv.ed ? this.tmv.ed.isHidden() : true;
		this.tmv.txt = this.tmv.ed ? this.tmv.ed.getElement() : false;
		return this.tmv;
	},
	// 1.0.9: previously made no distinction between 'Visual'
	// and 'Text' editor content since the shortcode had no special
	// presentation and could be handled as plain text in either
	// case. Now I hope to give it a presentation in the 'Visual'
	// editor and so it must be possible to get and set
	// the raw text content regardless of whether the 'Visual'
	// is displayed.
	get_edval : function() {
		var dat = this.get_mce_dat();
		var ed = dat.ed;
		if ( ed && dat.hid ) {
			if ( dat.txt ) {
				return dat.txt.value;
			}
		} else if ( ed ) {
			var bm;
			if ( tinymce.isIE ) {
				ed.focus();
				bm = ed.selection.getBookmark();
			}

			// hope to sync Visual content to textarea:
			tinymce.triggerSave();
			// the original textarea
			var t = dat.txt;
			var c = t ? t.value : ed.getContent({format : 'raw'});
			
			if ( tinymce.isIE ) {
				ed.focus();
				ed.selection.moveToBookmark(bm);
			}
			return c;
		}
		// fall through
		return jQuery(edCanvas).val();
	},
	set_edval : function(setval) {
		var dat = this.get_mce_dat();
		var ed = dat.ed;
		if ( ed && ! dat.hid && dat.old ) {
			var bm, r = false, t;

			if ( true || tinymce.isIE ) {
				ed.focus();
				bm = ed.selection.getBookmark();
				ed.setContent('', {format : 'raw'});
			}

			if ( (t = dat.txt) ) {
				t.value = setval;
				ed.load(t);
			} else {
				r = ed.setContent(setval, {format : 'raw'});
			}

			if ( true || tinymce.isIE ) {
				ed.focus();
				ed.selection.moveToBookmark(bm);
			}
			return r;
		} else if ( ed && ! dat.hid ) {
			ed.focus();
			var sel = ed.selection, st = sel ? sel.getStart() : false,
				r = ed.setContent(setval, {format : 'raw'}); //, no_events: 0});
			
			// As of tmce 4 I have not found a way to make node
			// filter run (for plugins iframe w/ vid), excecpt
			// this ugly hack hide/show (it just worked w/ tmce 3).
			// Sigh. Revisit this if new info appears.
			ed.nodeChanged();
			ed.hide();
			ed.show();

			if ( false && st && (sel = ed.selection) ) {
				sel.setCursorLocation(st);
			}
			return r;
		}

		// fall through
		return jQuery(edCanvas).val(setval);
	},
	put_at_cursor : function(sc) {
		var dat = this.get_mce_dat();
		var ed = dat.ed;

		if ( ! ed || dat.hid ) {
			send_to_editor(sc);
			return false;
		}

		// found in WP image edit plugin:
		// if insertion point is within the div block used
		// to contain the representation of the shortcode,
		// create new paragraph node and move insertion
		// point there; class of div we're checking is
		// "evhTemp" (same as WP image edit plugin, and
		// presumably others, so the benefit of this is
		// not ours alone)
		var node;
		node = ed.dom.getParent(ed.selection.getNode(), 'div.evhTemp');
		if ( node ) {
			var p = ed.dom.create('p');
			ed.dom.insertAfter(p, node);
			ed.selection.setCursorLocation(p, 0);
		}

		ed.selection.setContent(sc, {format : 'text'});
		// ed.selection.setContent() is not enough, because
		// without the next (set(get)) line, good not it is;
		// this apparently forces reprocessing for 'Visual'
		// content such that plugin callbacks get called
		// (like ed.onBeforeSetContent and ed.onPostProcess)
		ed.setContent(ed.getContent());
		// this is needed to sync change to 'Text' editor
		tinymce.triggerSave();

		return false;
	},

	// Old function versions for earlier SWFPut and TinyMCE:
	// in WP versions < 3.3
	get_edval_OLD : function() {
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
	set_edval_OLD : function(setval) {
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
	// out_at_cursor is not an old proc, but a wrapper around
	// old functionality
	put_at_cursor_OLD : function(sc) {
		send_to_editor(sc);
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

		var oa = [];
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
			return false;
		}
		l = l.substring(p + 1);

		var ce = "[/" + sc + "]";
		p = l.indexOf(ce);
		if ( p >= 0 ) {
			l = l.substring(p + ce.length);
		}
		if ( l.length ) {
			// the \n<br> are an attempt to leave a visible
			// indication of where the code/object was, even
			// struggling a bit against tinymce tag stripping
			oa[j ? (j - 1) : j++] += "\n<br/>\n<br/>\n" + l;
		}
		while ( i < va.length ) {
			oa[j++] = va[i++];
		}

		try {
			this.set_edval(oa.join(sep));
			this.last_match = '';
		} catch ( e ) {}

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
		} catch ( ex ) {
			console.log('repl_xed, RETURN EARLY: catch -- ' + ex.name + ': "' + ex.message + '"');
		}
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

				// lousy hack for the SWFPut tinymce plugin to
				// combat tinymce element-stripping breakage
				if ( k === 'caption' && $this.fpo.is(v, 0) ) {
					v = '';
				}
				$this['map'][k] = v;
			} else if ( this.type == "radio" ) {
				if ( this.checked !== undefined && this.checked ) {
					v = this.value;
					$this['map'][k] = v;
				}
			}
		});
		this.sanitize();
	},
	send_xed : function(f, id, cs, sc) {
		this.fill_map(f, id);
		var r = this.mk_shortcode(cs, sc);
		if ( r != null ) {
			this.put_at_cursor(r);
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
					// lousy hack for the SWFPut tinymce plugin to
					// combat tinymce element-stripping breakage
					if ( k === 'caption' && $this.fpo.is(v, 0) ) {
						v = '';
						this.value = v;
					}
				} else if ( this.type == "radio" && this.value == v ) {
					this.checked = 'checked';
				} else if ( this.type == "radio" ) {
					this.checked = '';
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
	form_apval : function(f, id, fr, to) {
		var len = id.length + 1;
		var v = null;
		var pat = "*[id^=" + id + "]";
		var all = jQuery(f).find(pat);
		var that = this;
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
					var t = that.trim(this.value);
					var u = that.trim(decodeURIComponent(v));
					if ( t.length > 0 && u.length > 0 ) {
						t += ' | ';
					}
					this.value = t + u;
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

