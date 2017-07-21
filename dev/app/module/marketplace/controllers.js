define([
	'marketplace/controller/listing-add',
	'marketplace/controller/listing-browse',
	'marketplace/controller/listing-detail',
	'marketplace/controller/listing-edit',
	'marketplace/controller/listing-home',
	'marketplace/controller/listing-invite-list',
	'marketplace/controller/listing-item',
	'marketplace/controller/listing-list',
	'marketplace/controller/listing-photo-detail',
	'marketplace/controller/listing-purchase',
	'marketplace/controller/listing-search'
], function() {

	angular.module('myapp.controllers')
		.controller('ListingAddCtrl', require('marketplace/controller/listing-add'))
		.controller('ListingBrowseCtrl', require('marketplace/controller/listing-browse'))
		.controller('ListingDetailCtrl', require('marketplace/controller/listing-detail'))
		.controller('ListingEditCtrl', require('marketplace/controller/listing-edit'))
		.controller('ListingHomeCtrl', require('marketplace/controller/listing-home'))
		.controller('ListingInviteListCtrl', require('marketplace/controller/listing-invite-list'))
		.controller('ListingItemCtrl', require('marketplace/controller/listing-item'))
		.controller('ListingListCtrl', require('marketplace/controller/listing-list'))
		.controller('ListingPhotoDetailCtrl', require('marketplace/controller/listing-photo-detail'))
		.controller('ListingPurchaseCtrl', require('marketplace/controller/listing-purchase'))
		.controller('ListingSearchCtrl', require('marketplace/controller/listing-search'));
});