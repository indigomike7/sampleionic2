define([
    'global/viewer',
    'global/base/Model',
    'global/site'
],function(Viewer, Model, Site){
    return Model.extend({
        idAttribute: 'iVideoId',
        sModelType: 'ultimatevideo_video',
        bShowRate: true, 
        user: {},
        getUrl: function() {
            return '#/app/ultimatevideo/video/' + this.getId();
        }
    });
});