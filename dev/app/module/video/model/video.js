define([
    'global/viewer',
    'global/base/Model',
    'global/site'
],function(Viewer, Model, Site){
    return Model.extend({
        idAttribute: 'iVideoId',
        sModelType: 'video',
        bShowRate: true, 
        user: {},
        getUrl: function() {
            return '#/app/video/' + this.getId();
        }
    });
});