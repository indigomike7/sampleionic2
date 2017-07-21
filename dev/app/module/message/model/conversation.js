define([
    'global/base/Model',
    'global/helper',
    'global/viewer',
    'user/model/user'
], function(Model, Helper, Viewer, Model) {

    return Model.extend({
        idAttribute: 'iConversationId',
        sModelType: 'message',
        getTitle: function() {
            return this.sTitle || '';
        },
        getBody: function() {
            return this.sBody || '';
        },
        getBodyParsed: function() {
            return Helper.prepareHTMLText(this.getBody());
        },
        getPosterId: function() {
            return this.iUserId || 0;
        },
        getPosterTitle: function() {
            return this.sFullName || '';
        },
        getPosterImageSrc: function() {
            return this.sUserImage || '';
        },
        isRead: function() {
            return this.bIsRead || false;
        },
        canReply: function() {
            return this.getFolderName() != 'sent';
        },
        getRecipients: function() {
            
            var aRecipients = this.aRecipients || [];
            var viewerIndex = -1;

            for (var i = 0; i < aRecipients.length; i++) {
                if (aRecipients[i].iUserId == Viewer.get('iUserId')) {
                    viewerIndex = i;
                }
            }

            if (viewerIndex > -1) {
                aRecipients.splice(viewerIndex, 1);
            }

            aRecipients = $.map(aRecipients, function(user) {
                return $.extend({}, Model, user);
            });

            return aRecipients;
        },
        getTotalRecipients: function() {
            return this.getRecipients().length;
        },
        getFolderName: function() {
            return this.sNameOfFolder || '';
        }
    });
});