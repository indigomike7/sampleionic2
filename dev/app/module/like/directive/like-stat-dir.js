define([
    'text!tpl/like/like-stat-dir.html'
], function(likeStatDirTpl) {

    return function() {

        return {
            restrict: 'E',
            template: likeStatDirTpl,
            controller: 'LikeStatCtrl',
            scope: {
                obj: '='
            }
        };
    };
});