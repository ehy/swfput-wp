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
 */

var SWFPut_putswf_video_xed = function () {}

SWFPut_putswf_video_xed.prototype = {
    options           : {},
    generateShortCode : function() {
        var content = this['options']['content'];
        delete this['options']['content'];

        var attrs = '';
        jQuery.each(this['options'], function(name, value){
            if (value != '') {
                attrs += ' ' + name + '="' + value + '"';
            }
        });
        return '[googleMap' + attrs + ']' + content + '[/googleMap]'
    },
    sendToEditor      : function(f) {
        var collection = jQuery(f).find("input[id^=wpYourPluginName]:not(input:checkbox),input[id^=wpYourPluginName]:checkbox:checked");
        var $this = this;
        collection.each(function () {
            var name = this.name.substring(13, this.name.length-1);
            $this['options'][name] = this.value;
        });
        send_to_editor(this.generateShortCode());
        return false;
    }
}

var SWFPut_putswf_video_var = new SWFPut_putswf_video_xed();

