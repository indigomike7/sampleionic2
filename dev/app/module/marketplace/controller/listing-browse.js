define([
    'global/base/BrowseController',
    'text!tpl/marketplace/listing-search.html'
], function(BrowseController, searchTemplate) {

    return function($scope, $injector, $state, $site) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;

        $site.requirePerm('marketplace.can_access_marketplace');
        $scope.canAddListing = $site.getPerm('marketplace.can_create_listing');

        $scope.listingListData = {
            iPage: 1,
            iAmountOfMarketplace: 10,
            sView: $state.params.view || 'all',
            iCategoryId: '',
            sCountryIso: '',
            sOrder: 'latest'
        };
    };
});