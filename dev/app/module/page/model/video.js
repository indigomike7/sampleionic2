vdefine([
    'global/base/Model'
],function(Model){
    return Model.extend({
        idAttribute: 'iVideoId',
        defaults: {
            sModelType: 'video'    
        },
        getImageSrc: function(){
            return this.UserProfileImg_Url;
        },
        getTitle: function(){
            return this.sTitle;
        }
    });
});