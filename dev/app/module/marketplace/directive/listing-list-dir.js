define([
	'marketplace/controller/listing-list',
	'text!tpl/marketplace/listing-list-dir.html'
], function(ListingListCtrl, ListingListDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: ListingListDirTpl,
			controller: ListingListCtrl
		};
	};
});