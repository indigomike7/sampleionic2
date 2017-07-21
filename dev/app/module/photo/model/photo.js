define([
    'global/base/Model',
    'global/site'
], function(Model, Site) {
    return Model.extend({
        idAttribute: 'iPhotoId',
        sModelType: 'album_photo',
        bCanView: true,
        sParentType: 'album',
        iParentId: 0,
        defaults: {},
        bCanDelete: false,
        bCanEdit: false,
        getUrl: function() {
            return '#/app/photos/' + this.sParentType + '/' + (this.iParentId ? this.iParentId : this.iAlbumId) + '/' + this.sModelType+ '/' + this.iPhotoId;
        },
        getImageSrc: function() {
            return this.UserProfileImg_Url || this.sPhotoUrl || '';
        },
        getTitle: function() {
            return this.sTitle;
        },
        isMainPhoto: function() {
            return this.bIsMainPhoto || false;
        },
        setMainPhoto: function() {
            this.bIsMainPhoto = true;
        },
        unsetMainPhoto: function() {
            this.bIsMainPhoto = false;
        }
    });
});