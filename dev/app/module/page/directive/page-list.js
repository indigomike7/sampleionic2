define([
	'text!tpl/page/page-list.html',
	'page/controller/page-list',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Ctrl
		};
	};
});