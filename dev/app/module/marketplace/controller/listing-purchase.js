define([
    'global/base/BaseController',
    'marketplace/model/listing'
], function(BaseController, ListingModel) {

    return function($scope, $injector, $modal, $state, $http2, gettext, gettextCatalog, $iap, $location) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        if (!ionic.Platform.isIOS()) {
        	$modal.alert(gettextCatalog.getString('Sorry, we are not supported to buy on this platform.'));
        	return $scope.goBack();
        }

        $scope.item = $.extend({}, ListingModel, {
            iListingId: $state.params.id
        });

        $scope.fetchData = function() {

            var postData = {
                iListingId: $scope.item.getId()
            };

            $http2.get('marketplace/detail', postData).success($scope.fetchDataSuccess).error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $.extend($scope.item, data);

            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchData', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        $scope.onBuy = function() {

            if ($scope.isProcessing) {
                return;
            }

            $scope.isProcessing = true;

            $scope.initIAP();
        };

        $scope.initIAP = function() {

            if (!$scope.item.getStoreKitPurchaseId()) {
                $modal.alert(gettextCatalog.getString('In-App Purchases is not available.'));
                return $scope.isProcessing = false;
            }

            if ($scope.item.getCurrencyId() != 'USD') {
                $modal.alert(gettextCatalog.getString('In-App Purchases does not support this currency. Please purchase in web site.'));
                return $scope.isProcessing = false;
            }

            $iap.initialize([$scope.item.getStoreKitPurchaseId()], $scope.initIAPSuccess, $scope.purchaseWithIAPSuccess, $scope.onIAPError);
        };

        $scope.initIAPSuccess = function(products, invalidIds) {

            $scope.storeKitPurchase = {};

            for (var i in products) {
                if (products[i].id == $scope.item.getStoreKitPurchaseId()) {
                    $scope.storeKitPurchase = $.extend({}, products[i], {
                        price: products[i].price.substr(1)
                    });
                }
            }

            if (!$scope.storeKitPurchase.id || invalidIds.indexOf($scope.item.getStoreKitPurchaseId()) > -1) {
                $modal.alert(gettextCatalog.getString('In-App Purchases is not available.'));
                return $scope.isProcessing = false;
            }

            $scope.addTransaction();
        };

        $scope.onIAPError = function(errorCode, errorMessage) {

            $scope.iInvoiceId ? $scope.purchaseWithIAPError.apply($scope, arguments) : $scope.initIAPError.apply($scope, arguments);
        };

        $scope.initIAPError = function(errorCode, errorMessage) {

            $modal.alert(errorMessage);
            $scope.isProcessing = false;
        };

        $scope.addTransaction = function() {

            var postData = {
                iListingId: $scope.item.getId()
            };

            console.log('addTransaction', JSON.stringify(postData));

            $http2.post('marketplace/transactionadd', postData).success($scope.addTransactionSuccess).error($scope.addTransactionError);
        };

        $scope.addTransactionSuccess = function(data) {

            console.log('addTransactionSuccess', JSON.stringify(data));

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.isProcessing = false;
            }

            $scope.iInvoiceId = data.iInvoiceId;

            if ($scope.item.isFree()) {
                $scope.updateTransaction('success');
            } else {
                $scope.purchaseWithIAP();
            }
        };

        $scope.addTransactionError = function() {

            console.warn('addTransactionError', JSON.stringify(arguments));
            $scope.isProcessing = false;
        };

        $scope.purchaseWithIAP = function() {

            var quantity = ($scope.storeKitPurchase.price == 0) ? 1 : Math.ceil($scope.item.getPrice() / $scope.storeKitPurchase.price);

            $iap.buy($scope.storeKitPurchase.id, quantity);
        };

        $scope.purchaseWithIAPSuccess = function(transactionId, productId) {

            $scope.updateTransaction('success', transactionId);
        };

        $scope.purchaseWithIAPError = function(errorCode, errorMessage) {

            $scope.isProcessing = false;

            $scope.updateTransaction('fail');

            $modal.alert(errorMessage || gettextCatalog.getString('Your payment is cancelled.'));
        };

        $scope.updateTransaction = function(sStatus, transactionId, orderId) {

            var postData = {
                iInvoiceId: $scope.iInvoiceId,
                sStatus: sStatus,
                sStoreKidTransactionId: transactionId,
                sDevice: (ionic.Platform.isIPad() ? 'ipad' : 'iphone')
            };

            console.log('updateTransaction', JSON.stringify(postData));

            var success = function(data) {
                $scope.updateTransactionSuccess(data, sStatus);
            };

            $http2.post('marketplace/transactionupdate', postData).success(success).error($scope.updateTransactionError).
            finally(function() {
                $scope.iInvoiceId = null;
            });
        };

        $scope.updateTransactionSuccess = function(data, sStatus) {

            console.log('updateTransactionSuccess', JSON.stringify(data));

            if (sStatus == 'fail') {
                return;
            }

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.isProcessing = false;
            }

            $location.path('app/listings');
            $modal.alert(gettextCatalog.getString('Your payment is completed.'));
        };

        $scope.updateTransactionError = function() {

            console.warn('updateTransactionError', JSON.stringify(arguments));
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.isProcessing = false;
        };

        $scope.fetchData();
    };
});