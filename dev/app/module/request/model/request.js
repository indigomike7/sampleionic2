define([
    'global/base/Model'
],function(Model){
    
    return Model.extend({
        idAttribute: 'iResourceId',
        sModelType: 'user',
        getTitle: function() {
            return this.sFullName || '';
        },
        getImageSrc: function() {
        	return this.UserProfileImg_Url || '';
        }
    });
});