define([
	'text!tpl/blog/blog-search-dir.html'
], function(blogSearchDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: blogSearchDirTpl,
			controller: 'BlogSearchCtrl'
		};
	};
});