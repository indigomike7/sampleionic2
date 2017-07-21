define([
    'member/directive/member-search',
    'member/directive/member-list',
],function(){
    angular.module('myapp.directives')
    .directive('memberSearchDir', require('member/directive/member-search'))
    .directive('memberListDir', require('member/directive/member-list'));
});
