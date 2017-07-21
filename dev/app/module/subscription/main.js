define([
	'subscription/controllers',
	'subscription/directives',
	'text!tpl/subscription/subscription-browse.html',
    'text!tpl/subscription/subscription-confirm.html',
    'text!tpl/subscription/subscription-upgrade.html'
], function() {

	angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.subscriptions', {
            module: 'subscription',
            url: '/subscriptions',
            cache: false,
            history: {
                title: gettext('Memberships'),
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/subscription/subscription-upgrade.html'),
                    controller: 'SubscriptionUpgradeCtrl',
                }
            }
        }).state('app.subscriptionsbrowse', {
            module: 'subscription',
            url: '/subscriptions/browse',
            cache: false,
            views: {
                menuContent: {
                    template: require('text!tpl/subscription/subscription-browse.html'),
                    controller: 'SubscriptionBrowseCtrl',
                }
            }
        }).state('app.subscriptionid', {
            module: 'subscription',
            url: '/subscription/:packageId',
            cache: false,
            history: false,
            views: {
                menuContent: {
                    template: require('text!tpl/subscription/subscription-confirm.html'),
                    controller: 'SubscriptionConfirmCtrl',
                }
            }
        }).state('app.subscriptionidpurchaseid', {
            module: 'subscription',
            url: '/subscription/:packageId/:purchaseId',
            cache: false,
            history: false,
            views: {
                menuContent: {
                    template: require('text!tpl/subscription/subscription-confirm.html'),
                    controller: 'SubscriptionConfirmCtrl',
                }
            }
        });
    });
});