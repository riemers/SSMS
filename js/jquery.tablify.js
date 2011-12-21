/*
Tablify version 1.0 (30.03.2009)
Copyright(c)2009 Remiya Solutions All Rights Reserved
Website: http://remiya.com

This script may be used for both free and commercial purposes only if the
following conditions are met:

 1. A link back to the author's website is provided on the website, where
    the script is being used.
	
 2. You are hereby licensed to make as many copies of this script as you
    need in order to distribute your own work (including for commercial use).
	You are specifically prohibited from charging, or requesting donations,
	for any such copies without prior written permission.
	
 3. You ARE NOT allowed to distribute for download the script via electronic
    means (Internet, e-mail, etc). This means that this software is to be
	available to download from the official website (http://remiya.com) ONLY.
 
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
jQuery.fn.tablify = function(type,options) {
    var d = {
	    blue01:{
		    headerAlign:"center",
			headerBackground:"#B9C9FE",
			headerBorderTop:"4px solid #AABCFE",
			headerFont:"Verdana, Geneva, Arial, Helvetica, sans-serif",
			headerFontColor:"black",
		    headerFontSize:13,
			headerPadding:4,
			rowsAlign:"left",
			rowsBackground:"#DFE6FF",
			rowsBorderTop:"1px solid white",
			rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"red",
		    rowsFontSize:12,
			rowsPadding:4,
			hover:"#CFDAFF",
			zebra:true,
			zebraBackground:"#EFF3FF"
		},
		blue02:{
		    headerAlign:"center",
			headerBackground:"#EFF6FF",
			headerBorder:"1px solid #5FA8FF",
			headerBorderTop:"1px solid #5FA8FF",
			headerFont:"Verdana, Geneva, Arial, Helvetica, sans-serif",
			headerFontColor:"#5FA8FF",
		    headerFontSize:13,
			headerPadding:4,
			rowsAlign:"left",
			rowsBackground:"#FFFFFF",
			rowsBorderBottom:"1px dashed #BFDCFF",
			rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"red",
		    rowsFontSize:12,
			rowsPadding:4,
			hover:"#FFFFDF",
			zebra:false,
			zebraBackground:"#EFF3FF"
		},
	    uni:{
		    headerAlign:"center",
		    headerBackground:"#666666",
			headerFont:"Verdana, Geneva, Arial, Helvetica, sans-serif",
			headerFontColor:"white",
		    headerFontSize:12,
			headerPadding:4,
			rowsAlign:"left",
			rowsBackground:"transparent",
			rowsFont:"Verdana, Geneva, Arial, Helvetica, sans-serif",
			rowsFontColor:"black",
		    rowsFontSize:11,
			rowsPadding:4,
			hover:"#DFF4FF",
			zebra:true,
			zebraBackground:"#D7D5C7"
		}
	}
    return this.each(function(){
	    // Style
		var style;
		if(type==1){style=d.blue01}
		else if(type==2){style=d.blue02}
	    else{style=d.uni}
		if(typeof document.jquery_tablify_ext === 'function'&&document.jquery_tablify_ext(type)!=false){
		    style = document.jquery_tablify_ext(type);
		}
		if(undefined!==options){$.extend(style, options);}
		// START: Data
        var has_header = ($(this).find("th").length>0)?true:false;
		var rows = (has_header)?$(this).find("tr:not(:first)"):$(this).find("tr");
		var header = (has_header)?$(this).find("tr:first"):false;
		// END: Data
		
		// START: Style
		    // Table settings
		if(style.tableBorderCollapse){$(this).css("border-collapse",style.tableBorderCollapse)}
		if(style.tableBorder){$(this).css("border",style.tableBorder)}
		if(style.tableBorderTop){$(this).css("border-top",style.tableBorderTop)}
		if(style.tableBorderBottom){$(this).css("border-bottom",style.tableBorderBottom)}
		if(style.tableBorderLeft){$(this).css("border-left",style.tableBorderLeft)}
		if(style.tableBorderRight){$(this).css("border-right",style.tableBorderRight)}
		if(style.tableBackground){$(this).css("background",style.tableBackground)}
		if(style.tableBackgroundImage){$(this).css("background-image","url("+style.tableBackgroundImage+")")}
		
		    // Header Settings
		if(header){
	        if(style.headerBackground)header.css("background",style.headerBackground);
			if(style.headerBackgroundImage){header.css("background-image","url("+style.headerBackgroundImage+")")}
			if(style.headerFontColor)header.css("color",style.headerFontColor)
			if(style.headerFont)header.css("font-family",style.headerFont)
			if(style.headerFontSize)header.css("font-size",style.headerFontSize);
			if(style.headerAlign)header.find("th").css("text-align",style.headerAlign);
			if(style.headerPadding)header.find("th").css("padding",style.headerPadding);
			if(style.headerBorder){header.find("th").css("border",style.headerBorder)}
		    if(style.headerBorderTop){header.find("th").css("border-top",style.headerBorderTop)}
			if(style.headerBorderBottom){header.find("th").css("border-bottom",style.headerBorderBottom)}
			if(style.headerBorderLeft){header.find("th").css("border-left",style.headerBorderLeft)}
			if(style.headerBorderRight){header.find("th").css("border-right",style.headerBorderRight)}
	    }
		    // Rows Settings
		if(style.rowsBackground)rows.css("background",style.rowsBackground);
		if(style.rowBackgroundImage){rows.css("background-image","url("+style.rowBackgroundImage+")")}
		if(style.rowsFont)rows.css("font-family",style.rowsFont)
		if(style.rowsFontColor)rows.css("color",style.rowsFontColor)
		if(style.rowsFontSize)rows.css("font-size",style.rowsFontSize)
		if(style.rowsAlign)rows.find("td").css("text-align",style.rowsAlign)
		if(style.rowsPadding)rows.find("td").css("padding",style.rowsPadding)
		if(style.rowsBorder){rows.find("td").css("border-top",style.rowsBorder)}
		if(style.rowsBorderTop){rows.find("td").css("border-top",style.rowsBorderTop)}
		if(style.rowsBorderBottom){rows.find("td").css("border-bottom",style.rowsBorderBottom)}
		if(style.rowsBorderLeft){rows.find("td").css("border-left",style.rowsBorderLeft)}
		if(style.rowsBorderRight){rows.find("td").css("border-right",style.rowsBorderRight)}				
		// END: Style
		
		// START: Highlighting rows
	    rows
		    .mouseover(function(){
			    $(this).css("background",style.hover);
			})
		    .mouseout(function(){
		        if(style.rowBackgroundImage){
				    $(this).css("background-image","url("+style.rowBackgroundImage+")")
				}else{
				    $(this).css("background",style.rowsBackground);
				}
				if(style.zebra==false)return;
				// Apply zebra effect
				if(has_header){
		             $(this).parent().find("tr:not(:first):nth-child(even)")
					     .css("background",style.zebraBackground);
				}else{
				    $(this).parent().find("tr:nth-child(odd)")
					    .css("background",style.zebraBackground);
				}
	        });
		// END: Highlighting rows
		
		// START: Zebra
		if(style.zebra==false)return;
	    if(has_header){
	        $(this).find("tr:nth-child(even)").css("background",style.zebraBackground);
	    }else{
 	        $(this).find("tr:nth-child(odd)").css("background",style.zebraBackground);
	    }
		// END: Zebra
		
		
  });
};