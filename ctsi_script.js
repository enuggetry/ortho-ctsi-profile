/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {

    function ctsiSort() {
        var vals = [];

		// publications
		if ( $("ul#ctsi-pub-list").children().length != 0 ) {
			$("div.pub-wrapper").hide();

			$( "ul#ctsi-pub-list li" ).each(function( index ) {
				vals.push($(this)[0].outerHTML);
			});
			$( "ul.publications li" ).each(function( index ) {
				vals.push($(this)[0].outerHTML);
			});
			vals.sort();
			vals.reverse(); // most recent on top.
			
			$("ul#ctsi-pub-list").html("");
			for(var i = 0, l = vals.length; i < l; i++) {
				$("ul#ctsi-pub-list").append(vals[i]);
			}
		}
		else
			$("ul#ctsi-pub-list").hide();
		
		
		if ( $("ul#ctsi-grant-list").children().length != 0 || $("ul#ctsi-award-list").children().length != 0 ) {
			$("div.grant-wrapper").hide();

			if ($("ul#ctsi-grant-list").children().length==0)
				$(".ctsi-grant-wrap1").hide();
			if ($("ul#ctsi-award-list").children().length==0)
				$(".ctsi-award-wrap1").hide();
			
			// grants
			vals = [];
			$( "ul#ctsi-grant-list li" ).each(function( index ) {
				vals.push($(this)[0].outerHTML);
			});
			$( "ul.grants li" ).each(function( index ) {
				vals.push($(this)[0].outerHTML);
			});
			vals.sort();
			vals.reverse(); // most recent on top.
			
			$("ul#ctsi-grant-list").html("");
			for(var i = 0, l = vals.length; i < l; i++) {
				$("ul#ctsi-grant-list").append(vals[i]);
			}
			
			// awards
			vals = [];
			$( "ul#ctsi-award-list li" ).each(function( index ) {
				vals.push($(this)[0].outerHTML);
			});
			$( "ul.awards li" ).each(function( index ) {
				vals.push($(this)[0].outerHTML);
			});
			vals.sort();
			vals.reverse(); // most recent on top.
			
			$("ul#ctsi-award-list").html("");
			for(var i = 0, l = vals.length; i < l; i++) {
				$("ul#ctsi-award-list").append(vals[i]);
			}

			if ($("ul#ctsi-grant-list").children().length==0)
				$(".ctsi-grant-wrap1").hide();
			if ($("ul#ctsi-award-list").children().length==0)
				$(".ctsi-award-wrap1").hide();
			
		}
		else
			$("div.ctsi-grant-wrapper").hide();
	}
    ctsiSort();
    
    if ($("#hidden-count").attr("value") != "0" && $("#hidden-count").length > 0 ) {
        $("ul#ctsi-pub-list").css("position","relative").css("top","-30px");
        $("ul#ctsi-grant-list").css("position","relative").css("top","-30px");
        $("ul#ctsi-award-list").css("position","relative").css("top","-30px");
        //$(".ctsi-award-wrap1").css("margin-top","30px");
		var btns = '<div align="right" style="position:relative;top:-40px;border-bottom:2px solid #bbb"><input class="ctsi-pub-tab ctsiHideBtn" type="button" value="Show Selected" onclick="ctsiHidePubs();" /><input class="ctsi-pub-tab ctsiShowBtn" type="button" value="Show All" onclick="ctsiShowPubs();" /></div>';
        $(btns).insertBefore("ul#ctsi-pub-list");
        $(btns).insertBefore("ul#ctsi-grant-list");
        $(btns).insertBefore("ul#ctsi-award-list");
    }
	
	ctsiHidePubs();
});
function ctsiHidePubs(){ // show selected
	(function( $ ) {
		$(function() {
			$(".ctsi-hide").hide();
			$(".ctsiShowBtn").css("background-color","#f0f0f0");
			$(".ctsiHideBtn").css("background-color","#ffffff");
			//$("#publications_li_div ul").show("slide");
		});
	})(jQuery);
}
function ctsiShowPubs() { // show all
	(function( $ ) {
		$(function() {
			$(".ctsi-hide").show();
			$(".ctsiShowBtn").css("background-color","#ffffff");
			$(".ctsiHideBtn").css("background-color","#f0f0f0");
			//$("#publications_li_div ul").show("slide");
		});
	})(jQuery);
}

