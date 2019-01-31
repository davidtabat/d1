jQuery(document).ready(function () {
	jQuery('#category-sidebar-nav > li > a.show-cat').click(function(){
	jQuery('#category-sidebar-nav li ul').slideUp();
	if (!jQuery(this).hasClass('active')){				  
		jQuery(this).next().slideToggle();
		jQuery('#category-sidebar-nav li a.show-cat').removeClass('active');
		jQuery(this).addClass('active');
	}
	else if (jQuery(this).hasClass('active')) {
		jQuery(this).removeClass('active');
	}
	});
	jQuery('#category-sidebar-nav > li > ul > li > a.show-cat').click(function(){
	jQuery('#category-sidebar-nav li ul li ul').slideUp();
	if (!jQuery(this).hasClass('active')){
		jQuery(this).next().slideToggle();
		jQuery('#category-sidebar-nav li ul li a.show-cat').removeClass('active');
		jQuery(this).addClass('active');
	}
	else if (jQuery(this).hasClass('active')) {
		jQuery(this).removeClass('active');
	}
	});
});