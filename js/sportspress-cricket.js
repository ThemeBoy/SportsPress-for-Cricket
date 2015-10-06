jQuery.fn.sp_swap = function(b){ 
    b = jQuery(b)[0]; 
    var a = this[0]; 
    var t = a.parentNode.insertBefore(document.createTextNode(''), a); 
    b.parentNode.insertBefore(a, b); 
    t.parentNode.insertBefore(b, t); 
    t.parentNode.removeChild(t); 
    return this; 
};

jQuery(document).ready(function($){
	$('.sp-template-event-performance-team-0-position-1').sp_swap('.sp-template-event-performance-team-1-position-1');
});