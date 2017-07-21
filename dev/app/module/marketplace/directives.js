define([
	'marketplace/directive/listing-list-dir',
	'marketplace/directive/listing-search-dir'
], function() {

	angular.module('myapp.directives')
		.directive('listingListDir', require('marketplace/directive/listing-list-dir'))
		.directive('listingSearchDir', require('marketplace/directive/listing-search-dir'));
});