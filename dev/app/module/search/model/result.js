define([
    'global/base/Model'
],function(Model){
    return Model.extend({
        idAttribute: 'id',
        sModelType: 'user',
        getTitle: function() {
            return this.sTitle;
        },
        getImageSrc: function(){
            return this.sImage;
        }
    });
});