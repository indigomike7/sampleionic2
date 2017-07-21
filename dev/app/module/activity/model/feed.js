define([
    'activity/plugin/headline',
    'global/base/Model',
    'global/helper',
    'global/viewer',
    'global/site'
], function(HeadlinePlugin, Model, Helper, Viewer, Site) {

    return Model.extend({
        idAttribute: 'iActionId',
        sModelType: 'feed',
        sParams: '',
        getContent: function() {
            var commentRegex = new RegExp('^comment_');
            if (commentRegex.test(this.getActionType())) {
                return this.getParam().body || '';
            }
            if (this.getActionType() == 'profile_photo_update') {
                return '';
            }
            return this.sContent || '';
        },
        getContentParsed: function() {
            var parsedContent = Helper.prepareHTMLText(this.getContent());
            parsedContent = Helper.parseHashTag(parsedContent);
            parsedContent = Helper.parseUserTag(parsedContent);
            if (this.getItemType() == 'event' || this.getItemType() == 'fevent')
            {
                parsedContent = Helper.removeImgs(parsedContent);
            }

            return parsedContent;
        },
        getCount: function(){
            return this.getAttachments().length;
        },
        showMaxPhoto: function(){
            var count = this.getCount();
            if(count > 5)
                return 5;
            return count;
        },
        getAttachments: function() {
            return this.aAttachments || [];
        },
        hasAttachment: function() {
            var atts = this.getAttachments();
            return atts.length > 0;
        },
        getAttachmentUrl: function() {
            var sType = this.getAttachments()[0].sType;
            if (sType == 'fevent') {
                sType = 'event';
            }
            return this.hasAttachment() ? ('#/app/' + sType + '/' + this.getAttachments()[0].iId) : '#';
        },
        getAttachmentTitle: function(gettextCatalog) {
            var title = gettextCatalog.getString('item');
            
            if (this.hasAttachment() && typeof(this.getAttachments()[0].sType) != 'undefined' && this.getAttachments()[0].sType != null) {
                // rename the title from pages (fox's item type) to human readable to avoid confusion
                switch (this.getAttachments()[0].sType) {
                    case 'article':
                        title = gettextCatalog.getString('article');
                        break;
                    case 'blog':
                        title = gettextCatalog.getString('blog');
                        break;
                    case 'event':
                    case 'fevent':
                        title = gettextCatalog.getString('event');
                        break;
                    case 'directory':
                        title = gettextCatalog.getString('business');
                        break;
                    case 'marketplace':
                    case 'advancedmarketplace':
                        title = gettextCatalog.getString('listing');
                        break;
                    case 'music_album':
                    case 'musicsharing_album':
                        title = gettextCatalog.getString('album');
                        break;
                    case 'music_song':
                    case 'musicsharing_song':
                        title = gettextCatalog.getString('song');
                        break;
                    case 'pages':
                        title = gettextCatalog.getString('page');
                        break;
                    case 'photo':
                    case 'advancedphoto':
                        title = gettextCatalog.getString('photo');
                        break;
                    case 'poll':
                        title = gettextCatalog.getString('poll');
                        break;
                    case 'quiz':
                        title = gettextCatalog.getString('quiz');
                        break;
                    case 'video':
                    case 'videochannel':
                        title = gettextCatalog.getString('video');
                        break;
                    case 'ultimatevideo/video':
                    case 'ultimatevideo_video':
                        title = gettextCatalog.getString('video');
                        break;
                    case 'ultimatevideo/playlist':
                    case 'ultimatevideo_playlist':
                        title = gettextCatalog.getString('playlist');
                        break;
                }
            }
            
            return title;
        },
        getAttachmentLink: function(gettextCatalog) {
            return '<a href="' + this.getAttachmentUrl() + '">' + this.getAttachmentTitle(gettextCatalog) + '</a>';
        },
        getActionType: function() {
            return this.sActionType || '';
        },
        getItemId: function() {
            return this.iItemId || 0;
        },
        getItemType: function() {
            return this.sItemType || '';
        },
        getItemTitle: function() {
            return this.sItemTitle || '';
        },
        getItemUrl: function() {
            return '#/app/' + this.getItemType() + '/' + this.getItemId();
        },
        getItemLink: function(len) {
            len = len || 22;
            return '<a href="' + this.getItemUrl() + '">' + this.getItemTitle() + '</a>';
        },
        getParam: function() {
            return this.sParams || {};
        },
        getObjectId: function() {
            return this.iObjectId || 0;
        },
        getObjectType: function() {
            return this.sObjectType || '';
        },
        getObjectTitle: function() {
            return this.sObjectTitle || '';
        },
        getObjectUrl: function() {
            return '#/app/' + this.getObjectType() + '/' + this.getObjectId();
        },
        getObjectLink: function(len) {
            len = len || 22;
            return '<a href="' + this.getObjectUrl() + '">' + this.getObjectTitle() + '</a>';
        },
        getParentId: function() {
            return this.iObjectParentId || 0;
        },
        getParentType: function() {
            return this.sObjectParentType || '';
        },
        getParentTitle: function() {
            return this.sObjectParentTitle || '';
        },
        getParentUrl: function() {
            return '#/app/' + this.getParentType() + '/' + this.getParentId();
        },
        getParentLink: function(len) {
            len = len || 22;
            return '<a href="' + this.getParentUrl() + '">' + this.getParentTitle() + '</a>';
        },
        getPosterLink: function(){
            return '<a class="poster-link" href="' + this.getPosterUrl() + '">' + this.getPosterTitle() + '</a>';
        },
        getOwnerTitle: function(){
            return this.sObjectOwnerTitle || '';
        },
        getOwnerId: function(){
            return this.iObjectOwnerId || 0;
        },
        getOwnerType: function(){
            return this.sObjectOwnerType || 'user';
        },
        getOwnerUrl: function(){
            return '#/app/' + this.getOwnerType() + '/' + this.getOwnerId();
        },
        getOwnerLink: function(){
            return '<a href="'+this.getOwnerUrl()+'">'+this.getOwnerTitle()+'</a>';
        },
        detailPhotoUrl: function($index){
            var obj = this.aAttachments[$index];
            //return '#/app/photos/' + obj.sParentType + '/' + obj.iParentId  + '/' + obj.sModelType + '/' + obj.iPhotoId;

            if(obj.iAlbumId > 0){
                return '#/app/photos/' + 'album' + '/' + obj.iAlbumId  + '/' + obj.sType + '/' + obj.iId;    
            }else{
                return '#/app/photos/' + this.sObjectOwnerType + '/' + this.iObjectOwnerId  + '/' + obj.sType + '/' + obj.iId;    
            }
        },
        getSocialShareUrl: function() {

            if (this.hasAttachment()) {
                var attachment = this.getAttachments()[0];
                if (attachment.sLink_Url) {
                    return attachment.sLink_Url;
                } else if (attachment.sType && attachment.iId) {
                    return Site.siteUrl + 'index.php?do=/' + attachment.sType + '/' + attachment.iId;
                }
            }

            return null;
        },
        hasParentUser: function() {
            return this.getParentTitle() && this.getParentId();
        },
        getSharedUserId: function() {
            return this.iSharedUserId || 0;
        },
        getSharedUserTitle: function() {
            return this.sSharedUserName || '';
        },
        getSharedUserUrl: function() {
            return this.getSharedUserId() ? '#/app/' + this.getSharedUserType() + '/' + this.getSharedUserId() : '';
        },
        getSharedUserLink: function() {
            return this.getSharedUserUrl() ? '<a href="' + this.getSharedUserUrl() + '">' + this.getSharedUserTitle() + '</a>' : '';
        },
        getSharedUserType: function() {
            return this.sSharedUserType || 'user';
        },
        getUrl: function() {
            return '#/app/' + this.getType() + '/' + this.getId() + '/' + this.getParentModuleId();
        },
        getHeadLine: function() {
            return (this.sHeadLine || '').replace(/\[x=(\d+)\]([^\[]+)\[\/x\]/ig, '<a href="#/app/user/$1">$2</a>');
        },
        getPrivacy: function(){
            return this.iPrivacy || 0;
        },
        getPrivacyIcon: function(iPrivacy){
            var aPrivacyIcons = [
                'ion-earth',
                'ion-person',
                'ion-person-stalker',
                'ion-locked'
            ];

            return aPrivacyIcons[iPrivacy];
        }
    });
});
