define([
    'global/directive/datepicker',
    'global/directive/img-cache',
    'global/directive/ng-background',
    'global/directive/ng-html-compile',
    'global/directive/ptr-pull-to-refresh',
    'global/directive/shorten-html-compile',
    'global/directive/tabs-overflow',
    'global/directive/timepicker',
    'global/directive/yn-content',
    'global/directive/yn-infinite-scroll',
    'global/directive/yn-refresher',
    'global/directive/textarea',
    'global/directive/input',
    'global/directive/yn-keyboard-attach'

],function(){
    angular.module('myapp.directives')
    .directive('datepicker', require('global/directive/datepicker'))
    .directive('imgCache', require('global/directive/img-cache'))
    .directive('ngBackground', require('global/directive/ng-background'))
    .directive('ngHtmlCompile', require('global/directive/ng-html-compile'))
    .directive('ptrPullToRefresh', require('global/directive/ptr-pull-to-refresh'))
    .directive('shortenHtmlCompile', require('global/directive/shorten-html-compile'))
    .directive('tabsOverflow', require('global/directive/tabs-overflow'))
    .directive('timepicker', require('global/directive/timepicker'))
    .directive('ynContent', require('global/directive/yn-content'))
    .directive('ynInfiniteScroll', require('global/directive/yn-infinite-scroll'))
    .directive('ynRefresher', require('global/directive/yn-refresher'))
    .directive('textarea', require('global/directive/textarea'))
    .directive('input', require('global/directive/input'))
    .directive('ynKeyboardAttach', require('global/directive/yn-keyboard-attach'))
    ;
});
