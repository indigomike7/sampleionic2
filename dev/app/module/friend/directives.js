define([
    'friend/directive/friend-autocomplete-list',
    'friend/directive/friend-search',
    'friend/directive/friend-list',
],function(){
    angular.module('myapp.directives')
    .directive('friendAutocompleteListDir', require('friend/directive/friend-autocomplete-list'))
    .directive('friendSearchDir', require('friend/directive/friend-search'))
    .directive('friendListDir', require('friend/directive/friend-list'));
});
