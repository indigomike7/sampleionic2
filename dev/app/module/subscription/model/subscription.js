define([
    'global/base/Model'
], function(Model) {

    return Model.extend({
        idAttribute: 'iPackageId',
        sModelType: 'subscription',
        getPackageImageSrc: function() {
            return this.sSubscriptionPackageImage || '';
        },
        getDefaultCost: function() {
            return this.sDefaultCost || '';
        },
        getCurrencySymbol: function() {
            return this.sCurrencySymbol || '';
        },
        getDefaultRecurringCost: function() {
            return this.sDefaultRecurringCost || '';
        },
        getRecurringPeriod: function() {
            return this.sRecurringPeriod.toLowerCase() || '';
        },
        getStoreKitPurchaseId: function() {
            return this.aStoreKitPurchaseId[ionic.Platform.isIPad() ? 'ipad' : 'iphone'] || '';
        },
        getCurrencyId: function() {
            return this.sDefaultCurrencyId || '';
        },
        getSignupPurchaseId: function() {
            return this.iSignupPurchaseId || 0;
        },
        getPlayStoreProductId: function() {
            return this.sPlayStoreProductId || '';
        },
        hasImage: function() {
            return this.bHasPackageImage ? true : false;
        }
    });
});