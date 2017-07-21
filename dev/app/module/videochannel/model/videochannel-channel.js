define([
    'global/viewer',
    'global/base/Model',
    'global/site'
],function(Viewer, Model, Site){
    return Model.extend({
        idAttribute: 'iChannelId',
        sModelType: 'videochannel-channel',
        user: {},
        canAddMoreVideo: function(){
            return true;
        },
        canAutoUpdate: function(){
            return this.bCanAutoUpdate || 0;
        },
    });
});