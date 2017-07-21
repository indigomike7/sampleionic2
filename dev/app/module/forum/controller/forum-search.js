define([], function() {

    return function($scope, $http2, $site, $modal, gettext, gettextCatalog, $location) {

        $scope.searchForm = {
            forumOptions: []
        };

        $scope.doGetSearchForm = function() {

            $http2.get('forum/getforumsearch').success($scope.doGetSearchFormSuccess).error($scope.doGetSearchFormError);
        };

        $scope.doGetSearchFormSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            var getForumOptions = function(items) {
                var options = [];
                for (var i = 0; i < items.length; i++) {
                    options.push(items[i]);
                    if (items[i].aSubForum.length) {
                        options = options.concat(getForumOptions(items[i].aSubForum));
                    }
                }
                return options;
            };

            $scope.searchForm.forumOptions = getForumOptions(data);
        };

        $scope.doGetSearchFormError = function() {

            console.error('doGetSearchForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.goSearch = function() {

            if (!$scope.searchData.sKeyword) {
                return $modal.alert(gettextCatalog.getString('Please provide keyword'));
            }

            $location.path('app/forum_' + $scope.searchData.sSearchType + 's/search/' + btoa(JSON.stringify($scope.searchData)));
        };

        $scope.doGetSearchForm();
    };
});