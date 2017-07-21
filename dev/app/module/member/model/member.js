define([
    'global/base/Model'
],function(Model){
    
    return Model.extend({
        idAttribute: 'iUserId',
        sModelType: 'user',
        getTitle: function() {
            return this.sTitle || this.sFullName || this.sUserName || this.title || this.full_name || this.sDisplayName || '';
        },
        getImageSrc: function(){
            return this.sPhotoUrl;
        },
        getPosterId: function(){
            return this.id;
        }
    });
});
