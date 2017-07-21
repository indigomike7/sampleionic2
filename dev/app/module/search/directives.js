define([
    'search/directive/search-form',
    'search/directive/search-result-list',
],function(){
    angular.module('myapp.directives')
    .directive('searchResultListDir', require('search/directive/search-result-list'))
    .directive('searchFormDir', require('search/directive/search-form'))
    ;
});
