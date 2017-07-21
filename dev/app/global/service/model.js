define([
    'moment',
    'global/helper',
    'global/site',
    'global/viewer'
], function(Moment, Helper) {
    return function($site, $viewer) {
        var _def = {
            idAttribute: '',
            sModelType: '',
            bCanView: true,
            bShowRate: false,
            bCanDelete: false,
            bCanEdit: false,
            bCanView: true,
            user: {},
            getId: function() {
                return this[this.idAttribute] || this.iId || this.id;
            },
            getType: function() {
                return this.sModelType;
            },
            showRate: function(){
                return this.bShowRate || false;  
            },
            extend: function(obj) {
                return jQuery.extend({}, this, obj);
            },
            canView: function() {
                return this.bCanView || false;
            },
            canEdit: function(){
                return this.bCanEdit || false;  
            },
            canDelete: function(){
                return this.bCanDelete || false;  
            },
            canShare: function() {
                return (this.bCanShare == void 0) ? 1 : (this.bCanShare ? 1 : 0);
            },
            canRate: function() {
                return this.bCanRate || false;
            },
            isRated: function() {
                return this.bIsRating || false;
            },
            getRateNumber: function() {
                return +parseFloat(this.fRating || 0).toFixed(1);
            },
            canLike: function() {
                return (!this.canView() || !this.bCanLike) ? false : true;
            },
            isLiked: function() {
                return this.bIsLiked || false;
            },
            getTotalLike: function() {
                return parseInt(this.iTotalLike, 10) || 0;
            },
            getLikedList: function() {
                return this.aUserLike || this.aLikes || [];
            },
            getUrl: function() {
                return '#/app/' + this.getType() + '/' + this.getId();
            },
            getCommentUrl: function() {
                return this.getUrl() + '/comment';
            },
            canComment: function() {
                return (!this.canView() || (!this.bCanComment && !this.bCanPostComment)) ? 0 : 1;
            },
            getTotalComment: function() {
                return parseInt(this.iTotalComment, 10) || 0;
            },
            getTitle: function() {
                return this.sTitle || '';
            },
            getDescription: function() {
                return this.sDescription || '';
            },
            getDescriptionParsed: function() {
                return Helper.prepareHTMLText(this.getDescription());
            },
            getPosterId: function() {
                return this.iUserId || this.iSenderId || this.iOwnerId || this.user.id;
            },
            getPosterType: function() {
                return 'user';
            },
            getPosterUrl: function() {
                return '#/app/' + this.getPosterType() + '/' + this.getPosterId();
            },
            getPosterTitle: function() {
                return this.sFullName || this.sFullname || this.sOwnerName || this.sUserName || '';
            },
            getPosterImageSrc: function() {
                return this.sUserImage || this.sUserImageUrl || this.sOwnerImage || this.sOwnerImageUrl || this.UserProfileImg_Url || this.sUserPhoto || '';
            },
            isOwner: function() {
                return this.getPosterId() == Viewer.get('iUserId');
            },
            getTimestamp: function() {
                return this.iTimeStamp * 1000 || this.iTimestamp * 1000 || 0;
            },
            fromNow: function() {
                return Moment(this.getTimestamp()).fromNow(true);
            },
            getSocialShareUrl: function() {
                return this.sHref || Site.getSiteUrl(this.getType() + '/' + this.getId());
            },
            hasSocialShareUrl: function() {
                return this.getSocialShareUrl() ? true : false;
            },
            cacheKey: function(){
                // return cache key to store fast cached object to use in case we want to access fast cache.
                return this.getType() + this.getId();
            },
        }; 
        /**
         * prototype
         */
        var _models = [];
        
        function factory (prop){
            return jQuery.extend({}, def, porop);
        }
        
        this.set = function($key,pattern){
            _models[$key] = pattern;
        };
        
        this.get = function($key){
            return _models[$key];
        };
        
    };
});
