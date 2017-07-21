define([
    'search/controller/search-browse',
    'search/controller/search-result-item',
],function(){
    angular.module('myapp.controllers')
    .controller('SearchBrowseCtrl', require('search/controller/search-browse'))
    .controller('SearchResultItemCtrl', require('search/controller/search-result-item'))
    ;
});
