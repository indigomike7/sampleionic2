define([
    'activity/controller/baseitem',
    'activity/controller/feed-add',
    'activity/controller/feed-detail',
    'activity/controller/newsfeeds',
    'activity/controller/feed-item'
],function(BaseItemCtrl, FeedAddCtrl, FeedDetailCtrl, NewsFeedsCtrl, FeedItemController){
    angular.module('myapp.controllers')
        .controller('ActivityNewsFeedController', NewsFeedsCtrl)
        .controller('ActivityBaseItemController', BaseItemCtrl)
        .controller('FeedAddController', FeedAddCtrl)
        .controller('FeedDetailController', FeedDetailCtrl)
        .controller('FeedItemController', FeedItemController);
});
