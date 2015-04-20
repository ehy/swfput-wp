<?php
/*
 *      putswf_tpl.php
 *      
 *      Copyright 2015 Ed Hynan <edhynan@gmail.com>
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

/* text editor: use real tabs of 4 column width, LF line ends */
/* human coder: keep line length <= 72 columns; break at params */

/**
 * This php file exists in the 'js' directory because it exists
 * in service of script(s) there -- it makes a template for
 * Underscores/Backbone, but using the operator overloads WP
 * defines in wp-includes/js/wp-util.js; it is used from there.
 */

/**
 *  scripts here have an object available named "data" with
 *  property objects controller, model, attchment, selection,
 *  mode, title, modal, uploader, library, multiple, and
 *  state ( == library)
 */
// NOTE on "template:" below: it is underscares compiled, and the
// the default compilation operators are overridden by WP in
// "options":
 //19                         options = {
 //20                                 evaluate:    /<#([\s\S]+?)#>/g,
 //21                                 interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
 //22                                 escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
 //23                                 variable:    'data'
 //24                         };
// Lines above are from wp-includes/js/wp-util.js
// -- see "http://underscorejs.org/#template"
?>
<script type="text/html" id="tmpl-putswf_video-details">
	<#
		var ifrm = false,
		dvif = false,
		head = '',
		cont = '',
		_putswf_mk_shortcode = wp.media.putswf_video._mk_shortcode,
		_putswf__putfrm = function () {
			var _tifd = '', _tif = '', _intvl;

			_intvl = setInterval( function () {
				var h, ivl = _intvl;
				
				// ready? or what
				if ( cont === '' ) {
					// TODO: wait indication
					console.log('SWFPut markup fetch by ajax waiting');
					return;
				} else if ( head === false ) {
					// TODO: better error message
					clearInterval(ivl);
					console.log('SWFPut markup fetch by ajax failed '+cont);
					_tif = document.createElement('span');
					_tif.innerHTML = cont;
					_tifd.appendChild(_tif);
					ifrm = false;
					return;
				}
				
				clearInterval(ivl);

				h = ! data.model.height ? 360 : data.model.height;
				_tifd = document.getElementById('putswf-dlg-content-wrapper');
				dvif = _tifd || false;

				_tif = document.createElement('iframe');
				ifrm = _tif;
				_tifd.appendChild(_tif);
				_tif.setAttribute('id', 'putswf-dlg-content-iframe');
				_tif.setAttribute('style', 'width:100%; height:'+h+'px;');
				_tif.setAttribute('class', 'putswf_video-details-iframe');
				_tif = (_tif.contentWindow) ? _tif.contentWindow : (_tif.contentDocument.document) ? _tif.contentDocument.document : _tif.contentDocument;
				_tif.document.open();
				_tif.document.write(
					'<!DOCTYPE html>' +
					'<html>' +
						'<head>' +
							'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' +
								head +
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
								'.alignleft {' +
								'display: inline;' +
								'float: left;' +
								'margin-right: 1.5em; }' +
								'' +
								'.alignright {' +
								'display: inline;' +
								'float: right;' +
								'margin-left: 1.5em; }' +
								'' +
								'.aligncenter {' +
								'clear: both;' +
								'display: block;' +
								'margin: 0 auto; }' +
								'' +
								'.alignright .caption {' +
								'padding-bottom: 0;' +
								'margin-bottom: 0; }' +
								'' +
								'.alignleft .caption {' +
								'padding-bottom: 0;' +
								'margin-bottom: 0; }' +
								'' +
								'.aligncenter .caption {' +
								'padding-bottom: 0;' +
								'margin-bottom: 0.75rem; }' +
							'</style>' +
						'</head>' +
						'<body id="putswf-iframe-body-wrapper" class="aint-got-none">' +
							'<div id="putswf-iframe-content-wrapper">' +
								cont +
								'<br/><span>&nbsp;</span>' +
							'</div>' +
						'</body>' +
					'</html>'
				); 
				_tif.document.close();
	
				var mutobsvr = window.MutationObserver
					|| window.WebKitMutationObserver
					|| window.MozMutationObserver || false,
				    resize = function() {
					if ( ifrm && _tif ) {
						var hd = jQuery( _tif.document.body ).height(),
						    hf = jQuery( ifrm ).height();
						// BUG: need some padding
						hd += 4;
						if ( hf < hd ) {
							jQuery( ifrm ).height( hd );
						}
					}
				};

				try {
					if ( mutobsvr ) {
						var nod = _tif.document;

						new mutobsvr( _.debounce( function() {
							resize();
						}, 100 ) )
						.observe( nod, {
							attributes: true,
							childList: true,
							subtree: true
						} );
					} else {
						throw ReferenceError('MutationObserver not supported');
					}
				} catch ( exptn ) {
					console.log('Exception: ' + exptn.message);
					for ( i = 1; i < 6; i++ ) {
						setTimeout( resize, i * 700 );
					}
				}
			}, 50 );
		},
		// TODO: this proc should maybe be a method of the data model
		_putswf_frolic_in_data = function (d) {
			var self = this,
			    oldatts = d.model.attributes,
			    newatts = (d.attachment && d.attachment.attributes)
			        ? d.attachment.attributes : false,
			    sctag   = oldatts.shortcode.tag,
			    caption = ( newatts && newatts.caption )
			        ? newatts.caption : oldatts.content
			    vid_add =
			        ( newatts && newatts.putswf_action === 'add_video' )
			        ? true : false,
			    vid_rpl =
			        ( newatts && newatts.putswf_action === 'replace_video' )
			        ? true : false,
			    vid_op = vid_add || vid_rpl || false,
			    // HACK: multi obj tagged onto single() obj
			    // for 'Add video' multiple selection
			    multi = newatts.putswf_attach_all || false;

			// Media frame poster tab
			if ( newatts && newatts.putswf_action === 'poster' ) {
				var uri =
				    newatts.id || newatts.url || newatts.link || '';
				oldatts.iimage = uri;
			}
			
			// Media frame add/replace video tab
			if ( vid_op ) {
				if ( vid_rpl ) {
					// TEMP until cation option
					oldatts.content = caption;
				}

				if ( newatts && (newatts.id || newatts.url) || multi ) {
					var m = newatts.id || newatts.url,
					    t = newatts.subtype
					        ? (newatts.subtype.split('-').pop())
					        : (newatts.filename
					            ? newatts.filename.split('.').pop()
					            : false
					        );

					oldatts.altvideo = oldatts.altvideo || '';
					
					if ( ! multi && t && t.toLowerCase() === 'flv' ) {
						oldatts.url = m;
						oldatts.altvideo = '';
					} else {
						// Replace
						if ( vid_rpl ) {
							if ( t && t.toLowerCase() === 'flv' ) {
								oldatts.url = m;
								oldatts.altvideo = '';
							} else {
								oldatts.altvideo = m;
								oldatts.url = '';
							}
						// Add -- multiple selection
						} else {
							// for html5 video, shortcode attr accepts
							// '|' separated list -- presumably the
							// same video in the supported types
							// (but not necessarily so)
							var am = [];

							if ( newatts.putswf_attach_all ) {
								var ta = newatts.putswf_attach_all.toArray();

								for ( var i = 0; i < ta.length; i++ ) {
									var tatt = ta[i].attributes,
								        t = tatt.subtype
								            ? ( tatt.subtype.split('-').pop())
								            : ( tatt.filename
								                    ? tatt.filename.split('.').pop()
								                    : false );

									am[i] = {
										uri: tatt.id || tatt.url,
										flv: ( t && t.toLowerCase() === 'flv' ) === true
									};
								}
							} else {
								am[0] = {
									uri: m,
									flv: ( t && t.toLowerCase() === 'flv' ) === true
								};
							}

							for ( var i = 0; i < am.length; i++ ) {
								var o;
								
								m = am[i].uri;
								
								if ( am[i].flv ) {
									oldatts.url = m; // last one wins
									continue;
								}
								
								if ( oldatts.altvideo.indexOf(m) >= 0 ) {
									continue;
								}
								
								o = oldatts.altvideo.length > 0
								    ? (oldatts.altvideo + '|') : '';
								
								oldatts.altvideo = o + m;
							}
						}
					}
				}
			} else {
				caption = oldatts.content;
			}

			return {
				code: _putswf_mk_shortcode(sctag, oldatts, caption),
				tag:  sctag
			};
		},
		_putswf_fetch = function () {
			var self = this,
			    pid = jQuery( '#post_ID' ).val() || 0,
			    atts = _putswf_frolic_in_data(data),
			    sctag = atts.tag,
			    scstr = atts.code;

			wp.ajax.send( 'parse_putswf_video_shortcode', {
				data: {
					post_ID: pid,
					type: sctag,
					shortcode: scstr
				}
			} )
			.done( function( response ) {
				if ( response ) {
 					head = response.head;
					cont = response.body;
				} else {
					head = false;
					cont = '<p>FAIL to get wp_ajax response</p>'
					console.log('.DONE BAD: CONT: ' + cont);
				}
				_putswf__putfrm();
			} )
			.fail( function( response ) {
				head = false;
				cont = '<p>FAIL onwp_ajax request</p>'
				console.log('.FAIL BAD: CONT: ' + cont);
				_putswf__putfrm();
			} );
		};
		
		_putswf_fetch();
	#>
	<div id="putswf-dlg-content-wrapper">
	</div>
	<#
	if ( false ) {
		var dat = data.model;
		for ( var t in dat ) {
			console.log("TMPL: data.model."+t+" == "+dat[t]);
		}
		//dat = data.model.attributes;
		//for ( var t in dat ) {
		//	console.log("ATTR: dat.attributes."+t+" == "+dat[t]);
		//}
		//dat = data.model.attributes.shortcode;
		//for ( var t in dat ) {
		//	console.log("SCOD: dat."+t+" == "+dat[t]);
		//}
		//console.log("SCOD.string: dat.string() == " + dat.string());
		//if ( data.attachment.attributes ) {
		//	var dat = data.attachment.attributes;
		//	for ( var t in dat ) {
		//		console.log("DATA: dat."+t+" == "+dat[t]);
		//	}
		//}
		
		//dat = data;
		//for ( var t in dat ) {
		//	console.log("DATA: prop "+t+" is  "+ (dat[t].SWFPut_cltag ? dat[t].SWFPut_cltag : 'NO SWFPUT TAG!' ) );
		//}
	}
	#>
</script>
