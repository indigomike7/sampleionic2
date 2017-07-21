define([], function() {

    return function() {

        this.list = [],
        this.products = {};

        var _this = this;
        var localStorage = window.localStorage || {};

        this.initialize = function(list, loadCallback, purchaseCallback, errorCallback) {

            console.log('IAP.initialize', JSON.stringify(arguments));

            this.list = list;
            this.loadCallback = loadCallback;
            this.purchaseCallback = purchaseCallback;
            this.errorCallback = errorCallback;

            // Check availability of the storekit plugin
            if (!window.storekit) {
                var errorMessage = 'In-App Purchases is not available.';
                this.errorCallback && this.errorCallback(1, errorMessage);
                return console.warn('FAIL: IAP.initialize', errorMessage);
            }

            // Initialize
            storekit.init({
                ready: this.onReady,
                purchase: this.onPurchase,
                restore: this.onRestore,
                error: this.onError
            });
        };

        this.onReady = function() {

            // Once setup is done, load all product data.
            storekit.load(_this.list, function(products, invalidIds) {

                console.log('DONE: storekit.load', JSON.stringify(arguments));

                _this.loaded = true;
                _this.loadCallback && _this.loadCallback(products, invalidIds);
            });
        };

        this.onPurchase = function(transactionId, productId /*, receipt*/ ) {

            console.log('IAP.onPurchase', JSON.stringify(arguments));

            var n = (localStorage['storekit.' + productId] | 0) + 1;
            localStorage['storekit.' + productId] = n;

            _this.purchaseCallback && _this.purchaseCallback(transactionId, productId);
        };

        this.onError = function(errorCode, errorMessage) {

            console.warn('IAP.onError', JSON.stringify(arguments));

            _this.errorCallback && _this.errorCallback(errorCode, errorMessage);
        };

        this.onRestore = function(transactionId, productId /*, transactionReceipt*/ ) {
            
            var n = (localStorage['storekit.' + productId] | 0) + 1;
            localStorage['storekit.' + productId] = n;
        };

        this.buy = function(productId, quantity) {

            console.log('IAP.buy', JSON.stringify(arguments));

            storekit.purchase(productId, quantity);
        };

        this.restore = function() {
            
            storekit.restore();
        };
    };
});