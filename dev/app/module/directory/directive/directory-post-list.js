define([
	'text!tpl/directory/directory-post-list.html',
	'directory/controller/directory-post-list',
], function(text,Ctrl) {

	return function() {

		return {
			restrict: 'E',
			template: text,
			controller: Ctrl
		};
	};
});