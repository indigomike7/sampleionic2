define([
    'poll/controller/poll-add',
    'poll/controller/poll-browse-poll',
    'poll/controller/poll-detail',
    'poll/controller/poll-edit',
    'poll/controller/poll-home',
    'poll/controller/poll-item',
    'poll/controller/poll-list',
    'poll/controller/poll-my-poll',
], function() {
    angular.module('myapp.controllers')
        .controller('PollItemCtrl',require('poll/controller/poll-item'))
        .controller('PollDetailCtrl', require('poll/controller/poll-detail'))
        .controller('PollEditCtrl', require('poll/controller/poll-edit'))
        .controller('PollAddCtrl', require('poll/controller/poll-add'))
        .controller('PollHomeCtrl', require('poll/controller/poll-home'))
        .controller('PollMyPollCtrl', require('poll/controller/poll-my-poll'))
        .controller('PollBrowsePollCtrl', require('poll/controller/poll-browse-poll'))
        ;
});