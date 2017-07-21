/**
 * @ngdoc directive
 * @name ynContent
 * @module ionic
 * @delegate ionic.service:$ionicScrollDelegate
 * @restrict E
 *
 * @description
 * The ynContent directive provides an easy to use content area that can be configured
 * to use Ionic's custom Scroll View, or the built in overflow scrolling of the browser.
 *
 * While we recommend using the custom Scroll features in Ionic in most cases, sometimes
 * (for performance reasons) only the browser's native overflow scrolling will suffice,
 * and so we've made it easy to toggle between the Ionic scroll implementation and
 * overflow scrolling.
 *
 * You can implement pull-to-refresh with the {@link ionic.directive:ionRefresher}
 * directive, and infinite scrolling with the {@link ionic.directive:ionInfiniteScroll}
 * directive.
 *
 * Be aware that this directive gets its own child scope. If you do not understand why this
 * is important, you can read [https://docs.angularjs.org/guide/scope](https://docs.angularjs.org/guide/scope).
 *
 * @param {string=} delegate-handle The handle used to identify this scrollView
 * with {@link ionic.service:$ionicScrollDelegate}.
 * @param {string=} direction Which way to scroll. 'x' or 'y' or 'xy'. Default 'y'.
 * @param {boolean=} locking Whether to lock scrolling in one direction at a time. Useful to set to false when zoomed in or scrolling in two directions. Default true.
 * @param {boolean=} padding Whether to add padding to the content.
 * of the content.  Defaults to true on iOS, false on Android.
 * @param {boolean=} scroll Whether to allow scrolling of content.  Defaults to true.
 * @param {boolean=} overflow-scroll Whether to use overflow-scrolling instead of
 * Ionic scroll.
 * @param {boolean=} scrollbar-x Whether to show the horizontal scrollbar. Default true.
 * @param {boolean=} scrollbar-y Whether to show the vertical scrollbar. Default true.
 * @param {string=} start-y Initial vertical scroll position. Default 0.
 * of the content.  Defaults to true on iOS, false on Android.
 * @param {expression=} on-scroll Expression to evaluate when the content is scrolled.
 * @param {expression=} on-scroll-complete Expression to evaluate when a scroll action completes.
 * @param {boolean=} has-bouncing Whether to allow scrolling to bounce past the edges
 * of the content.  Defaults to true on iOS, false on Android.
 */
define(function() {
    return function($timeout, $controller, $ionicBind) {
        return {
            restrict: 'E',
            require: '^?ionNavView',
            scope: true,
            controller: 'YnContentCtrl',
            priority: 800,
            compile: function(element, attr) {
                var innerElement;
                
                element.addClass('yn-content');
                element.css('min-height', window.innerHeight);
                window.addEventListener('orientationchange', function() {
                    element.css('min-height', window.innerHeight);
                });
                window.addEventListener('resize', function() {
                    element.css('min-height', window.innerHeight);
                });
                
                return {
                    pre: prelink
                };

                function prelink($scope, $element, $attr, navViewCtrl) {
                    var parentScope = $scope.$parent;
                    $scope.$watch(function() {
                        return (parentScope.$hasHeader ? ' has-header' : '') + (parentScope.$hasSubheader ? ' has-subheader' : '') + (parentScope.$hasFooter ? ' has-footer' : '') + (parentScope.$hasSubfooter ? ' has-subfooter' : '') + (parentScope.$hasTabs ? ' has-tabs' : '') + (parentScope.$hasTabsTop ? ' has-tabs-top' : '');
                    }, function(className, oldClassName) {
                        $element.removeClass(oldClassName);
                        $element.addClass(className);
                    });
                    //Only this ynContent should use these variables from parent scopes
                    $scope.$hasHeader = $scope.$hasSubheader = $scope.$hasFooter = $scope.$hasSubfooter = $scope.$hasTabs = $scope.$hasTabsTop = false;
                }
            }
        };
    }
});