define([
    'group/directive/group-search',
    'group/directive/group-list',
    'group/directive/group-member-list',
], function(){
    angular.module('myapp.directives')
    .directive('groupSearchDir', require('group/directive/group-search'))
    .directive('groupListDir', require('group/directive/group-list'))
    .directive('groupMemberListDir', require('group/directive/group-member-list'))
    ;
});
