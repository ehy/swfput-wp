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
 * As WordPress in GPL, this is cool. 
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

(function() {
	var mouse = {};
	var Node = tinymce.html.Node;

	/* Register the buttons */
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

			// URL passed is actually parent dir of this script
			t.url = url;
			var u = url.split('/');
			u[u.length - 1] = 'mce_ifm.php'; // iframe doc
			t.urlfm = u.join('/');

			t.editor = ed;
			//t._createButtons();
			
			//ed.addCommand('SWFPut_put', t._editImage);
			
			ed.onPreInit.add(function() {
				ed.schema.addValidElements('evhfrm[*]');
				
				ed.parser.addNodeFilter('evhfrm', function(nodes) {
					for ( var i = 0; i < nodes.length; i++ ) {
						t.from_pseudo(nodes[i]);
					}
				});

				ed.parser.addNodeFilter('iframe', function(nodes, name, args) {
					for ( var i = 0; i < nodes.length; i++ ) {
						var cl = nodes[i].attr('class');
						if ( cl && cl.indexOf('evh-pseudo') >= 0 ) {
							t.to_pseudo(nodes[i], name, args);
						}
					}
				});
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = ed.SWFPut_Set_code(o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = ed.SWFPut_Get_code(o.content);
			});

			ed.SWFPut_Set_code = function(content) {
				return t._do_shcode(content);
			};

			ed.SWFPut_Get_code = function(content) {
				return t._get_shcode(content);
			};

			// When inserting content, if the caret is inside a caption create new paragraph under
			// and move the caret there
			ed.onBeforeExecCommand.add( function( ed, cmd ) {
				var node, p;

				if ( cmd == 'mceInsertContent' ) {
					node = ed.dom.getParent(ed.selection.getNode(), 'div.mceTemp');

					if ( !node )
						return;

					p = ed.dom.create('p');
					ed.dom.insertAfter( p, node );
					ed.selection.setCursorLocation(p, 0);
				}
			});
		},
		
		rndmap : {},
		nrnd : function() {
			var r;
			do {
				r = '' + parseInt(32768 * Math.random() + 16384);
			} while ( r in this.rndmap );
			this.rndmap[r] = 0;
			return r;
		},

		from_pseudo : function(node) {
			if ( ! node ) return;
			var w, h, s, cl, rep;
			w = node.attr('width');
			h = node.attr('height');
			s = node.attr('src');
			cl = node.attr('class') || '';
			rep = new Node('iframe', 1);
			rep.attr({
				'width' : w,
				'height' : h,
				'class' : cl.indexOf('evh-pseudo') >= 0 ? cl : (cl+' evh-pseudo'),
				'src' : s
			});
			node.replace(rep);
		},
		to_pseudo : function(node, name, args) {
			if ( ! node ) return;
			var w, h, s, cl, rep;
			cl = node.attr('class') || '';
			if ( cl.indexOf('evh-pseudo') < 0 ) {
				return;
			}
			w = node.attr('width');
			h = node.attr('height');
			s = node.attr('src');
			rep = new Node('evhfrm', 1);
			rep.attr({
				'width' : w,
				'height' : h,
				'class' : cl,
				'src' : s
			});
			node.replace(rep);
		},

		_sc_atts2qs : function(ats, cap) {
			var dat = {};
			var t = this, qs = '', sep = '';
			var defs = t.defs;

			for ( var k in defs ) {
				var v = defs[k];
				var rx = new RegExp(' '+k+'="([^"]*)"');

				var p = ats.match(rx);
				if ( p && p[1] != '' ) {
					v = p[1];
				}

				dat[k] = v;
				switch ( k ) {
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
			
			dat.qs = qs;
			dat.caption = cap || '';

			return dat;
		},

		_sc_atts2if : function(url, ats, cap) {
			var t = this;
			var dat = t._sc_atts2qs(ats, cap);
			var qs = dat.qs;
			var id = '';
			var cls = '';
			var cap = dat.caption;
			var w = dat.width, h = dat.height;

			var r = '';
			r += '<dl id="'+id+'" class="wp-caption '+cls+'" style="width: '+w+'px">';
			r += '<dt class="wp-caption-dt">';
			r += '<evhfrm width="'+w+'" height="'+h+'" class="evh-pseudo" src="' + url;
			r += '?' + qs;
			r += '"></evhfrm>';
			r += '</dt>';
			r += '<dd class="wp-caption-dd">'+cap+'</dd></dl>';
			
			dat.code = r;
			return dat;
		},

		_do_shcode : function(content) {
			var t = this;
			var urlfm = t.urlfm;
			var defs = t.defs;
			
			return content.replace(
/([ \t]*<\/*p>)?([ \t]*<!-- SWFPut b -->)?[ \t]*(\[putswf_video([^\]]+)\]([\s\S]+?)\[\/putswf_video\])([ \t]*<!-- SWFPut e -->)?([ \t]*<\/*p>)?/g
			, function(a,b,c,d,e,f,g,h) {
				var pb = b, sc = d, atts = e, cap = f, pe = h;

				var ky, ok = false;
				for ( ky in t.rndmap ) {
					if ( t.rndmap[ky] == sc ) {
						ok = true;
						break;
					}
				}
				if ( ok === false ) {
					ky = t.nrnd();
					t.rndmap[ky] = sc;
				}
				
				var dat = t._sc_atts2if(t.urlfm, atts, cap);
				var r = '' + pb;
				r += '<div class="mceTemp mceIEcenter" >';
				r += '<!-- SWFPut b ' + ky + ' -->';
				r += dat.code;
				r += '<!-- SWFPut e ' + ky + ' -->';
				r += '</div>' + pe;

				return r;
			});
		},

		_get_shcode : function(content) {
			var t = this;

			return content.replace(
/([ \t]*<\/*p>)?([ \t]*<div [^>]+>)?<!-- SWFPut b ([0-9]*) -->.*<!-- SWFPut e (\3) -->([ \t]*<\/div>)?([ \t]*<\/*p>)?/g
			, function(a,b,c,d,e,f,g) {
				var pb = b, ky = d, cmp = e, pe = g;

				var rpl = pb;
				if ( ky.length && ky == cmp && t.rndmap[ky] ) {
					rpl += t.rndmap[ky];
				}
				rpl += pe;

				return rpl;
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

