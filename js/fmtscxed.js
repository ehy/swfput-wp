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
 * TinyMCE plugin to to present the SWFPut shortcode as
 * as something nicer than the raw code in formatted editor
 * 
 * wp-includes/js/tinymce/plugins/wpeditimage/editor_plugin_src.js
 * was used as a guide for this, and copy & paste code may remain.
 * As WordPress is GPL, this is cool. 
 */

//
// Tape reel character: 0x2707: ✇
//
// 0x27f2: ⟲
// 0x27f3: ⟳
//
// 0x3036: 〶
// 0x3020: 〠
// 0x3004: 〄
// 0x338d: ㎍
//
// 0x0903: ः
// 0x0487: ҇ 
//
// 0x27070x0487: ✇ ҇
//

if ( parseInt(tinymce.majorVersion) > 3 ) {
tinymce.PluginManager.add('swfput_mceplugin', function(editor, plurl) {
	var Node  = tinymce.html.Node;
	var ed    = editor;
	var url   = plurl;
	var urlfm = url.split('/');
	var that  = this;

	urlfm[urlfm.length - 1] = 'mce_ifm.php'; // iframe doc
	urlfm = urlfm.join('/');

	var defs  = {
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
	};

	ed.on('init', function() {
	});

	ed.on('preInit', function() {
		ed.schema.addValidElements('evhfrm[*]');
		
		ed.parser.addNodeFilter('evhfrm', function(nodes, name) {
			for ( var i = 0; i < nodes.length; i++ ) {
				from_pseudo(nodes[i]);
			}
		});

		/*
		 * This was put in place for symmetry with the above,
		 * but seems to be unnecessary; left for reference
		 * TODO: remove when certain
		//ed.parser.addNodeFilter('iframe', function(nodes, name) {
		ed.serializer.addNodeFilter('iframe', function(nodes, name) {
			for ( var i = 0; i < nodes.length; i++ ) {
				var cl = nodes[i].attr('class');
				if ( cl && cl.indexOf('evh-pseudo') >= 0 ) {
console.log('addNodeFilter for iframe called\n');
					to_pseudo(nodes[i], name);
				}
			}
		});
		*/
	});

	ed.on('BeforeSetContent', function(o) {
		if ( true || o.set ) {
			o.content = ed.SWFPut_Set_code(o.content);
			ed.nodeChanged();
		}
	});

	ed.on('PostProcess', function(o) {
		if ( o.get ) {
			o.content = ed.SWFPut_Get_code(o.content);
		}
	});

	ed.on('BeforeExecCommand', function(o) {
		var node, p, cmd = o.command;

		if ( cmd == 'mceInsertContent' ) {
			node = ed.dom.getParent(ed.selection.getNode(), 'div.evhTemp');

			if ( node ) {
				p = ed.dom.create('p');
				ed.dom.insertAfter(p, node);
				ed.selection.setCursorLocation(p, 0);
			}
		}
	});

	ed.SWFPut_Set_code = function(content) {
		return parseShortcode(content);
	};

	ed.SWFPut_Get_code = function(content) {
		return getShortcode(content);
	};
	
	var sc_map = {};
	var newkey = function() {
		var r;
		do {
			r = '' + parseInt(32768 * Math.random() + 16384);
		} while ( r in sc_map );
		sc_map[r] = {};
		return r;
	};

	var from_pseudo = function(node) {
		if ( ! node ) return node;
		var w, h, s, id, cl, rep = false;
		w = node.attr('width');
		h = node.attr('height');
		s = node.attr('src');
		cl = node.attr('class') || '';
		id = node.attr('id') || '';

		var k = (id !== '') ? (id.split('-'))[1] : false;
		if ( k ) {
			if ( k in sc_map && sc_map[k].node ) {
				rep = sc_map[k].node;
			}
		}

		if ( ! rep ) {
			rep = new Node('iframe', 1);
			rep.attr({
				'id' : id,
				'class' : cl.indexOf('evh-pseudo') >= 0 ? cl : (cl+' evh-pseudo mceItem'),
				'width' : w,
				'height' : h,
				'sandbox' : "allow-same-origin allow-pointer-lock allow-scripts",
				//'allowfullscreen' : '',
				//'seamless' : '',
				'src' : s
			});
			if ( k && k in sc_map ) {
				sc_map[k].node = rep;
			}
		}

		node.replace(rep);
		return node;
	};

	var to_pseudo = function(node, name) {
		if ( ! node ) return node;
		var w, h, s, id, cl, rep = false;
		id = node.attr('id') || '';
		cl = node.attr('class') || '';
		if ( cl.indexOf('evh-pseudo') < 0 ) {
			return;
		}
		w = node.attr('width');
		h = node.attr('height');
		s = node.attr('src');

		var k = (id !== '') ? (id.split('-'))[1] : false;
		if ( k ) {
			if ( k in sc_map && sc_map[k].pnode ) {
				rep = sc_map[k].pnode;
			}
		}

		if ( ! rep ) {
			rep = new Node('evhfrm', 1);
			rep.attr({
				'id' : id,
				'class' : cl,
				'width' : w,
				'height' : h,
				'src' : s
			});
			if ( k && k in sc_map ) {
				sc_map[k].pnode = rep;
			}
		}

		node.replace(rep);
		return node;
	};

	var _sc_atts2qs = function(ats, cap) {
		var dat = {};
		var qs = '', sep = '';

		for ( var k in defs ) {
			var v = defs[k];
			var rx = ' '+k+'="([^"]*)"';
			rx = new RegExp(rx);

			var p = ats.match(rx);
			if ( p && p[1] != '' ) {
				v = p[1];
			}

			dat[k] = v;
			switch ( k ) {
				case 'cssurl':
				case 'audio':
				case 'iimgbg':
				case 'quality':
				case 'mtype':
				case 'playpath':
				case 'classid':
				case 'codebase':
					continue;
				case 'displayaspect':
					// for new h5 video player vs. old WP plugin
					dat['aspect'] = v;
					qs += sep + 'aspect=' + encodeURIComponent(v);
					sep = '&';
					break;
				default:
					break;
			}

			qs += sep + k + '=' + encodeURIComponent(v);
			sep = '&';
		}
		
		if ( swfput_mceplug_inf !== undefined ) {
			qs += sep
				+ 'a=' + encodeURIComponent(swfput_mceplug_inf.a)
				+ '&'
				+ 'i=' + encodeURIComponent(swfput_mceplug_inf.i)
				+ '&'
				+ 'u=' + encodeURIComponent(swfput_mceplug_inf.u);
		}
		
		dat.qs = qs;
		dat.caption = cap || '';

		return dat;
	};

	var _sc_atts2if = function(url, ats, id, cap) {
		var dat = _sc_atts2qs(ats, cap);
		var qs = dat.qs;
		var w = parseInt(dat.width), h = parseInt(dat.height);
		var dlw = w + 60, fw = w + 16, fh = h + 16; // ugly
		var cls = 'wp-caption aligncenter';
		var sty = 'width: '+dlw+'px';
		var att = 'width="'+fw+'" height="'+fh+'" ' +
			'sandbox="allow-same-origin allow-pointer-lock allow-scripts" ' +
			''; //'allowfullscreen seamless ';
		cap = dat.caption;

		var r = '';
		r += '<dl id="dl-'+id+'" class="'+cls+' mceItem" style="'+sty+'">';
		r += '<dt class="wp-caption-dt mceItem" id="dt-'+id+'">';
		r += '<evhfrm id="'+id+'" class="evh-pseudo mceItem" '+att+' src="';
		r += url + '?' + qs;
		r += '"></evhfrm>';
		r += '</dt><dd class="wp-caption-dd mceItem" id="dd-'+id+'">' + cap;
		r += '</dd></dl>';
		
		dat.code = r;
		return dat;
	};

	var parseShortcode = function(content) {
		//sc_map = {};
		var uri = urlfm;
		
		return content.replace(
		/([\r\n]*)?(<p>)?(\[putswf_video([^\]]+)\]([\s\S]+?)\[\/putswf_video\])(<\/p>)?([\r\n]*)?/g
		, function(a,n1,p1, b,c,e, p2,n2) {
			var sc = b, atts = c, cap = e;
			var ky = newkey();

			sc_map[ky] = {};
			sc_map[ky].sc = sc;
			sc_map[ky].p1 = p1 || '';
			sc_map[ky].p2 = p2 || '';
			sc_map[ky].n1 = n1 || '';
			sc_map[ky].n2 = n2 || '';
			
			var dat = _sc_atts2if(uri, atts, 'evh-'+ky, cap);
			var w = dat.width, h = dat.height;
			var dlw = parseInt(w) + 60; // ugly
			//var cls = 'mceTemp mceIEcenter';
			var cls = 'evhTemp mceItem';

			var r = n1 || '';
			r += p1 || '';
			r += '<div id="evh-sc-'+ky+'" class="'+cls+'" style="width: '+dlw+'px">';
			r += dat.code;
			r += '</div>';
			r += p2 || '';
			r += n2 || '';

			return r;
		});
	};

	var getShortcode = function(content) {
		return content.replace(
		/<div ([^>]*class="evhTemp[^>]*)>((.*?)<\/div>)/g
		, function(a, att, lazy, cnt) {
			var ky = att.match(/id="evh-sc-([0-9]+)"/);
			
			if ( ky && ky[1] ) {
				ky = ky[1];
			} else {
				return a;
			}

			var sc = '', p1 = '', p2 = '', n1 = '', n2 = '';
			if ( sc_map[ky] ) {
				sc = sc_map[ky].sc || '';
				p1 = sc_map[ky].p1 || '';
				p2 = sc_map[ky].p2 || '';
				n1 = sc_map[ky].n1 || '';
				n2 = sc_map[ky].n2 || '';
				if ( cnt ) {
					var m = /.*<dd[^>]*>(.*)<\/dd>.*/.exec(cnt);
					if ( m && (m = m[1]) ) {
						sc = sc.replace(
						/^(.*\]).*(\[\/[a-zA-Z0-9_-]+\])$/
						, function(a, scbase, scclose) {
							return scbase + m + scclose;
						});
						sc_map[ky].sc = sc;
					}
				}
			}

			if ( ! sc || sc === '' ) {
				return a;
			}

			return n1 + p1 + sc + p2 + n2;
		});
	};

	// ?? found in wpeditimage for tinymce 4.x -- what uses these?
	return {
		_do_shcode: parseShortcode,
		_get_shcode: getShortcode
	};
		
});
} else {
(function() {
	var Node = tinymce.html.Node;
	var tmv  = parseInt(tinymce.majorVersion);
	var old  = (tmv < 4);

	tinymce.create('tinymce.plugins.SWFPut', {
		url : '',
		urlfm : '',
		editor : {},

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

		init : function(ed, url) {
			var t = this;
			var old = t.old;

			// URL passed is actually parent dir of this script
			t.url = url;
			var u = url.split('/');
			u[u.length - 1] = 'mce_ifm.php'; // iframe doc
			t.urlfm = u.join('/');

			t.editor = ed;
			
			ed.onPreInit.add(function() {
				ed.schema.addValidElements('evhfrm[*]');
				
				ed.parser.addNodeFilter('evhfrm', function(nodes, name) {
					for ( var i = 0; i < nodes.length; i++ ) {
						t.from_pseudo(nodes[i], name);
					}
				});
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = ed.SWFPut_Set_code(o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if ( o.get ) {
					o.content = ed.SWFPut_Get_code(o.content);
				}
			});


			// [from WP image edit plugin:]
			// When inserting content,
			// if the caret is inside a caption
			// create new paragraph under
			// and move the caret there
			ed.onBeforeExecCommand.add(function(ed, cmd, ui, val) {
				var node, p;

				if ( cmd == 'mceInsertContent' ) {
					node = ed.dom.getParent(ed.selection.getNode(), 'div.evhTemp');

					if ( node ) {
						p = ed.dom.create('p');
						ed.dom.insertAfter(p, node);
						ed.selection.setCursorLocation(p, 0);
					}
				}
			});

			ed.SWFPut_Set_code = function(content) {
				return t._do_shcode(content);
			};

			ed.SWFPut_Get_code = function(content) {
				return t._get_shcode(content);
			};
		},
		
		sc_map : {},

		newkey : function() {
			var r;
			do {
				r = '' + parseInt(32768 * Math.random() + 16384);
			} while ( r in this.sc_map );
			this.sc_map[r] = {};
			return r;
		},

		from_pseudo : function(node) {
			if ( ! node ) return node;
			var t = this;
			var w, h, s, id, cl, rep = false;
			w = node.attr('width');
			h = node.attr('height');
			s = node.attr('src');
			cl = node.attr('class') || '';
			id = node.attr('id') || '';

			var k = (id !== '') ? (id.split('-'))[1] : false;
			if ( k ) {
				if ( k in t.sc_map && t.sc_map[k].node ) {
					rep = t.sc_map[k].node;
				}
			}

			if ( ! rep ) {
				rep = new Node('iframe', 1);
				rep.attr({
					'id' : id,
					'class' : cl.indexOf('evh-pseudo') >= 0 ? cl : (cl+' evh-pseudo mceItem'),
					'width' : w,
					'height' : h,
					'sandbox' : "allow-same-origin allow-pointer-lock allow-scripts",
					//'allowfullscreen' : '',
					//'seamless' : '',
					'src' : s
				});
				if ( k && k in t.sc_map ) {
					t.sc_map[k].node = rep;
				}
			}

			node.replace(rep);
			return node;
		},

		to_pseudo : function(node, name) {
			if ( ! node ) return node;
			var w, h, s, id, cl, rep = false;
			id = node.attr('id') || '';
			cl = node.attr('class') || '';
			if ( cl.indexOf('evh-pseudo') < 0 ) {
				return;
			}
			w = node.attr('width');
			h = node.attr('height');
			s = node.attr('src');

			var k = (id !== '') ? (id.split('-'))[1] : false;
			if ( k ) {
				if ( k in t.sc_map && t.sc_map[k].pnode ) {
					rep = t.sc_map[k].pnode;
				}
			}

			if ( ! rep ) {
				rep = new Node('evhfrm', 1);
				rep.attr({
					'id' : id,
					'class' : cl,
					'width' : w,
					'height' : h,
					'src' : s
				});
				if ( k && k in t.sc_map ) {
					t.sc_map[k].pnode = rep;
				}
			}

			node.replace(rep);
			return node;
		},

		_sc_atts2qs : function(ats, cap) {
			var dat = {};
			var t = this, qs = '', sep = '';
			var defs = t.defs;

			for ( var k in defs ) {
				var v = defs[k];
				var rx = ' '+k+'="([^"]*)"';
				rx = new RegExp(rx);

				var p = ats.match(rx);
				if ( p && p[1] != '' ) {
					v = p[1];
				}

				dat[k] = v;
				switch ( k ) {
					case 'cssurl':
					case 'audio':
					case 'iimgbg':
					case 'quality':
					case 'mtype':
					case 'playpath':
					case 'classid':
					case 'codebase':
						continue;
					case 'displayaspect':
						// for new h5 video player vs. old WP plugin
						dat['aspect'] = v;
						qs += sep + 'aspect=' + encodeURIComponent(v);
						sep = '&';
						break;
					default:
						break;
				}

				qs += sep + k + '=' + encodeURIComponent(v);
				sep = '&';
			}
			
			if ( swfput_mceplug_inf !== undefined ) {
				qs += sep
					+ 'a=' + encodeURIComponent(swfput_mceplug_inf.a)
					+ '&'
					+ 'i=' + encodeURIComponent(swfput_mceplug_inf.i)
					+ '&'
					+ 'u=' + encodeURIComponent(swfput_mceplug_inf.u);
			}
			
			dat.qs = qs;
			dat.caption = cap || '';

			return dat;
		},

		_sc_atts2if : function(url, ats, id, cap) {
			var t = this;
			var dat = t._sc_atts2qs(ats, cap);
			var qs = dat.qs;
			var w = parseInt(dat.width), h = parseInt(dat.height);
			var dlw = w + 60, fw = w + 16, fh = h + 16; // ugly
			var cls = 'wp-caption aligncenter';
			var sty = 'width: '+dlw+'px';
			var att = 'width="'+fw+'" height="'+fh+'" ' +
				'sandbox="allow-same-origin allow-pointer-lock allow-scripts" ' +
				''; //'allowfullscreen seamless ';
			cap = dat.caption;

			var r = '';
			r += '<dl id="dl-'+id+'" class="'+cls+' mceItem" style="'+sty+'">';
			r += '<dt class="wp-caption-dt mceItem" id="dt-'+id+'">';
			r += '<evhfrm id="'+id+'" class="evh-pseudo mceItem" '+att+' src="';
			r += url + '?' + qs;
			r += '"></evhfrm>';
			r += '</dt><dd class="wp-caption-dd mceItem" id="dd-'+id+'">' + cap;
			r += '</dd></dl>';
			
			dat.code = r;
			return dat;
		},

		_do_shcode : function(content) {
			var t = this, urlfm = t.urlfm, defs = t.defs;
			
			//t.sc_map = {};
			
			return content.replace(
			/([\r\n]*)?(<p>)?(\[putswf_video([^\]]+)\]([\s\S]+?)\[\/putswf_video\])(<\/p>)?([\r\n]*)?/g
			, function(a,n1,p1, b,c,e, p2,n2) {
				var sc = b, atts = c, cap = e;
				var ky = t.newkey();

				t.sc_map[ky] = {};
				t.sc_map[ky].sc = sc;
				t.sc_map[ky].p1 = p1 || '';
				t.sc_map[ky].p2 = p2 || '';
				t.sc_map[ky].n1 = n1 || '';
				t.sc_map[ky].n2 = n2 || '';
				
				var dat = t._sc_atts2if(t.urlfm, atts, 'evh-'+ky, cap);
				var w = dat.width, h = dat.height;
				var dlw = parseInt(w) + 60; // ugly
				//var cls = 'mceTemp mceIEcenter';
				var cls = 'evhTemp';

				var r = n1 || '';
				r += p1 || '';
				r += '<div id="evh-sc-'+ky+'" class="'+cls+' mceItem" style="width: '+dlw+'px">';
				r += dat.code;
				r += '</div>';
				r += p2 || '';
				r += n2 || '';

				return r;
			});
		},

		_get_shcode : function(content) {
			var t = this;

			return content.replace(
			/<div ([^>]*class="evhTemp[^>]*)>((.*?)<\/div>)/g
			, function(a, att, lazy, cnt) {
				var ky = att.match(/id="evh-sc-([0-9]+)"/);
			
				if ( ky && ky[1] ) {
					ky = ky[1];
				} else {
					return a;
				}

				var sc = '', p1 = '', p2 = '', n1 = '', n2 = '';
				if ( t.sc_map[ky] ) {
					sc = t.sc_map[ky].sc || '';
					p1 = t.sc_map[ky].p1 || '';
					p2 = t.sc_map[ky].p2 || '';
					n1 = t.sc_map[ky].n1 || '';
					n2 = t.sc_map[ky].n2 || '';
					if ( cnt ) {
						var m = /.*<dd[^>]*>(.*)<\/dd>.*/.exec(cnt);
						if ( m && (m = m[1]) ) {
							sc = sc.replace(
							/^(.*\]).*(\[\/[a-zA-Z0-9_-]+\])$/
							, function(a, scbase, scclose) {
								return scbase + m + scclose;
							});
							t.sc_map[ky].sc = sc;
						}
					}
				}

				if ( ! sc || sc === '' ) {
					return a;
				}

				return n1 + p1 + sc + p2 + n2;
			});
		},

		createControl : function(n, cm) {
			return null;
		},

		getInfo : function() {
			return {
				longname : 'SWFPut Video Player',
				author : 'EdHynan@gmail.com',
				authorurl : '',
				infourl : '',
				version : '1.1'
			};
		}
     });

	/* Start the buttons */
	tinymce.PluginManager.add('swfput_mceplugin', tinymce.plugins.SWFPut);
})();
}

