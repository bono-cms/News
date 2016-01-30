$(function(){
	$.delete({
		categories : {
			category : {
				url : "/admin/module/news/category/delete"
			},
			post : {
				url : "/admin/module/news/post/delete"
			}
		}
	});
});