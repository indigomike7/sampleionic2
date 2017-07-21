define([
    'global/base/BaseController',
    'subscription/model/subscription'
], function(BaseController, SubscriptionModel) {

    return function($scope, $rootScope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $iap, $location, $viewer, $timeout) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.stateParams = $state.params;

        if ($scope.stateParams.subscriptionId) {
            if (window.nativeControl) {
                window.nativeControl.lockMenu();
                $scope.$on('$destroy',function() {
                    window.nativeControl.unlockMenu();
                });
            }
        }

        $scope.item = $.extend({}, SubscriptionModel, {
            iPackageId: $scope.stateParams.packageId
        });

        $scope.cancel = function() {
            $scope.stateParams.subscriptionId ? $location.path('app/login') : $scope.goBack();
        };

        $scope.fetchData = function() {

            var postData = {
                iPackageId: $scope.item.getId()
            };

            $http2.get('subscribe/detail', postData)
            .success($scope.fetchDataSuccess)
            .error($scope.fetchDataError);
        };

        $scope.fetchDataSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.cancel();
            }

            $scope.item = $.extend($scope.item, data);
            $scope.dataReady = true;
        };

        $scope.fetchDataError = function() {

            console.warn('fetchData', JSON.stringify(arguments));
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.cancel();
        };

        $scope.onBuy = function() {

            if ($scope.isProcessing) {
                return;
            }

            $scope.isProcessing = true;

            if ($scope.item.getDefaultCost() == 0) { // free package
                var cf = {
                    title: gettextCatalog.getString('Confirm'),
                    content: gettextCatalog.getString('Switching to ' + $scope.item.getTitle() + ' will unsubscribe your current Membership. Do you want to continue?'),
                    buttons: [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')],
                    callback: function(selected) {
                        if (selected == 1) {
                            $scope.addTransaction();
                        } else {
                            $scope.isProcessing = false;
                            $scope.$$phase || $scope.$apply();
                        }
                    }
                };

                return $modal.confirm(cf.content, cf.callback, cf.title, cf.buttons);
            }

            ionic.Platform.isIOS() ? $scope.initIAP() : $scope.initIAB();
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

            $scope.initPurchase();
        };

        $scope.onIAPError = function(errorCode, errorMessage) {

            $scope.iPurchaseId ? $scope.purchaseWithIAPError.apply($scope, arguments) : $scope.initIAPError.apply($scope, arguments);
        };

        $scope.initIAPError = function(errorCode, errorMessage) {

            $modal.alert(errorMessage);
            $scope.isProcessing = false;
        };

        $scope.initIAB = function() {

            if (!window.inappbilling || !$scope.item.getPlayStoreProductId()) {
                $modal.alert(gettextCatalog.getString('In-App Billing is not available.'));
                return $scope.isProcessing = false;
            }

            var successCb = function() {
                console.log('SUCCESS: initIAB', JSON.stringify(arguments));
                $scope.initPurchase();
            };

            var errorCb = function() {
                console.warn('ERROR: initIAB', JSON.stringify(arguments));
                $modal.alert(gettextCatalog.getString('In-App Billing is not available.'));
                return $scope.isProcessing = false;
            };

            var options = {
                showLog: true
            };

            var skus = $scope.item.getPlayStoreProductId();

            inappbilling.init(successCb, errorCb, options); //, skus);
        };

        $scope.initPurchase = function() {

            if ($scope.stateParams.subscriptionId) {
                // skip add transaction if user is just sign up because transaction is done automatically on server side
                $scope.iPurchaseId = $scope.stateParams.purchaseId;
                ionic.Platform.isIOS() ? $scope.purchaseWithIAP() : $scope.getPurchasesWithIAB();
            } else {
                $scope.addTransaction();
            }
        };

        $scope.addTransaction = function() {

            var postData = {
                iPackageId: $scope.item.getId()
            };

            console.log('addTransaction', JSON.stringify(postData));

            $http2.post('subscribe/transactionadd', postData)
            .success($scope.addTransactionSuccess)
            .error($scope.addTransactionError);
        };

        $scope.addTransactionSuccess = function(data) {

            console.log('addTransactionSuccess', JSON.stringify(data));

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.isProcessing = false;
            }

            $scope.iPurchaseId = data.iPurchaseId;

            if ($scope.item.getDefaultCost() == 0) {
                $scope.updateTransaction('success');
            } else {
                ionic.Platform.isIOS() ? $scope.purchaseWithIAP() : $scope.getPurchasesWithIAB();
            }
        };

        $scope.addTransactionError = function() {

            console.warn('addTransactionError', JSON.stringify(arguments));
            $scope.isProcessing = false;
        };

        $scope.purchaseWithIAP = function() {

            var quantity = ($scope.storeKitPurchase.price == 0) ? 1 : Math.ceil($scope.item.getDefaultCost() / $scope.storeKitPurchase.price);

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

        /**
         * Get owned products, if the product which user want to buy is owned, we must consume it to make available for purchase again
         */
        $scope.getPurchasesWithIAB = function() {

            var successCb = function(products) {
                console.log('SUCCESS: getPurchasesWithIAB', JSON.stringify(arguments));
                $scope.getPurchasesWithIABSuccess(products);
            };

            var errorCb = function(error) {
                console.warn('ERROR: getPurchasesWithIAB', JSON.stringify(arguments));
                $scope.getPurchasesWithIABError(error);
            };

            inappbilling.getPurchases(successCb, errorCb);
        };

        $scope.getPurchasesWithIABSuccess = function(products) {

            var isOwned = false;
            
            for (var i = 0; i < products.length; i++) {
                if (products[i].productId == $scope.item.getPlayStoreProductId()) {
                    isOwned = true;
                    break;
                }
            }

            if (isOwned) {
                $scope.consumePurchaseWithIAB();
            } else {
                $scope.purchaseWithIAB();
            }
        };

        $scope.getPurchasesWithIABError = function(error) {

            $scope.isProcessing = false;

            $scope.updateTransaction('fail');

            $modal.toast(gettextCatalog.getString('Can not connect to Store. Please try again.'));
        };

        $scope.consumePurchaseWithIAB = function() {

            var successCb = function(data) {
                console.log('SUCCESS: consumePurchaseWithIAB', JSON.stringify(arguments));
                $scope.consumePurchaseWithIABSuccess(data);
            };

            var errorCb = function(error) {
                console.warn('ERROR: consumePurchaseWithIAB', JSON.stringify(arguments));
                $scope.consumePurchaseWithIABError(error);
            };

            inappbilling.consumePurchase(successCb, errorCb, $scope.item.getPlayStoreProductId());
        };

        $scope.consumePurchaseWithIABSuccess = function(data) {

            $timeout($scope.purchaseWithIAB, 3e3);
        };

        $scope.consumePurchaseWithIABError = function(error) {

            $scope.isProcessing = false;

            $scope.updateTransaction('fail');

            $modal.toast(gettextCatalog.getString('Can not connect to Store. Please try again.'));
        };

        $scope.purchaseWithIAB = function() {

            var successCb = function(data) {
                console.log('SUCCESS: purchaseWithIAB', JSON.stringify(arguments));
                $scope.purchaseWithIABSuccess(data);
            };

            var errorCb = function(error) {
                console.warn('ERROR: purchaseWithIAB', JSON.stringify(arguments));
                $scope.purchaseWithIABError(error);
            };

            inappbilling.buy(successCb, errorCb, $scope.item.getPlayStoreProductId());
        };

        $scope.purchaseWithIABSuccess = function(data) {

            $scope.updateTransaction('success', null, data.orderId);
        };

        $scope.purchaseWithIABError = function(error) {

            $scope.isProcessing = false;

            $scope.updateTransaction('fail');

            $modal.toast(error.match(/\d+:([^\)]+)/)[1] || gettextCatalog.getString('Your payment is not completed.'));
        };

        $scope.updateTransaction = function(sStatus, transactionId, orderId) {

            var postData = {
                iPurchaseId: $scope.iPurchaseId,
                sStatus: sStatus,
                sStoreKidTransactionId: transactionId,
                sPlayStoreOrderId: orderId,
                sDevice: ionic.Platform.isAndroid() ? 'android' : (ionic.Platform.isIPad() ? 'ipad' : 'iphone')
            };

            console.log('updateTransaction', JSON.stringify(postData));

            var success = function(data) {
                $scope.updateTransactionSuccess(data, sStatus);
            };

            $http2.post('subscribe/transactionupdate', postData)
            .success(success)
            .error($scope.updateTransactionError)
            .finally(function() {
                $scope.iPurchaseId = null;
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

            if ($scope.stateParams.purchaseId) {
                if (!localStorage.getItem('token')) {
                    $location.path('app/login');
                    return $modal.alert(gettextCatalog.getString('Your payment is completed. Please login.'));
                }

                var viewer = JSON.parse(localStorage.getItem('viewer'));
                $rootScope.$broadcast('user:login', viewer);
                
                $location.path('app/newsfeed', true);
            } else {
                $scope.goBack();
            }

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