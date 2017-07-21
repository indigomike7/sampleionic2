define([
	'text!tpl/page/page-search.html',
	'page/controller/page-search',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Ctrl
		};
	};
});