define([
    'global/base/Model',
    'global/helper'
],function(Model, Helper){
    
    return Model.extend({
        idAttribute: 'iNotificationId',
        sModelType: 'notification',
        getPosterImageSrc: function() {
            return this.sUserImage || '';
        },
        getMessageParsed: function() {
            return this.sMessage.replace(/(<span [^>]+>)([^<]+)+(<\/span>?)+/ig, '<span class="positive">$2</span>');
        },
        getItemType: function() {
            return this.sItemType || '';
        },
        getItemId: function() {
            return this.iItemId || 0;
        },
        getOwnerId: function() {
            return this.iOwnerUserId || 0;
        },
        getNotificationType: function() {
            return this.sType || '';
        },
        getNotificationTypeId: function() {
            return this.sTypeId || '';
        },
        getLink: function() {
            return this.aLink || {};
        },
        getUrl: function() {

            var url = '';

            switch (this.getNotificationTypeId()) {
                case 'advancedphoto_like':
                case 'comment_advancedphoto':
                    url = '#/app/advancedphoto/' + (this.getLink().iPhotoId ? this.getLink().iPhotoId : this.getItemId());
                    break;
                case 'comment_photo':
                case 'photo_like':
                    url = '#/app/photo/' + (this.getLink().iPhotoId ? this.getLink().iPhotoId : this.getItemId());
                    break;
                case 'comment_feed':
                case 'comment_user_status':
                case 'feed_comment_profile':
                case 'user_status_like':
                    if (!this.getLink() || !this.getLink().iFeedId) {
                        break;
                    }
                    return '#/app/feed/' + this.getLink().iFeedId + '/feed';
                    break;
                case 'friend_accept':
                case 'friend_accepted':
                    url = '#/app/user/' + this.getLink().iUserId;
                    break;
                case 'comment_music_album':
                case 'music_album_like':
                    url = '#/app/music_album/' + this.getLink().iAlbumId;
                    break;
                case 'comment_music_song':
                case 'music_song_like':
                    url = '#/app/music_song/' + this.getLink().iSongId;
                    break;
                case 'comment_musicsharing_album':
                case 'musicsharing_album_like':
                    url = '#/app/musicsharing_album/' + this.getItemId();
                    break;
                case 'comment_photo_album':
                case 'photo_album_like':
                    url = '#/app/photo_album/' + this.getLink().iAlbumId;
                    break;
                case 'advancedphoto_album_like':
                case 'comment_advancedphoto_album':
                    url = '#/app/advancedphoto_album/' + this.getLink().iAlbumId;
                    break;
                case 'comment_video':
                case 'video_like':
                    url = '#/app/video/' + this.getLink().iVideoId;
                    break;
                case 'comment_videochannel':
                case 'videochannel':
                case 'videochannel_approved':
                case 'videochannel_favourite':
                case 'videochannel_like':
                    url = '#/app/videochannel/' + this.getItemId();
                    break;
                case 'pages_approved':
                    url = '#/app/pages/' + this.getLink().iPageId;
                    break;
                case 'pages_invite':
                    url = '#/app/pages/' + (this.getItemId() || this.getLink().iPageId || this.getLink().iResourceId);
                    break;
                case 'pages_comment':
                case 'pages_comment_like':
                    url = '#/app/page_activity/' + this.getLink().iPageId;
                    break;
                case 'pages_comment_feed':
                    url = '#/app/page_activity/' + this.getLink().iPageId;
                    break;
                case 'pages_joined':
                    url = '#/app/pages/' + this.getLink().iResourceId;
                    break;
                case 'blog_approved':
                case 'blog_like':
                case 'comment_blog':
                    url = '#/app/blog/' + this.getItemId();
                    break;
                case 'comment_poll':
                case 'poll':
                case 'poll_approved':
                case 'poll_like':
                    url = '#/app/poll/' + this.getItemId();
                    break;
                case 'comment_quiz':
                case 'quiz':
                case 'quiz_like':
                    url = '#/app/quiz/' + this.getItemId();
                    break;
                case 'forum_post_like':
                case 'forum_subscribed_post':
                case 'forum_thread_approved':
                    url = '#/app/forum_thread/' + this.getItemId();
                    break;
                case 'advancedmarketplace_approved':
                case 'advancedmarketplace_follow':
                case 'advancedmarketplace_invite':
                case 'advancedmarketplace_like':
                case 'comment_advancedmarketplace':
                    url = '#/app/advancedmarketplace/' + this.getItemId();
                    break;
                case 'comment_marketplace':
                case 'marketplace_approved':
                case 'marketplace_invite':
                case 'marketplace_like':
                    url = '#/app/marketplace/' + this.getItemId();
                    break;
                case 'feed_mini_like':
                    var pattern = /i.*Id/;
                    var iItemId = Helper.getObjPropByPatt(this.getLink(), pattern);
                    url = '#/app/' + this.getLink().sCommentType + '/' + iItemId;
                    break;
                // event and fevent
                case 'event_approved':
                case 'event_invite':
                case 'event_like':
                case 'fevent_approved':
                case 'fevent_invite':
                case 'fevent_like':
                    url = '#/app/event/' + (this.getItemId() || this.getLink().iEventId);
                    break;
                // event and fevent activity
                case 'comment_event':
                case 'comment_fevent':
                case 'event_comment':
                case 'event_comment_feed':
                case 'event_comment_like':
                case 'fevent_comment':
                case 'fevent_comment_feed':
                case 'fevent_comment_like':
                    url = '#/app/event/' + (this.getItemId() || this.getLink().iEventId) + '/activity';
                    break;
                case 'directory_approve_business':
                case 'directory_approve_claimrequest':
                case 'directory_expirenotify':
                case 'directory_invited':
                case 'directory_like':
                case 'directory_postitem':
                case 'directory_updateinfobusiness':
                    url = '#/app/directory/' + this.getItemId();
                    break;
                case 'directory_comment':
                case 'directory_comment_like':
                case 'directory_comment_feed':
                    url = '#/app/directory/business/' + this.getLink().iBusinessId + '/activity';
                    break;
                case 'ultimatevideo_likevideo':
                case 'ultimatevideo_commentvideo':
                    url = '#/app/ultimatevideo/video/' + this.getLink().iVideoId;
                    break;
                case 'ultimatevideo_likeplaylist':
                case 'ultimatevideo_commentplaylist':
                    url = '#/app/ultimatevideo/playlist/' + this.getLink().iPlaylistId;
                    break;
                default:
                    url = '#/none';
            }

            return url;
        },
        getPath: function() {
            return this.getUrl().replace('#/app', 'app');
        }
    });
});