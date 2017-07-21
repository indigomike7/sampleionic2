define([
    'global/base/Model',
],function(Model){
    
    return Model.extend({
        idAttribute: 'iUserId',
        sModelType: 'user',
        getTitle: function() {
        	return this.sTitle || this.sFullName || this.sUserName || this.title || this.full_name || this.sDisplayName || '';
        },
        getImageSrc: function() {
            return this.img || this.sPhotoUrl || this.sImage || this.UserProfileImg_Url || '';
        },
        canViewBasicInfo: function() {
            return this.bCanViewBasicInfo ? true : false;
        },
        canViewFriend: function() {
            return this.bCanViewFriend ? true : false;
        },
        canViewPhoto: function() {
            return this.bCanViewPhoto ? true : false;
        },
        canViewProfileInfo: function() {
            return this.bCanViewProfileInfo ? true : false;
        },
        canViewWall: function() {
            return this.bCanViewWall ? true : false;
        },
        canView: function() {
            return this.bCanView || false;
        }
    });
});
