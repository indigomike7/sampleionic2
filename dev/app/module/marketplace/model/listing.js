define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {

    return Model.extend({
        idAttribute: 'iListingId',
        sModelType: 'marketplace',
        bCanView: true,
        bCanLike: true,
        bIsAllowBuyInApp: true,
        getCurrencySymbol: function() {
            return this.sSymbol || '';
        },
        getCurrencyId: function() {
            return this.sCurrencyId || '';
        },
        getPrice: function() {
            return this.sPrice || 0;
        },
        isFree: function() {
            return parseFloat(this.getPrice()) == 0;
        },
        getImageSrc: function() {
            return this.sMarketplaceImage || '';
        },
        getCountry: function() {
            return this.sCountry || '';
        },
        getCity: function() {
            return this.sCity || '';
        },
        hasCity: function() {
            return this.getCity() ? true : false;
        },
        getLocation: function() {
            return this.getCountry() + (this.hasCity() ? (' » ' + this.getCity()) : '');
        },
        hasLocation: function() {
            return this.getLocation() ? true : false;
        },
        getMiniDescription: function() {
            return this.sMiniDescription || '';
        },
        hasMiniDescription: function() {
            return this.getMiniDescription() ? true : false;
        },
        isFeatured: function() {
            return this.bIsFeatured ? true : false;
        },
        getCountryIso: function() {
            return this.sCountryIso || '';
        },
        getCountryChildId: function() {
            return this.iCountryChildId || 0;
        },
        getCategoryIds: function() {
            return this.aCategoriesId || [];
        },
        isSell: function() {
            return this.bIsSell ? true : false;
        },
        getZipCode: function() {
            return this.sPostalCode || '';
        },
        hasZipCode: function() {
            return this.getZipCode() ? true : false;
        },
        getPrivacyId: function() {
            return parseInt(this.iPrivacy) || 0;
        },
        getPrivacyCommentId: function() {
            return parseInt(this.iPrivacyComment) || 0;
        },
        getPhotos: function() {
            return this.aImages || [];
        },
        hasPhotos: function() {
            return this.getPhotos().length > 0;
        },
        isPending: function() {
            return this.bIsPending ? true : false;
        },
        getCategory: function() {

            var sCategories = '';

            var aCategories = this.aCategoriesData || [];
            for (var i = 0; i < aCategories.length; i++) {
                sCategories += (i > 0 ? ' » ' + aCategories[i][0] : aCategories[i][0]);
            }

            return sCategories;
        },
        getText: function() {
            return this.sDescription || '';
        },
        getTextClean: function() {
            return this.getText().replace(/(<([^>]+)>)/ig,"");
        },
        getTextParsed: function() {
            return Helper.prepareHTMLText(this.getText());
        },
        getStoreKitPurchaseId: function() {
            var platform = '';
            if (ionic.Platform.isIOS()) {
                platform = ionic.Platform.isIPad() ? 'ipad' : 'iphone';
            }
            return platform ? this.aStoreKitPurchaseId[platform] : '';
        },
        canBuyInApp: function() {
            return this.bIsAllowBuyInApp;
        },
        showBuyInFullSite: function() {
            return this.bIsShowBuyInFullSite ? true : false;
        },
        getFullSiteUrl: function() {
            return this.sFullSiteUrl || '';
        },
        hasFullSiteUrl: function() {
            return this.getFullSiteUrl() ? true : false;
        },
        getSocialShareUrl: function() {
            return this.getFullSiteUrl();
        },
        getActionCount: function() {

            var cnt = 1;

            if (this.isSell() && this.getPrice() != 0) {
                if (this.canBuyInApp()) {
                    cnt++;
                }
                if (this.showBuyInFullSite() && this.hasFullSiteUrl()) {
                    cnt++;
                }
            }

            return cnt;
        },
        canSendMessage: function() {
            return this.bCanSendMessage ? true : false;
        },
        hasImage: function() {
            return this.bHasImage ? true : false;
        }
    });
});