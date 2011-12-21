/*
Tablify Extension version 1.0 (30.03.2009)
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
document.jquery_tablify_ext=function(style){
    var d = {
	    box:{
		    tableBorderTop:"6px solid #AABCFE",
			tableBorderBottom:"6px solid #AABCFE",
		    headerAlign:"left",
			headerBackground:"#B9C9FE",
			headerBorderBottom:"1px solid #FFFFFF",
		    headerFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			headerFontColor:"#003399",
			headerFontSize:14,
			headerPadding:"10px 8px",
			rowsAlign:"left",
			rowsBackground:"#E8EDFF",
			rowsBorderBottom:"2px solid #FFFFFF",
		    rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"#666699",
			rowsFontSize:12,
			rowsPadding:"8px",
			hover:"#D0DAFD"
		},
		clear:{
		    headerAlign:"left",
			headerBackground:"#EFF6FF",
			headerBorderTop:"1px solid #5FA8FF",
			headerBorderBottom:"1px solid #5FA8FF",			
			headerFont:"Verdana, Geneva, Arial, Helvetica, sans-serif",
			headerFontColor:"#5FA8FF",
		    headerFontSize:14,
			headerPadding:8,
			rowsAlign:"left",
			rowsBackground:"#FFFFFF",
			rowsBorderBottom:"1px dashed #BFDCFF",
			rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"red",
		    rowsFontSize:12,
			rowsPadding:8,
			hover:"#FFFFDF",
			zebra:false,
			zebraBackground:"#EFF3FF"
		},
		greenlife:{
		    tableBorderCollapse:"collapse",
		    headerAlign:"center",
			headerBackground:"#575757",
			headerBorderBottom:"1px dotted #fafafa",			
			headerBorderRight:"2px solid #333",	
			headerFont:"Verdana, Arial, Helvetica, sans-serif",
			headerFontColor:"#FFFFFF",
		    headerFontSize:14,
			headerPadding:"20px 10px",
			rowsAlign:"left",
			rowsBackground:"#FBFDF6",
			rowsBorder:"1px dotted #f5f5f5",
			rowsFont:"Verdana, Arial, Helvetica, sans-serif",
			rowsFontColor:"#9F9F9F",
		    rowsFontSize:12,
			rowsPadding:"20px 10px",
			hover:"#E9FFBF",
			zebra:true,
			zebraBackground:"#EDF7DC"
		},
		minimalist:{
		    headerAlign:"left",
			headerBorderBottom:"2px solid #6678B1",
		    headerFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			headerFontColor:"#003399",
			headerFontSize:14,
			headerPadding:"10px 8px",
			rowsAlign:"left",
		    rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"#666699",
			rowsFontSize:12,
			rowsPadding:"9px 8px 0"
		},
		newspaper:{
		    tableBorder:"1px solid #6699CC",
		    headerAlign:"left",
			headerBorderBottom:"1px dashed #6699CC",
		    headerFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			headerFontColor:"#003399",
			headerFontSize:14,
			headerPadding:"12px 17px",
			rowsAlign:"left",
		    rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"#666699",
			rowsFontSize:12,
			rowsBackground:"transparent",
			rowsPadding:"7px 17px",
			hover:"#D0DAFD"
		},
		seaglass:{
		    tableBackground:"#f1f8ee",
		    tableBorderCollapse:"collapse",
		    tableBorder:"1px solid #839E99",
		    headerAlign:"center",
			headerBackground:"#2C5755",
			//headerBorderBottom:"1px dashed #6699CC",
		    headerFont:"Georgia, Times New Roman, Times, serif",
			headerFontColor:"#FFFFFF",
			headerFontSize:14,
			headerPadding:"3px 3px 6px 3px",
			rowsAlign:"left",
		    rowsFont:"Lucida Sans Unicode,Lucida Grande,Sans-Serif",
			rowsFontColor:"#666699",
			rowsFontSize:12,
			rowsBackground:"transparent",
			rowsPadding:"3px 3px 6px 3px",
			hover:"#EFFFFE",
			zebra:true,
			zebraBackground:"#DBE6DD"
		}
	}
	if(undefined!=d[style]){
	    return d[style];
	}
	
	return false;
}