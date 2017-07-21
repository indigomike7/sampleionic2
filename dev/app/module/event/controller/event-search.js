define([], function() {

    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {

        if ($scope.searchEvents.sModule){
            $scope.searchForm = {
                categories: [
                    {iCategoryId: "all_cate", sName: gettextCatalog.getString('I\'m Attending')}
                ]
            };
        } else {
            $scope.searchForm = {
                categories: [
                    {iCategoryId: "all_cate", sName: gettextCatalog.getString('All')},
                    {iCategoryId: "attending", sName: gettextCatalog.getString('I\'m Attending')},
                    {iCategoryId: "may_attend", sName: gettextCatalog.getString('I May Attend')},
                    {iCategoryId: "not_attending", sName: gettextCatalog.getString('I\'m Not Attending')},
                    {iCategoryId: "invites", sName: gettextCatalog.getString('Event Invites')}
                ]
            };
        }

        $scope.doGetSearchForm = function() {

            $http2.get('event/listcategories')
            .success($scope.doGetSearchFormSuccess)
            .error($scope.doGetSearchFormError);
        };

        $scope.doGetSearchFormSuccess = function(data) {

        	if (data.error_code) {
        		return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
        	}

        	$scope.searchForm.categories = $scope.searchForm.categories.concat(data);
        };

        $scope.doGetSearchFormError = function() {

        	console.error('doGetSearchForm', arguments);
        	$modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.doGetSearchForm();
    };
});
