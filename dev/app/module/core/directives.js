define([
    'core/directive/core-action-list',
    'core/directive/core-back',
    'core/directive/core-header-stat',
    'core/directive/core-href',
    'core/directive/core-pagination',
    'core/directive/core-private-msg',
    'core/directive/core-stars',
    'core/directive/core-toggle-left',
    'core/directive/core-toggle-right'
], function() {

    angular.module('myapp.directives')
        .directive('backDir', require('core/directive/core-back'))
        .directive('toggleLeftDir', require('core/directive/core-toggle-left'))
        .directive('toggleRightDir', require('core/directive/core-toggle-right'))
        .directive('coreActionListDir', require('core/directive/core-action-list'))
        .directive('corePagination', require('core/directive/core-pagination'))
        .directive('coreStars', require('core/directive/core-stars'))
        .directive('headerStatDir', require('core/directive/core-header-stat'))
        .directive('hrefDir', require('core/directive/core-href'))
        .directive('privateMsgDir', require('core/directive/core-private-msg'))
        ;
});