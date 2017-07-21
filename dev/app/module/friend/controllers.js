define([
    'friend/controller/friend-autocomplete-list',
    'friend/controller/friend-browse',
    'friend/controller/friend-item',
    'friend/controller/friend-list',
    'friend/controller/friend-search',
], function() {
    angular.module('myapp.controllers')
        .controller('FriendBrowseFriendCtrl', require('friend/controller/friend-browse'))
        .controller('FriendAutocompleteListCtrl',require('friend/controller/friend-autocomplete-list'))
        .controller('FriendItemCtrl',require('friend/controller/friend-item'))
        .controller('FriendListCtrlCtrl',require('friend/controller/friend-list'))
        .controller('FriendSearchCtrl',require('friend/controller/friend-search'))
        ;
});