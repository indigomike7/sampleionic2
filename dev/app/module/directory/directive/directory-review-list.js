define([
    'text!tpl/directory/directory-review-list.html'
], function(text) {
    return function() {
        return {
            restrict: 'E',
            replace: true,
            template: text,
            controller: 'DirectoryReviewListCtrl'
        };
    };
});
