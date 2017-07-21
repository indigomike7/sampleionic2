define([
	'marketplace/controllers',
	'marketplace/directives',
	'marketplace/plugin/activity',
    'text!tpl/marketplace/listing-add.html',
    'text!tpl/marketplace/listing-browse.html',
    'text!tpl/marketplace/listing-detail.html',
    'text!tpl/marketplace/listing-edit.html',
    'text!tpl/marketplace/listing-home.html',
    'text!tpl/marketplace/listing-photo-detail.html',
    'text!tpl/marketplace/listing-purchase.html'
], function() {
	
	angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.listings', {
            module: 'marketplace',
            url: '/listings',
            cache: false,
            history: {
                title: gettext('Marketplace'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/marketplace/listing-home.html'),
                    controller: 'ListingHomeCtrl'
                },
                'tabContent@app.listings': {
                    template: require('text!tpl/marketplace/listing-browse.html'),
                    controller: 'ListingBrowseCtrl'
                }
            }
        }).state('app.listingsadd', {
            module: 'marketplace',
            url: '/listings/add',
            cache: false,
            history: false,
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-add.html'),
                    controller: 'ListingAddCtrl',
                }
            }
        }).state('app.listingsview', {
            module: 'marketplace',
            url: '/listings/:view',
            cache: false,
            history: {
                title: gettext('Marketplace'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/marketplace/listing-home.html'),
                    controller: 'ListingHomeCtrl'
                },
                'tabContent@app.listingsview': {
                    template: require('text!tpl/marketplace/listing-browse.html'),
                    controller: 'ListingBrowseCtrl'
                }
            }
        }).state('app.listingid', {
            module: 'marketplace',
            url: '/listing/:id',
            cache: false,
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-detail.html'),
                    controller: 'ListingDetailCtrl',
                }
            }
        }).state('app.marketplaceid', {
            module: 'marketplace',
            url: '/marketplace/:id',
            cache: false,
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-detail.html'),
                    controller: 'ListingDetailCtrl',
                }
            }
        }).state('app.advancedmarketplaceid', {
            module: 'marketplace',
            url: '/advancedmarketplace/:id',
            cache: false,
            sModelType: 'advancedmarketplace',
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-detail.html'),
                    controller: 'ListingDetailCtrl',
                }
            }
        }).state('app.listingidedit', {
            module: 'marketplace',
            url: '/listing/:id/edit',
            cache: false,
            history: false,
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-edit.html'),
                    controller: 'ListingEditCtrl',
                }
            }
        }).state('app.listingideditphoto', {
            module: 'marketplace',
            url: '/listing/:id/edit/photo',
            cache: false,
            history: false,
            contextView: 'photo',
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-edit.html'),
                    controller: 'ListingEditCtrl',
                }
            }
        }).state('app.listingidpurchase', {
            module: 'marketplace',
            url: '/listing/:id/purchase',
            cache: false,
            history: false,
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-purchase.html'),
                    controller: 'ListingPurchaseCtrl',
                }
            }
        }).state('app.listingidphotoid', {
            module: 'marketplace',
            url: '/listing/:id/:photoId',
            cache: false,
            views: {
                menuContent: {
                    template: require('text!tpl/marketplace/listing-photo-detail.html'),
                    controller: 'ListingPhotoDetailCtrl',
                }
            }
        });
    });
});