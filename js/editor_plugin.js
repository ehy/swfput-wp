/*
 *      editor_plugin.js
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
 * WP visual editor presentation of SWFPut video.
 * 
 * With v 2.9 (3.0) a new pretty dialog based simplified
 * UI is implemented based on (literally) the wp.media w/
 * Backbone and _'s that WP comments suggest was started
 * ~ v 3.5 -- this implementation is conditional on v >= 4.3.
 * 
 * See, in WP installation, wp-includes/js/{mce-view,media*}.js
 */

//
// A utitlity for this code, i.e. stuff in one place
//
var SWFPut_video_utility_obj_def = function() {
	// start new serial based on page load time --
	// not critical, meant to help avoid clashes,
	// but not under perverse conditions like rapid
	// multiple use incremenenting to values that might
	// be greater than initial value on next page load --
	// under such conditions, games over anyway.
	this.loadtime_serial = this.unixtime() & 0x0FFFFFFFFF;
};
SWFPut_video_utility_obj_def.prototype = {
	defprops  : {
		url: "",
		cssurl: "",
		iimage: "",
		width: "240",
		height: "180",
		mobiwidth: "0",
		audio: "false",       
		aspectautoadj: "false",
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
	
	mk_shortcode : function(sc, atts, cap) {
		var c = cap || '', s = '[' + sc,
		    defs = SWFPut_video_utility_obj_def.prototype.defprops;
		for ( var t in atts ) {
			if ( defs[ t ] === undefined ) {
				continue;
			}
			
			s += ' ' + t + '="' + atts[t] + '"';
		}
		return s + ']' + c + '[/' + sc + ']'
	},

	atts_filter : function(atts) {
		var defs = SWFPut_video_utility_obj_def.prototype.defprops,
		    outp = {};
		
		for ( var t in atts ) {
			if ( defs[ t ] !== undefined ) {
				outp[t] = atts[ t ];
			}
		}
		
		return outp;
	},

	// Like JS Date.now
	date_now: function() {
		return Date.now ? Date.now() : new Date().getTime();
	},
	
	// w/ 1 sec reso. like unix epoch time (JS funcs start at epoch)
	unixtime: function() {
		return (this.date_now() / 1000);
	},
	
	// set in ctor to epoch time in seconds
	loadtime_serial: 0,

	// get an empty shortcode for new insertion,
	// adding caption reminder with timestamp --
	// timestamp serves to make string unique for
	// wp.media use in a data-* attribute
	get_new_putswf_shortcode: function() {
		var d = new Date(),
		    s = '[putswf_video url="" iimage="" altvideo=""]',
		    e = '[/putswf_video]',
		    c = 'Edit me please! '
		        + d.toString() + ', '
		        + d.getTime() + 'ms';
		return s + c + e;
	},

	// the WP media root object, 'wp'
	_wp: wp || false,
	
	// use wp ajax to fetch attachment data from attachment id integer
	// -- result_cb is a function to call with results, arg 1 is id
	// arg 2 is object status: true == ok + response,
	// null == ok w/o response,
	// or false on fail, and response
	// this returns status object w/ status == 0 for pending;
	// if saved test again for res.status !== 0
	attachment_data_by_id: function(id, result_cb) {
		var pid = id,
		    res = { status: 0, response: null };

		if ( this._wp ) { // 'wp_ajax_get_attachment'
			this._wp.ajax.send( 'get-attachment', {
				data: {
					id: pid
				}
			} )
			.done( function( response ) {
				res.status = response ? true : null;
				res.response = response;
				if ( result_cb && typeof result_cb === 'function' ) {
					result_cb(id, res);
				}
			} )
			.fail( function( response ) {
				res.status = false;
				res.response = response;
				if ( result_cb && typeof result_cb === 'function' ) {
					result_cb(id, res);
				}
			} );
		}
	},
	
	// object to hold attachements keyed by attachment id
	attachments: {},
	
	// get an attachment obj from attachments, or by ajax if needed
	// -- 1st arg is id, 2nd is existing obj to (re)place in table and
	// is optional, 3rd is an optional callback taking the 2 args
	// described for 'attachment_data_by_id' plus a 3rd convenience
	// arg -- the cache object
	//
	// -- returns attachment object if possible, false on error, and
	// null on ajax call with result pending
	get_attachment_by_id: function(id, attach_put, result_cb) {
		if ( this.attachments.id === undefined ) {
			if ( attach_put !== undefined && attach_put ) {
				this.attachments.id = attach_put;
				return attach_put;
			} else {
				var obj = this.attachments, cb = result_cb || false;

				this.attachment_data_by_id(id, function (_id, _res) {
					if ( _res.status === true ) {
						obj[_id] = _res.response;
					} else {
						obj[_id] = false;
					}
					if ( typeof cb === 'function' ) {
						cb(_id, _res, obj);
					}
				} );

				return null;
			}
		} else {
			if ( attach_put !== undefined && attach_put ) {
				this.attachments.id = attach_put;
			}
			return this.attachments.id;
		}
		
		return false;
	}
};

var SWFPut_video_utility_obj = 
	new SWFPut_video_utility_obj_def(wp || false);


//
// Experimental wp.media based presentation in/of editor thing
//

// Our button (next to "Add Media") calls this
var SWFPut_add_button_func = function(btn) {
	var ivl, ivlmax = 100, tid = btn.id, ed,
	    sc = SWFPut_video_utility_obj.get_new_putswf_shortcode(),
	    dat = SWFPut_putswf_video_inst.get_mce_dat(),
	    enc = window.encodeURIComponent( sc ),
	    div = false;

	if ( ! (dat && dat.ed) ) {
		alert('Failed to get visual editor');
		return false;
	}
	
	ed = dat.ed;
	ed.selection.setContent( sc + '&nbsp;', {format : 'text'} );

	ivl = setInterval( function() {
		var divel, // raw browser dom element vs. mce object
		    got = false,
		    $ = ed.$;
		    
		if ( div === false ) {
		    var w = $( '.wpview-wrap' );

			w.each( function(cur) {
				var at;
				// wacky: mce.dom.query.each  passing number, not obj
				cur = w[cur];			
				at = cur.getAttribute( 'data-wpview-text' );
	
				if ( at && at.indexOf( enc ) >= 0 ) {
					divel = cur;    // keep raw . . .
					div = $( cur ); // . . . and mce obj
					return false;
				}
			} );
		}
	
		if ( div !== false ) {
			var f = div.find( 'iframe' );
			if ( f ) {
				ed.selection.select( divel, true );
				ed.selection.scrollIntoView( divel );
				div.trigger( 'click' );
				got = true;
			}
		}

		if ( (--ivlmax <= 0) || got ) {
			clearInterval( ivl );
		}
	}, 1500);

	return false;
};

// get an attachment obj from attachments, or by ajax if needed
// -- 1st arg is id, 2nd is existing obj to (re)place in table and
// is optional
var SWFPut_get_attachment_by_id = function(id, attach_put, result_cb) {
	return SWFPut_video_utility_obj
	    ? SWFPut_video_utility_obj.get_attachment_by_id(id, attach_put, result_cb || false)
	    : false;
};

// specific to putswf shortcode attr url and altvideo.
// and iimage -- these might have a URL or WP
// attachment ID -- in the latter case get the
// wp "attachment" object w/ ajax and cache the
// objs for later use
var SWFPut_cache_shortcode_ids = function(sc, cb) {
	var aatt = [
		sc.get( 'url' ),
		sc.get( 'altvideo' ),
		sc.get( 'iimage' )
	], _cb = (cb && typeof cb === 'function') ? cb : false;

	_.each(aatt, function(s) {
		if ( s != undefined ) {
			_.each(s.split('|'), function(t) {
				var m = t.match(/^[ \t]*([0-9]+)[ \t]*$/);
				if ( m && m[1] ) {
					var res = SWFPut_get_attachment_by_id(m[1], false, _cb);
					
					if ( res !== null && _cb !== false ) {
						var o = SWFPut_video_utility_obj.attachments;
						cb(m[1], o[m[1]], o);
					}
				}
			} );
		}
	} );
};

// get html document for iframe: head and body params
// are fetched by wp_ajax, returned by SWFPut plugin php;
// styles and bodcls (bodyClasses) are WP hosekeeping.
// this was pulled from within WP method
var SWFPut_get_iframe_document = function(head, styles, bodcls, body) {
	head	= head || '';
	styles	= styles || '';
	bodcls	= bodcls || '';
	body	= body || '';

	return (
	'<!DOCTYPE html>' +
	'<html>' +
		'<head>' +
			'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' +
			head +
			styles +
			'<style>' +
				'html {' +
					'background: transparent;' +
					'padding: 0;' +
					'margin: 0;' +
				'}' +
				'body#wpview-iframe-sandbox {' +
					'background: transparent;' +
					'padding: 1px 0 !important;' +
					'margin: -1px 0 0 !important;' +
				'}' +
				'body#wpview-iframe-sandbox:before,' +
				'body#wpview-iframe-sandbox:after {' +
					'display: none;' +
					'content: "";' +
				'}' +
				'.fix-alignleft {' +
				'display: block;' +
				'min-height: 32px;' +
				'margin-top: 0;' +
				'margin-bottom: 0;' +
				'margin-left: 0px;' +
				'margin-right: auto; }' +
				'' +
				'.fix-alignright {' +
				'display: block;' +
				'min-height: 32px;' +
				'margin-top: 0;' +
				'margin-bottom: 0;' +
				'margin-right: 0px;' +
				'margin-left: auto; }' +
				'' +
				'.fix-aligncenter {' +
				'clear: both;' +
				'display: block;' +
				'margin: 0 auto; }' +
				'' +
				'.alignright .caption {' +
				'padding-bottom: 0;' +
				'margin-bottom: 0.1rem; }' +
				'' +
				'.alignleft .caption {' +
				'padding-bottom: 0;' +
				'margin-bottom: 0.1rem; }' +
				'' +
				'.aligncenter .caption {' +
				'padding-bottom: 0;' +
				'margin-bottom: 0.1rem; }' +
				'' +
			'</style>' +
		'</head>' +
		'<script type="text/javascript">' +
			'var evhh5v_sizer_maxheight_off = true;' +
		'</script>' +
		'<body id="wpview-iframe-sandbox" class="' + bodcls + '">' +
			body +
		'</body>' +
		'<script type="text/javascript">' +
			'( function() {' +
				'["alignright", "aligncenter", "alignleft"].forEach( function( c, ix, ar ) {' +
					'var nc = "fix-" + c, mxi = 100,' +
					    'cur = document.getElementsByClassName( c ) || [];' +
					'for ( var i = 0; i < cur.length; i++ ) {' +
						'var e = cur[i],' +
						    'mx = 0 + mxi,' +
						    'iv = setInterval( function() {' +
								'var h = e.height || e.offsetHeight;' +
								'if ( h && h > 0 ) {' +
									'var cl = e.getAttribute( "class" );' +
									'cl = cl.replace( c, nc );' +
									'e.setAttribute( "class", cl );' +
									'h += 2; e.setAttribute( "height", h );' +
									'setTimeout( function() {' +
										'h -= 2; e.setAttribute( "height", h );' +
									'}, 250 );' +
									'clearInterval( iv );' +
								'} else {' +
									'if ( --mx < 1 ) {' +
										'clearInterval( iv );' +
									'}' +
								'}' +
							'}, 50 );' +
					'}' +
				'} );' +
			'}() );' +
		'</script>' +
	'</html>');
};

// Get / help the 'Add SWFPut Video' button
(function() {
	var btn = document.getElementById('evhvid-putvid-input-0');

	if ( btn != undefined ) {
		btn.onclick = 'return false;';
		btn.addEventListener(
			'click',
			function (e) {
				// must stop event due to way WP/jquery is handling
				// it propagated to ancestor element selected on
				// class .insert-media, which our button must use
				// for CSS snazziness
				e.stopPropagation();
				e.preventDefault();
				btn.blur();
				SWFPut_add_button_func(btn);
			},
			false
		);
	}
}());

// MVC
(function(wp, $, _, Backbone) {
	var media = wp.media,
		baseSettings = SWFPut_video_utility_obj.defprops,
		l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n,
	    mce  = wp.mce,
	    dbg  = true;

	var M = { // ...edia
		state: [],
	
		// setIframes copied from mce-view.js for broken call
		// to MutationObserver.observe() --
		// arg 1 was iframeDoc.body but body lacks interface Node
		// and more importantly we need to control the markup
		// written into the iframe document
		setIframes: function ( head, body, callback, rendered ) {
			var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver,
				self = this;

			this.getNodes( function( editor, node, contentNode ) {
				var dom = editor.dom,
					styles = '',
					bodyClasses = editor.getBody().className || '',
					editorHead = editor.getDoc().getElementsByTagName( 'head' )[0];

				tinymce.each( dom.$( 'link[rel="stylesheet"]', editorHead ), function( link ) {
					if ( link.href && link.href.indexOf( 'skins/lightgray/content.min.css' ) === -1 &&
						link.href.indexOf( 'skins/wordpress/wp-content.css' ) === -1 ) {

						styles += dom.getOuterHTML( link );
					}
				} );

				// Seems the browsers need a bit of time to insert/set the view nodes,
				// or the iframe will fail especially when switching Text => Visual.
				setTimeout( function() {
					var iframe, iframeDoc, observer, i;

					contentNode.innerHTML = '';

					iframe = dom.add( contentNode, 'iframe', {
						/* jshint scripturl: true */
						src: tinymce.Env.ie ? 'javascript:""' : '',
						frameBorder: '0',
						allowTransparency: 'true',
						scrolling: 'no',
						'class': 'wpview-sandbox',
						style: {
							width: '100%',
							display: 'block'
						}
					} );

					dom.add( contentNode, 'div', { 'class': 'wpview-overlay' } );

					iframeDoc = iframe.contentWindow.document;

					iframeDoc.open();
					iframeDoc.write(
						SWFPut_get_iframe_document( head, styles, bodyClasses, body )
					);
					iframeDoc.close();

					function resize() {
						var $iframe, iframeDocHeight;

						// Make sure the iframe still exists.
						if ( iframe.contentWindow ) {
							$iframe = $( iframe );
							iframeDocHeight = $( iframeDoc.body ).height();

							if ( $iframe.height() !== iframeDocHeight ) {
								$iframe.height( iframeDocHeight );
								editor.nodeChanged();
							}
						}
					}

					$( iframe.contentWindow ).on( 'load', resize );

					if ( MutationObserver ) {
						var n = iframeDoc; // iframeDoc.body // WP core bug -- had body
						observer = new MutationObserver( _.debounce( resize, 100 ) );

						observer.observe( n, {
							attributes: true,
							childList: true,
							subtree: true
						} );

						$( node ).one( 'wp-mce-view-unbind', function() {
							observer.disconnect();
						} );
					} else {
						for ( i = 1; i < 6; i++ ) {
							setTimeout( resize, i * 700 );
						}
					}

					function classChange() {
						iframeDoc.body.className = editor.getBody().className;
					}

					editor.on( 'wp-body-class-change', classChange );

					$( node ).one( 'wp-mce-view-unbind', function() {
						editor.off( 'wp-body-class-change', classChange );
					} );

					callback && callback.call( self, editor, node, contentNode );
				}, 50 );
			}, rendered );
		},

		// Sad hack: the replaceMarkers in wp.mce.view is overriden
		// because it fails when our captions have markup elements,
		// but in addition whitespace differs, so naive comps will
		// fail, and additionally in addition tinymce cannot refrain
		// from diddling with the elements and adds attributes that
		// cause comp fail; therefore, this string prep. function
		marker_comp_prepare: function(str) {
			var ostr,
			    rx1 = /[ \t]*data-mce[^=]*="[^"]*"/g,
			    rx2 = /[ \t]{2,}/g;

			ostr = str.substr(0).replace(rx1, '');
			if ( ostr ) {
				ostr = ostr.replace(rx2, ' ');
			}

			return ostr || str;
		},

		/**
		 * Replaces all marker nodes tied to this view instance.
		 * 
		 * EH: override here due to naive comparision that fails
		 * when captions have markup
		 */
		replaceMarkers: function() {
			this.getMarkers( function( editor, node ) {
				var c1 = M.marker_comp_prepare( $( node ).html() ),
				    c2 = M.marker_comp_prepare( this.text );

				if ( ! this.loader && c1 !== c2 ) {
					editor.dom.setAttrib( node, 'data-wpview-marker', null );
					return;
				}

				editor.dom.replace(
					editor.dom.createFragment(
						'<div class="wpview-wrap" data-wpview-text="' + this.encodedText + '" data-wpview-type="' + this.type + '">' +
							'<p class="wpview-selection-before">\u00a0</p>' +
							'<div class="wpview-body" contenteditable="false">' +
								'<div class="wpview-content wpview-type-' + this.type + '"></div>' +
							'</div>' +
							'<p class="wpview-selection-after">\u00a0</p>' +
						'</div>'
					),
					node
				);
			} );
		},

		/**
		 * Tries to find a text match in a given string.
		 *
		 * @param {String} content The string to scan.
		 *
		 * @return {Object}
		 * 
		 * EH: originally overridden for debugging, now
		 * kept in place to add capencoded= to attrs
		 */
		match: function( content ) {
			//var match = wp.shortcode.next( this.type, content );
			var rx = /\[(\[?)(putswf_video)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*(?:\[(?!\/\2\])[^\[]*)*)(\[\/\2\]))?)(\]?)/g,
			    match = rx.exec( content );

			if ( match ) {
				var c1, c2;

				c1 = ' capencoded="' + encodeURIComponent(match[5]) + '"';
				c2 = match[3].indexOf(' capencoded=');
				if ( c2 < 0 ) {
					c2 = match[3] + c1;
				} else {
					c2 = match[3].replace(/ capencoded="[^"]*"/g, c1);
				}
				
				return {
					index: match.index,
					content: match[0],
					options: {
						shortcode: new wp.shortcode({
							tag:     match[2],
							attrs:   c2,
							type:    match[6] ? 'closed' : 'single',
							content: match[5]
						})
					}
				};
			}
		},

		edit: function( text, update ) {
			var media = wp.media[ this.type ],
				frame = media.edit( text );

			this.pausePlayers && this.pausePlayers();

			_.each( this.state, function( state ) {
				frame.state( state ).on( 'update', function( selection ) {
					var s = media.shortcode( selection ).string()
					update( s );
				} );
			} );

			frame.on( 'close', function() {
				frame.detach();
			} );

			frame.open();
		}
	};

	var V = _.extend( {}, M, { // ...ideo
		action: 'parse_putswf_video_shortcode',

		initialize: function() {
			var self = this;
			
			this.fetch();
			
			this.getEditors( function( editor ) {
				editor.on( 'wpview-selected', function() {
					self.pausePlayers();
				} );
			} );
		},

		fetch: function () {
			var self = this,
			    atts = SWFPut_video_utility_obj.atts_filter(
			        this.shortcode.attrs.named),
			    sc =
			        SWFPut_video_utility_obj.mk_shortcode(
			            this.shortcode.tag,
			            atts,
			            this.shortcode.content),
			    ng = this.shortcode.string();

			wp.ajax.send( this.action, {
				data: {
					post_ID: $( '#post_ID' ).val() || 0,
					type: this.shortcode.tag,
					shortcode: sc
				}
			} )
			.done( function( response ) {
				self.render( response );
			} )
			.fail( function( response ) {
				if ( self.url ) {
					self.removeMarkers();
				} else {
					self.setError( response.message || response.statusText, 'admin-media' );
				}
			} );
		},

		stopPlayers: function( event_arg ) {
			var rem = event_arg; // might be Event or string

			this.getNodes( function( editor, node, content ) {
				var p, win,
					iframe = $( 'iframe.wpview-sandbox', content ).get(0);

				if ( iframe && ( win = iframe.contentWindow ) && win.evhh5v_sizer_instances ) {
					try {
						for ( p in win.evhh5v_sizer_instances ) {
							var vi = win.evhh5v_sizer_instances[p],
							    v = vi.va_o || false, // H5V
							    f = vi.o    || false, // flash
							    act = (event_arg === 'pause')
							        ? 'pause' : 'stop';

							// use 'stop()' or 'pause()'
							// the latter is gentler
							if ( v && (typeof v[act] === 'function') ) {
								v[act]();
							}
							if ( f && (typeof f[act] === 'function') ) {
								f[act]();
							}
						}
					} catch( err ) {
						var e = err.message;
					}
					
					if ( rem === 'remove' ) {
						iframe.contentWindow = null;
						iframe = null;
					}
				}
			});
		},

		pausePlayers: function() {
			this.stopPlayers && this.stopPlayers( 'pause' );
		},

	} );

	mce.views.register( 'putswf_video', _.extend( {}, V, {
		state: [ 'putswf_video-details' ]
	} ) );
	

	// NOTE: several of the objects below have a 'media' object
	// usually assigned in initialize() -- but these are not necessarily
	// the same type, much less same obj.

	// MODEL: available as 'data.model' within frame content template
	media.model.putswf_postMedia = Backbone.Model.extend({

		SWFPut_cltag: 'media.model.putswf_postMedia',

		// called with shortcode attributes including shortcode object
		initialize: function(o) {
			this.attachment = this.initial_attrs = false;

			if ( o !== undefined && o.shortcode !== undefined ) {
				var that = this, sc = o.shortcode,
				    pat = /^[ \t]*([^ \t]*(.*[^ \t])?)[ \t]*$/;
				
				this.initial_attrs = o;
				this.poster  = '';
				this.flv     = '';
				this.html5s  = [];
				
				if ( sc.iimage ) {
					var m = pat.exec(sc.iimage);
					this.poster = (m && m[1]) ? m[1] : sc.iimage;
				}
				if ( sc.url ) {
					var m = pat.exec(sc.url);
					this.flv = (m && m[1]) ? m[1] : sc.url;
				}
				if ( sc.altvideo ) {
					var t = sc.altvideo, a = t.split('|');
					for ( var i = 0; i < a.length; i++ ) {
						var m = pat.exec(a[i]);
						if ( m && m[1] ) {
							this.html5s.push(m[1]);
						}
					}
				}
				
				SWFPut_cache_shortcode_ids( sc, function(id, r, c) {
					var sid = '' + id;

					that.initial_attrs.id_cache = c;
					if ( that.initial_attrs.id_array === undefined ) {
						that.initial_attrs.id_array = [];
					}

					that.initial_attrs.id_array.push(id);

					if ( that.poster === sid ) {
						that.poster = c[id];
					} else if ( that.flv === sid ) {
						that.flv = c[id];
					} else if ( that.html5s !== null ) {
						for ( var i = 0; i < that.html5s.length; i++ ) {
							if ( that.html5s[i] === sid ) {
								that.html5s[i] = c[id];
							}
						}
					}
				} );
			}
		},
		
		poster : null,
		flv    : null,
		html5s : null,
		
		setSource: function( attachment ) {
			this.attachment = attachment;
			this.extension = attachment.get( 'filename' ).split('.').pop();

			if ( this.get( 'src' ) && this.extension === this.get( 'src' ).split('.').pop() ) {
				this.unset( 'src' );
			}

			if ( _.contains( wp.media.view.settings.embedExts, this.extension ) ) {
				this.set( this.extension, this.attachment.get( 'url' ) );
			} else {
				this.unset( this.extension );
			}

			try {
				var am, multi = attachment.get( 'putswf_attach_all' );
				
				if ( multi && multi.toArray().length < 1 ) {
					delete this.attachment.putswf_attach_all;
				}
			} catch ( e ) {
			}
		},

		changeAttachment: function( attachment ) {
			var self = this;

			this.setSource( attachment );

			this.unset( 'src' );
			_.each( _.without( wp.media.view.settings.embedExts, this.extension ), function( ext ) {
				self.unset( ext );
			} );
		}
		,

		// methods specific to SWFPut

		cleanup_media: function() {
			var a = [],
			    mp4 = false, ogg = false, webm = false;
			
			for ( var i = 0; i < this.html5s.length; i++ ) {
				var m = this.html5s[i];
				
				if ( typeof m === 'object' ) {
					var t = m.subtype
					        ? (m.subtype.split('-').pop())
					        : (m.filename
					            ? m.filename.split('.').pop()
					            : false
					        );

					// last wins
					switch ( t ) {
						case 'mp4':
						case 'm4v':
						case 'mv4':
							mp4 = m;
							break;
						case 'ogg':
						case 'ogv':
						case 'vorbis':
							ogg = m;
							break;
						case 'webm':
						case 'wbm':
						case 'vp8':
						case 'vp9':
							webm = m;
							break;
					}
				} else {
					a.push(m);
				}
			}
			
			if ( mp4 ) {
				a.push(mp4);
			}
			if ( ogg ) {
				a.push(ogg);
			}
			if ( webm ) {
				a.push(webm);
			}
		
			this.html5s = a;
		},
		
		// get uri related strings -- if display is true
		// get URL if available as it's informative;
		// else use id integers if available
		get_poster: function( display ) {
			display = display || false;
			
			if ( display && this.poster !== null ) {
				return ( typeof this.poster === 'object' )
				    ? this.poster.url
				    : this.poster;
			}

			if ( this.poster !== null ) {
				return ( typeof this.poster === 'object' )
				    ? ('' + this.poster.id)
				    : this.poster;
			}
			
			return '';
		},
		
		get_flv: function( display ) {
			display = display || false;
			
			if ( display && this.flv !== null ) {
				return ( typeof this.flv === 'object' )
				    ? this.flv.url
				    : this.flv;
			}

			if ( this.flv !== null ) {
				return ( typeof this.flv === 'object' )
				    ? ('' + this.flv.id)
				    : this.flv;
			}
			
			return '';
		},
		
		get_html5s: function( display ) {
			var s ='';
			display = display || false;
			
			if ( this.html5s === null ) {
				return s;
			}

			for ( var i = 0; i < this.html5s.length; i++ ) {
				var cur = this.html5s[i], addl = '';

				if ( s !== '' ) {
					addl += ' | ';
				}

				addl += ( typeof cur === 'object' )
				    ? (display ? cur.url : ('' + cur.id))
				    : cur;
			
				s += addl;
			}

			return s;
		},
		
		putswf_postex: function() {
		},

	}); // media.model.putswf_postMedia = Backbone.Model.extend({

	// media.view.MediaFrame.Select -> media.view.MediaFrame.MediaDetails
	media.view.MediaFrame.Putswf_mediaDetails = media.view.MediaFrame.Select.extend({ //media.view.MediaFrame.MediaDetails.extend({ //
		defaults: {
			id:      'putswf_media',
			//id:      'media',
			url:     '',
			menu:    'media-details',
			content: 'media-details',
			toolbar: 'media-details',
			type:    'link',
			priority: 121 // 120
		},

		SWFPut_cltag: 'media.view.MediaFrame.Putswf_mediaDetails',

		initialize: function( options ) {
			//var controller = options.controller || false,
			//    model = options.model || false,
			//    attachment = options.attachment || false;
			
			this.DetailsView = options.DetailsView;
			this.cancelText = options.cancelText;
			this.addText = options.addText;

			this.media = new media.model.putswf_postMedia( options.metadata ); //PostMedia( options.metadata );
			this.options.selection = new media.model.Selection( this.media.attachment, { multiple: true } );// { multiple: false } ); //
			media.view.MediaFrame.Select.prototype.initialize.apply( this, arguments );
			//media.view.MediaFrame.MediaDetails.prototype.initialize.apply( this, arguments );
		}
		,

		bindHandlers: function() {
			var menu = this.defaults.menu;

			media.view.MediaFrame.Select.prototype.bindHandlers.apply( this, arguments );
			//media.view.MediaFrame.MediaDetails.prototype.bindHandlers.apply( this, arguments );

			this.on( 'menu:create:' + menu, this.createMenu, this );
			this.on( 'content:render:' + menu, this.renderDetailsContent, this );
			this.on( 'menu:render:' + menu, this.renderMenu, this );
			this.on( 'toolbar:render:' + menu, this.renderDetailsToolbar, this );
		},

		// lots of following code copied right from
		// wp-includes/js/media-audiovideo.js
		renderDetailsContent: function() {
			var attach = this.state().media.attachment;
			var view = new this.DetailsView({
				controller: this,
				model: this.state().media,
				attachment: attach //this.state().media.attachment // 
			}).render();

			this.content.set( view );
		}
		,
		renderMenu: function( view ) {
			var lastState = this.lastState(),
				previous = lastState && lastState.id,
				frame = this;
	
			view.set({
				cancel: {
					text:     this.cancelText,
					priority: 20,
					click:    function() {
						if ( previous ) {
							frame.setState( previous );
						} else {
							frame.close();
						}
					}
				},
				separateCancel: new media.View({
					className: 'separator',
					priority: 40
				})
			});
	
		},

		setPrimaryButton: function(text, handler) {
			this.toolbar.set( new media.view.Toolbar({
				controller: this,
				items: {
					button: {
						style:    'primary',
						text:     text,
						priority: 80,
						click:    function() {
							var controller = this.controller;
							handler.call( this, controller, controller.state() );
							// Restore and reset the default state.
							controller.setState( controller.options.state );
							controller.reset();
						}
					}
				}
			}) );
		},

		renderDetailsToolbar: function() {
			this.setPrimaryButton( l10n.update, function( controller, state ) {
				controller.close();
				state.trigger( 'update', controller.media.toJSON() );
			} );
		},

		renderReplaceToolbar: function() {
			this.setPrimaryButton( l10n.replace, function( controller, state ) {
				var attachment = state.get( 'selection' ).single();
				controller.media.changeAttachment( attachment );
				state.trigger( 'replace', controller.media.toJSON() );
			} );
		},

		renderAddSourceToolbar: function() {
			this.setPrimaryButton( this.addText, function( controller, state ) {
				var attachment = state.get( 'selection' ).single();
				controller.media.setSource( attachment );
				state.trigger( 'add-source', controller.media.toJSON() );
			} );
		}

	}); // media.view.MediaFrame.Putswf_mediaDetails = media.view.MediaFrame.MediaDetails.extend({ // = media.view.MediaFrame.Select.extend({

	media.view.SWFPutDetails = media.view.Settings.AttachmentDisplay.extend({
		SWFPut_cltag: 'media.view.SWFPutDetails',

		initialize: function() {
			_.bindAll(this, 'success');
			this.players = [];
			this.listenTo( this.controller, 'close', media.putswf_mixin.unsetPlayers );
			this.on( 'ready', this.setPlayer );
			this.on( 'media:setting:remove', media.putswf_mixin.unsetPlayers, this );
			this.on( 'media:setting:remove', this.render );
			this.on( 'media:setting:remove', this.setPlayer );
			this.events = _.extend( this.events, {
				'click .remove-setting' : 'removeSetting',
				//'change .content-track' : 'setTracks',
				//'click .remove-track' : 'setTracks',
				'click .add-media-source' : 'addSource'
			} );

			media.view.Settings.AttachmentDisplay.prototype.initialize.apply( this, arguments );
		},

		prepare: function() {
			var model = this.model;

			return _.defaults({
				model: model //model: this.model.toJSON()
			}, this.options );
		},

		/**
		 * Remove a setting's UI when the model unsets it
		 *
		 * @fires wp.media.view.MediaDetails#media:setting:remove
		 *
		 * @param {Event} e
		 */
		removeSetting : function(e) {
			var wrap = $( e.currentTarget ).parent(), setting;
			setting = wrap.find( 'input' ).data( 'setting' );

			if ( setting ) {
				this.model.unset( setting );
				this.trigger( 'media:setting:remove', this );
			}

			wrap.remove();
		},

		/**
		 *
		 * @fires wp.media.view.MediaDetails#media:setting:remove
		 */
		setTracks : function() {
			//var tracks = '';
            //
			//_.each( this.$('.content-track'), function(track) {
			//	tracks += $( track ).val();
			//} );
            //
			//this.model.set( 'content', tracks );
			//this.trigger( 'media:setting:remove', this );
		},

		addSource : function( e ) {
			this.controller.lastMime = $( e.currentTarget ).data( 'mime' );
			this.controller.setState( 'add-' + this.controller.defaults.id + '-source' );
		},

		/**
		 * @global MediaElementPlayer
		 */
		setPlayer : function() {
			//if ( ! this.players.length && this.media ) {
			//	this.players.push( new MediaElementPlayer( this.media, this.settings ) );
			//}
		},

		/**
		 * @abstract
		 */
		setMedia : function() {
			return this;
		},

		success : function(mejs) {
			//var autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
            //
			//if ( 'flash' === mejs.pluginType && autoplay ) {
			//	mejs.addEventListener( 'canplay', function() {
			//		mejs.play();
			//	}, false );
			//}
            //
			//this.mejs = mejs;
		},

		/**
		 * @returns {media.view.MediaDetails} Returns itself to allow chaining
		 */
		render: function() {
			var self = this;

			media.view.Settings.AttachmentDisplay.prototype.render.apply( this, arguments );
			setTimeout( function() { self.resetFocus(); }, 10 );

			this.settings = _.defaults( {
				success : this.success
			}, baseSettings );

			return this.setMedia();
		},

		resetFocus: function() {
			this.$( '.putswf_video-details-iframe' ).scrollTop( 0 );
		}
	}, {
		instances : 0,

		/**
		 * When multiple players in the DOM contain the same src, things get weird.
		 *
		 * @param {HTMLElement} elem
		 * @returns {HTMLElement}
		 */
		// EH: above is orig comment from WP core
		prepareSrc : function( elem ) {
			var i = media.view.SWFPutDetails.instances++;
			// EH: in SWFPut the following loop will only be effactive
			// if sources were set in the metabox along with
			// types -- otherwise harmless
			_.each( $( elem ).find( 'source' ), function( source ) {
				source.src = [
					source.src,
					source.src.indexOf('?') > -1 ? '&' : '?',
					'_=',
					i
				].join('');
			} );

			return elem;
		}
	});

	//media.view.Putswf_videoDetails = media.view.MediaFrame.Putswf_mediaDetails.extend({
	media.view.Putswf_videoDetails = media.view.SWFPutDetails.extend({
		//className: 'putswf_video-details',
		className: 'putswf_video-mediaframe-details',
		template:  media.template('putswf_video-details'),

		SWFPut_cltag: 'media.view.Putswf_videoDetails',

		initialize: function() {
			_.bindAll(this, 'success');
			this.players = [];
			this.listenTo( this.controller, 'close', wp.media.putswf_mixin.unsetPlayers );
			this.on( 'ready', this.setPlayer );
			this.on( 'media:setting:remove', wp.media.putswf_mixin.unsetPlayers, this );
			this.on( 'media:setting:remove', this.render );
			this.on( 'media:setting:remove', this.setPlayer );
			this.events = _.extend( this.events, {
			//	'click .remove-setting' : 'removeSetting',
			//	'change .content-track' : 'setTracks',
			//	'click .remove-track' : 'setTracks',
				'click .add-media-source' : 'addSource'
			} );

			this.init_data = (arguments && arguments[0])
			    ? arguments[0] : false;
			
			media.view.SWFPutDetails.prototype.initialize.apply( this, arguments );
		},

		setMedia: function() {
			var v1 = this.$('.evhh5v_vidobjdiv'),
				v2 = this.$('.wp-caption'),
				video = v1 || v2,
				found = video.find( 'video' )
				     || video.find( 'canvas' )
				     || video.find( 'object' );

			if ( found ) {
				video.show();
				this.media = media.view.SWFPutDetails.prepareSrc( video.get(0) );
			} else {
				video.hide();
				this.media = false;
			}

			return this;
		}
	});

	// TITLE
	media.controller.Putswf_videoDetails = media.controller.State.extend({
		defaults: {
			id: 'putswf_video-details', //'putswf_video', //
			toolbar: 'putswf_video-details',
			title: 'SWFPut Video Details', //l10n.putswf_videoDetailsTitle,
			content: 'putswf_video-details',
			menu: 'putswf_video-details',
			router: false,
			priority: 60
		},

		SWFPut_cltag: 'media.controller.Putswf_videoDetails',

		initialize: function( options ) {
			// media should be a media.model.putswf_postMedia
			this.media = options.media;
			media.controller.State.prototype.initialize.apply( this, arguments );
		}

		,
		setSource: function( attachment ) {
			this.attachment = attachment;
			this.extension = attachment.get( 'filename' ).split('.').pop();

			//if ( this.get( 'src' ) && this.extension === this.get( 'src' ).split('.').pop() ) {
			//	this.unset( 'src' );
			//}
            //
			//if ( _.contains( wp.media.view.settings.embedExts, this.extension ) ) {
			//	this.set( this.extension, this.attachment.get( 'url' ) );
			//} else {
			//	this.unset( this.extension );
			//}
		}

	});

	// media.view.MediaFrame.Putswf_mediaDetails
	media.view.MediaFrame.Putswf_videoDetails = media.view.MediaFrame.Putswf_mediaDetails.extend({ // media.view.MediaFrame.MediaDetails.extend({
		defaults: {
			id:      'putswf_video',
			url:     '',
			menu:    'putswf_video-details',
			content: 'putswf_video-details',
			toolbar: 'putswf_video-details',
			type:    'link',
			title:    'SWFPut Video -- Media', //l10n.putswf_videoDetailsTitle,
			priority: 120
		},

		SWFPut_cltag: 'media.view.MediaFrame.Putswf_videoDetails',

		initialize: function( options ) {
			this.media = options.media;
			options.DetailsView = media.view.Putswf_videoDetails;
			options.cancelText = 'Cancel Edit'; //l10n.putswf_videoDetailsCancel;
			options.addText = 'Add Video'; //l10n.putswf_videoAddSourceTitle;
			media.view.MediaFrame.Putswf_mediaDetails.prototype.initialize.call( this, options );
		},

		bindHandlers: function() {
			media.view.MediaFrame.Putswf_mediaDetails.prototype.bindHandlers.apply( this, arguments );

			this.on( 'toolbar:render:replace-putswf_video', this.renderReplaceToolbar, this );
			this.on( 'toolbar:render:add-putswf_video-source', this.renderAddSourceToolbar, this );
			this.on( 'toolbar:render:putswf_poster-image', this.renderSelectPosterImageToolbar, this );
			//this.on( 'toolbar:render:add-track', this.renderAddTrackToolbar, this );
		},

		createStates: function() {
			this.states.add([
				new media.controller.Putswf_videoDetails( {
					media: this.media
				} ),

				new media.controller.MediaLibrary( {
					type: 'video',
					id: 'replace-putswf_video',
					title: 'Replace Media', //l10n.putswf_videoReplaceTitle,
					toolbar: 'replace-putswf_video',
					media: this.media,
					menu: 'putswf_video-details'
				} ),
            
				new media.controller.MediaLibrary( {
					type: 'video',
					id: 'add-putswf_video-source',
					title: 'Add Media', //l10n.putswf_videoAddSourceTitle,
					toolbar: 'add-putswf_video-source',
					media: this.media,
					multiple: true,
					//syncSelection: true,
					menu: 'putswf_video-details'
					//menu: false
				} ),
            
				new media.controller.MediaLibrary( {
					type: 'image',
					id: 'select-poster-image',
					title: l10n.SelectPosterImageTitle
					    ? l10n.SelectPosterImageTitle
					    : 'Set Initial (poster) Image',
					toolbar: 'putswf_poster-image',
					media: this.media,
					menu: 'putswf_video-details'
				} ),
			]);
		},

		renderSelectPosterImageToolbar: function() {
			this.setPrimaryButton( 'Select Poster Image', function( controller, state ) {
				var attachment = state.get( 'selection' ).single();

				attachment.attributes.putswf_action = 'poster';
				controller.media.changeAttachment( attachment );
				state.trigger( 'replace', controller.media.toJSON() );
			} );
		},

		renderReplaceToolbar: function() {
			this.setPrimaryButton( 'Replace Video', function( controller, state ) {
				var attachment = state.get( 'selection' ).single();

				attachment.attributes.putswf_action = 'replace_video';
				controller.media.changeAttachment( attachment );
				state.trigger( 'replace', controller.media.toJSON() );
			} );
		},

		renderAddSourceToolbar: function() {
			this.setPrimaryButton( this.addText, function( controller, state ) {
				var attachment = state.get( 'selection' ).single(); //; //
				var attach_all = state.get( 'selection' ) || false;

				attachment.attributes.putswf_action = 'add_video';
				// w/ multiple add the full del monte --
				// dirty hack unto blech -- but there are errors w/
				// the multiple selection I haven't figured out yet
				if ( attach_all && attach_all.multiple ) {
					attachment.attributes.putswf_attach_all = attach_all;
				}

				controller.media.setSource( attachment );
				state.trigger( 'add-source', controller.media.toJSON() );
			} );
		},
	});

	wp.media.putswf_mixin = {
		putswfSettings: baseSettings,

		SWFPut_cltag: 'wp.media.putswf_mixin',

		removeAllPlayers: function() {
		},

		/**
		 * Override the MediaElement method for removing a player.
		 *	MediaElement tries to pull the audio/video tag out of
		 *	its container and re-add it to the DOM.
		 */
		removePlayer: function(t) {
		},
		/**
		 * Allows any class that has set 'player' to a MediaElementPlayer
		 *  instance to remove the player when listening to events.
		 *
		 *  Examples: modal closes, shortcode properties are removed, etc.
		 */
		unsetPlayers : function() {
		}
	}; // wp.media.putswf_mixin = {

	// Sort of basic media type object: the MCE view object gets
	// one of the from wp.media[ this.type ] in its edit method
	// then calls edit on this
	wp.media.putswf_video = {
		//coerce : wp.media.coerce,

		defaults : baseSettings,

		SWFPut_cltag: 'wp.media.putswf_video',

		_mk_shortcode : SWFPut_video_utility_obj.mk_shortcode,

		_atts_filter : SWFPut_video_utility_obj.atts_filter,

		// called by MCE view::edit -- the arg is
		// an unmolested shortcode string
		edit : function( data ) {
			var frame,
				shortcode = wp.shortcode.next( 'putswf_video', data ).shortcode,
				attrs, aext,
				MediaFrame = media.view.MediaFrame;

			attrs = shortcode.attrs.named;
			attrs.content = shortcode.content;
			attrs.shortcode = shortcode;
			aext = {
				frame: 'putswf_video',
				state: 'putswf_video-details',
				metadata: _.defaults( attrs, this.defaults )
			};

			frame = new media.view.MediaFrame.Putswf_videoDetails( aext );
			
			media.frame = frame;

			return frame;
		},

		// the MCE view object update callback calls this (statically,
		// dont't use this) as in:
		// var shortcode = wp.media[ self.type ].shortcode( selection ).string();
		shortcode : function( model_atts ) { // arg is "selection"
			var content, sc, atts;

			sc = model_atts.shortcode.tag;
			content = model_atts.content; //.substr(0);
			atts = wp.media.putswf_video._atts_filter(model_atts);

			// code elsewhere adds props to selection, so the use of
			// _atts_filter() is required to make a working
			// shortcode
			//return wp.media.putswf_video._mk_shortcode( sc, atts, content );
			return new wp.shortcode({
				tag: sc,
				attrs: atts,
				content: content
			});
		}
	};

}(wp, jQuery, _, Backbone));

