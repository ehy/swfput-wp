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
?>
<script type="text/html" id="tmpl-putswf_video-details">
	<#
		var ifrm = false,
		dvif = false,
		head = '',
		cont = '',
		_putswf_mk_shortcode = function(sc, atts, cap) {
			var c = cap || '', s = '[' + sc,
			    defs = SWFPut_video_utility_obj.defprops;
			for ( var t in atts ) {
				if ( defs[t] === undefined ) {
					continue;
				}
				
				s += ' ' + t + '="' + atts[t] + '"';
			}
			return s + ']' + c + '[/' + sc + ']'
		},
		_putswf__putfrm = function () {
			var _tifd = '', _tif = '';
			setTimeout( function () {
				var h = ! data.model.height ? 360 : data.model.height;

				_tifd = document.getElementById('putswf-dlg-content-wrapper');
				dvif = _tifd || false;

				if ( head === false ) {
					_tif = document.createElement('span');
					_tif.innerHTML = cont;
					_tifd.appendChild(_tif);
					ifrm = false;
					return
				}
				
				_tif = document.createElement('iframe');
				_tifd.appendChild(_tif);
				_tif.setAttribute('id', 'putswf-dlg-content-iframe');
				_tif.setAttribute('style', 'width:100%; height:'+h+'px;');
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
							'</div>' +
						'</body>' +
					'</html>'
				); 
				_tif.document.close();
				ifrm = _tif;
	
				var resize = function() {
					if ( ifrm && ifrm.document ) {
						var h = jQuery( ifrm.document.body ).height();
						jQuery( ifrm ).height( h );
					}
				};

				try {
					if ( MutationObserver ) {
						var nod = ifrm.document;

						new MutationObserver( _.debounce( function() {
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
		_putswf_fetch = function () {
			var self = this,
			    pid = jQuery( '#post_ID' ).val() || 0,
			    sctag = data.model.attributes.shortcode.tag,
			    caption = data.model.attributes.content,
			    scstr = _putswf_mk_shortcode(sctag, data.model.attributes, caption);

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
					//console.log('.DONE OK: HEAD: ' + head);
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
			console.log("TMPL: dat."+t+" == "+dat[t]);
		}
		dat = data.model.attributes;
		for ( var t in dat ) {
			console.log("ATTR: dat."+t+" == "+dat[t]);
		}
		dat = data.model.attributes.shortcode;
		for ( var t in dat ) {
			console.log("SCOD: dat."+t+" == "+dat[t]);
		}
		console.log("SCOD.string: dat.string() == " + dat.string());
		dat = data;
		for ( var t in dat ) {
			console.log("DATA: dat."+t+" == "+dat[t]);
		}
	}
	#>
</script>
