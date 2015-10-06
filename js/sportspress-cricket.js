(function($) {
	var a = $('.sp-template-event-performance-team-0-position-1')[0];
	var b = $('.sp-template-event-performance-team-1-position-1')[0];
	if ( undefined !== a && undefined !== b ) {
		var t = a.parentNode.insertBefore(document.createTextNode(''), a); 
		b.parentNode.insertBefore(a, b); 
		t.parentNode.insertBefore(b, t); 
		t.parentNode.removeChild(t); 
	}
})(jQuery);