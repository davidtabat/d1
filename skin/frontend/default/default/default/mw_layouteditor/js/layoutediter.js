/**
 * 
 */
var LayoutEditer = Class.create();
LayoutEditer.prototype = {
    initialize: function(saveUrl, resetUrl, exitUrl){
    	this._saveUrl 	= saveUrl;
    	this._resetUrl 	= resetUrl;
    	this._exitUrl 	= exitUrl;
    	
    	$mw(".mw_left").parent().addClass('sidebar_left');
    	$mw(".mw_right").parent().addClass('sidebar_right');
    	$mw( ".sidebar_left" ).sortable({
    		connectWith: ".sidebar_left"
    	});
    	$mw( ".sidebar_right" ).sortable({
    		connectWith: ".sidebar_right"
    	});
    	$mw(".mw_block" ).addClass("sort-able highlight");
    	$mw(".sidebar").disableSelection();
    	
    	$mw("#highlight_draggable").click(function(){
    		if(this.checked) $mw(".mw_block").addClass("highlight");
    		else $mw(".mw_block" ).removeClass("highlight");
    	});
    },
    saveLayout: function(){
    	var right_blocks=new Array()
		count = 0;
		$mw(".mw_right .mw_block_name").each(function(){
			right_blocks[count++] = $mw(this).html();
		});
		
		var left_blocks=new Array()
		count = 0;
		$mw(".mw_left .mw_block_name").each(function(){
			left_blocks[count++] = $mw(this).html();
		});
		
		new Ajax.Request(this._saveUrl, {
	        parameters: {"right":$(right_blocks).toJSON(),'left':$(left_blocks).toJSON(),'current_hander':$('current_handle').value},
	        onSuccess: function(transport) {
	            try {
	                if (transport.responseText.isJSON()) {
	                    var response = transport.responseText.evalJSON();
	                    
	                } else {
	                }
	            }
	            catch (e) {
	            }
	        }
		});
    },
    
    resetLayout: function(){
    	new Ajax.Request(this._resetUrl, {
	        parameters: {'current_hander':$('current_handle').value},
	        onSuccess: function(transport) {
	        	window.location.reload();
	        }
		});
    },
    
    exit:function(){
    	new Ajax.Request(this._exitUrl, {
	        onSuccess: function(transport) {
	        	window.close();
	        }
		});
    }
}