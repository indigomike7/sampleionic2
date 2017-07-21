define([
    'page/directive/page-search',
    'page/directive/page-list',
    'page/directive/page-member-list',
], function(){
    angular.module('myapp.directives')
    .directive('pageSearchDir', require('page/directive/page-search'))
    .directive('pageListDir', require('page/directive/page-list'))
    .directive('pageMemberListDir', require('page/directive/page-member-list'))
    ;
});
