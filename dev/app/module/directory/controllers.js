define([
    'directory/controller/directory-activity',
    'directory/controller/directory-all',
    'directory/controller/directory-all-item',
    'directory/controller/directory-claim',
    'directory/controller/directory-claim-item',
    'directory/controller/directory-detail',
    'directory/controller/directory-edit',
    'directory/controller/directory-edit-cover',
    'directory/controller/directory-events',
    'directory/controller/directory-faq-list',
    'directory/controller/directory-faqs',
    'directory/controller/directory-favourite',
    'directory/controller/directory-favourite-item',
    'directory/controller/directory-follow',
    'directory/controller/directory-follow-item',
    'directory/controller/directory-follower-list',
    'directory/controller/directory-followers',
    'directory/controller/directory-member-list',
    'directory/controller/directory-members',
    'directory/controller/directory-music',
    'directory/controller/directory-music-detail',
    'directory/controller/directory-music-list',
    'directory/controller/directory-home',
    'directory/controller/directory-invite',
    'directory/controller/directory-item',
    'directory/controller/directory-list',
    'directory/controller/directory-my',
    'directory/controller/directory-my-item',
    'directory/controller/directory-overview',
    'directory/controller/directory-review-edit',
    'directory/controller/directory-review-item',
    'directory/controller/directory-review-list',
    'directory/controller/directory-reviews',
    'directory/controller/directory-reviews-add',
    'directory/controller/directory-search',
    'directory/controller/directory-add-step1',
    'directory/controller/directory-add-step2',
    'directory/controller/directory-detail-photo',
    'directory/controller/directory-detail-video',
    'directory/controller/directory-detail-videochannel',
    'directory/controller/directory-upload-photo',
    'directory/controller/directory-discussion',
    'directory/controller/directory-topic-add',
    'directory/controller/directory-topic-detail',
    'directory/controller/directory-topic-edit-post',
    'directory/controller/directory-topic-list',
    'directory/controller/directory-topic-quote-post',
    'directory/controller/directory-topic-reply',
    'directory/controller/directory-post-item',
], function() {
    angular.module('myapp.controllers')
        .controller('DirectoryActivityCtrl', require('directory/controller/directory-activity'))
        .controller('DirectoryAllCtrl', require('directory/controller/directory-all'))
        .controller('DirectoryAllItemCtrl', require('directory/controller/directory-all-item'))
        .controller('DirectoryClaimCtrl', require('directory/controller/directory-claim'))
        .controller('DirectoryClaimItemCtrl', require('directory/controller/directory-claim-item'))
        .controller('DirectoryDetailCtrl', require('directory/controller/directory-detail'))
        .controller('DirectoryEventsCtrl', require('directory/controller/directory-events'))
        .controller('DirectoryFaqListCtrl', require('directory/controller/directory-faq-list'))
        .controller('DirectoryFaqsCtrl', require('directory/controller/directory-faqs'))
        .controller('DirectoryFavouriteCtrl', require('directory/controller/directory-favourite'))
        .controller('DirectoryFavouriteItemCtrl', require('directory/controller/directory-favourite-item'))
        .controller('DirectoryFollowCtrl', require('directory/controller/directory-follow'))
        .controller('DirectoryFollowItemCtrl', require('directory/controller/directory-follow-item'))
        .controller('DirectoryFollowerListCtrl', require('directory/controller/directory-follower-list'))
        .controller('DirectoryFollowersCtrl', require('directory/controller/directory-followers'))
        .controller('DirectoryMemberListCtrl', require('directory/controller/directory-member-list'))
        .controller('DirectoryMembersCtrl', require('directory/controller/directory-members'))
        .controller('DirectoryMusicCtrl', require('directory/controller/directory-music'))
        .controller('DirectoryMusicDetailCtrl', require('directory/controller/directory-music-detail'))
        .controller('DirectoryMusicListCtrl', require('directory/controller/directory-music-list'))
        .controller('DirectoryHomeCtrl', require('directory/controller/directory-home'))
        .controller('DirectoryInviteCtrl', require('directory/controller/directory-invite'))
        .controller('DirectoryItemCtrl', require('directory/controller/directory-item'))
        .controller('DirectoryListCtrl', require('directory/controller/directory-list'))
        .controller('DirectoryMyCtrl', require('directory/controller/directory-my'))
        .controller('DirectoryMyItemCtrl', require('directory/controller/directory-my-item'))
        .controller('DirectoryOverviewCtrl', require('directory/controller/directory-overview'))
        .controller('DirectoryReviewEditCtrl', require('directory/controller/directory-review-edit'))
        .controller('DirectoryReviewItemCtrl', require('directory/controller/directory-review-item'))
        .controller('DirectoryReviewListCtrl', require('directory/controller/directory-review-list'))
        .controller('DirectoryReviewsAddCtrl', require('directory/controller/directory-reviews-add'))
        .controller('DirectoryReviewsCtrl', require('directory/controller/directory-reviews'))
        .controller('DirectorySearchCtrl', require('directory/controller/directory-search'))
        .controller('DirectoryAddStep1Ctrl', require('directory/controller/directory-add-step1'))
        .controller('DirectoryAddStep2Ctrl', require('directory/controller/directory-add-step2'))
        .controller('DirectoryDetailPhotoCtrl', require('directory/controller/directory-detail-photo'))
        .controller('DirectoryDetailVideoCtrl', require('directory/controller/directory-detail-video'))
        .controller('DirectoryDetailVideochannelCtrl', require('directory/controller/directory-detail-videochannel'))
        .controller('DirectoryUploadPhotoCtrl', require('directory/controller/directory-upload-photo'))
        .controller('DirectoryTopicAddCtrl', require('directory/controller/directory-topic-add'))
        .controller('DirectoryTopicDetailCtrl', require('directory/controller/directory-topic-detail'))
        .controller('DirectoryTopicEditPostCtrl', require('directory/controller/directory-topic-edit-post'))
        .controller('DirectoryTopicListCtrl', require('directory/controller/directory-topic-list'))
        .controller('DirectoryTopicQuotePostCtrl', require('directory/controller/directory-topic-quote-post'))
        .controller('DirectoryTopicReplyCtrl', require('directory/controller/directory-topic-reply'))
        .controller('DirectoryPostItemCtrl', require('directory/controller/directory-post-item'))
        .controller('DirectoryDiscussionCtrl', require('directory/controller/directory-discussion'))
        .controller('DirectoryEditCtrl', require('directory/controller/directory-edit'))
        .controller('DirectoryEditCoverCtrl', require('directory/controller/directory-edit-cover'))
});
