define([
	'text!tpl/core/core-pagination.html'
], function(text) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: 'CorePaginationCtrl',
			scope: {
				currentPage: '=',
				totalPage: '='
			}
		}
	};
});