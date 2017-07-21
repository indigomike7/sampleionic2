define([
    'global/helper',
    'text!tpl/global/shorten-html-compile.html'
], function(Helper, template) {
    return function($filter, gettextCatalog) {
        return {
            restrict: 'AE',
            template: template,
            link: function(scope, element, attrs) {

                scope.finalMoreBtnText = scope.moreBtnText || gettextCatalog.getString('More');
                scope.finalLessBtnText = scope.lessBtnText || gettextCatalog.getString('Less');

                scope.finalMoreBtnFn = scope.moreBtnFn || scope.showMore;
                scope.finalLessBtnFn = scope.lessBtnFn || scope.showLess;

                scope.$watch('shortenHtmlCompile', function(newValue, oldValue) {
                    var newCleanValue = Helper.stripTags(newValue);
                    var newParsedValue = Helper.prepareHTMLText(newValue);
                    scope.isOverLimit = scope.limit && (newCleanValue.length > scope.limit);
                    scope.shortHtml = scope.isOverLimit ? $filter('limitTo')(newCleanValue, scope.limit) + '...' : newParsedValue;
                    scope.longHtml = newParsedValue;
                    // Reset
                    scope.showLess();
                });
            },
            controller: function($scope, $ionicScrollDelegate, $timeout) {
                $scope.showMore = function() {
                    $scope.bShowMore = 1;
                    $timeout(function() {
                        $ionicScrollDelegate.resize();
                    }, 200);
                };

                $scope.showLess = function() {
                    $scope.bShowMore = 0;
                    $timeout(function() {
                        $ionicScrollDelegate.resize();
                    }, 200);
                };
            },
            scope: {
                limit: '@',
                showMoreBtn: '@',
                showLessBtn: '@',
                moreBtnText: '@',
                lessBtnText: '@',
                moreBtnFn: '=',
                lessBtnFn: '=',
                shortenHtmlCompile: '='
            }
        };
    };
});