/**
 * jsToolTip v1.0
 * replace browser based tooltips with common styled ones automatically.
 *
 * requires:
 * - prototype javascript framework (http://www.prototypejs.org/)
 * - script.aculo.us javascript effects library (http://script.aculo.us/)
 *
 * for more details see: http://www.flamelab.de/article/javascript-tooltip-class.php
 *
 * licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/	
 *
 * @author roland koch, flame@flamelab.de
 * @version 14.08.2007
*/

// tooltip settings
var offX = 15; // x offset from mousepointer
var offY = 15;  // y offset from mouse pointer
var typelist = Array("div", "img"); // html elements to use
var styleclass = "ttstyle"; // style class to use for tooltip

/**
* class ToolTip
* main class for grabbing elements, creating tooltip items and
* control replacing of browser tooltip
*/
var ToolTip = Class.create();
ToolTip.prototype = {

	/**
	* init class
	*/
	initialize: function(){

		if (!document.getElementsByTagName) return;
		
		for(var i = 0; i < typelist.length; i++){
			this.grabItems(typelist[i]);
		}
		
	},
	
	/**
	* grab all html elements given in typelist
	*
	* @param type String html type to replace
	*/
	grabItems: function(type){
	
		var elemlist = document.getElementsByTagName(type);

		for (var i = 0; i < elemlist.length; i++){
			this.setToolTip(elemlist[i]);
		}
		
	},	
	
	/**
	* create ToolTipItem and replace browser styled tooltips
	*
	* @param elem HTMLElement element to set new tooltip 
	*/
	setToolTip: function(elem){
	
		var relAttribute = String(elem.getAttribute('rel'));
			
		if (!relAttribute.toLowerCase().match('tooltip')) return;
		if(!elem.attributes['id']) return;
		
		var idAttribute = elem.attributes['id'].nodeValue;
		if(elem.attributes['title'])var titleAttribute = elem.attributes['title'].nodeValue;
	
		if(elem.attributes['title'])elem.attributes['title'].nodeValue = "";
		if(elem.attributes['alt'])elem.attributes['alt'].nodeValue = "";
	
		new ToolTipItem(idAttribute, titleAttribute);
			
	}
	
}

/**
* class ToolTipItem
* singel tooltip for html element
*/
var ToolTipItem = Class.create();
ToolTipItem.prototype = {

	/**
	* init item
	*/
	initialize: function(elem, label){
	
		this.elem = "tooltip_" + elem;
		
		var objBody = document.getElementsByTagName("body").item(0);
		var objTooltip = document.createElement("div");
		objTooltip.setAttribute('id', this.elem);
		objTooltip.setAttribute('class', styleclass);
		objBody.appendChild(objTooltip);
		
		$(this.elem).innerHTML = label;
		Element.hide(this.elem);
		$(this.elem).style.position = 'absolute';
		
		var ref = this;
		Event.observe($(elem), "mouseover", function(e){ref.start();});
		Event.observe($(elem), "mouseout", function(e){ref.stop();});
		
	},
	
	/**
	* show tooltip
	*/
	start: function(){
		
		new Effect.Appear(this.elem, { duration: 0.6, from: 0.0, to: 1.0 });
		var obj = this;
		document.onmousemove = function(evt){obj.render(evt);};
		
	},
	
	/**
	* hide tooltip
	*/
	stop: function(){
	
		new Effect.Fade(this.elem, { duration: 0.1});
		document.onmousemove = "";
		
	},
	
	/**
	* render tooltip
	*/
	render: function(evt){
	
		var obj = $(this.elem).style;
		obj.left = (parseInt(this.mouseX(evt))+ offX -350) + 'px';
		obj.top = (parseInt(this.mouseY(evt))+ offY) + 'px';
		
	},
	
	/**
	* mouse follow xpoisition
	*/
	mouseX: function(evt) {
	
		if (!evt) evt = window.event;
		if (evt.pageX) return evt.pageX;
		else if (evt.clientX)return evt.clientX + (document.documentElement.scrollLeft ?  document.documentElement.scrollLeft : document.body.scrollLeft);
		else return 0;
		
	},
	
	/**
	* mouse follow ypoisition
	*/
	mouseY: function(evt) {
	
		if (!evt) evt = window.event;
		if (evt.pageY) return evt.pageY;
		else if (evt.clientY)return evt.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
		else return 0;
		
	}
	
}  

// start tootip when page data is loaded
function initToolTip() { myToolTip = new ToolTip(); }
Event.observe(window, 'load', initToolTip, false);
 
