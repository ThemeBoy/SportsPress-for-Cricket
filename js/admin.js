jQuery(document).ready(function($){
	// Switch performance tables
	$(".sp-performance-table-bar a").click(function() {
		i = $(this).closest("li").index();
		$(this).addClass("current").closest("li").siblings().find("a").removeClass("current");
		$(this).closest(".sp-performance-table-bar").siblings(".sp-data-table-container").hide().eq(i).show();
	});

	// Hide substitute dropdown
	$(".sp-status-selector select:first-child").unbind("change").siblings().hide();
});