define([
	'marketplace/controller/listing-search',
	'text!tpl/marketplace/listing-search.html'
], function(ListingSearchCtrl, listingSearchDirTpl) {

	return function() {

		return {
			restrict: 'E',
			template: listingSearchDirTpl,
			controller: ListingSearchCtrl
		};
	};
});