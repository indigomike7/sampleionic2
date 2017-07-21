define([
    'global/base/ItemController'
], function(ItemController) {
    return function($scope, $injector, gettext, gettextCatalog, $modal, $site, $http2, $rootScope) {

        $injector.invoke(ItemController, this, {
            $scope: $scope
        });

        $scope.makeRead = function() {
        	var postData = {
	        	iNotificationId: $scope.item.getId()
	        };

	        $http2.post('notification/makeread', postData)
	        .success($scope.makeReadSuccess);
        };

        $scope.makeReadSuccess = function(data) {

        	if (data.error_code) {
        		return; // skip this error
        	};
        	$scope.items.deleteItem($scope.item.getId());
            window.location.href = $scope.item.getUrl();
        };
    };
});