define([
    'global/base/Model',
    'global/helper',
    'global/viewer'
], function(Model, Helper, Viewer) {

    return Model.extend({
        idAttribute: 'iMessageId',
        sModelType: 'message',
        getPosterImageSrc: function() {
            return this.sUserImage || '';
        },
        getBody: function() {
            return this.sBody || '';
        },
        getBodyParsed: function() {
            return Helper.prepareHTMLText(this.getBody());
        },
        isViewer: function() {
            return this.iUserId == Viewer.get('iUserId');
        }
    });
});