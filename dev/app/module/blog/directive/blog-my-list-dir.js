define([
	'text!tpl/blog/blog-my-list-dir.html'
], function() {

	return function() {

		return {
			restrict: 'E',
			template: require('text!tpl/blog/blog-my-list-dir.html'),
			controller: 'BlogListCtrl'
		};
	};
});