define([
    'global/viewer',
    'global/base/Model',
    'global/site'
],function(Viewer, Model, Site){
    return Model.extend({
        idAttribute: 'iVideoId',
        sModelType: 'videochannel',
        user: {},
    });
});