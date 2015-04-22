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
					_tifd = document.getElementById('putswf-dlg-content-wrapper');
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
								'display: block;' +
								'margin-left: 0;' +
								'margin-right: auto; }' +
								'' +
								'.alignright {' +
								'display: block;' +
								'margin-right: 0;' +
								'margin-left: auto; }' +
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
			}, 150 );
		},
		// TODO: this proc should maybe be a method of the data model
		_putswf_frolic_in_data = function (d) {
			var self = this, tmp,
			    model = d.model,
			    oldatts = model.attributes,
			    newatts = (d.attachment && d.attachment.attributes)
			        ? d.attachment.attributes : false,
			    sctag   = oldatts.shortcode.tag,
			    caption = oldatts.caption || oldatts.shortcode.content; //( newatts && newatts.caption ) ? newatts.caption : oldatts.content
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
				model.poster = newatts;
			}
			
			// Media frame add/replace video tab
			if ( vid_op ) {
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
						model.flv = newatts;
						model.html5s = [];
					} else {
						// Replace
						if ( vid_rpl ) {
							if ( t && t.toLowerCase() === 'flv' ) {
								oldatts.url = m;
								oldatts.altvideo = '';
								model.flv = newatts;
								model.html5s = [];
							} else {
								oldatts.altvideo = m;
								oldatts.url = '';
								model.flv = '';
								model.html5s = [newatts];
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
									
									if ( am[i].flv ) {
										model.flv = tatt;
									} else {
										model.html5s.push(tatt);
									}
								}
							} else {
								am[0] = {
									uri: m,
									flv: ( t && t.toLowerCase() === 'flv' ) === true
								};
								if ( am[0].flv ) {
									model.flv = newatts;
								} else {
									model.html5s.push(newatts);
								}
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
							
							model.cleanup_media();
						}
					}
				}
			}
			
			oldatts.content = caption;
			oldatts.shortcode.content = caption;
			oldatts.caption = caption;
			
			// TODO: remove reduncancies above made by this
			tmp = model.get_poster(true);
			if ( tmp ) {
				oldatts.iimage = tmp;
			}
			tmp = model.get_flv(true);
			if ( tmp ) {
				oldatts.url = tmp;
			}
			tmp = model.get_html5s(true);
			if ( tmp ) {
				oldatts.altvideo = tmp;
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
					cont = 'FAIL to get wp_ajax response'
					console.log('.DONE BAD: CONT: ' + cont);
				}
				_putswf__putfrm();
			} )
			.fail( function( response ) {
				head = false;
				cont = 'FAIL onwp_ajax request'
				console.log('.FAIL BAD: CONT: ' + cont);
				_putswf__putfrm();
			} );
		};
		
		_putswf_fetch();
	#>
	<style>
		.putswf-dlg-content-controls .setting {
			margin: 0.5rem 1.5rem;
		}
		div:not([class="dimensions"]) > label > span {
			margin-left: 1.5rem;
			display: block;
		}
		div span {
			display: block;
		}
		.putswf-dlg-content-controls input {
			display: inline;
			margin: 0.5rem 1.5rem;
		}
		input ~ span {
			display: inline !important;
			margin-bottom: 0.5rem !important;
			margin-left: 0.5rem !important;
		}
		input[type=checkbox] {
			-webkit-appearance: checkbox;
			box-sizing: border-box;
			border: 1px solid #bbb;
			clear: none;
			cursor: pointer;
			display: inline-block;
			line-height: 0;
			height: 16px;
			outline: 0;
			margin: 0.5rem 0rem;
			padding: 0!important;
			text-align: center;
			vertical-align: middle;
			width: 16px;
			min-width: 16px;
		}
		div .putswf-dlg-content-wrapper {
			height: auto;
		}
		textarea {
			margin-left: 1.5rem;
			overflow: auto;
			background: none repeat scroll;
		}
		input[type=text], textarea {
			width: 36rem;
		}
		div[class="dimensions"] {
			margin-left: 0.rem !important;
		}
		div[class="dimensions"] input[type=text] {
			display: inline !important;
			margin-right: 0.rem;
			margin-left:  0.rem;
			width: 3.2rem;
		}
		div[class="dimensions"] label span {
			display: inline;
			margin-left: 0.rem ;
		}
		div[class="dimensions"] span {
			display: inline;
			margin-right: 0.rem;
			margin-left: 0.rem ;
		}
		div[class="dimensions"] label {
			display: inline;
			margin-left: 0.rem !important;
		}
	</style>
	<div class="putswf-dlg-content-outer">
	<div id="putswf-dlg-content-wrapper">
	</div>
	<div class="putswf-dlg-content-controls">
	<label class="setting">
		<span>Caption</span>
		<textarea rows="3" wrap="soft" placeholder="optional caption" data-setting="content" value="{{ data.model.caption || data.model.content }}" />
	</label>
	<label class="setting">
		<span>Flash Video URL/ID</span>
		<input type="text" disabled="disabled" data-setting="url" value="{{ data.model.get_flv(true) }}" />
		<a class="remove-setting"><?php _e( 'Remove' ); ?></a>
	</label>
	<label class="setting">
		<span>HTML5 Video URL/ID</span>
		<input type="text" disabled="disabled" data-setting="altvideo" value="{{ data.model.get_html5s(true) }}" />
		<a class="remove-setting"><?php _e( 'Remove' ); ?></a>
	</label>
	<div class="dimensions">
		<label class="setting">
			<span>Width</span>
			<input type="text" data-setting="width" value="{{ data.model.attributes.width }}" />
		</label>
		<label class="setting">
			<span>Height</span>
			<input type="text" data-setting="height" value="{{ data.model.attributes.height }}" />
		</label>
	</div>
	<div class="setting align">
		<span>Align</span>
		<div class="button-group button-large" data-setting="align">
			<button class="button" value="left">Left</button>
			<button class="button active" value="center">Center</button>
			<button class="button" value="right">Right</button>
			<button class="button" value="none">None</button>
		</div>
	</div>
	<div class="setting preload">
		<span><?php _e( 'Preload' ); ?></span>
		<div class="button-group button-large" data-setting="preload">
			<button class="button" value="auto"><?php _ex( 'Auto', 'auto preload' ); ?></button>
			<button class="button" value="metadata"><?php _e( 'Metadata' ); ?></button>
			<button class="button" value="none"><?php _e( 'None' ); ?></button>
			<button class="button active" value="image">Per Poster</button>
		</div>
	</div>
	<label class="setting checkbox-setting">
		<input type="checkbox" data-setting="play" />
		<span><?php _e( 'Autoplay' ); ?></span>
	</label>

	<label class="setting checkbox-setting">
		<input type="checkbox" data-setting="loop" />
		<span><?php _e( 'Loop' ); ?></span>
	</label>
	</div>
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
