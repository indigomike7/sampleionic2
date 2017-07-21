define([
    'jquery',
    'moment',
    'global/helper',
    'global/site',
    'global/viewer'
], function(jQuery, Moment, Helper, Site, Viewer) {

    return {
        idAttribute: '',
        sModelType: '',
        bShowRate: false,
        bCanView: true,
        bCanLike: true,
        bCanComment: true,
        bCanDelete: false,
        bCanEdit: false,
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
        canDisLike: function() {
            return (!this.canView() || !this.bCanDislike) ? false : true;
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
        canDislike: function() {
            return (!this.canView() || !this.bCanDislike) ? false : true;
        },
        isDisliked: function() {
            return this.bIsDisliked || false;
        },
        getTotalDislike: function() {
            return parseInt(this.iTotalDislike, 10) || 0;
        },
        getDislikedList: function() {
            return this.aUserDislike || this.aDislikes || [];
        },
        getTotalShare: function() {
            return parseInt(this.iTotalShare, 10) || '';
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
            return this.sUserType || 'user';
        },
        getPosterUrl: function() {
            return '#/app/' + this.getPosterType() + '/' + this.getPosterId();
        },
        getPosterTitle: function() {
            return this.sFullName || this.sFullname || this.sOwnerName || this.sUserName || this.user.title || '';
        },
        getPosterImageSrc: function() {
            return this.sUserImage || this.sUserImageUrl || this.sOwnerImage || this.sOwnerImageUrl || this.UserProfileImg_Url || this.sUserPhoto || this.user.img || '';
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
            return this.sLink_Url || (Site.siteUrl + 'index.php?do=/' + this.getType() + '/' + this.getId());
        },
        hasSocialShareUrl: function() {
            return this.getSocialShareUrl() ? true : false;
        },
        cacheKey: function(){
            // return cache key to store fast cached object to use in case we want to access fast cache.
            return this.getType() + this.getId();
        },
        getImageSrc: function() {
            return this.sPhotoUrl || '';
        },
        getParentModuleId: function() {
            return this.parentModuleId || '';
        },
        getItemId: function() {
            return this.iItemId || 0;
        },
        getItemType: function() {
            return this.sItemType || '';
        }
    };
});