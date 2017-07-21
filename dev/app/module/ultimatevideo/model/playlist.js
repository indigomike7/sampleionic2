define([
    'global/viewer',
    'global/base/Model',
    'global/site'
],function(Viewer, Model, Site){
    return Model.extend({
        idAttribute: 'iPlaylistId',
        sModelType: 'ultimatevideo_playlist',
        bShowRate: true,
        user: {},
        getUrl: function() {
            return '#/app/ultimatevideo/playlist/' + this.getId();
        }
    });
});