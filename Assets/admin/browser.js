
$(function(){
	$.delete({
		categories : {
			category : {
				url : "/admin/module/news/category/delete.ajax"
			},
			post : {
				url : "/admin/module/news/post/delete.ajax"
			}
		}
	});
});
