define([
    'global/base/ListController',
    'global/helper'
], function(ListCtrl, Helper) {
    return function($scope, $injector, gettextCatalog, $sce) {
        $injector.invoke(ListCtrl, this, {
            $scope: $scope
        });

        $.extend($scope, {
            apiService: 'directory/fetch_faqs',
            noMoreText: gettextCatalog.getString('No more faq'),
            itemModel: {},
            listById: false,
            enableLoadMore: false,
            getQueryData: function() {
                return {};
            },
        });

        $scope.prepareItems = function(items) {
            return (items || []).map(function(item) {
                return $.extend({}, item, {
                    sAnswer: $sce.trustAsHtml(Helper.prepareHTMLText(item.sAnswer))
                });
            });
        };

        $scope.onLoadMoreEnd = function() {
            Helper.preventIframeLink();
        };
    };
});
