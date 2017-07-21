define([
    'global/base/Model'
],function(Model){
    
    return Model.extend({
        idAttribute: 'iUserId',
        sModelType: 'user',
        getPosterId: function(){
            return this.getId();
        },
        getImageSrc: function() {
            return this.sPhotoUrl || this.UserProfileImg_Url || '';
        },
        getTitle: function() {
            return this.sTitle || this.sFullName || '';
        }
    });
});
