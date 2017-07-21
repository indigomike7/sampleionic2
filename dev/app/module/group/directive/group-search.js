define([
	'text!tpl/group/group-search.html',
	'group/controller/group-search',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Ctrl
		};
	};
});