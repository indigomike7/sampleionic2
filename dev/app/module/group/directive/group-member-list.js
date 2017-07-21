define([
	'text!tpl/group/group-member-list.html',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text
		};
	};
});