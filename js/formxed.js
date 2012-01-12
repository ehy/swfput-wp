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
 * 
 */

var SWFPut_putswf_video_xed = function () {}

SWFPut_putswf_video_xed.prototype = {
	defs : {
		url: "",
		cssurl: "",
		width: "240",
		height: "180",
		audio: "false",       
		aspectautoadj: "true",
		displayaspect: "0",   
		pixelaspect: "0",     
		volume: "50",         
		play: "false",        
		hidebar: "false",     
		disablebar: "false",  
		barheight: "36",
		quality: "high",
		allowfull: "true",
		allowxdom: "false",
		loop: "false",
		mtype: "application/x-shockwave-flash",
		playpath: "",
		classid: "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000",
		codebase: "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0"
	},
	map : {},
	last_from : 65535,
	put_shortcode : function(cs, sc) {
		var c = this['map'][cs];
		delete this['map'][cs];
		
		var atts = '';
		jQuery.each(this['map'], function(name, value){
			if ( value != '' ) {
				atts += ' ' + name + '="' + value + '"';
			}
		});
		
		var ret = '[' + sc + atts + ']';
		if ( c.length > 0 ) {
			ret += c + '[/' + sc + ']';
		}
		return ret;
	},
	sc_from_line : function(l, cs, sc) {
		var cb = "[" + sc;
		var ce = "[/" + sc + "]";
		var p = l.indexOf(cb, 0);
		if ( p < 0 ) {
			return false;
		}
		l = l.substring(p + cb.length);
		p = l.indexOf(ce, 0);
		if ( p > 0 ) {
			l = l.slice(0, p);
			p = l.indexOf("]", 0);
			// must be found
			if ( p < 0 ) {
				return false;
			}
			this['map'][cs] = l.substring(p + 1);
		}
		p = l.indexOf("]", 0);
		if ( p < 0 ) {
			return false;
		}
		l = l.slice(0, p);
		while ( (p = l.indexOf("=", 0)) > 0 ) {
			var k = l.slice(0, p);
			while ( k.charAt(0) == " " ) k = k.substring(1);
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
			l = l.substring(p + 1);
		}
		return true;
	},
	from_xed : function(f, id, cs, sc) {
		var v = jQuery(edCanvas).val();
		if ( v == null ) {
			return;
		}
		this.set_fm('defs', f, id);
		var va = v.split("\r\n");
		if ( va.length < 2 ) va = v.split("\n");
		if ( va.length < 2 ) va = v.split("\r");
		if ( this.last_from >= va.length ) {
			this.last_from = 0;
		}
		var i = this.last_from;
		var iinit = i;
		for ( ; i < va.length; i++ ) {
			var l = va[i];
			this['map'] = {};
			if ( this.sc_from_line(l, cs, sc) == true ) {
				break;
			}
		}
		this.last_from = i + 1;
		if ( i < va.length ) {
			this.set_fm('map', f, id);
		} else if ( iinit > 0 ) {
			// start again from 0
			this.from_xed(f, id, cs, sc);
		}
	},
	send_xed : function(f, id, cs, sc) {
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
		send_to_editor(this.put_shortcode(cs, sc));
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
					if ( v != '' ) {
						this.value = v;
					}
				}
			}
		});
		return false;
	},
	reset_fm : function(f, id) {
		this.set_fm('defs', f, id);
	}
}

var SWFPut_putswf_video_inst = new SWFPut_putswf_video_xed();

