define([
    'search/controllers',
    'search/directives',
    'text!tpl/search/search-browse.html',
],function(){
     angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider
        .state('app.searchBrowse', {
            module: 'search',
            url: '/search',
            cache: false,
            history: {
                title: gettext('Search'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/search/search-browse.html'),
                    controller: 'SearchBrowseCtrl',
                }
            }
        })
        ;
    });
});
