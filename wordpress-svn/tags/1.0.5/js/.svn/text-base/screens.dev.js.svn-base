//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 3 of the License, or
//  (at your option) any later version.
//  
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//  
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//  MA 02110-1301, USA.
//

/**
 * For plugin option/settings page:
 * Controller for 'Screen Options' -- hide/show (toggle
 * .style.display "none" : "block") of div element containing
 *  introductory text (or whatever)
 */
var evhplg_ctl_screenopt = function (id_chk) {
	this.chk = document.getElementById(id_chk);
	this.chk.spbl = this;
	this.chk.addEventListener('click', this.clk, false);
};
evhplg_ctl_screenopt.prototype = {
	chk : null,
	all : {},
	add : function (id) {
		this.all[id] = document.getElementById(id);
	},
	tog : function (ch) {
		var dis = ch ? "none" : "block";
		for ( var k in this.all ) {
			this.all[k].style.display = dis;
		}
	},
	clk : function () {
		this.spbl.tog(this.checked ? false : true);
		return false;
	}
};
var evhplg_obj_screenopt = {};
function addto_evhplg_obj_screenopt(id, target) {
	if ( evhplg_obj_screenopt[id] == undefined )
		evhplg_obj_screenopt[id] = new evhplg_ctl_screenopt(id);
	evhplg_obj_screenopt[id].add(target);
};

/**
 * For plugin option/settings page forms: textarea pairs
 * Transfer whole lines from one textarea to another,
 * in response to one of two buttons (actually, any elements
 * with a click event)
 */

var evhplg_ctl_textpair = function (id_tl, id_tr, id_bl, id_br, dbg) {
	this.tx_l = document.getElementById(id_tl);
	this.tx_l.spbl = this;
	this.tx_l.addEventListener('dblclick', this.clk_tx, false);
	this.tx_r = document.getElementById(id_tr);
	this.tx_r.spbl = this;
	this.tx_r.addEventListener('dblclick', this.clk_tx, false);

	this.bt_l = document.getElementById(id_bl);
	this.bt_l.spbl = this;
	this.bt_l.addEventListener('click', this.clk_btl, false);
	this.bt_r = document.getElementById(id_br);
	this.bt_r.spbl = this;
	this.bt_r.addEventListener('click', this.clk_btr, false);

	if ( dbg !== null && dbg != "" ) {
		this.dbg  = document.getElementById(dbg);
	}
};

evhplg_ctl_textpair.prototype = {
	tx_l : null,
	tx_r : null,
	bt_l : null,
	bt_r : null,
	clk_btl : function () {
		var ctl = this.spbl;
		var fr = ctl.tx_l;
		var to = ctl.tx_r;
		var r = ctl.movcur(fr, to);
		if ( r )
			to.focus();
		return r;
	},
	clk_btr : function () {
		var ctl = this.spbl;
		var to = ctl.tx_l;
		var fr = ctl.tx_r;
		var r = ctl.movcur(fr, to);
		if ( r )
			to.focus();
		return r;
	},
	clk_tx : function () {
		var ctl = this.spbl;
		ctl.selcur(this);
		this.focus();
	},
	movcur : function (fr, to) {
		l = this.cutcur(fr);
		if ( l !== false ) {
			return this.putcur(to, l);
		}
		return false;
	},
	cutcur : function (tx) {
		this.selcur(tx);
		var t, s, e, v = this.sanitx(tx.value);
		if ( ! (s = tx.selectionStart) )
			s = 0;
		if ( ! (e = tx.selectionEnd) && e !== 0 )
			e = s;
		if ( e < s ) {
			t = s;
			s = e;
			e = t;
		}
		if ( s === e ) {
			return false;
		}
		t = v.slice(s, e);
		tx.value = v.slice(0, s) + v.substring(e);
		return t;
	},
	putcur : function (tx, val) {
		var s, v = this.sanitx(tx.value);
		if ( ! (s = tx.selectionStart) )
			s = 0;
		while ( s > 0 ) {
			if ( v.charAt(s) === "\n" ) {
				++s;
				break;
			}
			--s;
		}
		tx.value = v.slice(0, s) + this.sanitx(val) + v.substring(s);
		tx.selectionStart = s;
		tx.selectionEnd   = s + val.length;
		return true;
	},
	selcur : function (tx) {
		var s, e, v = tx.value;
		if ( ! (s = tx.selectionStart) )
			s = 0;
		if ( ! (e = tx.selectionEnd) )
			e = s;
		if ( e < s )
			s = e;
		var p = s, l = v.length;
		while ( --p >= 0 ) {
			if ( v.charAt(p) === "\n" ) {
				break;
			}
		}
		s = p + 1;
		p = e = s;
		while ( ++p < l ) {
			if ( v.charAt(p) === "\n" ) {
				break;
			}
		}
		e = p;
		// include '\n'
		if ( e < l ) {
			e++;
		}
		tx.selectionStart = s;
		tx.selectionEnd   = e;
	},
	sanitx : function (tx) {
		var l = tx.length;
		if ( l < 1 || tx.charAt(l - 1) == "\n" ) {
			return tx;
		}
		return tx + "\n";
	},
	dbg  : null,
	dbg_msg : function (msg) {
		if ( this.dbg !== null ) {
			this.dbg.innerHTML += '<br/>' + msg;
		}
	}
}; // evhplg_ctl_textpair

var evhplg_ctl_textpair_objmap = {
	form_1 : null,
	form_2 : null,
	form_3 : null,
	form_4 : null,
	form_5 : null,
	form_6 : null,
	fpo    : null
};
