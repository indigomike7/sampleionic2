/**
 * @ngdoc directive
 * @name ynRefresher
 * @module ionic
 * @restrict E
 * @parent ionic.directive:ionContent, ionic.directive:ionScroll
 * @description
 * Allows you to add pull-to-refresh to a scrollView.
 *
 * Place it as the first child of your {@link ionic.directive:ionContent} or
 * {@link ionic.directive:ionScroll} element.
 *
 * When refreshing is complete, $broadcast the 'scroll.refreshComplete' event
 * from your controller.
 *
 * @usage
 *
 * ```html
 * <yn-content ng-controller="MyController">
 *   <yn-refresher
 *     pulling-text="Pull to refresh..."
 *     on-refresh="doRefresh()">
 *   </yn-refresher>
 *   <ion-list>
 *     <ion-item ng-repeat="item in items"></ion-item>
 *   </ion-list>
 * </yn-content>
 * ```
 * ```js
 * angular.module('testApp', ['ionic'])
 * .controller('MyController', function($scope, $http) {
 *   $scope.items = [1,2,3];
 *   $scope.doRefresh = function() {
 *     $http.get('/new-items')
 *      .success(function(newItems) {
 *        $scope.items = newItems;
 *      })
 *      .finally(function() {
 *        // Stop the yn-refresher from spinning
 *        $scope.$broadcast('scroll.refreshComplete');
 *      });
 *   };
 * });
 * ```
 *
 * @param {expression=} on-refresh Called when the user pulls down enough and lets go
 * of the refresher.
 * @param {expression=} on-pulling Called when the user starts to pull down
 * on the refresher.
 * @param {string=} pulling-icon The icon to display while the user is pulling down.
 * Default: 'ion-arrow-down-c'.
 * @param {string=} pulling-text The text to display while the user is pulling down.
 * @param {string=} refreshing-icon The icon to display after user lets go of the
 * refresher.
 * @param {string=} refreshing-text The text to display after the user lets go of
 * the refresher.
 * @param {boolean=} disable-pulling-rotation Disables the rotation animation of the pulling
 * icon when it reaches its activated threshold. To be used with a custom `pulling-icon`.
 *
 */
define([
    'text!tpl/global/yn-refresher.html'
], function(template) {
    return function($ionicBind, $timeout) {
        return {
            restrict: 'E',
            replace: true,
            require: '^ynContent',
            template: template,
            compile: function($element, $attrs) {
                // if (angular.isUndefined($attrs.pullingIcon)) {
                //     $attrs.$set('pullingIcon', 'ion-ios7-arrow-down');
                // }
                // if (angular.isUndefined($attrs.refreshingIcon)) {
                //     $attrs.$set('refreshingIcon', 'ion-loading-d');
                // }
                return function($scope, $element, $attrs, YnContentCtrl) {
                    $ionicBind($scope, $attrs, {
                        // pullingIcon: '@',
                        pullingText: '@',
                        // refreshingIcon: '@',
                        refreshingText: '@',
                        // disablePullingRotation: '@',
                        $onRefresh: '&onRefresh',
                        $onPulling: '&onPulling'
                    });
                    YnContentCtrl.setRefresher($scope, $element[0]);
                    $scope.$on('scroll.refreshComplete', function() {
                        $scope.$evalAsync(function() {
                            YnContentCtrl.finishPullToRefresh();
                        });
                    });
                };
            }
        };
    }
});