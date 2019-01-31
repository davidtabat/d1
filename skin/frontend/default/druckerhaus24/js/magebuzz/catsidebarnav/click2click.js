jQuery(document).ready(function () {

    jQuery('#category-sidebar-nav .nav-5 .nav-5-1 .right.show-cat').first().remove();

	jQuery('#category-sidebar-nav > li > a.show-cat').click(function(){

	if (!jQuery(this).hasClass('active')){				  
		jQuery(this).next().slideToggle();
		jQuery(this).addClass('active');
	}
	else if (jQuery(this).hasClass('active')) {
		jQuery(this).next().slideToggle();
		jQuery(this).removeClass('active');
	}
	});
	jQuery('#category-sidebar-nav > li > ul > li > a.show-cat').click(function(){
	
	if (!jQuery(this).hasClass('active')){
		jQuery(this).next().slideToggle();
		jQuery(this).addClass('active');
	}
	else if (jQuery(this).hasClass('active')) {
		jQuery(this).next().slideToggle();
		jQuery(this).removeClass('active');
	}
	});


});