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
 * For Wordpress shortcode tags in the post editor; js to link
 * html form with shortcode attributes
 * 
 * based on example at:
 * 	http://bluedogwebservices.com/wordpress-25-shortcodes/
 * 
 */

var SWFPut_putswf_video_xed = function () {}

SWFPut_putswf_video_xed.prototype = {
    map : {},
    put_shortcode : function(cs, sc) {
        var c = this['map'][cs];
        delete this['map'][cs];

        var atts = '';
        jQuery.each(this['map'], function(name, value){
            if (value != '') {
                atts += ' ' + name + '="' + value + '"';
            }
        });

        var ret = '[' + sc + atts + ']';
        if ( c.length > 0 ) {
			ret += c + '[/' + sc + ']';
		}
        return ret;
    },
    send_xed : function(f, id, cs, sc) {
		var len = id.length + 1;
		var pat = "input[id^=" + id + "]:not(input:checkbox)";
		pat += ",input[id^=" + id + "]:checkbox:checked";
        var all = jQuery(f).find(pat);
        var $this = this;
        all.each(function () {
            var k = this.name.substring(len, this.name.length - 1);
            $this['map'][k] = this.value;
        });
        send_to_editor(this.put_shortcode(cs, sc));
        return false;
    }
}

var SWFPut_putswf_video_inst = new SWFPut_putswf_video_xed();

