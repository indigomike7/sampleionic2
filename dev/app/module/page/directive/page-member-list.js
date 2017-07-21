define([
	'text!tpl/page/page-member-list.html',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});