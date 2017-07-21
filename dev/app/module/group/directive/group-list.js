define([
	'text!tpl/group/group-list.html',
	'group/controller/group-list',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Ctrl
		};
	};
});